<?php
session_start();

// Kullanıcının giriş yapıp yapmadığını ve yetkisini kontrol et
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || $_SESSION['admin_yetki'] !== 1) {
    header("Location: index.php"); // Yetkisiz kullanıcıyı panel giriş sayfasına yönlendir
    exit;
}

// Veritabanı bağlantısını dahil et
require_once 'db_baglanti.php';

// Kategorileri çek
$sql = "SELECT * FROM kategoriler";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Paneli - Kategoriler</title>
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
            <h2>Kategoriler</h2>

            <a href="kategori_ekle.php" class="button">Yeni Kategori Ekle</a>

            <table id="kategoriler-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ad</th>
                        <th>Açıklama</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row["id"] . "</td>";
                            echo "<td>" . $row["ad"] . "</td>";
                            echo "<td>" . $row["aciklama"] . "</td>";
                            echo "<td>";
                            echo "<div class='button-group'>";
                            echo "<a href='kategori_duzenle.php?id=" . $row["id"] . "' class='button'>Düzenle</a>";
                            echo "<a href='kategori_sil.php?id=" . $row["id"] . "' class='button'>Sil</a>";
                            echo "</div>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>Kategori bulunamadı.</td></tr>";
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