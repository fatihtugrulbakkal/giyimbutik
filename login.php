<?php
session_start(); // Oturumu başlat

// Veritabanı bağlantısını dahil et
require_once 'db_baglanti.php';

// POST ile gelen verileri al
$username = $_POST['username'];
$password = $_POST['password'];

// Kullanıcıyı veritabanında sorgula (SQL enjeksiyonuna karşı güvenli hale getirildi)
$stmt = $conn->prepare("SELECT id, sifre FROM kullanicilar WHERE kullanici_adi = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // Şifre kontrolü (GÜVENLİ DEĞİL - Sadece örnek)
    if ($password === $row['sifre']) { 
        // Oturum değişkenlerini ayarla
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['logged_in'] = true;

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Hatalı şifre.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Kullanıcı bulunamadı.']);
}

$stmt->close();
$conn->close();
?>