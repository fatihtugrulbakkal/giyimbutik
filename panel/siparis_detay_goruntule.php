<?php
session_start();

// Kullanıcının giriş yapıp yapmadığını ve yetkisini kontrol et
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || $_SESSION['admin_yetki'] !== 1) {
    header("Location: index.php"); // Yetkisiz kullanıcıyı panel giriş sayfasına yönlendir
    exit;
}

// Veritabanı bağlantısını dahil et
require_once 'db_baglanti.php';

// Sipariş ID'sini URL'den al ve kontrol et
$siparisId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($siparisId <= 0) {
    echo "Geçersiz sipariş numarası.";
    exit;
}

// Sipariş bilgilerini çek (kullanıcı bilgileri ile birleştir)
$stmt = $conn->prepare("SELECT s.*, k.kullanici_adi FROM siparisler s LEFT JOIN kullanicilar k ON s.kullanici_id = k.id WHERE s.id = ?");
$stmt->bind_param("i", $siparisId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $siparis = $result->fetch_assoc();
} else {
    echo "Sipariş bulunamadı.";
    exit;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Paneli - Sipariş Detayı</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#update-form').on('submit', function(e) {
                e.preventDefault();

                var form = $(this);
                var formData = form.serialize();

                $.ajax({
                    url: 'siparis_guncelle.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('Sipariş durumu başarıyla güncellendi.');
                            // Durum bilgisini güncelle
                            $('#siparis-durum').text(response.yeni_durum);
                        } else {
                            alert('Hata: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Bir hata oluştu.');
                    }
                });
            });
        });
    </script>
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
            <?php if (isset($siparis)): ?>
            <h2>Sipariş Detayı - #<?php echo $siparis['id']; ?></h2>
            <div class="siparis-bilgileri">
                <p><strong>Sipariş Tarihi:</strong> <?php echo date("d.m.Y H:i", strtotime($siparis['siparis_tarihi'])); ?></p>
                <p><strong>Durum:</strong> <span id="siparis-durum"><?php echo $siparis['durum']; ?></span></p>
                <p><strong>Müşteri:</strong> <?php echo $siparis['kullanici_adi']; ?></p>
                <p><strong>Ad:</strong> <?php echo htmlspecialchars($siparis['ad']); ?></p>
                <p><strong>Soyad:</strong> <?php echo htmlspecialchars($siparis['soyad']); ?></p>
                <p><strong>E-posta:</strong> <?php echo htmlspecialchars($siparis['eposta']); ?></p>
                <p><strong>Telefon:</strong> <?php echo htmlspecialchars($siparis['telefon']); ?></p>
            </div>

            <form id="update-form" action="siparis_guncelle.php" method="post">
                <input type="hidden" name="id" value="<?php echo $siparis['id']; ?>">

                <label for="durum">Sipariş Durumu:</label>
                <select name="durum" id="durum">
                    <?php
                    $durumlar = ["Onay Bekliyor", "Hazırlanıyor", "Kargoya Verildi", "Tamamlandı", "İptal Edildi"];
                    foreach ($durumlar as $durum) {
                        $selected = ($siparis['durum'] == $durum) ? "selected" : "";
                        echo "<option value='" . $durum . "' " . $selected . ">" . $durum . "</option>";
                    }
                    ?>
                </select>
                <button type="submit" class="button">Kaydet</button>
            </form>

            <table id="urunler-table">
                <thead>
                    <tr>
                        <th>Ürün ID</th>
                        <th>Ürün Adı</th>
                        <th>Adet</th>
                        <th>Birim Fiyat</th>
                        <th>Toplam Fiyat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Sipariş edilen ürünleri çek
                    $urunlerStmt = $conn->prepare("SELECT * FROM siparis_urunler WHERE siparis_id = ?");
                    $urunlerStmt->bind_param("i", $siparisId);
                    $urunlerStmt->execute();
                    $urunlerResult = $urunlerStmt->get_result();
                    while ($urun = $urunlerResult->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $urun['urun_id'] . "</td>";
                        echo "<td>" . htmlspecialchars($urun['urun_adi']) . "</td>";
                        echo "<td>" . $urun['adet'] . "</td>";
                        echo "<td>" . $urun['birim_fiyat'] . " TL</td>";
                        echo "<td>" . ($urun['adet'] * $urun['birim_fiyat']) . " TL</td>";
                        echo "</tr>";
                    }
                    $urunlerStmt->close();
                    ?>
                </tbody>
            </table>

            <div class="toplam-tutar">
                <strong>Toplam Tutar:</strong> <?php echo $siparis['toplam_tutar']; ?> TL
            </div>
            <?php else: ?>
            <p>Sipariş bulunamadı veya yetkiniz yok.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/script.js"></script>
</body>
</html>

<?php
$conn->close();
?>