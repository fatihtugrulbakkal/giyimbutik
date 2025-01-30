<?php
session_start();

// Kullanıcının giriş yapıp yapmadığını ve yetkisini kontrol et
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || $_SESSION['admin_yetki'] !== 1) {
    header("Location: index.php"); // Yetkisiz kullanıcıyı panel giriş sayfasına yönlendir
    exit;
}

// Veritabanı bağlantısını dahil et
require_once 'db_baglanti.php';

// Siparişleri çek
$sql = "SELECT s.id, s.kullanici_id, k.kullanici_adi, s.ad, s.soyad, s.eposta, s.telefon, s.toplam_tutar, s.siparis_tarihi, s.durum 
        FROM siparisler s
        LEFT JOIN kullanicilar k ON s.kullanici_id = k.id
        ORDER BY s.siparis_tarihi DESC";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Paneli - Siparişler</title>
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
            <h2>Siparişler</h2>

            <table id="orders-table">
                <thead>
                    <tr>
                        <th>Sipariş ID</th>
                        <th>Kullanıcı ID</th>
                        <th>Kullanıcı Adı</th>
                        <th>Ad</th>
                        <th>Soyad</th>
                        <th>E-posta</th>
                        <th>Telefon</th>
                        <th>Toplam Tutar</th>
                        <th>Sipariş Tarihi</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>#" . $row["id"] . "</td>";
                            echo "<td>" . $row["kullanici_id"] . "</td>";
                            echo "<td>" . $row["kullanici_adi"] . "</td>";
                            echo "<td>" . htmlspecialchars($row["ad"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["soyad"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["eposta"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["telefon"]) . "</td>";
                            echo "<td>" . $row["toplam_tutar"] . " TL</td>";
                            echo "<td>" . date("d.m.Y H:i", strtotime($row["siparis_tarihi"])) . "</td>";
                            echo "<td>";
                            echo "<div class='button-group'>";
                            echo "<a href='siparis_detay_goruntule.php?id=" . $row["id"] . "' class='button'>Detayları Gör ve Düzenle</a>";
                            echo "</div>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='10'>Sipariş bulunamadı.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>

<?php
$conn->close();
?>