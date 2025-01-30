<?php
session_start();
require_once 'db_baglanti.php';

// Kategori adını URL'den al
$kategori_adi = isset($_GET['kategori']) ? $_GET['kategori'] : '';

// Sayfa başlığını ve h2 etiketini kategori ismine göre ayarla
$page_title = $kategori_adi ? ucfirst(htmlspecialchars($kategori_adi)) . " - GiyimButik" : "GiyimButik";

// Kategoriye ait ürünleri çek
$urunler = [];
if (!empty($kategori_adi)) {
    $stmt = $conn->prepare("SELECT u.id, u.ad, u.resim, u.fiyat FROM urunler u LEFT JOIN kategoriler k ON u.kategori_id = k.id WHERE k.ad = ?");
    $stmt->bind_param("s", $kategori_adi);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $urunler[] = $row;
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <section id="products">
        <div class="container">
            <h2><?php echo $kategori_adi = isset($_GET['kategori']) ? $_GET['kategori'] : ''; ?></h2>
            <div class="products">
                <?php if (count($urunler) > 0): ?>
                    <?php foreach ($urunler as $urun): ?>
                        <div class="product">
                            <div class="product-image">
                                <img src="<?php echo htmlspecialchars($urun['resim']); ?>" alt="<?php echo htmlspecialchars($urun['ad']); ?>">
                            </div>
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($urun['ad']); ?></h3>
                                <p class="price"><?php echo $urun['fiyat']; ?> TL</p>
                            </div>
                            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                                <div class="product-actions">
                                    <a href="#" class="button button-block add-to-cart-button" id="<?php echo $urun['id']; ?>">Sepete Ekle</a>
                                </div>
                            <?php else: ?>
                                <p>Ürünleri ve sepete ekleme özelliğini görmek için lütfen <a href="#" id="login-button">giriş yapın</a>.</p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Bu kategoriye ait ürün bulunamadı.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <div id="login-modal" class="modal">
      <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2>Giriş Yap</h2>
        <form id="login-form">
          <label for="username">Kullanıcı Adı:</label>
          <input type="text" id="username" name="username" required>

          <label for="password">Şifre:</label>
          <input type="password" id="password" name="password" required>

          <button type="submit" class="button">Giriş Yap</button>
        </form>
      </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="script.js"></script>

    <script src="script.js"></script>
    <script>
    // Sayfa yüklendiğinde tüm login-link elementlerine tıklama olayını dinle
    document.addEventListener('DOMContentLoaded', function() {
        var loginLinks = document.querySelectorAll('.login-link');
        loginLinks.forEach(link => {
            link.addEventListener('click', function(event) {
                event.preventDefault(); // Linkin varsayılan davranışını engelle
                document.getElementById('login-modal').style.display = 'block'; // Giriş modalını göster
            });
        });
    });
    </script>

</body>
</html>

<?php $conn->close(); ?>