<?php
$servername = "localhost";
$username = "root"; // Veritabanı kullanıcı adınız
$password = ""; // Veritabanı şifreniz (GÜVENLİ DEĞİL)
$dbname = "giyimbutik";

// Bağlantıyı oluştur
$conn = new mysqli($servername, $username, $password, $dbname);

// Bağlantıyı kontrol et
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}
?>