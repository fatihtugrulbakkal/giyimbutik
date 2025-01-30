<?php
session_start();

// Kullanıcının giriş yapıp yapmadığını ve yetkisini kontrol et
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || $_SESSION['admin_yetki'] !== 1) {
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim.']);
    exit;
}

require_once 'db_baglanti.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = $_POST['id'];
    $newYetki = $_POST['yetki'];

    $stmt = $conn->prepare("UPDATE kullanicilar SET yetki = ? WHERE id = ?");
    $stmt->bind_param("ii", $newYetki, $userId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Yetki güncellenirken bir hata oluştu.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek.']);
}
?>