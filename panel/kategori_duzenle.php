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
$kategori = null;

// Düzenlenecek kategori ID'sini al
$kategori_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Düzenlenecek kategori bilgilerini çek
if ($kategori_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM kategoriler WHERE id = ?");
    $stmt->bind_param("i", $kategori_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $kategori = $result->fetch_assoc();
    } else {
        $error = "Kategori bulunamadı.";
    }

    $stmt->close();
}

// Form gönderildiyse, güncelleme işlemini yap
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['guncelle'])) {
    $kategori_adi = $_POST['kategori_adi'];
    $aciklama = $_POST['aciklama'];

    $stmt = $conn->prepare("UPDATE kategoriler SET ad = ?, aciklama = ? WHERE id = ?");
    $stmt->bind_param("ssi", $kategori_adi, $aciklama, $kategori_id);

    if ($stmt->execute()) {
        $success = "Kategori başarıyla güncellendi.";
        // Güncellenmiş bilgileri tekrar çek
        $kategori['ad'] = $kategori_adi;
        $kategori['aciklama'] = $aciklama;
    } else {
        $error = "Kategori güncellenirken bir hata oluştu: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Paneli - Kategori Düzenle</title>
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
            <h2>Kategori Düzenle</h2>

            <?php if (!empty($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <p class="success"><?php echo $success; ?></p>
            <?php endif; ?>

            <?php if ($kategori): ?>
                <form action="" method="post">
                    <label for="kategori_adi">Kategori Adı:</label>
                    <input type="text" id="kategori_adi" name="kategori_adi" value="<?php echo $kategori['ad']; ?>" required><br>

                    <label for="aciklama">Açıklama:</label>
                    <textarea id="aciklama" name="aciklama"><?php echo $kategori['aciklama']; ?></textarea><br>

                    <input type="submit" name="guncelle" value="Kategoriyi Güncelle" class="button">
                </form>
            <?php else: ?>
                <p>Düzenlenecek kategori bulunamadı.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/script.js"></script>
</body>
</html>