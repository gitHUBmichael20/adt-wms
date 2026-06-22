<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Keranjang Keluar
 */
class Cartout extends MY_Controller 
{
    private $id_user;

    public function __construct()
    {
        parent::__construct();
        
        $is_login       = $this->session->userdata('is_login');
        $this->id_user  = $this->session->userdata('id_user');

        if (!$is_login) {
            $this->session->set_flashdata('warning', 'Anda belum login');
            redirect(base_url('login'));
            return;
        }
    }

    public function index()
    {
        $this->session->unset_userdata('keyword');

        $data['title']              = 'Easy WMS - Keranjang Keluar';
        $data['breadcrumb_title']   = "Keranjang Keluar";
        $data['breadcrumb_path']    = 'Barang Keluar / Keranjang Keluar';
        $data['page']               = 'pages/cartout/index';
        $data['content']            = $this->cartout->select([
                'barang.id AS id_barang', 'barang.nama', 'barang.harga',
                'barang.id_satuan', 'keranjang_keluar.id AS id', 
                'keranjang_keluar.qty AS qty_barang_keluar'
            ])
            ->where('keranjang_keluar.id_user', $this->id_user)
            ->join('barang')
            ->get();

        // Load list penerima untuk dropdown
        $this->load->model('Recipients_model', 'recipients_list');
        $data['recipients'] = $this->recipients_list->get();

        $this->view($data);
    }

    /**
     * Menampung barang yang akan dikurangi kuantitasnya
     */
    public function add()
    {
        if (!$_POST || $this->input->post('qty_keluar') < 1) {
            $this->session->set_flashdata('error', 'Kuantitas tidak boleh kosong');
            redirect(base_url('items/out'));
            return;
        }
        
        $input = (object) $this->input->post(null, true);

        $this->cartout->table = 'keranjang_keluar';
        $cart = $this->cartout->where('id_barang', $input->id_barang)
                             ->where('id_user', $this->id_user)
                             ->first();

        if ($cart) {
            $data = ['qty' => $cart->qty + $input->qty_keluar];

            if ($this->cartout->where('id', $cart->id)
                              ->where('id_user', $this->id_user)
                              ->update($data)
            ) {
                $this->session->set_flashdata('success', 'Kuantitas berhasil diubah');
            } else {
                $this->session->set_flashdata('error', 'Oops! Terjadi kesalahan');
            }

            redirect(base_url('cartout'));
            return;
        }

        $data = [
            'id_user'   => $this->id_user,
            'id_barang' => $input->id_barang,
            'qty'       => $input->qty_keluar
        ];

        if ($this->cartout->create($data)) {
            $this->session->set_flashdata('success', 'Barang berhasil dimasukan ke keranjang');
        } else {
            $this->session->set_flashdata('error', 'Oops! Terjadi kesalahan');
        }

        redirect(base_url('cartout'));
    }

    /**
     * Update kuantitas di keranjang belanja
     */
    public function update()
    {
        if (!$_POST || $this->input->post('qty_barang_keluar') < 1) {
            $this->session->set_flashdata('error', 'Kuantitas tidak boleh kosong');
            redirect(base_url('cartout'));
        }

        $id = $this->input->post('id');
        $id_barang = $this->input->post('id_barang');

        $data['content'] = $this->cartout->where('id_barang', $id_barang)
                                         ->where('id', $id)
                                         ->first();

        if (!$data['content']) {
            $this->session->set_flashdata('warning', 'Data tidak ditemukan');
            redirect(base_url('cartout'));
        }

        $data['input'] = (object) $this->input->post(null, true);

        $cart = ['qty' => $data['input']->qty_barang_keluar];

        if ($this->cartout->where('id', $id)
                         ->where('id_barang', $id_barang)
                         ->where('id_user', $this->id_user)
                         ->update($cart)
        ) {
            $this->session->set_flashdata('success', 'Kuantitas berhasil diubah');
        } else {
            $this->session->set_flashdata('error', 'Oops! Terjadi kesalahan');
        }

        redirect(base_url('cartout'));
    }

