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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Form gönderildiğinde yapılacak işlemler
    $ad = $_POST['ad'];
    $fiyat = $_POST['fiyat'];
    $kategori = $_POST['kategori'];
    $stok = $_POST['stok'];
    $resim_url = $_POST['resim_url']; // Yeni eklenen resim URL alanı

    // Resim yükleme veya URL kullanma
    if (!empty($resim_url)) {
        // Resim URL'i kullanılıyorsa
        $resim_yolu = $resim_url;
    } elseif (isset($_FILES['resim']) && $_FILES['resim']['error'] == UPLOAD_ERR_OK) {
        // Dosya yükleme kullanılıyorsa
        $resim_adi = $_FILES['resim']['name'];
        $resim_gecici_ad = $_FILES['resim']['tmp_name'];

        // Geçici resim adı oluştur (benzersiz - unique)
        $resim_yeni_ad = uniqid('urun_', true) . '.' . strtolower(pathinfo($resim_adi, PATHINFO_EXTENSION));

        $hedef_klasor = "../assets/images/"; // Resimlerin yükleneceği klasör, "panel" klasörünün dışında
        $hedef_dosya = $hedef_klasor . $resim_yeni_ad;

        if (move_uploaded_file($resim_gecici_ad, $hedef_dosya)) {
            $resim_yolu = "assets/images/" . $resim_yeni_ad;
        } else {
            $error = "Resim yüklenirken bir hata oluştu.";
        }
    } else {
        $error = "Lütfen bir resim dosyası seçin veya resim URL'i girin.";
    }

    // Hata yoksa veritabanına ekle
    if (empty($error)) {
        $sql = "INSERT INTO urunler (ad, resim, fiyat, kategori, stok) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdsi", $ad, $resim_yolu, $fiyat, $kategori, $stok);

        if ($stmt->execute()) {
            $success = "Ürün başarıyla eklendi.";
        } else {
            $error = "Ürün eklenirken bir hata oluştu: " . $stmt->error;
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Paneli - Ürün Ekle</title>
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
            <h2>Yeni Ürün Ekle</h2>
            <?php if (!empty($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <p class="success"><?php echo $success; ?></p>
            <?php endif; ?>
            <form action="" method="post" enctype="multipart/form-data">
                <label for="ad">Ürün Adı:</label>
                <input type="text" id="ad" name="ad" required><br>

                <label for="resim">Resim (Dosya):</label>
                <input type="file" id="resim" name="resim"><br>

                <label for="resim_url">Resim URL'i:</label>
                <input type="text" id="resim_url" name="resim_url"><br>

                <label for="fiyat">Fiyat:</label>
                <input type="number" id="fiyat" name="fiyat" step="0.01" required><br>

                <label for="kategori">Kategori:</label>
                <input type="text" id="kategori" name="kategori" required><br>

                <label for="stok">Stok:</label>
                <input type="number" id="stok" name="stok" required><br>

                <input type="submit" value="Ürün Ekle" class="button">
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/script.js"></script>
</body>
</html>