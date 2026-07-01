<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Docx_generator Library
 *
 * Menghasilkan file DOCX invoice/DO dari template dengan cara:
 * 1. Copy template DOCX (ZIP)
 * 2. Baca document.xml dari dalam ZIP
 * 3. Replace seluruh blok tabel item dengan data baru (XML yang dibangun dari data)
 * 4. Replace placeholder teks lainnya
 * 5. Tulis kembali ke ZIP, simpan ke folder backup, lalu stream ke browser
 *
 * Cara pakai:
 *   $this->load->library('Docx_generator');
 *   $this->docx_generator->generate_invoice($data, $list_barang, $save_path);
 *   $this->docx_generator->generate_delivery_order($data, $list_barang, $penerima, $save_path);
 */
class Docx_generator
{
    private $CI;
    private $template_dir;

    public function __construct()
    {
        $this->CI = &get_instance();
        // Folder templates relatif terhadap FCPATH (root proyek)
        $this->template_dir = FCPATH . 'templates/';
    }

    // =========================================================================
    // PUBLIC: Invoice Barang Masuk
    // =========================================================================

    /**
     * @param object $barang_masuk   Row dari DB (id_barang_masuk, waktu, nama, id_user, nama_supplier, ...)
     * @param array  $list_barang    Array of row (nama, qty, harga, subtotal, id_satuan)
     * @param string $save_path      Path lengkap untuk menyimpan backup (misal: FCPATH.'application/invoices/inv_001.docx')
     */
    public function generate_invoice($barang_masuk, $list_barang, $save_path)
    {
        $template_path = $this->template_dir . 'invoice_template.docx';

        if (!file_exists($template_path)) {
            show_error('Template invoice tidak ditemukan: ' . $template_path);
        }

        // Hitung total
        $total_incl_ppn = array_sum(array_column((array) $list_barang, 'subtotal'));
        $harga_pokok    = round($total_incl_ppn / 1.11);
        $ppn            = $total_incl_ppn - $harga_pokok;

        // Tanggal (bisa di-override dari form)
        $tanggal = !empty($barang_masuk->custom_date)
            ? $barang_masuk->custom_date
            : date('d F Y', strtotime($barang_masuk->waktu));

        // Nomor invoice: FDI/INV/... (bisa di-override dari form)
        $inv_no = !empty($barang_masuk->custom_inv_no)
            ? $barang_masuk->custom_inv_no
            : 'FDI/INV/' . date('Y/m', strtotime($barang_masuk->waktu))
                . '/' . str_pad($barang_masuk->id_barang_masuk, 5, '0', STR_PAD_LEFT);

        // No. SP dan BTB (bisa di-override dari form)
        $no_sp = !empty($barang_masuk->custom_no_sp)
            ? $barang_masuk->custom_no_sp
            : (string)$barang_masuk->id_barang_masuk;
        $btb   = !empty($barang_masuk->custom_btb) ? $barang_masuk->custom_btb : '';

        // NPWP supplier
        $npwp_supplier = !empty($barang_masuk->npwp_supplier)
            ? 'NPWP : ' . $barang_masuk->npwp_supplier
            : '';

        // Alamat supplier — gabung ke baris supplier box
        $alamat_supplier = !empty($barang_masuk->alamat_supplier)
            ? $barang_masuk->alamat_supplier
            : '';

        // Baca XML dari template
        $xml = $this->_read_xml_from_docx($template_path, 'word/document.xml');

        // --- 1. Replace teks simpel ---
        $replacements = [
            // Tanggal di header
            '18 Mei 2026'         => $tanggal,
            // Invoice number
            'FDI/INV/2026/04/01354' => $inv_no,
            // No. SP
            '2022916'             => $no_sp,
            // Nama supplier di text box — akan diikuti alamat dan NPWP
            'PT. INDOMARCO PRISMATAMA'          => $this->_xml_escape(!empty($barang_masuk->nama_supplier) ? $barang_masuk->nama_supplier : '-'),
            'Gedung Menara Indomaret'            => $this->_xml_escape($alamat_supplier),
            'Jl. Boulevard Pantai Indah Kapuk, Kamal Muara, Penjaringan, Kota ADM Jakarta Utara, DKI Jakarta 14470' => '',
            'NPWP : 0013379946092000'            => $this->_xml_escape($npwp_supplier),
            // Terbilang
            'Tiga Juta Empat Ratus Tiga Belas Ribu Dua Ratus Lima Puluh Rupiah'
            => $this->_xml_escape($this->_terbilang($total_incl_ppn) . ' Rupiah'),
            // Summary table values
            'Rp3.075.000'   => 'Rp' . number_format($total_incl_ppn, 0, ',', '.'),
            'Rp2.818.750'   => 'Rp' . number_format($harga_pokok, 0, ',', '.'),
            'Rp338.250'     => 'Rp' . number_format($ppn, 0, ',', '.'),
            'Rp3.413.250'   => 'Rp' . number_format($total_incl_ppn, 0, ',', '.'),
            // Nama staff
            'Yudha Kurnia Pangestu' => 'Yudha Kurnia Pangestu',
        ];

        // Lakukan replace sederhana pada teks dalam <w:t>
        $xml = $this->_replace_wt_text($xml, $replacements);

        // --- 2. Replace baris tabel item ---
        $xml = $this->_replace_invoice_items($xml, $list_barang);

        // Tulis kembali ke DOCX
        $docx_bytes = $this->_write_xml_to_docx($template_path, 'word/document.xml', $xml);

        // Simpan backup
        $this->_ensure_dir(dirname($save_path));
        file_put_contents($save_path, $docx_bytes);

        // Stream ke browser
        $filename = basename($save_path);
        $this->_stream_docx($docx_bytes, $filename);
    }

