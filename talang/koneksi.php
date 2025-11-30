<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'db_pendakian';

// Buat koneksi ke database
$db = new mysqli($host, $username, $password, $database);

// Juga buat alias lama agar file lain tetap bekerja
$koneksi = $db;

// Cek koneksi
if ($db->connect_error) {
    die("Koneksi gagal: " . $db->connect_error);
}

// Charset standar UTF-8
$db->set_charset("utf8");
?>
