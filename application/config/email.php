<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Konfigurasi Email - Easy WMS
|--------------------------------------------------------------------------
| Notifikasi otomatis dikirim ke carlosimbolon23@gmail.com setiap ada
| transaksi barang masuk / barang keluar, dari semua user & admin.
|
| SETUP:
|   1. Ganti 'smtp_user' dengan Gmail lo
|   2. Ganti 'smtp_pass' dengan App Password Gmail
|      (bukan password biasa — buat di: myaccount.google.com/apppasswords)
|   3. Pastikan 2-Step Verification Gmail sudah aktif
|--------------------------------------------------------------------------
*/

$config['protocol']     = 'smtp';
$config['smtp_host']    = 'ssl://smtp.googlemail.com';
$config['smtp_port']    = 465;
$config['smtp_timeout'] = 10;
$config['smtp_user']    = 'carlosimbolon23@gmail.com';   // <-- GANTI INI
$config['smtp_pass']    = 'rwaj yabv seai pldv';       // <-- GANTI INI (App Password)
$config['charset']      = 'utf-8';
$config['newline']      = "\r\n";
$config['mailtype']     = 'html';
$config['wordwrap']     = TRUE;
$config['validate']     = FALSE;