    /**
     * Delete suatu cart di halaman cart
     */
    public function delete()
    {
        if (!$_POST) {
            $this->session->set_flashdata('error', 'Akses pengeluaran barang dari keranjang ditolak!');
            redirect(base_url('home'));
        }

        $id = $this->input->post('id');

        if (!$this->cartout->where('id', $id)->first()) {
            $this->session->set_flashdata('warning', 'Maaf data tidak ditemukan');
            redirect(base_url('cartout'));
        }

        if ($this->cartout->where('id', $id)->delete()) {
            $this->session->set_flashdata('success', '1 Barang berhasil dikeluarkan dari keranjang');
        } else {
            $this->session->set_flashdata('error', 'Oops, terjadi suatu kesalahan');
        }

        redirect(base_url('cartout'));
    }

    /**
     * Menghapus seluruh isi keranjang
     */
    public function drop()
    {
        if (!$_POST) {
            $this->session->set_flashdata('error', 'Aksi ditolak');
            redirect(base_url('cartout'));
        }

        if ($this->cartout->where('id_user', $this->id_user)->count() < 1) {
            $this->session->set_flashdata('warning', 'Tidak ada barang di dalam keranjang');
            redirect(base_url('cartout'));
        }

        $this->cartout->where('id_user', $this->id_user)->delete();

        if ($this->cartout->count() < 1) { 
            $this->cartout->resetIndex();
        }

        $this->session->set_flashdata('success', 'Keranjang keluar anda telah dibersihkan');

        redirect(base_url('cartout'));
    }

    /**
     * STEP 1: Kirim email konfirmasi sebelum checkout barang keluar.
     * Dipanggil saat user klik tombol "Checkout".
     * Data checkout disimpan sementara di session, lalu email konfirmasi dikirim.
     */
    public function request_checkout()
    {
        if (!isset($this->id_user)) {
            $this->session->set_flashdata('error', 'Akses checkout ditolak!');
            redirect(base_url('home'));
        }

        // Cek keranjang tidak kosong
        $outputCartCount = $this->cartout->where('id_user', $this->id_user)->count();
        if (!$outputCartCount) {
            $this->session->set_flashdata('warning', 'Tidak ada barang yang akan dikeluarkan!');
            redirect(base_url('cartout'));
            return;
        }

        if (!$this->cartout->validateStock()) {
            return $this->index();
        }

        // Ambil data form dari POST
        $id_penerima    = $this->input->post('id_penerima');
        $no_po          = $this->input->post('no_po');
        $keterangan     = $this->input->post('keterangan');
        $serial_numbers = $this->input->post('serial_numbers');

        // Generate token unik (berlaku 30 menit)
        $token   = bin2hex(random_bytes(24));
        $expires = time() + 1800;

        // Ambil isi keranjang untuk ditampilkan di email
        $this->cartout->table = 'keranjang_keluar';
        $cart_items = $this->cartout->select([
                'barang.id AS id_barang', 'barang.nama', 'barang.harga',
                'barang.id_satuan', 'keranjang_keluar.qty'
            ])
            ->where('keranjang_keluar.id_user', $this->id_user)
            ->join('barang')
            ->get();

        // Simpan pending checkout di session
        $pending = [
            'token'          => $token,
            'expires'        => $expires,
            'id_penerima'    => $id_penerima,
            'no_po'          => $no_po,
            'keterangan'     => $keterangan,
            'serial_numbers' => $serial_numbers,
            'id_user'        => $this->id_user,
            'type'           => 'keluar',
        ];
        $this->session->set_userdata('pending_checkout_out', $pending);

        // Ambil data user
        $user = $this->db->where('id', $this->id_user)->get('user')->row();

        // Ambil data penerima jika ada
        $penerima = null;
        if ($id_penerima) {
            $penerima = $this->db->where('id', $id_penerima)->get('penerima')->row();
        }

        // Kirim email konfirmasi
        $sent = $this->_send_email_konfirmasi_keluar($token, $user, $penerima, $cart_items, $no_po, $keterangan);

        if ($sent) {
            $this->session->set_flashdata('info', 
                'Email konfirmasi telah dikirim ke <strong>' . htmlspecialchars($user->email) . '</strong>. '
                . 'Silakan cek email Anda dan klik tombol konfirmasi untuk melanjutkan proses barang keluar. '
                . 'Link berlaku selama 30 menit.'
            );
        } else {
            $this->session->set_flashdata('warning', 
                'Gagal mengirim email konfirmasi. Silakan coba lagi.'
            );
        }

        redirect(base_url('cartout'));
    }

