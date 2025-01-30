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
$user = null;

// Düzenlenecek kullanıcının bilgilerini çek
if (isset($_GET['id'])) {
    $userId = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM kullanicilar WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
    } else {
        $error = "Kullanıcı bulunamadı.";
    }
    $stmt->close();
} else {
    $error = "Kullanıcı ID'si belirtilmemiş.";
}

// Form gönderildiyse, güncelleme işlemini yap
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $userId = $_POST['id'];
    $username = $_POST['username'];
    $ad = $_POST['ad'];
    $soyad = $_POST['soyad'];
    $email = $_POST['email'];
    $yetki = $_POST['yetki'];

    $stmt = $conn->prepare("UPDATE kullanicilar SET kullanici_adi = ?, ad = ?, soyad = ?, eposta = ?, yetki = ? WHERE id = ?");
    $stmt->bind_param("ssssii", $username, $ad, $soyad, $email, $yetki, $userId);

    if ($stmt->execute()) {
        $success = "Kullanıcı başarıyla güncellendi.";
        // Güncellenmiş bilgileri tekrar çek
        $user = ['kullanici_adi' => $username, 'ad' => $ad, 'soyad' => $soyad, 'eposta' => $email, 'yetki' => $yetki];
    } else {
        $error = "Kullanıcı güncellenirken bir hata oluştu: " . $stmt->error;
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
    <title>Yönetici Paneli - Kullanıcı Düzenle</title>
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
            <h2>Kullanıcı Düzenle</h2>

            <?php if (!empty($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <p class="success"><?php echo $success; ?></p>
            <?php endif; ?>

            <?php if ($user): ?>
                <form action="" method="post">
                    <input type="hidden" name="id" value="<?php echo $userId; ?>">
                    <input type="hidden" name="update" value="1">
                    <label for="username">Kullanıcı Adı:</label>
                    <input type="text" id="username" name="username" value="<?php echo $user['kullanici_adi']; ?>" required><br>

                    <label for="ad">Ad:</label>
                    <input type="text" id="ad" name="ad" value="<?php echo $user['ad']; ?>"><br>

                    <label for="soyad">Soyad:</label>
                    <input type="text" id="soyad" name="soyad" value="<?php echo $user['soyad']; ?>"><br>

                    <label for="email">E-posta:</label>
                    <input type="email" id="email" name="email" value="<?php echo $user['eposta']; ?>" required><br>

                    <label for="yetki">Yetki:</label>
                    <select id="yetki" name="yetki">
                        <option value="0" <?php echo ($user['yetki'] == 0 ? 'selected' : ''); ?>>Normal</option>
                        <option value="1" <?php echo ($user['yetki'] == 1 ? 'selected' : ''); ?>>Yönetici</option>
                    </select><br>

                    <input type="submit" value="Güncelle" class="button">
                </form>
            <?php else: ?>
                <p>Kullanıcı bulunamadı.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/script.js"></script>
</body>
</html>