<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Konfirmasi Checkout (Publik — tidak butuh login)
 *
 * Admin cukup klik tombol dari email, tidak perlu login terlebih dahulu.
 * Token disimpan di tabel `pending_checkout` (bukan di session user).
 *
 * URL: confirm/checkout/{token}/{action}
 *   action: 'yes' = lanjutkan proses
 *   action: 'no'  = batalkan proses
 */
class Confirm extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Tidak ada cek session — endpoint ini memang publik (token-based)
    }

    /**
     * Halaman konfirmasi checkout dari link email admin.
     * Tidak memerlukan login.
     */
    public function checkout($token = null, $action = null)
    {
        if (!$token || !$action) {
            $this->_show_result('error', 'Link konfirmasi tidak valid.');
            return;
        }

        // Ambil pending checkout dari database berdasarkan token
        $pending = $this->db
            ->where('token', $token)
            ->where('used', 0)
            ->get('pending_checkout')
            ->row();

        if (!$pending) {
            $this->_show_result('error', 'Token konfirmasi tidak ditemukan atau sudah pernah digunakan.');
            return;
        }

        // Cek kadaluarsa
        if (strtotime($pending->expires_at) < time()) {
            $this->_show_result('error', 'Link konfirmasi sudah kadaluarsa. Minta pengguna untuk melakukan checkout ulang.');
            return;
        }

        // Tandai token sebagai sudah digunakan (anti double-click)
        $this->db->where('token', $token)->update('pending_checkout', ['used' => 1]);

        if ($action === 'no') {
            $this->_show_result('warning', 'Proses ' . ($pending->type === 'masuk' ? 'Barang Masuk' : 'Barang Keluar') . ' telah dibatalkan oleh admin.');
            return;
        }

        if ($action !== 'yes') {
            $this->_show_result('error', 'Aksi tidak dikenali.');
            return;
        }

        // Jalankan proses sesuai tipe transaksi
        $payload = json_decode($pending->payload, true);
        $id_user = (int) $pending->id_user;

        if ($pending->type === 'masuk') {
            $this->_proses_masuk($pending, $payload, $id_user);
        } else {
            $this->_proses_keluar($pending, $payload, $id_user);
        }
    }

    // ---------------------------------------------------------------
    // PROSES BARANG MASUK
    // ---------------------------------------------------------------

    private function _proses_masuk($pending, $payload, $id_user)
    {
        $id_supplier = (int) $payload['id_supplier'];

        // Cek keranjang masih ada
        $cart = $this->db->where('id_user', $id_user)->get('keranjang_masuk')->result_array();
        if (empty($cart)) {
            $this->_show_result('warning', 'Keranjang sudah kosong! Mungkin sudah dikonfirmasi sebelumnya.');
            return;
        }

        // Insert ke barang_masuk
        $this->db->insert('barang_masuk', [
            'id_user'     => $id_user,
            'id_supplier' => $id_supplier,
        ]);
        $id_barang_masuk = $this->db->insert_id();

        if (!$id_barang_masuk) {
            $this->_show_result('error', 'Terjadi kesalahan saat memproses transaksi. Silakan hubungi administrator.');
            return;
        }

        // Pindahkan keranjang ke barang_masuk_detail
        foreach ($cart as $row) {
            $row['id_barang_masuk'] = $id_barang_masuk;
            unset($row['id'], $row['id_user']);
            $this->db->insert('barang_masuk_detail', $row);
        }

        // Hapus keranjang
        $this->db->delete('keranjang_masuk', ['id_user' => $id_user]);

        // Ambil data untuk email laporan
        $barang_masuk = $this->db
            ->select('barang_masuk.id AS id_barang_masuk, barang_masuk.waktu, user.nama, user.email, supplier.nama AS nama_supplier, supplier.telefon AS telefon_supplier, supplier.alamat AS alamat_supplier')
            ->join('user', 'barang_masuk.id_user = user.id', 'left')
            ->join('supplier', 'barang_masuk.id_supplier = supplier.id', 'left')
            ->where('barang_masuk.id', $id_barang_masuk)
            ->get('barang_masuk')
            ->row();

        $list_barang = $this->db
            ->select('barang_masuk_detail.qty, barang_masuk_detail.subtotal, barang.id_satuan, barang.nama, barang.harga')
            ->join('barang', 'barang_masuk_detail.id_barang = barang.id', 'left')
            ->where('barang_masuk_detail.id_barang_masuk', $id_barang_masuk)
            ->get('barang_masuk_detail')
            ->result();

        // Kirim email laporan ke admin
        $this->_send_email_masuk($barang_masuk, $list_barang);

        $this->_show_result('success',
            'Transaksi Barang Masuk #' . $id_barang_masuk . ' berhasil dikonfirmasi dan diproses.',
            $barang_masuk, $list_barang, 'masuk'
        );
    }

    // ---------------------------------------------------------------
    // PROSES BARANG KELUAR
    // ---------------------------------------------------------------

    private function _proses_keluar($pending, $payload, $id_user)
    {
        $id_penerima    = isset($payload['id_penerima'])    ? $payload['id_penerima']    : null;
        $no_po          = isset($payload['no_po'])          ? $payload['no_po']          : null;
        $keterangan     = isset($payload['keterangan'])     ? $payload['keterangan']     : null;
        $serial_numbers = isset($payload['serial_numbers']) ? $payload['serial_numbers'] : [];

        // Cek keranjang masih ada
        $cart = $this->db->where('id_user', $id_user)->get('keranjang_keluar')->result_array();
        if (empty($cart)) {
            $this->_show_result('warning', 'Keranjang sudah kosong! Mungkin sudah dikonfirmasi sebelumnya.');
            return;
        }

        // Validasi stok (pastikan tidak melebihi stok tersedia)
        foreach ($cart as $row) {
            $barang = $this->db->where('id', $row['id_barang'])->get('barang')->row();
            if (!$barang) continue;

            $masuk  = (int) $this->db->select_sum('qty')->where('id_barang', $row['id_barang'])->get('barang_masuk_detail')->row()->qty;
            $keluar = (int) $this->db->select_sum('qty')->where('id_barang', $row['id_barang'])->get('barang_keluar_detail')->row()->qty;
            $stok   = $masuk - $keluar;

            if ($row['qty'] > $stok) {
                $this->_show_result('error',
                    'Stok barang <strong>' . htmlspecialchars($barang->nama) . '</strong> tidak mencukupi. '
                    . 'Stok tersedia: ' . $stok . ', diminta: ' . $row['qty'] . '. '
                    . 'Silakan minta pengguna memperbarui keranjang.'
                );
                return;
            }
        }

        // Insert ke barang_keluar
        $this->db->insert('barang_keluar', [
            'id_user'     => $id_user,
            'id_penerima' => $id_penerima ?: null,
            'no_po'       => $no_po       ?: null,
            'keterangan'  => $keterangan  ?: null,
        ]);
        $id_barang_keluar = $this->db->insert_id();

        if (!$id_barang_keluar) {
            $this->_show_result('error', 'Terjadi kesalahan saat memproses transaksi. Silakan hubungi administrator.');
            return;
        }

        // Pindahkan keranjang ke barang_keluar_detail
        foreach ($cart as $row) {
            $row['id_barang_keluar'] = $id_barang_keluar;
            $id_barang_row = $row['id_barang'];
            $row['serial_number'] = (!empty($serial_numbers) && !empty($serial_numbers[$id_barang_row]))
                ? $serial_numbers[$id_barang_row]
                : null;
            unset($row['id'], $row['id_user']);
            $this->db->insert('barang_keluar_detail', $row);
        }

        // Hapus keranjang
        $this->db->delete('keranjang_keluar', ['id_user' => $id_user]);

        // Ambil data untuk email laporan
        $barang_keluar = $this->db
            ->select('user.id AS id_user, user.nama, user.email, barang_keluar.id AS id_barang_keluar, barang_keluar.waktu, barang_keluar.no_po, barang_keluar.keterangan, barang_keluar.id_penerima')
            ->join('user', 'barang_keluar.id_user = user.id', 'left')
            ->where('barang_keluar.id', $id_barang_keluar)
            ->get('barang_keluar')
            ->row();

        $list_barang = $this->db
            ->select('barang_keluar_detail.qty, barang_keluar_detail.serial_number, barang.id_satuan, barang.nama, barang.harga')
            ->join('barang', 'barang_keluar_detail.id_barang = barang.id', 'left')
            ->where('barang_keluar_detail.id_barang_keluar', $id_barang_keluar)
            ->get('barang_keluar_detail')
            ->result();

        // Kirim email laporan ke admin
        $this->_send_email_keluar($barang_keluar, $list_barang);

        $this->_show_result('success',
            'Transaksi Barang Keluar #' . $id_barang_keluar . ' berhasil dikonfirmasi dan diproses.',
            $barang_keluar, $list_barang, 'keluar'
        );
    }

    // ---------------------------------------------------------------
    // TAMPILAN HASIL KONFIRMASI (halaman HTML mandiri)
    // ---------------------------------------------------------------

    private function _show_result($type, $message, $transaksi = null, $list_barang = null, $jenis = null)
    {
        $colors = [
            'success' => ['bg' => '#1a7a3c', 'light' => '#e8f5eb', 'border' => '#d1e8d4', 'icon' => '✅'],
            'warning' => ['bg' => '#b45309', 'light' => '#fef3c7', 'border' => '#fde68a', 'icon' => '⚠️'],
            'error'   => ['bg' => '#b91c1c', 'light' => '#fee2e2', 'border' => '#fecaca', 'icon' => '❌'],
        ];
        $c = $colors[$type] ?? $colors['error'];

        $rows_html = '';
        $total     = 0;
        if ($list_barang) {
            foreach ($list_barang as $item) {
                $harga    = isset($item->harga) ? $item->harga : 0;
                $qty      = isset($item->qty)   ? $item->qty   : 0;
                $subtotal = ($jenis === 'masuk') ? round($qty * $harga * 1.11) : ($qty * $harga);
                $total   += $subtotal;
                $ppn_note = ($jenis === 'masuk') ? '<br><small style="color:#888;">(incl. PPN 11%)</small>' : '';
                $rows_html .= '
                <tr>
                    <td style="padding:10px 14px;border-bottom:1px solid #e8f0e9;">' . htmlspecialchars($item->nama) . '</td>
                    <td style="padding:10px 14px;border-bottom:1px solid #e8f0e9;text-align:center;">' . $qty . ' ' . htmlspecialchars($item->id_satuan) . '</td>
                    <td style="padding:10px 14px;border-bottom:1px solid #e8f0e9;text-align:right;">Rp ' . number_format($harga, 0, ',', '.') . '</td>
                    <td style="padding:10px 14px;border-bottom:1px solid #e8f0e9;text-align:right;">Rp ' . number_format($subtotal, 0, ',', '.') . $ppn_note . '</td>
                </tr>';
            }
        }

        $table_html = '';
        if ($rows_html) {
            $table_html = '
            <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid ' . $c['border'] . ';border-radius:6px;overflow:hidden;margin-top:20px;">
                <tr style="background:' . $c['light'] . ';">
                    <th style="padding:10px 14px;font-size:12px;text-align:left;">Nama Barang</th>
                    <th style="padding:10px 14px;font-size:12px;text-align:center;">Qty</th>
                    <th style="padding:10px 14px;font-size:12px;text-align:right;">Harga Satuan</th>
                    <th style="padding:10px 14px;font-size:12px;text-align:right;">Subtotal</th>
                </tr>
                ' . $rows_html . '
                <tr style="background:' . $c['light'] . ';">
                    <td colspan="3" style="padding:12px 14px;font-size:13px;font-weight:700;text-align:right;">TOTAL</td>
                    <td style="padding:12px 14px;font-size:14px;font-weight:700;text-align:right;">Rp ' . number_format($total, 0, ',', '.') . '</td>
                </tr>
            </table>';
        }

        echo '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Konfirmasi Checkout — Easy WMS</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: "Segoe UI", Arial, sans-serif; background: #f3f4f6; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .card { background: #fff; border-radius: 10px; box-shadow: 0 4px 16px rgba(0,0,0,0.10); max-width: 680px; width: 100%; overflow: hidden; }
        .header { background: ' . $c['bg'] . '; padding: 28px 32px; }
        .header .badge { font-size: 11px; color: rgba(255,255,255,0.7); letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 6px; }
        .header .title { font-size: 22px; font-weight: 700; color: #fff; }
        .body { padding: 28px 32px; }
        .alert { background: ' . $c['light'] . '; border: 1px solid ' . $c['border'] . '; border-radius: 6px; padding: 16px 20px; font-size: 14px; color: #374151; line-height: 1.6; }
        .footer { background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 16px 32px; font-size: 12px; color: #9ca3af; }
        a.btn { display: inline-block; background: ' . $c['bg'] . '; color: #fff; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-size: 14px; font-weight: 600; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <div class="badge">Easy WMS — Hasil Konfirmasi</div>
            <div class="title">' . $c['icon'] . ' ' . ucfirst($type === 'success' ? 'Berhasil' : ($type === 'warning' ? 'Dibatalkan' : 'Gagal')) . '</div>
        </div>
        <div class="body">
            <div class="alert">' . $message . '</div>
            ' . $table_html . '
            <a class="btn" href="' . base_url('login') . '">Masuk ke Dashboard Easy WMS</a>
        </div>
        <div class="footer">
            Halaman ini dihasilkan otomatis oleh <strong>Easy WMS</strong>. Anda bisa menutup tab ini.
        </div>
    </div>
</body>
</html>';
    }

    // ---------------------------------------------------------------
    // EMAIL LAPORAN BARANG MASUK (setelah dikonfirmasi admin)
    // ---------------------------------------------------------------

    private function _send_email_masuk($barang_masuk, $list_barang)
    {
        $this->load->library('email');
        $this->load->config('email');

        $admin_email    = $this->config->item('admin_email');
        $total          = 0;
        $rows_html      = '';

        foreach ($list_barang as $item) {
            $subtotal   = round($item->qty * $item->harga * 1.11);
            $total     += $subtotal;
            $rows_html .= '
            <tr>
                <td style="padding:10px 14px;border-bottom:1px solid #e8f0e9;">' . htmlspecialchars($item->nama) . '</td>
                <td style="padding:10px 14px;border-bottom:1px solid #e8f0e9;text-align:center;">' . $item->qty . ' ' . htmlspecialchars($item->id_satuan) . '</td>
                <td style="padding:10px 14px;border-bottom:1px solid #e8f0e9;text-align:right;">Rp ' . number_format($item->harga, 0, ',', '.') . '</td>
                <td style="padding:10px 14px;border-bottom:1px solid #e8f0e9;text-align:right;">Rp ' . number_format($subtotal, 0, ',', '.') . '<br><small style="color:#888;">(incl. PPN 11%)</small></td>
            </tr>';
        }

        $nama_user     = isset($barang_masuk->nama)           ? htmlspecialchars($barang_masuk->nama)           : '-';
        $email_user    = isset($barang_masuk->email)          ? htmlspecialchars($barang_masuk->email)          : '-';
        $nama_supplier = isset($barang_masuk->nama_supplier)  ? htmlspecialchars($barang_masuk->nama_supplier)  : '-';
        $id_transaksi  = isset($barang_masuk->id_barang_masuk) ? $barang_masuk->id_barang_masuk : '-';
        $waktu         = isset($barang_masuk->waktu) ? $barang_masuk->waktu : date('Y-m-d H:i:s');
        $waktu_fmt     = date('d F Y, H:i:s', strtotime($waktu));

        $subject = '[BARANG MASUK] Transaksi #' . $id_transaksi . ' Berhasil Diproses — Easy WMS';

        $message = '
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"/></head>
<body style="margin:0;padding:0;background:#f0f4f0;font-family:\'Segoe UI\',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f4f0;padding:32px 0;">
  <tr><td align="center">
    <table width="620" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
      <tr>
        <td style="background:#1a7a3c;padding:28px 32px;">
          <div style="font-size:11px;color:#a8ddb5;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:4px;">Easy WMS — Laporan Transaksi</div>
          <div style="font-size:22px;font-weight:700;color:#ffffff;">📦 Barang Masuk Diproses</div>
        </td>
      </tr>
      <tr>
        <td style="padding:24px 32px 0;">
          <p style="font-size:14px;color:#374151;line-height:1.6;margin:0;">
            Kepada Yth. Admin,<br><br>
            Transaksi <strong>Barang Masuk #' . $id_transaksi . '</strong> telah <strong style="color:#1a7a3c;">dikonfirmasi dan berhasil diproses</strong> pada ' . $waktu_fmt . '.
          </p>
        </td>
      </tr>
      <tr>
        <td style="padding:20px 32px;">
          <table width="100%" cellpadding="0" cellspacing="0" style="background:#f6faf7;border-radius:6px;border:1px solid #d1e8d4;">
            <tr><td colspan="2" style="padding:12px 16px;background:#e8f5eb;border-bottom:1px solid #d1e8d4;"><strong style="font-size:12px;color:#1a7a3c;text-transform:uppercase;">Informasi Transaksi</strong></td></tr>
            <tr>
              <td style="padding:10px 16px;color:#6b7280;font-size:13px;width:40%;border-bottom:1px solid #e8f0e9;">Diajukan Oleh</td>
              <td style="padding:10px 16px;font-size:13px;font-weight:600;border-bottom:1px solid #e8f0e9;">' . $nama_user . ' (' . $email_user . ')</td>
            </tr>
            <tr>
              <td style="padding:10px 16px;color:#6b7280;font-size:13px;">Supplier</td>
              <td style="padding:10px 16px;font-size:13px;font-weight:600;color:#1a7a3c;">' . $nama_supplier . '</td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td style="padding:0 32px 20px;">
          <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #d1e8d4;border-radius:6px;overflow:hidden;">
            <tr style="background:#e8f5eb;">
              <th style="padding:10px 14px;font-size:12px;color:#1a7a3c;text-align:left;">Nama Barang</th>
              <th style="padding:10px 14px;font-size:12px;color:#1a7a3c;text-align:center;">Qty</th>
              <th style="padding:10px 14px;font-size:12px;color:#1a7a3c;text-align:right;">Harga Satuan</th>
              <th style="padding:10px 14px;font-size:12px;color:#1a7a3c;text-align:right;">Subtotal</th>
            </tr>
            ' . $rows_html . '
            <tr style="background:#f6faf7;">
              <td colspan="3" style="padding:12px 14px;font-size:13px;font-weight:700;text-align:right;">TOTAL</td>
              <td style="padding:12px 14px;font-size:14px;font-weight:700;color:#1a7a3c;text-align:right;">Rp ' . number_format($total, 0, ',', '.') . '</td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td style="background:#f6faf7;border-top:1px solid #e8f0e9;padding:20px 32px;">
          <p style="font-size:12px;color:#9ca3af;margin:0;">Email ini dikirim otomatis oleh <strong>Easy WMS</strong>.</p>
        </td>
      </tr>
    </table>
  </td></tr>
</table>
</body>
</html>';

        $this->email->initialize(['mailtype' => 'html', 'charset' => 'utf-8']);
        $this->email->clear();
        $this->email->from($this->config->item('smtp_user'), 'Easy WMS Notification');
        $this->email->to($admin_email);
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->send();
    }

    // ---------------------------------------------------------------
    // EMAIL LAPORAN BARANG KELUAR (setelah dikonfirmasi admin)
    // ---------------------------------------------------------------

    private function _send_email_keluar($barang_keluar, $list_barang)
    {
        $this->load->library('email');
        $this->load->config('email');

        $admin_email    = $this->config->item('admin_email');
        $total          = 0;
        $rows_html      = '';

        foreach ($list_barang as $item) {
            $subtotal   = $item->qty * $item->harga;
            $total     += $subtotal;
            $rows_html .= '
            <tr>
                <td style="padding:10px 14px;border-bottom:1px solid #fce8e8;">' . htmlspecialchars($item->nama) . '</td>
                <td style="padding:10px 14px;border-bottom:1px solid #fce8e8;text-align:center;">' . $item->qty . ' ' . htmlspecialchars($item->id_satuan) . '</td>
                <td style="padding:10px 14px;border-bottom:1px solid #fce8e8;text-align:right;">Rp ' . number_format($item->harga, 0, ',', '.') . '</td>
                <td style="padding:10px 14px;border-bottom:1px solid #fce8e8;text-align:right;">Rp ' . number_format($subtotal, 0, ',', '.') . '</td>
            </tr>';
        }

        $nama_user    = isset($barang_keluar->nama)            ? htmlspecialchars($barang_keluar->nama)            : '-';
        $email_user   = isset($barang_keluar->email)           ? htmlspecialchars($barang_keluar->email)           : '-';
        $id_transaksi = isset($barang_keluar->id_barang_keluar) ? $barang_keluar->id_barang_keluar : '-';
        $waktu        = isset($barang_keluar->waktu) ? $barang_keluar->waktu : date('Y-m-d H:i:s');
        $waktu_fmt    = date('d F Y, H:i:s', strtotime($waktu));

        $subject = '[BARANG KELUAR] Transaksi #' . $id_transaksi . ' Berhasil Diproses — Easy WMS';

        $message = '
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"/></head>
<body style="margin:0;padding:0;background:#f4f0f0;font-family:\'Segoe UI\',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f0f0;padding:32px 0;">
  <tr><td align="center">
    <table width="620" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
      <tr>
        <td style="background:#b91c1c;padding:28px 32px;">
          <div style="font-size:11px;color:#fca5a5;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:4px;">Easy WMS — Laporan Transaksi</div>
          <div style="font-size:22px;font-weight:700;color:#ffffff;">📤 Barang Keluar Diproses</div>
        </td>
      </tr>
      <tr>
        <td style="padding:24px 32px 0;">
          <p style="font-size:14px;color:#374151;line-height:1.6;margin:0;">
            Kepada Yth. Admin,<br><br>
            Transaksi <strong>Barang Keluar #' . $id_transaksi . '</strong> telah <strong style="color:#b91c1c;">dikonfirmasi dan berhasil diproses</strong> pada ' . $waktu_fmt . '.
          </p>
        </td>
      </tr>
      <tr>
        <td style="padding:20px 32px;">
          <table width="100%" cellpadding="0" cellspacing="0" style="background:#fdf6f6;border-radius:6px;border:1px solid #fecaca;">
            <tr><td colspan="2" style="padding:12px 16px;background:#fee2e2;border-bottom:1px solid #fecaca;"><strong style="font-size:12px;color:#b91c1c;text-transform:uppercase;">Informasi Transaksi</strong></td></tr>
            <tr>
              <td style="padding:10px 16px;color:#6b7280;font-size:13px;width:40%;border-bottom:1px solid #fce8e8;">Diajukan Oleh</td>
              <td style="padding:10px 16px;font-size:13px;font-weight:600;border-bottom:1px solid #fce8e8;">' . $nama_user . ' (' . $email_user . ')</td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td style="padding:0 32px 20px;">
          <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #fecaca;border-radius:6px;overflow:hidden;">
            <tr style="background:#fee2e2;">
              <th style="padding:10px 14px;font-size:12px;color:#b91c1c;text-align:left;">Nama Barang</th>
              <th style="padding:10px 14px;font-size:12px;color:#b91c1c;text-align:center;">Qty</th>
              <th style="padding:10px 14px;font-size:12px;color:#b91c1c;text-align:right;">Harga Satuan</th>
              <th style="padding:10px 14px;font-size:12px;color:#b91c1c;text-align:right;">Subtotal</th>
            </tr>
            ' . $rows_html . '
            <tr style="background:#fdf6f6;">
              <td colspan="3" style="padding:12px 14px;font-size:13px;font-weight:700;text-align:right;">TOTAL</td>
              <td style="padding:12px 14px;font-size:14px;font-weight:700;color:#b91c1c;text-align:right;">Rp ' . number_format($total, 0, ',', '.') . '</td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td style="background:#fdf6f6;border-top:1px solid #fce8e8;padding:20px 32px;">
          <p style="font-size:12px;color:#9ca3af;margin:0;">Email ini dikirim otomatis oleh <strong>Easy WMS</strong>.</p>
        </td>
      </tr>
    </table>
  </td></tr>
</table>
</body>
</html>';

        $this->email->initialize(['mailtype' => 'html', 'charset' => 'utf-8']);
        $this->email->clear();
        $this->email->from($this->config->item('smtp_user'), 'Easy WMS Notification');
        $this->email->to($admin_email);
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->send();
    }
}

/* End of file Confirm.php */
