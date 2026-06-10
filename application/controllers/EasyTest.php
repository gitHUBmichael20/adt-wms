<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Jakarta');

/**
 * Controller cetak PDF invoice barang keluar
 * Dipanggil: /easytest/index/{id_barang_keluar}
 */
class EasyTest extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

        $is_login = $this->session->userdata('is_login');
        if (!$is_login) {
            $this->session->set_flashdata('warning', 'Anda belum login');
            redirect(base_url('login'));
            return;
        }
    }

    public function index($id_barang_keluar = null)
    {
        if (!$id_barang_keluar) {
            $this->session->set_flashdata('error', 'ID transaksi tidak ditemukan');
            redirect(base_url('outputs'));
            return;
        }

        require_once $_SERVER['DOCUMENT_ROOT'] . 'vendor/autoload.php';

        // Ambil header transaksi keluar
        $barang_keluar = $this->db
            ->select('barang_keluar.id, barang_keluar.waktu, user.nama AS nama_user')
            ->from('barang_keluar')
            ->join('user', 'barang_keluar.id_user = user.id', 'left')
            ->where('barang_keluar.id', $id_barang_keluar)
            ->get()->row();

        if (!$barang_keluar) {
            $this->session->set_flashdata('warning', 'Data transaksi tidak ditemukan');
            redirect(base_url('outputs'));
            return;
        }

        // Ambil detail barang yang keluar
        $detail = $this->db
            ->select('barang.nama, barang.harga, satuan.nama AS nama_satuan, barang_keluar_detail.qty')
            ->from('barang_keluar_detail')
            ->join('barang', 'barang_keluar_detail.id_barang = barang.id', 'left')
            ->join('satuan', 'barang.id_satuan = satuan.id', 'left')
            ->where('barang_keluar_detail.id_barang_keluar', $id_barang_keluar)
            ->get()->result();

        $tanggal  = date('d/m/Y', strtotime($barang_keluar->waktu));
        $total_qty = 0;

        $mpdf = new \Mpdf\Mpdf();
        $mpdf->SetHTMLHeader('<img src="' . base_url('assets/design/header.png') . '">');

        $html = '
        <div>
            <br><br><br><br><br><br><br>
            <h1 align="center">Invoice Bukti Pengeluaran Barang</h1><br>
            <p>No ID Transaksi : ' . $barang_keluar->id . '</p>
            <p>Operator        : ' . htmlspecialchars($barang_keluar->nama_user) . '</p>
            <p>Tanggal         : ' . $tanggal . '</p>
            <table border="1" cellpadding="5" style="width:100%">
                <tr>
                    <th align="center">No</th>
                    <th align="center">Nama Barang</th>
                    <th align="center">Satuan</th>
                    <th align="center">Harga Satuan</th>
                    <th align="center">Jumlah</th>
                    <th align="center">Subtotal</th>
                </tr>';

        $no = 1;
        $grand_total = 0;
        foreach ($detail as $d) {
            $subtotal     = $d->qty * $d->harga;
            $grand_total += $subtotal;
            $total_qty   += $d->qty;

            $html .= '<tr>';
            $html .= '<td align="center">' . $no++ . '</td>';
            $html .= '<td>' . htmlspecialchars($d->nama) . '</td>';
            $html .= '<td align="center">' . htmlspecialchars($d->nama_satuan) . '</td>';
            $html .= '<td align="right">Rp ' . number_format($d->harga, 0, ',', '.') . '</td>';
            $html .= '<td align="center">' . $d->qty . '</td>';
            $html .= '<td align="right">Rp ' . number_format($subtotal, 0, ',', '.') . '</td>';
            $html .= '</tr>';
        }

        $html .= '
                <tr>
                    <td align="center" colspan="4"><b>Total</b></td>
                    <td align="center"><b>' . $total_qty . '</b></td>
                    <td align="right"><b>Rp ' . number_format($grand_total, 0, ',', '.') . '</b></td>
                </tr>
            </table><br>
            <h6>Mengetahui</h6><br><br><br>
            <h6>' . htmlspecialchars($barang_keluar->nama_user) . '</h6>
        </div>';

        $mpdf->WriteHTML($html);
        $mpdf->Output('invoice-keluar-' . $id_barang_keluar . '.pdf', 'I');
    }
}

/* End of file EasyTest.php */
