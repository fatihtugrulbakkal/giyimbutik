<?php
session_start();

// Kullanıcının giriş yapıp yapmadığını ve yetkisini kontrol et
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || $_SESSION['admin_yetki'] !== 1) {
    header("Location: index.php"); // Yetkisiz kullanıcıyı panel giriş sayfasına yönlendir
    exit;
}

// Veritabanı bağlantısını dahil et
require_once 'db_baglanti.php';

$error = "";
$success = "";

// Form gönderildiğinde güncelleme yap
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['guncelle'])) {
    foreach ($_POST as $anahtar => $yeni_deger) {
        if ($anahtar != 'guncelle') {
            $stmt = $conn->prepare("SELECT dosya, eski_metin, eski_deger FROM site_ayarlari WHERE anahtar = ?");
            $stmt->bind_param("s", $anahtar);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $dosya_yolu = $row['dosya'];
                $eski_metin = $row['eski_metin'];
                $eski_deger = $row['eski_deger'];

                // Sadece metin ve buton yazısı güncellemelerini işle
                if (strpos($anahtar, '_yazisi') !== false || strpos($anahtar, '_metin') !== false) {
                    // Dosya yolunu güncellemeden önce, gerçek dosya yolunu kontrol et
                    if ($dosya_yolu == 'header') {
                        // Eğer dosya 'header' olarak belirtilmişse, tüm sayfa dosyalarını güncelle
                        $sayfa_dosyalari = ['../index.php', '../urunler.php', '../sepetim.php', '../profil.php'];
                        foreach ($sayfa_dosyalari as $dosya_yolu) {
                            if (file_exists($dosya_yolu)) {
                                $dosya_icerigi = file_get_contents($dosya_yolu);
                                $yeni_icerik = str_replace($eski_metin, $yeni_deger, $dosya_icerigi);
                                file_put_contents($dosya_yolu, $yeni_icerik);
                            } else {
                                $error = "Dosya bulunamadı: " . $dosya_yolu;
                            }
                        }
                    } else {
                        $gercek_dosya_yolu = $dosya_yolu;
                        if (file_exists($gercek_dosya_yolu)) {
                            $dosya_icerigi = file_get_contents($gercek_dosya_yolu);
                            $yeni_icerik = str_replace($eski_metin, htmlspecialchars($yeni_deger), $dosya_icerigi);
                            file_put_contents($gercek_dosya_yolu, $yeni_icerik);
                        } else {
                            $error = "Dosya bulunamadı: " . $gercek_dosya_yolu;
                        }
                    }

                    // Veritabanını güncelle
                    if (empty($error)) {
                        $update_stmt = $conn->prepare("UPDATE site_ayarlari SET yeni_metin = ? WHERE anahtar = ?");
                        $update_stmt->bind_param("ss", $yeni_deger, $anahtar);

                        if ($update_stmt->execute()) {
                            $success = "Ayarlar başarıyla güncellendi.";
                            // Güncellenen değeri $ayarlar dizisinde de güncelle
                            if (isset($ayarlar[$anahtar])) {
                                $ayarlar[$anahtar]['yeni_metin'] = $yeni_deger;
                            }
                        } else {
                            $error = "Veritabanı güncellenirken bir hata oluştu: " . $update_stmt->error;
                        }
                        $update_stmt->close();
                    }
                }
            }
            $stmt->close();
        }
    }
}

// Güncellenebilir metinleri ve görselleri veritabanından çek
$sql = "SELECT * FROM site_ayarlari WHERE anahtar NOT LIKE '%_resim%'";
$result = $conn->query($sql);
$ayarlar = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $ayarlar[$row['anahtar']] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Paneli - Ayarlar</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .content form {
            margin-top: 20px;
        }
        .content label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .content textarea, .content input[type="text"], .content input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .content .button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            background-color: #d9534f;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .content .button:hover {
            background-color: #c9302c;
        }
        .content .error {
            color: #d9534f;
            margin-bottom: 15px;
        }
        .content .success {
            color: #5cb85c;
            margin-bottom: 15px;
        }
    </style>
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
            <h2>Site Ayarları</h2>

            <?php if (!empty($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <p class="success"><?php echo $success; ?></p>
            <?php endif; ?>

            <form action="" method="post" enctype="multipart/form-data">
                <?php foreach ($ayarlar as $anahtar => $ayar): ?>
                    <?php if (strpos($anahtar, '_yazisi') !== false): ?>
                        <label for="<?php echo $anahtar; ?>"><?php echo ucfirst(str_replace('_', ' ', $anahtar)); ?>:</label>
                        <input type="text" id="<?php echo $anahtar; ?>" name="<?php echo $anahtar; ?>" value="<?php echo $ayar['yeni_metin']; ?>" required><br>
                    <?php else: ?>
                        <label for="<?php echo $anahtar; ?>"><?php echo ucfirst(str_replace('_', ' ', $anahtar)); ?>:</label>
                        <textarea id="<?php echo $anahtar; ?>" name="<?php echo $anahtar; ?>" rows="4" cols="50" required><?php echo htmlspecialchars_decode($ayar['yeni_metin']); ?></textarea><br>
                    <?php endif; ?>
                <?php endforeach; ?>

                <input type="submit" name="guncelle" value="Ayarları Güncelle" class="button">
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/script.js"></script>
</body>
</html>