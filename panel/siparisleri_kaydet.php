<?php
session_start();

// Kullanıcının giriş yapıp yapmadığını ve yetkisini kontrol et
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || $_SESSION['admin_yetki'] !== 1) {
    exit; // Yetkisiz kullanıcılar için betiği sonlandır
}

require_once 'db_baglanti.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['siparisler'])) {
    $degisiklikler = $_POST['siparisler'];

    foreach ($degisiklikler as $degisiklik) {
        $siparisId = $degisiklik['id'];
        $yeniDurum = $degisiklik['durum'];

        $stmt = $conn->prepare("UPDATE siparisler SET durum = ? WHERE id = ?");
        $stmt->bind_param("si", $yeniDurum, $siparisId);
        $stmt->execute();
        $stmt->close();
    }

    echo "Değişiklikler kaydedildi.";
}

$conn->close();
?>