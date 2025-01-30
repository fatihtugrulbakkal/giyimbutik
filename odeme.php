<?php
session_start();

// Kullanıcının giriş yapıp yapmadığını kontrol et
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php"); // Giriş yapmamışsa ana sayfaya yönlendir
    exit;
}

// Veritabanı bağlantısını dahil et
require_once 'db_baglanti.php';

$error = "";
$message = "";

// Sepeti onayla
if (isset($_POST['sepeti_onayla'])) {
    $cart = isset($_COOKIE['sepet']) ? json_decode(urldecode($_COOKIE['sepet']), true) : [];

    if (count($cart) > 0) {
        // Kullanıcının bakiyesini çek
        $userId = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT bakiye FROM kullanicilar WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $bakiye = $user['bakiye'];
        } else {
            $error = "Kullanıcı bilgileri bulunamadı.";
            $stmt->close();
            $conn->close();
            exit;
        }
        $stmt->close();

        // Toplam tutarı hesapla
        $totalPrice = 0;
        foreach ($cart as $item) {
            $productId = $item['id'];
            $stmt = $conn->prepare("SELECT fiyat FROM urunler WHERE id = ?");
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $product = $result->fetch_assoc();
                $totalPrice += $product['fiyat'] * $item['quantity'];
            } else {
                $error = "Ürün bilgileri bulunamadı.";
                $stmt->close();
                $conn->close();
                exit;
            }
            $stmt->close();
        }

        // Bakiye yeterli mi kontrol et
        if ($bakiye >= $totalPrice) {
            // Bakiyeden düş
            $newBalance = $bakiye - $totalPrice;
            $stmt = $conn->prepare("UPDATE kullanicilar SET bakiye = ? WHERE id = ?");
            $stmt->bind_param("di", $newBalance, $userId);
            $stmt->execute();
            $stmt->close();

            // Sipariş bilgilerini al (ad, soyad, e-posta, telefon)
            $userInfoStmt = $conn->prepare("SELECT ad, soyad, eposta, telefon FROM kullanicilar WHERE id = ?");
            $userInfoStmt->bind_param("i", $userId);
            $userInfoStmt->execute();
            $userInfoResult = $userInfoStmt->get_result();
            if ($userInfoResult->num_rows > 0) {
                $user = $userInfoResult->fetch_assoc();
                $ad = $user['ad'];
                $soyad = $user['soyad'];
                $eposta = $user['eposta'];
                $telefon = $user['telefon'];
            } else {
                $error = "Kullanıcı bilgileri alınırken bir hata oluştu.";
                exit;
            }
            $userInfoStmt->close();

            // Siparişi veritabanına kaydet
            $siparisTarihi = date("Y-m-d H:i:s"); // Şu anki tarih ve saat
            $siparisDurumu = "Onay Bekliyor"; // Varsayılan sipariş durumu

            // Sipariş bilgilerini ekle
            $insertOrderStmt = $conn->prepare("INSERT INTO siparisler (kullanici_id, ad, soyad, eposta, telefon, toplam_tutar, siparis_tarihi, durum) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $insertOrderStmt->bind_param("isssssss", $userId, $ad, $soyad, $eposta, $telefon, $totalPrice, $siparisTarihi, $siparisDurumu);
            $insertOrderStmt->execute();

            // Son eklenen siparişin ID'sini al
            $siparisId = $conn->insert_id;
            $insertOrderStmt->close();

            // Sipariş ürünlerini ekle
            foreach ($cart as $item) {
                $productId = $item['id'];
                $quantity = $item['quantity'];

                // Ürünün güncel fiyatını ve adını çek
                $productStmt = $conn->prepare("SELECT ad, fiyat FROM urunler WHERE id = ?");
                $productStmt->bind_param("i", $productId);
                $productStmt->execute();
                $productResult = $productStmt->get_result();
                if ($productResult->num_rows > 0) {
                    $product = $productResult->fetch_assoc();
                    $urunAdi = $product['ad'];
                    $urunFiyat = $product['fiyat'];
                } else {
                    $error = "Ürün bilgileri güncellenirken bir hata oluştu.";
                    exit;
                }
                $productStmt->close();

                // Sipariş ürünlerini ekle
                $insertProductStmt = $conn->prepare("INSERT INTO siparis_urunler (siparis_id, urun_id, urun_adi, adet, birim_fiyat) VALUES (?, ?, ?, ?, ?)");
                $insertProductStmt->bind_param("iissi", $siparisId, $productId, $urunAdi, $quantity, $urunFiyat);
                $insertProductStmt->execute();
                $insertProductStmt->close();
            }

            // Sepeti boşalt
            setcookie('sepet', '', time() - 3600, '/'); // Cookie'yi sil
            unset($_SESSION['sepet']); // Oturumdaki sepet değişkenini temizle (eğer varsa)
            echo "<script>
            localStorage.removeItem('sepet');
            </script>"; // Local Storage'ı temizle

            // Başarılı mesajı değişkene ata
            $message = "Siparişiniz başarıyla alındı! Ödeme işleminiz tamamlandı.";

            // İşlem başarılı olduktan sonra, sepet sayısını güncellemek için JavaScript kodunu ekleyin
            echo "<script>
                if (typeof updateCartIcon === 'function') {
                    updateCartIcon();
                }
            </script>";
        } else {
            $error = "Yetersiz bakiye. Lütfen bakiyenizi kontrol edin.";
        }
    } else {
        $error = "Sepetiniz boş.";
    }
}

