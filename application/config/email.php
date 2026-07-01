<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Konfigurasi Email - Easy WMS
|--------------------------------------------------------------------------
| Notifikasi otomatis dikirim ke email admin (smtp_user) setiap ada
| transaksi barang masuk / barang keluar, dari semua user.
| Email konfirmasi juga dikirim ke admin, bukan ke user.
|
| SETUP:
|   1. Ganti 'smtp_user' dengan Gmail admin
|   2. Ganti 'smtp_pass' dengan App Password Gmail
|      (bukan password biasa — buat di: myaccount.google.com/apppasswords)
|   3. Pastikan 2-Step Verification Gmail sudah aktif
|--------------------------------------------------------------------------
*/

$config['protocol']     = 'smtp';
$config['smtp_host']    = 'ssl://smtp.googlemail.com';
$config['smtp_port']    = 465;
$config['smtp_timeout'] = 10;
$config['smtp_user']    = 'carlosimbolon23@gmail.com';   // <-- GANTI INI (email admin)
$config['smtp_pass']    = 'rwaj yabv seai pldv';         // <-- GANTI INI (App Password)
$config['charset']      = 'utf-8';
$config['newline']      = "\r\n";
$config['mailtype']     = 'html';
$config['wordwrap']     = TRUE;
$config['validate']     = FALSE;

/*
|--------------------------------------------------------------------------
| Email Admin
|--------------------------------------------------------------------------
| Email tujuan untuk semua notifikasi sistem dan konfirmasi checkout.
| Secara default sama dengan smtp_user di atas.
| Ganti nilai ini jika email admin berbeda dari email pengirim.
|--------------------------------------------------------------------------
*/
$config['admin_email']  = 'michaelcarlo865@gmail.com';
