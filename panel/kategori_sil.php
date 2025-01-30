<?php
session_start();

// Kullanıcının giriş yapıp yapmadığını ve yetkisini kontrol et
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || $_SESSION['admin_yetki'] !== 1) {
    header("Location: index.php"); // Yetkisiz kullanıcıyı panel giriş sayfasına yönlendir
    exit;
}

require_once 'db_baglanti.php';

$error = "";
$success = "";
$kategori_adi = ""; // Silinecek kategori adını burada tanımla

if (isset($_GET['id'])) {
    $kategori_id = intval($_GET['id']);

    // Kategori adını çek, silme onayı mesajında göstermek için
    $stmt = $conn->prepare("SELECT ad FROM kategoriler WHERE id = ?");
    $stmt->bind_param("i", $kategori_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $kategori = $result->fetch_assoc();
        $kategori_adi = $kategori['ad'];
    } else {
        $error = "Kategori bulunamadı.";
    }
    $stmt->close();

    // Silme işlemi onayı alındıysa
    if (isset($_POST['confirm_delete'])) {
        // Kategoriye ait ürünleri kontrol et
        $checkStmt = $conn->prepare("SELECT COUNT(*) as urunSayisi FROM urunler WHERE kategori_id = ?");
        $checkStmt->bind_param("i", $kategori_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $urunSayisi = $checkResult->fetch_assoc()['urunSayisi'];
        $checkStmt->close();

        if ($urunSayisi > 0) {
            $error = "Bu kategoriye ait ürünler bulunmaktadır. Önce ürünleri silin veya başka bir kategoriye taşıyın.";
        } else {
            // Kategoriyi sil
            $stmt = $conn->prepare("DELETE FROM kategoriler WHERE id = ?");
            $stmt->bind_param("i", $kategori_id);

            if ($stmt->execute()) {
                $success = "Kategori başarıyla silindi.";
                header("Location: kategoriler.php"); // Silme işleminden sonra kategori listesine yönlendir
                exit;
            } else {
                $error = "Kategori silinirken bir hata oluştu: " . $stmt->error;
            }

            $stmt->close();
        }
    }
} else {
    $error = "Kategori ID'si belirtilmemiş.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Paneli - Kategori Sil</title>
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
                <li><a href="kategoriler.php">Kategoriler</a></li>
                <li><a href="yorumlar.php">Yorumlar</a></li>
                <li><a href="raporlar.php">Raporlar</a></li>
                <li><a href="ayarlar.php">Ayarlar</a></li>
            </ul>
        </div>
        <div class="content">
            <h2>Kategori Sil</h2>

            <?php if (!empty($error)): ?>
                <p class="error"><?php echo $error; ?></p>
                <a href="kategoriler.php" class="button">Kategori Listesine Dön</a>
            <?php elseif (!empty($success)): ?>
                <p class="success"><?php echo $success; ?></p>
                <a href="kategoriler.php" class="button">Kategori Listesine Dön</a>
            <?php else: ?>
                <p>Aşağıdaki kategoriyi silmek istediğinize emin misiniz?</p>
                <p><strong>Kategori Adı:</strong> <?php echo htmlspecialchars($kategori_adi); ?></p>
                <form action="" method="post">
                    <input type="hidden" name="confirm_delete" value="1">
                    <button type="submit" class="button">Evet, Sil</button>
                    <a href="kategoriler.php" class="button">İptal</a>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/script.js"></script>
</body>
</html>