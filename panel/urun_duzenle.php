<?php
session_start();

// Kullanıcının giriş yapıp yapmadığını ve yetkisini kontrol et
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || $_SESSION['admin_yetki'] !== 1) {
    header("Location: index.php");
    exit;
}

require_once 'db_baglanti.php';

$error = "";
$success = "";
$urun = null;

// Düzenlenecek ürünün bilgilerini çek
if (isset($_GET['id'])) {
    $urun_id = $_GET['id'];
    $sql = "SELECT * FROM urunler WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $urun_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $urun = $result->fetch_assoc();
    } else {
        $error = "Ürün bulunamadı.";
    }
    $stmt->close();
}

// Form gönderildiğinde yapılacak işlemler
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $ad = $_POST['ad'];
    $fiyat = $_POST['fiyat'];
    $kategori_id = $_POST['kategori_id'];
    $stok = $_POST['stok'];
    $urun_id = $_POST['id'];
    $resim_yolu = $urun['resim']; // Mevcut resim yolunu varsayılan olarak kullan

    // Resim güncelleme işlemi
    if (isset($_FILES['resim']) && $_FILES['resim']['error'] == UPLOAD_ERR_OK) {
        // Yeni resim dosyası yükleniyorsa
        $resim_adi = $_FILES['resim']['name'];
        $resim_gecici_ad = $_FILES['resim']['tmp_name'];
        $hedef_klasor = "../assets/images/"; // Resimlerin yükleneceği klasör
        $resim_yeni_ad = uniqid('urun_', true) . '.' . strtolower(pathinfo($resim_adi, PATHINFO_EXTENSION));
        $hedef_dosya = $hedef_klasor . $resim_yeni_ad;

        if (move_uploaded_file($resim_gecici_ad, $hedef_dosya)) {
            $resim_yolu = "assets/images/" . $resim_yeni_ad;
        } else {
            $error = "Resim yüklenirken bir hata oluştu.";
        }
    }

    // Hata yoksa veritabanında güncelle
    if (empty($error)) {
        $sql = "UPDATE urunler SET ad = ?, fiyat = ?, kategori_id = ?, stok = ?, resim = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdissi", $ad, $fiyat, $kategori_id, $stok, $resim_yolu, $urun_id);

        if ($stmt->execute()) {
            $success = "Ürün başarıyla güncellendi.";
            // Güncellenmiş ürün bilgilerini tekrar çek
            $urun = array_merge($urun, ['ad' => $ad, 'fiyat' => $fiyat, 'kategori_id' => $kategori_id, 'stok' => $stok, 'resim' => $resim_yolu]);
        } else {
            $error = "Ürün güncellenirken bir hata oluştu: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Kategorileri çek
$kategoriler = [];
$kategori_sql = "SELECT id, ad FROM kategoriler";
$kategori_result = $conn->query($kategori_sql);
if ($kategori_result->num_rows > 0) {
    while($kategori_row = $kategori_result->fetch_assoc()) {
        $kategoriler[$kategori_row['id']] = $kategori_row['ad'];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Paneli - Ürün Düzenle</title>
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
            <h2>Ürün Düzenle</h2>
            <?php if (!empty($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <p class="success"><?php echo $success; ?></p>
            <?php endif; ?>
            <?php if ($urun): ?>
                <form action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $urun['id']; ?>">
                    <label for="ad">Ürün Adı:</label>
                    <input type="text" id="ad" name="ad" value="<?php echo $urun['ad']; ?>" required><br>

                    <label for="resim">Resim:</label>
                    <input type="file" id="resim" name="resim"><br>
                    <img src="../<?php echo $urun['resim']; ?>" alt="<?php echo $urun['ad']; ?>" style="width: 100px; height: auto;"><br>

                    <label for="fiyat">Fiyat:</label>
                    <input type="number" id="fiyat" name="fiyat" step="0.01" value="<?php echo $urun['fiyat']; ?>" required><br>

                    <label for="kategori_id">Kategori:</label>
                    <select id="kategori_id" name="kategori_id">
                        <?php foreach ($kategoriler as $id => $kategori_ad): ?>
                            <option value="<?php echo $id; ?>" <?php echo ($urun['kategori_id'] == $id) ? 'selected' : ''; ?>><?php echo $kategori_ad; ?></option>
                        <?php endforeach; ?>
                    </select><br>

                    <label for="stok">Stok:</label>
                    <input type="number" id="stok" name="stok" value="<?php echo $urun['stok']; ?>" required><br>

                    <input type="submit" name="update" value="Ürün Güncelle" class="button">
                </form>
            <?php else: ?>
                <p>Düzenlenecek ürün bulunamadı.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/script.js"></script>
</body>
</html>