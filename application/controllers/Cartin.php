<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Keranjang Masuk
 */
class Cartin extends MY_Controller 
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

        $data['title']              = 'ADT WMS - Keranjang Masuk';
        $data['breadcrumb_title']   = "Keranjang Masuk";
        $data['breadcrumb_path']    = 'Barang Masuk / Keranjang Masuk';
        $data['page']               = 'pages/cartin/index';
        $data['content']            = $this->cartin->select([
                'barang.id AS id_barang', 'barang.nama', 'barang.harga',
                'barang.id_satuan', 'keranjang_masuk.id AS id', 
                'keranjang_masuk.qty AS qty_barang_masuk', 'keranjang_masuk.subtotal'
            ])
            ->where('keranjang_masuk.id_user', $this->id_user)
            ->join('barang')
            ->get();

        // Kirim list supplier untuk dropdown
        $data['suppliers'] = getSuppliers();

        $this->view($data);
    }

    /**
     * Menampung barang yang akan ditambah kuantitasnya
     */
    public function add()
    {
        if (!$_POST || $this->input->post('qty_masuk') < 1) {
            $this->session->set_flashdata('error', 'Kuantitas tidak boleh kosong');
            redirect(base_url('items/in'));
            return;
        }
        
        $input = (object) $this->input->post(null, true);

        // Mengambil data barang yang dipilih
        $this->cartin->table = 'barang';
        $barang = $this->cartin->where('id', $input->id_barang)->first();

        // Mekanisme penambahan kuantitas
        $this->cartin->table = 'keranjang_masuk';
        $cart = $this->cartin->where('id_barang', $input->id_barang)
                             ->where('id_user', $this->id_user)
                             ->first();

        // Subtotal sudah termasuk PPN 11%: harga * qty * 1.11
        $subtotal_penambahan = round($barang->harga * $input->qty_masuk * 1.11);

        if ($cart) {
            $data = [
                'qty'       => $cart->qty + $input->qty_masuk,
                'subtotal'  => $cart->subtotal + $subtotal_penambahan
            ];

            if ($this->cartin->where('id', $cart->id)
                             ->where('id_user', $this->id_user)
                             ->update($data)
            ) {
                $this->session->set_flashdata('success', 'Barang berhasil ditambahkan');
            } else {
                $this->session->set_flashdata('error', 'Oops! Terjadi kesalahan');
            }

            redirect(base_url('cartin'));
            return;
        }

        $data = [
            'id_user'   => $this->id_user,
            'id_barang' => $input->id_barang,
            'qty'       => $input->qty_masuk
        ];

        if ($this->cartin->create($data)) {
            $this->session->set_flashdata('success', 'Barang berhasil dimasukan ke keranjang');
        } else {
            $this->session->set_flashdata('error', 'Oops! Terjadi kesalahan');
        }

        redirect(base_url('cartin'));
    }

    /**
     * Update kuantitas di keranjang belanja
     */
    public function update()
    {
        if (!$_POST || $this->input->post('qty_barang_masuk') < 1) {
            $this->session->set_flashdata('error', 'Kuantitas tidak boleh kosong');
            redirect(base_url('cartin'));
        }

        $id = $this->input->post('id');
        $id_barang = $this->input->post('id_barang');

        $data['content'] = $this->cartin->where('id_barang', $id_barang)
                                        ->where('id', $id)
                                        ->first();

        if (!$data['content']) {
            $this->session->set_flashdata('warning', 'Data tidak ditemukan');
            redirect(base_url('cartin'));
        }

        $this->cartin->table = 'barang';
        $barang = $this->cartin->where('id', $data['content']->id_barang)->first();

        $data['input'] = (object) $this->input->post(null, true);
        // Subtotal sudah termasuk PPN 11%: harga * qty * 1.11
        $subtotal_pembaharuan = round($data['input']->qty_barang_masuk * $barang->harga * 1.11);

        $cart = [
            'qty'      => $data['input']->qty_barang_masuk,
            'subtotal' => $subtotal_pembaharuan
        ];

        $this->cartin->table  = 'keranjang_masuk';
        if ($this->cartin->where('id', $id)
                         ->where('id_barang', $id_barang)
                         ->where('id_user', $this->id_user)
                         ->update($cart)
        ) {
            $this->session->set_flashdata('success', 'Kuantitas berhasil diubah');
        } else {
            $this->session->set_flashdata('error', 'Oops! Terjadi kesalahan');
        }

        redirect(base_url('cartin'));
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

        if (!$this->cartin->where('id', $id)->first()) {
            $this->session->set_flashdata('warning', 'Maaf data tidak ditemukan');
            redirect(base_url('cartin'));
        }

        if ($this->cartin->where('id', $id)->delete()) {
            $this->session->set_flashdata('success', '1 Barang berhasil dikeluarkan dari keranjang');
        } else {
            $this->session->set_flashdata('error', 'Oops, terjadi suatu kesalahan');
        }

        redirect(base_url('cartin'));
    }

    /**
     * Menghapus seluruh isi keranjang
     */
    public function drop()
    {
        if (!$_POST) {
            $this->session->set_flashdata('error', 'Aksi ditolak');
            redirect(base_url('cartin'));
        }

        if ($this->cartin->where('id_user', $this->id_user)->count() < 1) {
            $this->session->set_flashdata('warning', 'Tidak ada barang di dalam keranjang');
            redirect(base_url('cartin'));
        }

        $this->cartin->where('id_user', $this->id_user)->delete();

        if ($this->cartin->count() < 1) { 
            $this->cartin->resetIndex();
        }

        $this->session->set_flashdata('success', 'Keranjang masuk anda telah dibersihkan');

        redirect(base_url('cartin'));
    }

    /**
     * STEP 1: Kirim email konfirmasi ke ADMIN sebelum checkout barang masuk.
     * Token disimpan di database (bukan session), sehingga admin tidak perlu login.
     */
    public function request_checkout()
    {
        if (!isset($this->id_user)) {
            $this->session->set_flashdata('error', 'Akses checkout ditolak!');
            redirect(base_url('home'));
        }

        // Validasi: supplier harus dipilih
        $id_supplier = $this->input->post('id_supplier');
        if (empty($id_supplier)) {
            $this->session->set_flashdata('error', 'Harap pilih supplier terlebih dahulu!');
            redirect(base_url('cartin'));
            return;
        }

        // Validasi: supplier harus ada di database
        $supplier_check = $this->db->where('id', $id_supplier)->where('status', 'aktif')->get('supplier')->row();
        if (!$supplier_check) {
            $this->session->set_flashdata('error', 'Supplier tidak valid!');
            redirect(base_url('cartin'));
            return;
        }

        // Cek keranjang tidak kosong
        $inputCartCount = $this->cartin->where('id_user', $this->id_user)->count();
        if (!$inputCartCount) {
            $this->session->set_flashdata('warning', 'Tidak ada barang yang akan dimasukan!');
            redirect(base_url('cartin'));
            return;
        }

        // Generate token unik (berlaku 30 menit)
        $token      = bin2hex(random_bytes(24));
        $expires_at = date('Y-m-d H:i:s', time() + 1800);

        // Ambil isi keranjang untuk ditampilkan di email
        $this->cartin->table = 'keranjang_masuk';
        $cart_items = $this->cartin->select([
                'barang.id AS id_barang', 'barang.nama', 'barang.harga',
                'barang.id_satuan', 'keranjang_masuk.qty', 'keranjang_masuk.subtotal'
            ])
            ->where('keranjang_masuk.id_user', $this->id_user)
            ->join('barang')
            ->get();

        // Simpan pending checkout di DATABASE (bukan session)
        $this->db->insert('pending_checkout', [
            'token'      => $token,
            'type'       => 'masuk',
            'id_user'    => $this->id_user,
            'payload'    => json_encode(['id_supplier' => $id_supplier]),
            'cart_items' => json_encode($cart_items),
            'expires_at' => $expires_at,
            'used'       => 0,
        ]);

        // Ambil data user untuk info di email
        $user = $this->db->where('id', $this->id_user)->get('user')->row();

        // Kirim email konfirmasi ke ADMIN
        $admin_email = $this->config->item('admin_email');
        $sent = $this->_send_email_konfirmasi_masuk($token, $user, $supplier_check, $cart_items, $admin_email);

        if ($sent) {
            $this->session->set_flashdata('info', 
                'Email konfirmasi telah dikirim ke admin (<strong>' . htmlspecialchars($admin_email) . '</strong>). '
                . 'Proses barang masuk akan dilanjutkan setelah admin mengklik tombol konfirmasi di email. '
                . 'Link berlaku selama 30 menit.'
            );
        } else {
            $this->session->set_flashdata('warning', 
                'Gagal mengirim email konfirmasi ke admin. Silakan coba lagi.'
            );
            // Hapus token yang gagal terkirim
            $this->db->where('token', $token)->delete('pending_checkout');
        }

        redirect(base_url('cartin'));
    }

    /**
     * Kirim email KONFIRMASI ke ADMIN sebelum checkout barang masuk.
     * Admin langsung klik tombol — tidak perlu login.
     */
    private function _send_email_konfirmasi_masuk($token, $user, $supplier, $cart_items, $admin_email)
    {
        $total_keseluruhan = 0;
        $rows_html         = '';

        foreach ($cart_items as $item) {
            $subtotal            = round($item->qty * $item->harga * 1.11);
            $total_keseluruhan  += $subtotal;
            $rows_html          .= '
                <tr>
                    <td style="padding:10px 14px; border-bottom:1px solid #e8f0e9;">'
                        . htmlspecialchars($item->nama) . '</td>
                    <td style="padding:10px 14px; border-bottom:1px solid #e8f0e9; text-align:center;">'
                        . $item->qty . ' ' . htmlspecialchars($item->id_satuan) . '</td>
                    <td style="padding:10px 14px; border-bottom:1px solid #e8f0e9; text-align:right;">Rp '
                        . number_format($item->harga, 0, ',', '.') . '</td>
                    <td style="padding:10px 14px; border-bottom:1px solid #e8f0e9; text-align:right;">Rp '
                        . number_format($subtotal, 0, ',', '.') . '<br><small style="color:#888;">(incl. PPN 11%)</small></td>
                </tr>';
        }

        $nama_user     = htmlspecialchars($user->nama);
        $email_user    = htmlspecialchars($user->email);
        $nama_supplier = htmlspecialchars($supplier->nama);
        $waktu_fmt     = date('d F Y, H:i:s');
        $expire_fmt    = date('d F Y, H:i:s', time() + 1800);

        // URL konfirmasi mengarah ke controller Confirm (publik, tanpa login)
        $url_yes = base_url('confirm/checkout/' . $token . '/yes');
        $url_no  = base_url('confirm/checkout/' . $token . '/no');

        $subject = '[KONFIRMASI ADMIN] Barang Masuk Menunggu Persetujuan — ADT WMS';

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
          <div style="font-size:11px;color:#a8ddb5;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:4px;">ADT WMS — Konfirmasi Admin Diperlukan</div>
          <div style="font-size:22px;font-weight:700;color:#ffffff;">📦 Permintaan Barang Masuk</div>
        </td>
      </tr>
      <tr>
        <td style="padding:24px 32px 8px;">
          <p style="font-size:14px;color:#374151;line-height:1.7;margin:0;">
            Kepada Yth. <strong>Admin ADT WMS</strong>,<br><br>
            Pengguna <strong>' . $nama_user . '</strong> (' . $email_user . ') mengajukan proses 
            <strong>Barang Masuk</strong> pada ' . $waktu_fmt . '.<br>
            Silakan tinjau dan konfirmasi permintaan ini.<br>
            <span style="color:#b45309;font-size:13px;">⏰ Link konfirmasi berlaku hingga <strong>' . $expire_fmt . '</strong>.</span>
          </p>
        </td>
      </tr>
      <!-- INFO SUPPLIER -->
      <tr>
        <td style="padding:16px 32px 0;">
          <table width="100%" cellpadding="0" cellspacing="0" style="background:#f6faf7;border-radius:6px;border:1px solid #d1e8d4;">
            <tr><td colspan="2" style="padding:10px 16px;background:#e8f5eb;border-bottom:1px solid #d1e8d4;"><strong style="font-size:12px;color:#1a7a3c;text-transform:uppercase;">Informasi Pengajuan</strong></td></tr>
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
      <!-- TABEL BARANG -->
      <tr>
        <td style="padding:16px 32px 8px;">
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
              <td style="padding:12px 14px;font-size:14px;font-weight:700;color:#1a7a3c;text-align:right;">Rp ' . number_format($total_keseluruhan, 0, ',', '.') . '</td>
            </tr>
          </table>
        </td>
      </tr>
      <!-- TOMBOL KONFIRMASI -->
      <tr>
        <td style="padding:24px 32px;">
          <p style="font-size:13px;color:#374151;margin:0 0 16px;"><strong>Tindakan Admin:</strong> Klik salah satu tombol di bawah — tidak perlu login.</p>
          <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
              <td align="center" style="padding:0 8px;">
                <a href="' . $url_yes . '" style="display:inline-block;background:#1a7a3c;color:#ffffff;text-decoration:none;padding:14px 36px;border-radius:6px;font-size:15px;font-weight:700;letter-spacing:0.3px;">
                  ✅ Setujui &amp; Proses Barang Masuk
                </a>
              </td>
            </tr>
            <tr><td style="padding:12px;"></td></tr>
            <tr>
              <td align="center" style="padding:0 8px;">
                <a href="' . $url_no . '" style="display:inline-block;background:#dc2626;color:#ffffff;text-decoration:none;padding:14px 36px;border-radius:6px;font-size:15px;font-weight:700;letter-spacing:0.3px;">
                  ❌ Tolak &amp; Batalkan Proses
                </a>
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td style="background:#f6faf7;border-top:1px solid #e8f0e9;padding:18px 32px;">
          <p style="font-size:12px;color:#9ca3af;margin:0;line-height:1.6;">
            Email ini dikirim otomatis oleh <strong>ADT WMS</strong>. Jangan teruskan link ini ke pihak yang tidak berwenang.
          </p>
        </td>
      </tr>
    </table>
  </td></tr>
</table>
</body>
</html>';

        $email_config = [
            'protocol'     => $this->config->item('protocol'),
            'smtp_host'    => $this->config->item('smtp_host'),
            'smtp_port'    => $this->config->item('smtp_port'),
            'smtp_timeout' => $this->config->item('smtp_timeout'),
            'smtp_user'    => $this->config->item('smtp_user'),
            'smtp_pass'    => $this->config->item('smtp_pass'),
            'charset'      => 'utf-8',
            'mailtype'     => 'html',
            'newline'      => "\r\n",
            'wordwrap'     => TRUE,
        ];
        $this->email->initialize($email_config);
        $this->email->clear();
        $this->email->from($this->config->item('smtp_user'), 'ADT WMS Notification');
        $this->email->to($admin_email);
        $this->email->subject($subject);
        $this->email->message($message);
        return $this->email->send();
    }
}


/* End of file Cartin.php */
