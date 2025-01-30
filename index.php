<?php
session_start();
require_once 'db_baglanti.php';

// Site ayarlarını veritabanından çek
$siteAyarlari = [];
$sql = "SELECT anahtar, yeni_metin, yeni_deger FROM site_ayarlari";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $siteAyarlari[$row['anahtar']] = $row;
    }
}

// Varsayılan değerlerle eksik ayarları doldur
$defaultAyarlari = [
    'site_basligi' => ['yeni_metin' => 'GiyimButik'],
    'hero_baslik' => ['yeni_metin' => '<h2>Son Moda Trendleri Keşfedin</h2>'],
    'hero_aciklama' => ['yeni_metin' => '<p>Şıklığınızı tamamlayacak en yeni koleksiyonlar burada!</p>'],
    'alisverise_basla_yazisi' => ['yeni_metin' => 'Alışverişe Başla'],
    'footer_yazi' => ['yeni_metin' => '<p>&copy; 2023 GiyimButik. Tüm hakları saklıdır.</p>'],
    'yeni_gelenler' => ['yeni_metin' => 'Yeni Gelenler'],
    'kadin' => ['yeni_metin' => 'Kadın'],
    'erkek' => ['yeni_metin' => 'Erkek'],
    'cocuk' => ['yeni_metin' => 'Çocuk'],
    'indirim' => ['yeni_metin' => 'İndirim'],
    'sepetim' => ['yeni_metin' => 'Sepetim']
];

foreach ($defaultAyarlari as $anahtar => $varsayilan) {
    if (!isset($siteAyarlari[$anahtar])) {
        $siteAyarlari[$anahtar] = $varsayilan;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars_decode($siteAyarlari['site_basligi']['yeni_metin']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="/"><?php echo htmlspecialchars_decode($siteAyarlari['site_basligi']['yeni_metin']); ?></a>
            </div>
            <button class="menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <nav>
                <ul class="menu">
                    <?php
                    // Kategorileri veritabanından çek
                    $kategori_sorgu = "SELECT ad, link FROM kategoriler";
                    $kategori_sonuc = $conn->query($kategori_sorgu);
                    if ($kategori_sonuc->num_rows > 0) {
                        while ($kategori = $kategori_sonuc->fetch_assoc()) {
                            $kategori_adi = htmlspecialchars($kategori['ad']);
                            $link = !empty($kategori['link']) ? $kategori['link'] : '#'; // Link varsa onu kullan, yoksa # kullan
                            echo '<li><a href="kategori.php?kategori=' . urlencode($kategori_adi) . '">' . $kategori_adi . '</a></li>';
                        }
                    }
                    ?>
                </ul>
                <ul class="user-actions">
                    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                        <li><a href="sepetim.php"><?php echo htmlspecialchars_decode($siteAyarlari['sepetim']['yeni_metin']); ?> (<?php echo isset($_COOKIE['sepet']) ? count(json_decode(urldecode($_COOKIE['sepet']), true)) : 0; ?>)</a></li>
                        <?php
                        // Giriş yapmış kullanıcının kullanıcı adını çek
                        $userId = $_SESSION['user_id'];
                        $stmt = $conn->prepare("SELECT kullanici_adi FROM kullanicilar WHERE id = ?");
                        $stmt->bind_param("i", $userId);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($result->num_rows > 0) {
                            $user = $result->fetch_assoc();
                            $username = $user['kullanici_adi'];
                        }
                        $stmt->close();
                        ?>
                        <li><a href="profil.php" class="button button-secondary" id="profile-button">Merhaba, <?php echo $username; ?></a></li>
                        <li><a href="logout.php" class="button button-secondary" id="logout-button">Çıkış Yap</a></li>
                    <?php else: ?>
                        <li><a href="kayit.php" class="button button-secondary">Kayıt Ol</a></li>
                        <li><a href="#" class="button button-secondary" id="login-button">Giriş Yap</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <section id="hero">
        <div class="container">
            <div class="hero-content">
                <?php echo $siteAyarlari['hero_baslik']['yeni_metin']; ?>
                <?php echo $siteAyarlari['hero_aciklama']['yeni_metin']; ?>
                <a href="urunler.php" class="button" id="shop-now-button"><?php echo htmlspecialchars_decode($siteAyarlari['alisverise_basla_yazisi']['yeni_metin']); ?></a>
            </div>
        </div>
    </section>

    <section id="featured-products">
        <div class="container">
            <h2>Öne Çıkan Ürünler</h2>
            <div class="products">
                <?php
                // Ürünleri veritabanından çek
                $sql = "SELECT id, ad, resim, fiyat FROM urunler";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<div class="product">';
                        echo '<div class="product-image"><img src="' . $row["resim"] . '" alt="' . $row["ad"] . '"></div>';
                        echo '<div class="product-info"><h3>' . $row["ad"] . '</h3>';
                        echo '<p class="price">' . $row["fiyat"] . ' TL</p></div>';

                        // Giriş yapmış kullanıcılar için Sepete Ekle butonu, yapmamışlar için uyarı mesajı
                        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
                            echo '<div class="product-actions"><a href="#" class="button button-block add-to-cart-button" id="' . $row["id"] . '">Sepete Ekle</a></div>';
                        } else {
                            echo "<p>Ürünleri ve sepete ekleme özelliğini görmek için lütfen <a href='#' id='login-button'>giriş yapın</a>.</p>";
                        }

                        echo '</div>';
                    }
                } else {
                    echo "Ürün bulunamadı.";
                }
                $conn->close();
                ?>
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

    <footer>
        <div class="container">
            <?php echo $siteAyarlari['footer_yazi']['yeni_metin']; ?>
        </div>
    </footer>
    <script src="script.js"></script>
</body>
</html>