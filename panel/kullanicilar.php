<?php
session_start();

// Kullanıcının giriş yapıp yapmadığını ve yetkisini kontrol et
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || $_SESSION['admin_yetki'] !== 1) {
    header("Location: index.php"); // Yetkisiz kullanıcıyı panel giriş sayfasına yönlendir
    exit;
}

// Veritabanı bağlantısını dahil et
require_once 'db_baglanti.php';

// Kullanıcıları çek
$sql = "SELECT id, kullanici_adi, ad, soyad, eposta, yetki, bakiye FROM kullanicilar";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Paneli - Kullanıcılar</title>
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
            <h2>Kullanıcılar</h2>

            <table id="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kullanıcı Adı</th>
                        <th>Ad</th>
                        <th>Soyad</th>
                        <th>E-posta</th>
                        <th>Bakiye</th>
                        <th>Yetki</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row["id"] . "</td>";
                            echo "<td>" . $row["kullanici_adi"] . "</td>";
                            echo "<td>" . $row["ad"] . "</td>";
                            echo "<td>" . $row["soyad"] . "</td>";
                            echo "<td>" . $row["eposta"] . "</td>";
                            echo "<td>" . $row["bakiye"] . " TL</td>";
                            echo "<td>" . ($row["yetki"] == 1 ? "Yönetici" : "Normal") . "</td>";
                            echo "<td>";
                            echo "<div class='button-group'>";
                            echo "<a href='bakiye_yukle.php?id=" . $row["id"] . "' class='button'>Bakiye Yükle</a>";
                            echo "<a href='kullanici_duzenle.php?id=" . $row["id"] . "' class='button'>Düzenle</a> ";
                            echo "<button class='button sil-button' data-id='" . $row["id"] . "'>Sil</button>";
                            echo "</div>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8'>Kullanıcı bulunamadı.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
        $(document).ready(function() {
            // Kullanıcı Silme
            $('#users-table').on('click', '.sil-button', function() {
                if (!confirm('Kullanıcıyı silmek istediğinize emin misiniz?')) return;

                const userId = $(this).data('id');
                const $this = $(this);

                $.ajax({
                    url: 'kullanici_sil.php',
                    type: 'POST',
                    data: { id: userId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('Kullanıcı başarıyla silindi.');
                            // Kullanıcıyı listeden kaldır veya sayfayı yenile
                            $this.closest('tr').remove(); // Butona en yakın tr elementini kaldırır
                            // location.reload(); // Sayfayı yenilemek için bu satırı da kullanabilirsiniz
                        } else {
                            alert('Kullanıcı silinirken bir hata oluştu.');
                        }
                    },
                    error: function() {
                        alert('Bir hata oluştu.');
                    }
                });
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>