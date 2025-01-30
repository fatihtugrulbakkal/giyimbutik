<?php
session_start();

// Kullanıcının giriş yapıp yapmadığını kontrol et
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php"); // Giriş yapmamışsa ana sayfaya yönlendir
    exit;
}

// Veritabanı bağlantısını dahil et
require_once 'db_baglanti.php';

// Kullanıcı bilgilerini çek
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT kullanici_adi, eposta, ad, soyad, telefon, dogum_tarihi, bakiye FROM kullanicilar WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $username = $user['kullanici_adi'];
    $email = $user['eposta'];
    $ad = $user['ad'];
    $soyad = $user['soyad'];
    $telefon = $user['telefon'];
    $dogumTarihi = $user['dogum_tarihi'];
    $bakiye = $user['bakiye'];
} else {
    // Kullanıcı bulunamazsa bir hata mesajı göster
    echo "Kullanıcı bilgileri bulunamadı.";
    exit;
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilim - GiyimButik</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #f4f4f4;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0;
        }
        #profile {
            padding: 40px 0;
        }
        .profile-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
            display: flex;
        }
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .profile-header h2 {
            color: #333;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .profile-content {
            flex-direction: column;
            width: 75%;
        }
        .profile-section {
            flex: 1;
            margin-right: 30px;
            display: none; /* Varsayılan olarak gizle */
        }
        .profile-section:first-child{
            display: block;
        }
        .profile-section h3 {
            color: #333;
            font-weight: 600;
            margin-bottom: 20px;
            border-bottom: 2px solid #d9534f;
            padding-bottom: 10px;
        }
        .profile-info p {
            margin: 10px 0;
            font-size: 16px;
            color: #555;
        }
        .profile-info strong {
            font-weight: 600;
            color: #333;
        }
        .profile-actions {
            text-align: center;
        }
        .button {
            background-color: #d9534f;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
            border: none;
            cursor: pointer;
            margin-right: 10px;
        }
        .button:hover {
            background-color: #c9302c;
        }
        .profile-form {
            margin-top: 30px;
        }
        .profile-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .profile-form input[type="text"],
        .profile-form input[type="email"],
        .profile-form input[type="tel"],
        .profile-form input[type="date"]{
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 15px;
            box-sizing: border-box;
            font-size: 16px;
        }
        .profile-form .button {
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
        .profile-form .button:hover {
            background-color: #4cae4c;
        }
        .profile-sidebar {
            width: 25%;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .profile-sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .profile-sidebar li {
            margin-bottom: 15px;
        }

        .profile-sidebar a {
            display: block;
            padding: 10px 15px;
            background-color: #f8f9fa;
            color: #333;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .profile-sidebar a:hover {
            background-color: #e9ecef;
        }
        .profile-sidebar a.active { /* Aktif link stili */
            background-color: #d9534f;
            color: #fff;
        }
        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .order-table th, .order-table td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .order-table th {
            background-color: #f4f4f4;
        }

        .order-table tr:nth-child(even) {
            background-color: #f8f8f8;
        }

        .order-table tr:hover {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
<header>
    <div class="container">
        <div class="logo">
            <a href="/">GiyimButik</a>
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
                    <li><a href="sepetim.php">Sepetim (<?php echo isset($_COOKIE['sepet']) ? count(json_decode(urldecode($_COOKIE['sepet']), true)) : 0; ?>)</a></li>
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

    <section id="profile">
        <div class="container">
            <div class="profile-container">
                <div class="profile-sidebar">
                    <ul>
                        <li><a href="#profile-info" class="active" data-target="profile-info">Profil Bilgileri</a></li>
                        <li><a href="#order-history" data-target="order-history">Sipariş Geçmişi</a></li>
                        <li><a href="#change-password" data-target="change-password">Şifre Değiştir</a></li>
                        <li><a href="#addresses" data-target="addresses">Adreslerim</a></li>
                        <li><a href="#favorites" data-target="favorites">Favori Ürünler</a></li>
                        <li><a href="#settings" data-target="settings">Ayarlar</a></li>
                    </ul>
                </div>
                <div class="profile-content">
                    <div class="profile-section" id="profile-info">
                        <div class="profile-info">
                            <p><strong>Ad:</strong> <?php echo $ad ?? 'Belirtilmemiş'; ?></p>
                            <p><strong>Soyad:</strong> <?php echo $soyad ?? 'Belirtilmemiş'; ?></p>
                            <p><strong>Kullanıcı Adı:</strong> <?php echo $username; ?></p>
                            <p><strong>E-posta:</strong> <?php echo $email; ?></p>
                            <p><strong>Telefon:</strong> <?php echo $telefon ?? 'Belirtilmemiş'; ?></p>
                            <p><strong>Doğum Tarihi:</strong> <?php echo $dogumTarihi ?? 'Belirtilmemiş'; ?></p>
                            <p><strong>Bakiye:</strong> <?php echo $bakiye; ?> TL</p>
                        </div>
                        <div class="profile-actions">
                            <a href="#" class="button" onclick="editProfile()">Bilgileri Düzenle</a>
                        </div>
                    </div>
                    <div class="profile-section" id="order-history">
                      <h3>Sipariş Geçmişi</h3>
                      <?php
                      // Kullanıcının siparişlerini çek
                      $orderStmt = $conn->prepare("
                          SELECT s.id, s.siparis_tarihi, s.toplam_tutar, s.durum
                          FROM siparisler s
                          WHERE s.kullanici_id = ?
                          ORDER BY s.siparis_tarihi DESC
                      ");
                      $orderStmt->bind_param("i", $userId);
                      $orderStmt->execute();
                      $orderResult = $orderStmt->get_result();

                      if ($orderResult->num_rows > 0) {
                          echo "<table class='order-table'>";
                          echo "<thead><tr><th>Sipariş No</th><th>Tarih</th><th>Toplam Tutar</th><th>Durum</th><th>Detaylar</th></tr></thead><tbody>";
                          while ($order = $orderResult->fetch_assoc()) {
                              echo "<tr>";
                              echo "<td>#" . $order['id'] . "</td>";
                              echo "<td>" . date("d.m.Y H:i", strtotime($order['siparis_tarihi'])) . "</td>";
                              echo "<td>" . $order['toplam_tutar'] . " TL</td>";
                              echo "<td>" . $order['durum'] . "</td>";
                              echo "<td><a href='siparis_detay.php?id=" . $order['id'] . "' class='button'>Detaylar</a></td>";
                              echo "</tr>";
                          }
                          echo "</tbody></table>";
                      } else {
                          echo "<p>Sipariş geçmişi bulunamadı.</p>";
                      }
                      $orderStmt->close();
                      ?>
                    </div>
                    <div class="profile-section" id="change-password">
                        <h3>Şifre Değiştir</h3>
                        <p>Buradan şifrenizi değiştirebilirsiniz.</p>
                    </div>
                    <div class="profile-section" id="addresses">
                        <h3>Adreslerim</h3>
                        <p>Kayıtlı adreslerinizi burada görebilir ve düzenleyebilirsiniz.</p>
                    </div>
                    <div class="profile-section" id="favorites">
                        <h3>Favori Ürünler</h3>
                        <p>Favori ürünlerinizi burada görebilirsiniz.</p>
                    </div>
                    <div class="profile-section" id="settings">
                        <h3>Ayarlar</h3>
                        <p>Hesap ayarlarınızı buradan yapabilirsiniz.</p>
                    </div>
                </div>
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
            <p>&copy; 2023 GiyimButik. Tüm hakları saklıdır.</p>
        </div>
    </footer>
    <script src="script.js"></script>
    <script>
        function editProfile() {
            alert("Bilgileri düzenleme özelliği yakında aktif olacak!");
        }
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
    const profileSidebarLinks = document.querySelectorAll('.profile-sidebar a');
    const profileSections = document.querySelectorAll('.profile-section');

    profileSidebarLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault();

            // Tüm bölümleri gizle
            profileSections.forEach(section => {
                section.style.display = 'none';
            });

            // Tüm linklerden active sınıfını kaldır
            profileSidebarLinks.forEach(l => l.classList.remove('active'));

            // Tıklanan linke active sınıfını ekle
            this.classList.add('active');

            // İlgili bölümü göster
            const targetId = this.getAttribute('data-target');
            const targetSection = document.getElementById(targetId);
            if (targetSection) {
                targetSection.style.display = 'block';
            }
        });
    });

    // Sayfa yüklendiğinde "Profil Bilgileri" bölümünü aktif et
    const profileInfoLink = document.querySelector('.profile-sidebar a[data-target="profile-info"]');
    const profileInfoSection = document.getElementById('profile-info');
    if (profileInfoLink && profileInfoSection) {
        profileInfoLink.classList.add('active');
        profileInfoSection.style.display = 'block';
    }
});
    </script>
</body>
</html>