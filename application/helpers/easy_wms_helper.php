<?php

/**
 * Mendapatkan jumlah karyawan
 */
function getJumlahStaff()
{
    $CI =& get_instance();
    return $CI->db->get('user')->num_rows();
}

/**
 * Mendapatkan jumlah supplier
 */
function getJumlahSupplier()
{
    $CI =& get_instance();
    return $CI->db->get('supplier')->num_rows();
}

/**
 * Mendapatkan jumlah supplier
 */
function getJumlahBarang()
{
    $CI =& get_instance();
    return $CI->db->where('qty !=', 0)->get('barang')->num_rows();
}

/**
 * Mendapatkan jumlah stok
 */
function getJumlahStok()
{
    $CI =& get_instance();
    $CI->db->select_sum('qty');
    $result = $CI->db->get('barang')->row();  
    return $result->qty;
}

/**
 * Mendapatkan seluruh list satuan barang
 */
function getUnits()
{
    $CI =& get_instance();
    $CI->db->where('status', 'valid');
    return $CI->db->get('satuan')->result();
}

/**
 * Mendapatkan satuan barang berdasarkan id
 */
function getUnitName($id_satuan)
{
    $CI =& get_instance();
    $CI->db->where('id', $id_satuan);
    return $CI->db->get('satuan')->row()->nama;
}

/**
 * Mendapatkan list supplier
 */
function getSuppliers()
{
    $CI =& get_instance();
    $CI->db->where('status', 'aktif');
    return $CI->db->get('supplier')->result();
}

/**
 * Mengenkripsi input
 */
function hashEncrypt($input)
{
    $hash = password_hash($input, PASSWORD_DEFAULT);
    return $hash;
}

/**
 * Mendecrypt hash password dari table user
 * Mengembalikan true jika plain-text sama
 */
function hashEncryptVerify($input, $hash)
{
    if (password_verify($input, $hash)) {
        return true;
    } else {
        return false;
    }
}
/**
 * Mengkonversi angka menjadi terbilang dalam Bahasa Indonesia
 * Support hingga ratusan triliun
 *
 * @param  int|float $angka
 * @return string
 */
function angkaTerbilang($angka)
{
    $angka = (int) round(abs($angka));

    $satuan = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima',
               'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh',
               'Sebelas', 'Dua Belas', 'Tiga Belas', 'Empat Belas', 'Lima Belas',
               'Enam Belas', 'Tujuh Belas', 'Delapan Belas', 'Sembilan Belas'];

    if ($angka === 0) return 'Nol';

    if ($angka < 20) return $satuan[$angka];

    if ($angka < 100) {
        $puluhan = (int)($angka / 10);
        $sisa    = $angka % 10;
        return $satuan[$puluhan] . ' Puluh' . ($sisa > 0 ? ' ' . $satuan[$sisa] : '');
    }

    if ($angka < 1000) {
        $ratusan = (int)($angka / 100);
        $sisa    = $angka % 100;
        $prefix  = ($ratusan === 1) ? 'Seratus' : $satuan[$ratusan] . ' Ratus';
        return $prefix . ($sisa > 0 ? ' ' . angkaTerbilang($sisa) : '');
    }

    if ($angka < 1000000) {
        $ribuan = (int)($angka / 1000);
        $sisa   = $angka % 1000;
        $prefix = ($ribuan === 1) ? 'Seribu' : angkaTerbilang($ribuan) . ' Ribu';
        return $prefix . ($sisa > 0 ? ' ' . angkaTerbilang($sisa) : '');
    }

    if ($angka < 1000000000) {
        $jutaan = (int)($angka / 1000000);
        $sisa   = $angka % 1000000;
        return angkaTerbilang($jutaan) . ' Juta' . ($sisa > 0 ? ' ' . angkaTerbilang($sisa) : '');
    }

    if ($angka < 1000000000000) {
        $miliar = (int)($angka / 1000000000);
        $sisa   = $angka % 1000000000;
        return angkaTerbilang($miliar) . ' Miliar' . ($sisa > 0 ? ' ' . angkaTerbilang($sisa) : '');
    }

    // Hingga ratusan triliun
    $triliun = (int)($angka / 1000000000000);
    $sisa    = $angka % 1000000000000;
    return angkaTerbilang($triliun) . ' Triliun' . ($sisa > 0 ? ' ' . angkaTerbilang($sisa) : '');
}