    /**
     * STEP 2: Konfirmasi checkout dari link email (barang keluar).
     * URL: cartout/confirm/{token}/{action}
     * action: 'yes' = lanjutkan, 'no' = batalkan
     */
    public function confirm($token = null, $action = null)
    {
        if (!$token || !$action) {
            $this->session->set_flashdata('error', 'Link konfirmasi tidak valid.');
            redirect(base_url('cartout'));
            return;
        }

        $pending = $this->session->userdata('pending_checkout_out');

        if (!$pending || $pending['token'] !== $token) {
            $this->session->set_flashdata('error', 'Token konfirmasi tidak ditemukan atau sudah digunakan.');
            redirect(base_url('cartout'));
            return;
        }

        if (time() > $pending['expires']) {
            $this->session->unset_userdata('pending_checkout_out');
            $this->session->set_flashdata('error', 'Link konfirmasi sudah kadaluarsa (30 menit). Silakan checkout ulang.');
            redirect(base_url('cartout'));
            return;
        }

        if ($action === 'no') {
            $this->session->unset_userdata('pending_checkout_out');
            $this->session->set_flashdata('warning', 'Proses barang keluar dibatalkan.');
            redirect(base_url('cartout'));
            return;
        }

        if ($action !== 'yes') {
            $this->session->set_flashdata('error', 'Aksi tidak dikenali.');
            redirect(base_url('cartout'));
            return;
        }

        // Pastikan session user cocok
        if ($pending['id_user'] != $this->id_user) {
            $this->session->set_flashdata('error', 'Konfirmasi tidak valid untuk akun ini.');
            redirect(base_url('cartout'));
            return;
        }

        // Hapus pending agar tidak bisa digunakan dua kali
        $this->session->unset_userdata('pending_checkout_out');

        // Cek ulang keranjang
        $outputCartCount = $this->cartout->where('id_user', $this->id_user)->count();
        if (!$outputCartCount) {
            $this->session->set_flashdata('warning', 'Keranjang sudah kosong!');
            redirect(base_url('cartout'));
            return;
        }

        if (!$this->cartout->validateStock()) {
            return $this->index();
        }

        $id_penerima    = $pending['id_penerima'];
        $no_po          = $pending['no_po'];
        $keterangan     = $pending['keterangan'];
        $serial_numbers = $pending['serial_numbers'];

        $data['id_user']      = $this->id_user;
        $data['id_penerima']  = $id_penerima ? $id_penerima : null;
        $data['no_po']        = $no_po ? $no_po : null;
        $data['keterangan']   = $keterangan ? $keterangan : null;
        $this->cartout->table = 'barang_keluar';

        if ($id_barang_keluar = $this->cartout->create($data)) {
            $cart = $this->db->where('id_user', $this->id_user)
                             ->get('keranjang_keluar')
                             ->result_array();

            foreach ($cart as $row) {
                $row['id_barang_keluar'] = $id_barang_keluar;
                $id_barang_row = $row['id_barang'];
                $row['serial_number'] = (!empty($serial_numbers) && !empty($serial_numbers[$id_barang_row]))
                    ? $serial_numbers[$id_barang_row]
                    : null;
                unset($row['id'], $row['id_user']);
                $this->db->insert('barang_keluar_detail', $row);
            }

            $this->db->delete('keranjang_keluar', ['id_user' => $this->id_user]);

            $this->session->set_flashdata('success', 'Pengeluaran barang berhasil dikonfirmasi dan diproses!');

            // Ambil data untuk email laporan
            $this->cartout->table = 'barang_keluar';
            $barang_keluar = $this->cartout->select([
                    'user.id AS id_user', 'user.nama', 'user.email',
                    'barang_keluar.id AS id_barang_keluar', 'barang_keluar.waktu',
                    'barang_keluar.no_po', 'barang_keluar.keterangan',
                    'barang_keluar.id_penerima'
                ])
                ->join('user')
                ->where('barang_keluar.id', $id_barang_keluar)
                ->where('barang_keluar.id_user', $this->id_user)
                ->first();

            $this->cartout->table = 'barang_keluar_detail';
            $list_barang = $this->cartout->select([
                    'barang_keluar_detail.qty',
                    'barang_keluar_detail.serial_number',
                    'barang.id_satuan', 'barang.nama', 'barang.harga',
                ])
                ->join('barang')
                ->where('barang_keluar_detail.id_barang_keluar', $id_barang_keluar)
                ->get();

            // Kirim email laporan
            $this->_send_email_keluar($barang_keluar, $list_barang);

            redirect(base_url('outputs/detail/' . $id_barang_keluar));
            return;

        } else {
            $this->session->set_flashdata('error', 'Oops! Terjadi kesalahan saat memproses checkout.');
            redirect(base_url('cartout'));
        }
    }

