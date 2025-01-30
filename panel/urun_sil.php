<?php
session_start();

// Kullanıcının giriş yapıp yapmadığını kontrol et
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

require_once 'db_baglanti.php';

$error = "";
$success = "";

if (isset($_GET['id'])) {
    $urun_id = $_GET['id'];

    // Ürünü veritabanından sil
    $sql = "DELETE FROM urunler WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $urun_id);

    if ($stmt->execute()) {
        $success = "Ürün başarıyla silindi.";
    } else {
        $error = "Ürün silinirken bir hata oluştu: " . $stmt->error;
    }

    $stmt->close();
} else {
    $error = "Ürün ID'si belirtilmemiş.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Paneli - Ürün Sil</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container dashboard-container">
        <div class="sidebar">
            <h3>Yönetim Paneli</h3>
            <ul>
                <li><a href="dashboard.php">Kontrol Paneli</a></li>
                <li><a href="urunler.php">Ürünler</a></li>
                <li><a href="kullanicilar.php">Kullanıcılar</a></li>
                <li><a href="siparisler.php">Siparişler</a></li>
                </ul>
        </div>
        <div class="content">
            <h2>Ürün Sil</h2>
            <?php if (!empty($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <p class="success"><?php echo $success; ?></p>
            <?php endif; ?>
            <a href="urunler.php" class="button">Ürün Listesine Dön</a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/script.js"></script>
</body>
</html>