    // =========================================================================
    // PUBLIC: Delivery Order Barang Keluar
    // =========================================================================

    /**
     * @param object      $barang_keluar  Row dari DB
     * @param array       $list_barang    Array of row (nama, qty, id_satuan, serial_number optional)
     * @param object|null $penerima       Row penerima (nama, divisi, alamat) atau null
     * @param string      $save_path      Path backup
     */
    public function generate_delivery_order($barang_keluar, $list_barang, $penerima, $save_path)
    {
        $template_path = $this->template_dir . 'delivery_order_template.docx';

        if (!file_exists($template_path)) {
            show_error('Template delivery order tidak ditemukan: ' . $template_path);
        }

        // Nomor DO
        $do_no = 'FDI/DO/' . date('Y/m', strtotime($barang_keluar->waktu))
            . '/' . str_pad($barang_keluar->id_barang_keluar, 5, '0', STR_PAD_LEFT);

        $tanggal      = 'Bekasi, ' . date('d F Y', strtotime($barang_keluar->waktu));
        $nama_penerima = $penerima ? $penerima->nama : '-';
        $no_po        = !empty($barang_keluar->no_po) ? $barang_keluar->no_po : '-';

        $xml = $this->_read_xml_from_docx($template_path, 'word/document.xml');

        $nama_penerima_full = $penerima
            ? ($penerima->nama . ($penerima->divisi ? ' - ' . $penerima->divisi : ''))
            : '-';

        $replacements = [
            'FDI/DO/2026/04/01354' => $do_no,
            'Bekasi, 6 Mei 2026'   => $tanggal,
            'SP 2148353'           => $no_po,
            'BOGOR - DEVELOPMENT'  => $this->_xml_escape($nama_penerima_full),
        ];

        $xml = $this->_replace_wt_text($xml, $replacements);

        // Replace baris tabel item DO
        $xml = $this->_replace_do_items($xml, $list_barang, $barang_keluar);

        // Ship To box — jika ada penerima, ganti blok
        if ($penerima) {
            $xml = $this->_replace_shipto($xml, $penerima);
        }

        $docx_bytes = $this->_write_xml_to_docx($template_path, 'word/document.xml', $xml);

        $this->_ensure_dir(dirname($save_path));
        file_put_contents($save_path, $docx_bytes);

        $filename = basename($save_path);
        $this->_stream_docx($docx_bytes, $filename);
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Baca satu file XML dari dalam DOCX (ZIP) dan return sebagai string
     */
    private function _read_xml_from_docx($docx_path, $xml_entry)
    {
        $zip = new ZipArchive();
        if ($zip->open($docx_path) !== true) {
            show_error('Tidak bisa membuka template: ' . $docx_path);
        }
        $xml = $zip->getFromName($xml_entry);
        $zip->close();

        if ($xml === false) {
            show_error("Entry '{$xml_entry}' tidak ditemukan dalam template.");
        }
        return $xml;
    }

    /**
     * Tulis XML yang sudah dimodifikasi kembali ke salinan DOCX
     * dan return bytes DOCX baru
     */
    private function _write_xml_to_docx($template_path, $xml_entry, $new_xml)
    {
        // Copy template ke temp file
        $tmp = tempnam(sys_get_temp_dir(), 'docx_');
        copy($template_path, $tmp);

        $zip = new ZipArchive();
        if ($zip->open($tmp) !== true) {
            show_error('Tidak bisa membuka salinan template untuk ditulis.');
        }
        $zip->addFromString($xml_entry, $new_xml);
        $zip->close();

        $bytes = file_get_contents($tmp);
        unlink($tmp);
        return $bytes;
    }

    /**
     * Replace teks di dalam elemen <w:t>...</w:t> berdasarkan peta $replacements.
     * Menangani kasus teks terpecah di multiple <w:r> runs dalam satu paragraf.
     */
    private function _replace_wt_text($xml, $replacements)
    {
        foreach ($replacements as $search => $replace) {
            // Langkah 1: simple replace (teks tidak terpecah)
            $xml = str_replace($this->_xml_escape($search), $this->_xml_escape($replace), $xml);
            $xml = str_replace($search, $this->_xml_escape($replace), $xml);

            // Langkah 2: cross-run replace (teks terpecah antar <w:r>)
            $xml = $this->_cross_run_replace($xml, $search, $replace);
        }
        return $xml;
    }

    /**
     * Replace teks yang terpecah antar multiple <w:t> elemen.
     * Membangun regex yang cocok dengan karakter target yang dipisahkan oleh tag XML antar runs.
     */
    private function _cross_run_replace($xml, $search, $replace)
    {
        $es = $this->_xml_escape($search);
        $er = $this->_xml_escape($replace);

        // Pattern antar runs: penutup w:t, lalu konten apa saja (bukan batas tabel/paragraf), lalu pembuka w:t
        $between = '(?:<\/w:t>(?:(?!<\/w:tbl>)(?!<\/w:p>).)*?<w:t[^>]*>)?';

        // Bangun pattern dari karakter pencarian
        $chars = preg_split('//u', $es, -1, PREG_SPLIT_NO_EMPTY);
        $pattern = '';
        foreach ($chars as $i => $c) {
            $pattern .= preg_quote($c, '/');
            if ($i < count($chars) - 1) {
                $pattern .= $between;
            }
        }

        $new_xml = preg_replace_callback(
            '/' . $pattern . '/s',
            function ($m) use ($er) {
                // Replace semua <w:t> content dalam match: taruh er di yang pertama, kosongkan sisanya
                $full = $m[0];
                $first = true;
                $result = preg_replace_callback(
                    '/<w:t([^>]*)>([^<]*)<\/w:t>/',
                    function ($tm) use (&$first, $er) {
                        if ($first) {
                            $first = false;
                            $space = (strpos($er, ' ') !== false) ? ' xml:space="preserve"' : '';
                            return '<w:t' . $space . '>' . $er . '</w:t>';
                        }
                        return '<w:t></w:t>';
                    },
                    $full
                );
                return $result;
            },
            $xml
        );

        return $new_xml !== null ? $new_xml : $xml;
    }

    /**
     * Replace satu baris contoh item di tabel invoice (Barang Masuk)
     * dengan baris-baris dari $list_barang.
     *
     * Pendekatan: cari <w:tr> pertama yang bukan header (berisi "Brother TD-2310D")
     * lalu ganti dengan baris-baris baru.
     */
    private function _replace_invoice_items($xml, $list_barang)
    {
        // Buat XML baris-baris baru
        $rows_xml = '';
        $no = 1;
        foreach ($list_barang as $b) {
            $b = (object) $b;
            $nama     = $this->_xml_escape($b->nama);
            $qty      = $this->_xml_escape((string)$b->qty);
            $harga    = 'Rp' . number_format($b->harga, 0, ',', '.');
            $subtotal = 'Rp' . number_format($b->subtotal, 0, ',', '.');

            $rows_xml .= $this->_invoice_row_xml($no++, $nama, $qty, $harga, $subtotal);
        }

        // Cari dan ganti baris contoh: baris w:tr yang mengandung "Brother TD-2310D Label"
        $xml = preg_replace(
            '/<w:tr\b[^>]*>(?:(?!<w:tr\b).)*Brother TD-2310D Label(?:(?!<\/w:tr>).)*<\/w:tr>/s',
            $rows_xml,
            $xml
        );

        return $xml;
    }

    /**
     * Buat XML satu baris tabel invoice (5 kolom: No, Deskripsi, Qty, Harga, Jumlah)
     * Mewarisi lebar kolom dari template: 625, 3679, 2152, 2153, 2153 DXA
     */
    private function _invoice_row_xml($no, $nama, $qty, $harga, $jumlah)
    {
        $rPr = '<w:rPr><w:bCs/><w:sz w:val="20"/><w:szCs w:val="24"/></w:rPr>';
        return <<<XML
<w:tr>
  <w:tc><w:tcPr><w:tcW w:w="625" w:type="dxa"/></w:tcPr>
    <w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r>{$rPr}<w:t>{$no}</w:t></w:r></w:p></w:tc>
  <w:tc><w:tcPr><w:tcW w:w="3679" w:type="dxa"/></w:tcPr>
    <w:p><w:r>{$rPr}<w:t xml:space="preserve">{$nama}</w:t></w:r></w:p></w:tc>
  <w:tc><w:tcPr><w:tcW w:w="2152" w:type="dxa"/></w:tcPr>
    <w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r>{$rPr}<w:t>{$qty}</w:t></w:r></w:p></w:tc>
  <w:tc><w:tcPr><w:tcW w:w="2153" w:type="dxa"/></w:tcPr>
    <w:p><w:pPr><w:jc w:val="right"/></w:pPr><w:r>{$rPr}<w:t>{$harga}</w:t></w:r></w:p></w:tc>
  <w:tc><w:tcPr><w:tcW w:w="2153" w:type="dxa"/></w:tcPr>
    <w:p><w:pPr><w:jc w:val="right"/></w:pPr><w:r>{$rPr}<w:t>{$jumlah}</w:t></w:r></w:p></w:tc>
</w:tr>
XML;
    }

    /**
     * Replace baris contoh di tabel Delivery Order
     * - Kolom Serial Number dihapus, SN digabung ke deskripsi produk
     */
    private function _replace_do_items($xml, $list_barang, $barang_keluar)
    {
        $keterangan = !empty($barang_keluar->keterangan) ? $barang_keluar->keterangan : '';

        $rows_xml = '';
        $no = 1;
        foreach ($list_barang as $b) {
            $b = (object) $b;
            $nama          = $this->_xml_escape($b->nama);
            $qty           = $this->_xml_escape((string)$b->qty);
            $satuan        = $this->_xml_escape(ucfirst($this->_get_unit_name($b->id_satuan)));
            $ket           = $this->_xml_escape($keterangan);
            // Serial number: jika ada, tampilkan; kosongkan jika tidak ada
            $serial_number = !empty($b->serial_number) ? $this->_xml_escape($b->serial_number) : '';

            $rows_xml .= $this->_do_row_xml($no++, $nama, $qty, $satuan, $serial_number, $ket);
        }

        // Header tabel baru (5 kolom)
        $header_xml = $this->_do_header_xml();

        // Cari tabel yang mengandung "Deskripsi Produk", ganti header dan semua baris data
        $xml = preg_replace_callback(
            '/(<w:tbl\b)(.*?)((<w:tr\b(?:(?!<w:tr\b).)*?Deskripsi Produk(?:(?!<\/w:tr>).)*?<\/w:tr>))(.*?)(<\/w:tbl>)/s',
            function ($m) use ($rows_xml, $header_xml) {
                return $m[1] . $m[2] . $header_xml . $rows_xml . $m[6];
            },
            $xml
        );

        return $xml;
    }

    /**
     * Buat XML baris header tabel DO (5 kolom: No, Deskripsi, Kuantitas, Satuan, Keterangan)
     * Lebar total: 510 + 4680 + 900 + 900 + 1440 = 8430 dxa
     */
    private function _do_header_xml()
    {
        $rPr = '<w:rPr><w:b/><w:color w:val="FFFFFF"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr>';
        $shd = '<w:shd w:val="clear" w:color="auto" w:fill="1565C0"/>';
        return <<<XML
<w:tr>
  <w:tc><w:tcPr><w:tcW w:w="510" w:type="dxa"/>{$shd}</w:tcPr>
    <w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r>{$rPr}<w:t>No</w:t></w:r></w:p></w:tc>
  <w:tc><w:tcPr><w:tcW w:w="4680" w:type="dxa"/>{$shd}</w:tcPr>
    <w:p><w:r>{$rPr}<w:t>Deskripsi Produk</w:t></w:r></w:p></w:tc>
  <w:tc><w:tcPr><w:tcW w:w="900" w:type="dxa"/>{$shd}</w:tcPr>
    <w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r>{$rPr}<w:t>Kuantitas</w:t></w:r></w:p></w:tc>
  <w:tc><w:tcPr><w:tcW w:w="900" w:type="dxa"/>{$shd}</w:tcPr>
    <w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r>{$rPr}<w:t>Satuan</w:t></w:r></w:p></w:tc>
  <w:tc><w:tcPr><w:tcW w:w="1440" w:type="dxa"/>{$shd}</w:tcPr>
    <w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r>{$rPr}<w:t>Keterangan</w:t></w:r></w:p></w:tc>
</w:tr>
XML;
    }

    /**
     * Buat XML satu baris tabel DO (5 kolom: No, Deskripsi, Kuantitas, Satuan, Keterangan)
     * Deskripsi berisi nama produk dan jika ada serial number, ditambahkan baris baru di bawahnya.
     */
    private function _do_row_xml($no, $nama, $qty, $satuan, $serial_number, $keterangan)
    {
        $rPr = '<w:rPr><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr>';

        // Bangun konten deskripsi: nama + (jika SN ada) line break + SN
        $deskripsi_content = '<w:r>' . $rPr . '<w:t xml:space="preserve">' . $nama . '</w:t></w:r>';
        if ($serial_number !== '') {
            $deskripsi_content .= '<w:r><w:br/></w:r>';
            $deskripsi_content .= '<w:r>' . $rPr . '<w:t xml:space="preserve">' . $serial_number . '</w:t></w:r>';
        }

        return <<<XML
<w:tr>
  <w:tc><w:tcPr><w:tcW w:w="510" w:type="dxa"/></w:tcPr>
    <w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r>{$rPr}<w:t>{$no}</w:t></w:r></w:p></w:tc>
  <w:tc><w:tcPr><w:tcW w:w="4680" w:type="dxa"/></w:tcPr>
    <w:p>{$deskripsi_content}</w:p></w:tc>
  <w:tc><w:tcPr><w:tcW w:w="900" w:type="dxa"/></w:tcPr>
    <w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r>{$rPr}<w:t>{$qty}</w:t></w:r></w:p></w:tc>
  <w:tc><w:tcPr><w:tcW w:w="900" w:type="dxa"/></w:tcPr>
    <w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r>{$rPr}<w:t>{$satuan}</w:t></w:r></w:p></w:tc>
  <w:tc><w:tcPr><w:tcW w:w="1440" w:type="dxa"/></w:tcPr>
    <w:p><w:r>{$rPr}<w:t xml:space="preserve">{$keterangan}</w:t></w:r></w:p></w:tc>
</w:tr>
XML;
    }

    /**
     * Ganti konten Ship To box (text box floating di DO template)
     * dengan nama & alamat penerima asli.
     * Template punya teks "BOGOR - DEVELOPMENT" di sana.
     */
    private function _replace_shipto($xml, $penerima)
    {
        // Inject penerima info into the Ship To textbox.
        // Template Ship To box only has "Ship To :" then an empty paragraph.
        // We add divisi/nama/alamat after "Ship To :".
        $divisi  = !empty($penerima->divisi)  ? $this->_xml_escape($penerima->divisi)  : '';
        $nama    = !empty($penerima->nama)    ? $this->_xml_escape($penerima->nama)    : '';
        $alamat  = !empty($penerima->alamat)  ? $this->_xml_escape($penerima->alamat)  : '';

        // Build replacement paragraphs for the text box
        $extra = '';
        if ($divisi) {
            $extra .= '<w:p><w:r><w:rPr><w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t xml:space="preserve">' . $divisi . '</w:t></w:r></w:p>';
        }
        if ($nama) {
            $extra .= '<w:p><w:r><w:rPr><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t xml:space="preserve">' . $nama . '</w:t></w:r></w:p>';
        }
        if ($alamat) {
            $extra .= '<w:p><w:r><w:rPr><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t xml:space="preserve">' . $alamat . '</w:t></w:r></w:p>';
        }

        if ($extra) {
            // Insert extra paragraphs after the "Ship To :" paragraph inside txbxContent
            $xml = preg_replace(
                '/(<w:t>Ship To :<\/w:t><\/w:r><\/w:p>)/',
                '$1' . $extra,
                $xml,
                1
            );
        }
        return $xml;
    }

    /**
     * Escape string untuk XML
     */
    private function _xml_escape($str)
    {
        return htmlspecialchars((string)$str, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    /**
     * Stream DOCX ke browser sebagai download
     */
    private function _stream_docx($bytes, $filename)
    {
        // Matikan output buffering CI sebelum stream
        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($bytes));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');

        echo $bytes;
        exit;
    }

    /**
     * Buat direktori jika belum ada
     */
    private function _ensure_dir($dir)
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * Ambil nama satuan dari DB (sama seperti helper getUnitName)
     */
    private function _get_unit_name($id_satuan)
    {
        $CI = &get_instance();
        $CI->db->where('id', $id_satuan);
        $row = $CI->db->get('satuan')->row();
        return $row ? $row->nama : '';
    }

    /**
     * Konversi angka ke terbilang Bahasa Indonesia
     */
    private function _terbilang($angka)
    {
        $angka = (int) round(abs($angka));
        $satuan = [
            '',
            'Satu',
            'Dua',
            'Tiga',
            'Empat',
            'Lima',
            'Enam',
            'Tujuh',
            'Delapan',
            'Sembilan',
            'Sepuluh',
            'Sebelas',
            'Dua Belas',
            'Tiga Belas',
            'Empat Belas',
            'Lima Belas',
            'Enam Belas',
            'Tujuh Belas',
            'Delapan Belas',
            'Sembilan Belas'
        ];

        if ($angka === 0) return 'Nol';
        if ($angka < 20)  return $satuan[$angka];
        if ($angka < 100) {
            $p = (int)($angka / 10);
            $s = $angka % 10;
            return $satuan[$p] . ' Puluh' . ($s > 0 ? ' ' . $satuan[$s] : '');
        }
        if ($angka < 1000) {
            $r = (int)($angka / 100);
            $s = $angka % 100;
            $pre = ($r === 1) ? 'Seratus' : $satuan[$r] . ' Ratus';
            return $pre . ($s > 0 ? ' ' . $this->_terbilang($s) : '');
        }
        if ($angka < 1000000) {
            $r = (int)($angka / 1000);
            $s = $angka % 1000;
            $pre = ($r === 1) ? 'Seribu' : $this->_terbilang($r) . ' Ribu';
            return $pre . ($s > 0 ? ' ' . $this->_terbilang($s) : '');
        }
        if ($angka < 1000000000) {
            $r = (int)($angka / 1000000);
            $s = $angka % 1000000;
            return $this->_terbilang($r) . ' Juta' . ($s > 0 ? ' ' . $this->_terbilang($s) : '');
        }
        $r = (int)($angka / 1000000000);
        $s = $angka % 1000000000;
        return $this->_terbilang($r) . ' Miliar' . ($s > 0 ? ' ' . $this->_terbilang($s) : '');
    }
}

/* End of file Docx_generator.php */