    /**
     * Kirim email KONFIRMASI sebelum checkout barang keluar.
     */
    private function _send_email_konfirmasi_keluar($token, $user, $penerima, $cart_items, $no_po, $keterangan)
    {
        $total_keseluruhan = 0;
        $rows_html         = '';

        foreach ($cart_items as $item) {
            $subtotal            = $item->qty * $item->harga;
            $total_keseluruhan  += $subtotal;
            $rows_html          .= '
                <tr>
                    <td style="padding:10px 14px; border-bottom:1px solid #fce8e8;">'
                        . htmlspecialchars($item->nama) . '</td>
                    <td style="padding:10px 14px; border-bottom:1px solid #fce8e8; text-align:center;">'
                        . $item->qty . ' ' . htmlspecialchars($item->id_satuan) . '</td>
                    <td style="padding:10px 14px; border-bottom:1px solid #fce8e8; text-align:right;">Rp '
                        . number_format($item->harga, 0, ',', '.') . '</td>
                    <td style="padding:10px 14px; border-bottom:1px solid #fce8e8; text-align:right;">Rp '
                        . number_format($subtotal, 0, ',', '.') . '</td>
                </tr>';
        }

        $nama_user    = htmlspecialchars($user->nama);
        $nama_penerima = $penerima ? htmlspecialchars($penerima->nama . ' — ' . $penerima->divisi) : '<em>Tidak ditentukan</em>';
        $waktu_fmt    = date('d F Y, H:i:s');
        $expire_fmt   = date('d F Y, H:i:s', time() + 1800);
        $no_po_html   = $no_po ? htmlspecialchars($no_po) : '<em>-</em>';
        $ket_html     = $keterangan ? htmlspecialchars($keterangan) : '<em>-</em>';

        $url_yes = base_url('cartout/confirm/' . $token . '/yes');
        $url_no  = base_url('cartout/confirm/' . $token . '/no');

        $subject = '[KONFIRMASI] Proses Barang Keluar — Easy WMS';

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
          <div style="font-size:11px;color:#fca5a5;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:4px;">Easy WMS — Konfirmasi Diperlukan</div>
          <div style="font-size:22px;font-weight:700;color:#ffffff;">📤 Konfirmasi Barang Keluar</div>
        </td>
      </tr>
      <tr>
        <td style="padding:24px 32px 8px;">
          <p style="font-size:14px;color:#374151;line-height:1.7;margin:0;">
            Kepada Yth. <strong>' . $nama_user . '</strong>,<br><br>
            Anda telah mengajukan proses <strong>Barang Keluar</strong> pada ' . $waktu_fmt . '.<br>
            Silakan konfirmasi apakah proses ini akan dijalankan atau dibatalkan.<br>
            <span style="color:#b45309;font-size:13px;">⏰ Link konfirmasi berlaku hingga <strong>' . $expire_fmt . '</strong>.</span>
          </p>
        </td>
      </tr>
      <!-- INFO -->
      <tr>
        <td style="padding:16px 32px 0;">
          <table width="100%" cellpadding="0" cellspacing="0" style="background:#fdf6f6;border-radius:6px;border:1px solid #fecaca;">
            <tr><td colspan="2" style="padding:10px 16px;background:#fee2e2;border-bottom:1px solid #fecaca;"><strong style="font-size:12px;color:#b91c1c;text-transform:uppercase;">Informasi Pengajuan</strong></td></tr>
            <tr>
              <td style="padding:10px 16px;color:#6b7280;font-size:13px;width:40%;border-bottom:1px solid #fce8e8;">Diajukan Oleh</td>
              <td style="padding:10px 16px;font-size:13px;font-weight:600;border-bottom:1px solid #fce8e8;">' . $nama_user . '</td>
            </tr>
            <tr>
              <td style="padding:10px 16px;color:#6b7280;font-size:13px;border-bottom:1px solid #fce8e8;">Penerima</td>
              <td style="padding:10px 16px;font-size:13px;font-weight:600;color:#b91c1c;border-bottom:1px solid #fce8e8;">' . $nama_penerima . '</td>
            </tr>
            <tr>
              <td style="padding:10px 16px;color:#6b7280;font-size:13px;border-bottom:1px solid #fce8e8;">No. PO</td>
              <td style="padding:10px 16px;font-size:13px;border-bottom:1px solid #fce8e8;">' . $no_po_html . '</td>
            </tr>
            <tr>
              <td style="padding:10px 16px;color:#6b7280;font-size:13px;">Keterangan</td>
              <td style="padding:10px 16px;font-size:13px;">' . $ket_html . '</td>
            </tr>
          </table>
        </td>
      </tr>
      <!-- TABEL BARANG -->
      <tr>
        <td style="padding:16px 32px 8px;">
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
              <td style="padding:12px 14px;font-size:14px;font-weight:700;color:#b91c1c;text-align:right;">Rp ' . number_format($total_keseluruhan, 0, ',', '.') . '</td>
            </tr>
          </table>
        </td>
      </tr>
      <!-- TOMBOL KONFIRMASI -->
      <tr>
        <td style="padding:24px 32px;">
          <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
              <td align="center" style="padding:0 8px;">
                <a href="' . $url_yes . '" style="display:inline-block;background:#1a7a3c;color:#ffffff;text-decoration:none;padding:14px 36px;border-radius:6px;font-size:15px;font-weight:700;letter-spacing:0.3px;">
                  ✅ Ya, Jalankan Proses Barang Keluar
                </a>
              </td>
            </tr>
            <tr><td style="padding:12px;"></td></tr>
            <tr>
              <td align="center" style="padding:0 8px;">
                <a href="' . $url_no . '" style="display:inline-block;background:#dc2626;color:#ffffff;text-decoration:none;padding:14px 36px;border-radius:6px;font-size:15px;font-weight:700;letter-spacing:0.3px;">
                  ❌ Tidak, Batalkan Proses
                </a>
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td style="background:#fdf6f6;border-top:1px solid #fce8e8;padding:18px 32px;">
          <p style="font-size:12px;color:#9ca3af;margin:0;line-height:1.6;">
            Email ini dikirim otomatis oleh <strong>Easy WMS</strong>. Jangan teruskan link ini ke pihak lain.<br>
            Jika Anda tidak merasa mengajukan proses ini, abaikan email ini.
          </p>
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
        $this->email->to($user->email);
        $this->email->subject($subject);
        $this->email->message($message);
        return $this->email->send();
    }

