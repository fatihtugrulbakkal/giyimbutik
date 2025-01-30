<?php
session_start();

// Kullanıcının giriş yapıp yapmadığını ve yetkisini kontrol et
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || $_SESSION['admin_yetki'] !== 1) {
    header("Location: index.php"); // Yetkisiz kullanıcıyı panel giriş sayfasına yönlendir
    exit;
}

// Veritabanı bağlantısını dahil et
require_once 'db_baglanti.php';

// Ürünleri ve kategorilerini çek
$sql = "SELECT u.id, u.ad, u.resim, u.fiyat, u.stok, k.ad AS kategori_adi
        FROM urunler u
        LEFT JOIN kategoriler k ON u.kategori_id = k.id";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Paneli - Ürünler</title>
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
            <h2>Ürünler</h2>

            <a href="urun_ekle.php" class="button">Yeni Ürün Ekle</a>

            <table id="products-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Resim</th>
                        <th>Ad</th>
                        <th>Fiyat</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row["id"] . "</td>";
                            echo "<td><img src='../" . $row["resim"] . "' alt='" . $row["ad"] . "' style='width: 50px; height: 50px;'></td>";
                            echo "<td>" . $row["ad"] . "</td>";
                            echo "<td>" . $row["fiyat"] . " TL</td>";
                            echo "<td>" . ($row["kategori_adi"] ?? 'Belirtilmemiş') . "</td>";
                            echo "<td>" . $row["stok"] . "</td>";
                            echo "<td>";
                            echo "<div class='button-group'>";
                            echo "<a href='urun_duzenle.php?id=" . $row["id"] . "' class='button'>Düzenle</a> ";
                            echo "<a href='urun_sil.php?id=" . $row["id"] . "' class='button' onclick='return confirm(\"Emin misiniz?\")'>Sil</a>";
                            echo "</div>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7'>Ürün bulunamadı.</td></tr>";
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