// Sepet bilgisini al ve toplam tutarı hesapla (sayfa ilk yüklendiğinde)
$cart = isset($_COOKIE['sepet']) ? json_decode(urldecode($_COOKIE['sepet']), true) : [];
$totalPrice = 0;
if (count($cart) > 0) {
    foreach ($cart as $item) {
        $productId = $item['id'];
        $stmt = $conn->prepare("SELECT fiyat FROM urunler WHERE id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            $totalPrice += $product['fiyat'] * $item['quantity'];
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ödeme Yap - GiyimButik</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="style.css">
    <style>
        /* Ödeme sayfası için ek stiller */
        #payment {
            padding: 40px 0;
        }
        #payment h2 {
            margin-bottom: 30px;
            font-weight: 700;
        }
        .payment-form {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 600px;
            margin: 0 auto;
        }
        .payment-form .button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            background-color: #5cb85c;
            color: white;
            transition: background-color 0.3s ease;
        }
        .payment-form .button:hover {
            background-color: #4cae4c;
        }
        .error {
            color: #d9534f;
            margin-bottom: 15px;
        }
        .success {
            color: #5cb85c;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<header>
    <div class="container">
        <div class="logo">
            <a href="/">
            <?php
            $sql = "SELECT yeni_metin FROM site_ayarlari WHERE anahtar = 'site_basligi'";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                echo htmlspecialchars_decode($row['yeni_metin']);
            } else {
                echo "GiyimButik";
            }
            ?>
            </a>
        </div>
        <button class="menu-toggle">
            <i class="fas fa-bars"></i>
        </button>
        <nav>
            <ul class="menu">
                <li><a href="#">
                <?php
                $sql = "SELECT yeni_metin FROM site_ayarlari WHERE anahtar = 'yeni_gelenler'";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    echo htmlspecialchars_decode($row['yeni_metin']);
                } else {
                    echo "Yeni Gelenler";
                }
                ?>
                </a></li>
                <li><a href="#">
                <?php
                $sql = "SELECT yeni_metin FROM site_ayarlari WHERE anahtar = 'kadin'";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    echo htmlspecialchars_decode($row['yeni_metin']);
                } else {
                    echo "Kadın";
                }
                ?>
                </a></li>
                <li><a href="#">
                <?php
                $sql = "SELECT yeni_metin FROM site_ayarlari WHERE anahtar = 'erkek'";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    echo htmlspecialchars_decode($row['yeni_metin']);
                } else {
                    echo "Erkek";
                }
                ?>
                </a></li>
                <li><a href="#">
                <?php
                $sql = "SELECT yeni_metin FROM site_ayarlari WHERE anahtar = 'cocuk'";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    echo htmlspecialchars_decode($row['yeni_metin']);
                } else {
                    echo "Çocuk";
                }
                ?>
                </a></li>
                <li><a href="#">
                <?php
                $sql = "SELECT yeni_metin FROM site_ayarlari WHERE anahtar = 'indirim'";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    echo htmlspecialchars_decode($row['yeni_metin']);
                } else {
                    echo "İndirim";
                }
                ?>
                </a></li>
            </ul>
            <ul class="user-actions">
                <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                    <li><a href="sepetim.php">
                    <?php
                    $sql = "SELECT yeni_metin FROM site_ayarlari WHERE anahtar = 'sepetim'";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        echo htmlspecialchars_decode($row['yeni_metin']);
                    } else {
                        echo "Sepetim";
                    }
                    ?>
                    (<?php echo isset($_COOKIE['sepet']) ? count(json_decode(urldecode($_COOKIE['sepet']), true)) : 0; ?>)</a></li>
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

    <section id="payment">
        <div class="container">
            <h2>Ödeme Yap</h2>
            <?php if (!empty($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php elseif (!empty($message)): ?>
                <p class="success"><?php echo $message; ?></p>
                <p>Ana sayfaya yönlendiriliyorsunuz...</p>
            <?php else: ?>
                <form action="" method="post" class="payment-form">
                    <p>Ödenecek Tutar: <?php echo $totalPrice; ?> TL</p>
                    <button type="submit" name="sepeti_onayla" class="button">Sepeti Onayla ve Ödemeyi Tamamla</button>
                </form>
            <?php endif; ?>
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
    <?php
        $sql = "SELECT yeni_metin FROM site_ayarlari WHERE anahtar = 'footer_yazi'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo htmlspecialchars_decode($row['yeni_metin']);
        } else {
            echo "<p>&copy; 2023 GiyimButik. Tüm hakları saklıdır.</p>";
        }
        $conn->close();