    /**
     * Kirim email laporan BARANG KELUAR (setelah dikonfirmasi & diproses)
     */
    private function _send_email_keluar($barang_keluar, $list_barang)
    {
        $total_keseluruhan = 0;
        $rows_html         = '';

        foreach ($list_barang as $item) {
            $subtotal               = $item->qty * $item->harga;
            $total_keseluruhan     += $subtotal;
            $rows_html             .= '
                <tr>
                    <td style="padding:10px 14px; border-bottom:1px solid #fce8e8;">'
                        . htmlspecialchars($item->nama) . '</td>
                    <td style="padding:10px 14px; border-bottom:1px solid #fce8e8; text-align:center;">'
                        . $item->qty . ' ' . htmlspecialchars($item->id_satuan) . '</td>
                    <td style="padding:10px 14px; border-bottom:1px solid #fce8e8; text-align:right;">Rp '
                        . number_format($item->harga, 0, ',', '.') . '</td>
                    <td style="padding:10px 14px; border-bottom:1px solid #fce8e8; text-align:right;">Rp '
                        . number_format($subtotal, 0, ',', '.') . '</td>
                </tr>';
        }

        $nama_user    = isset($barang_keluar->nama)  ? htmlspecialchars($barang_keluar->nama)  : '-';
        $email_user   = isset($barang_keluar->email) ? htmlspecialchars($barang_keluar->email) : '-';
        $id_transaksi = isset($barang_keluar->id_barang_keluar) ? $barang_keluar->id_barang_keluar : '-';
        $waktu        = isset($barang_keluar->waktu) ? $barang_keluar->waktu : date('Y-m-d H:i:s');
        $waktu_fmt    = date('d F Y, H:i:s', strtotime($waktu));

        $subject = '[BARANG KELUAR] Transaksi #' . $id_transaksi . ' — Easy WMS';

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
            Kepada Yth. Tim Manajemen,<br><br>
            Transaksi <strong>Barang Keluar #' . $id_transaksi . '</strong> telah <strong style="color:#b91c1c;">dikonfirmasi dan berhasil diproses</strong> pada ' . $waktu_fmt . '.
          </p>
        </td>
      </tr>
      <tr>
        <td style="padding:20px 32px;">
          <table width="100%" cellpadding="0" cellspacing="0" style="background:#fdf6f6;border-radius:6px;border:1px solid #fecaca;">
            <tr><td colspan="2" style="padding:12px 16px;background:#fee2e2;border-bottom:1px solid #fecaca;"><strong style="font-size:12px;color:#b91c1c;text-transform:uppercase;">Informasi Transaksi</strong></td></tr>
            <tr>
              <td style="padding:10px 16px;color:#6b7280;font-size:13px;width:40%;border-bottom:1px solid #fce8e8;">Diproses Oleh</td>
              <td style="padding:10px 16px;font-size:13px;font-weight:600;border-bottom:1px solid #fce8e8;">' . $nama_user . '</td>
            </tr>
            <tr>
              <td style="padding:10px 16px;color:#6b7280;font-size:13px;">Email Pengguna</td>
              <td style="padding:10px 16px;font-size:13px;">' . $email_user . '</td>
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
              <td style="padding:12px 14px;font-size:14px;font-weight:700;color:#b91c1c;text-align:right;">Rp ' . number_format($total_keseluruhan, 0, ',', '.') . '</td>
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
        $this->email->to($this->config->item('smtp_user'));
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->send();
    }
}


/* End of file Cartout.php */
