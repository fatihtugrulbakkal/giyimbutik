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
$kullanici = null;

// Kullanıcı ID'sini URL'den al
$kullanici_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($kullanici_id <= 0) {
    $error = "Geçersiz kullanıcı ID'si.";
} else {
    // Kullanıcı bilgilerini çek
    $stmt = $conn->prepare("SELECT id, kullanici_adi, ad, soyad, bakiye FROM kullanicilar WHERE id = ?");
    $stmt->bind_param("i", $kullanici_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $kullanici = $result->fetch_assoc();
    } else {
        $error = "Kullanıcı bulunamadı.";
    }
    $stmt->close();

    // Form gönderildiyse, bakiye güncelleme işlemini yap
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['bakiye'])) {
        $yeni_bakiye = floatval($_POST['bakiye']);

        if ($yeni_bakiye > 0) {
            $stmt = $conn->prepare("UPDATE kullanicilar SET bakiye = bakiye + ? WHERE id = ?");
            $stmt->bind_param("di", $yeni_bakiye, $kullanici_id);

            if ($stmt->execute()) {
                $success = "Bakiye başarıyla güncellendi.";
                // Güncellenmiş bakiyeyi al
                $kullanici['bakiye'] += $yeni_bakiye;
            } else {
                $error = "Bakiye güncellenirken bir hata oluştu: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Lütfen geçerli bir bakiye miktarı girin.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Paneli - Bakiye Yükle</title>
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
            <h2>Bakiye Yükle</h2>

            <?php if (!empty($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <p class="success"><?php echo $success; ?></p>
            <?php endif; ?>

            <?php if ($kullanici): ?>
                <p><strong>Kullanıcı Adı:</strong> <?php echo $kullanici['kullanici_adi']; ?></p>
                <p><strong>Ad Soyad:</strong> <?php echo $kullanici['ad'] . ' ' . $kullanici['soyad']; ?></p>
                <p><strong>Mevcut Bakiye:</strong> <?php echo $kullanici['bakiye']; ?> TL</p>

                <form action="" method="post">
                    <label for="bakiye">Eklenecek Bakiye Miktarı (TL):</label>
                    <input type="number" id="bakiye" name="bakiye" min="0.01" step="0.01" required>
                    <button type="submit" class="button">Bakiyeyi Güncelle</button>
                </form>
            <?php else: ?>
                <p>Kullanıcı bulunamadı.</p>
            <?php endif; ?>
            <br>
            <a href="kullanicilar.php" class="button">Geri Dön</a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/script.js"></script>
</body>
</html>