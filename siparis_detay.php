<?php
session_start();

// Kullanıcının giriş yapıp yapmadığını kontrol et
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php"); // Giriş yapmamışsa ana sayfaya yönlendir
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

// Sipariş bilgilerini çek
$stmt = $conn->prepare("SELECT s.*, k.kullanici_adi FROM siparisler s JOIN kullanicilar k ON s.kullanici_id = k.id WHERE s.id = ? AND s.kullanici_id = ?");
$stmt->bind_param("ii", $siparisId, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $siparis = $result->fetch_assoc();
} else {
    echo "Sipariş bulunamadı veya yetkiniz yok.";
    exit;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sipariş Detayı - GiyimButik</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f0f0f0;
        }
        .toplam-tutar {
            margin-top: 20px;
            text-align: right;
            font-size: 18px;
        }
        .siparis-bilgileri p {
            margin: 10px 0;
            font-size: 16px;
            color: #555;
        }
        .siparis-bilgileri strong {
            font-weight: 600;
            color: #333;
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
                    <li><a href="#">Yeni Gelenler</a></li>
                    <li><a href="#">Kadın</a></li>
                    <li><a href="#">Erkek</a></li>
                    <li><a href="#">Çocuk</a></li>
                    <li><a href="#">İndirim</a></li>
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

    <div class="container">
        <h2>Sipariş Detayı - #<?php echo $siparis['id']; ?></h2>
        <div class="siparis-bilgileri">
            <p><strong>Sipariş Tarihi:</strong> <?php echo date("d.m.Y H:i", strtotime($siparis['siparis_tarihi'])); ?></p>
            <p><strong>Durum:</strong> <?php echo $siparis['durum']; ?></p>
            <p><strong>Ad:</strong> <?php echo htmlspecialchars($siparis['ad']); ?></p>
            <p><strong>Soyad:</strong> <?php echo htmlspecialchars($siparis['soyad']); ?></p>
            <p><strong>E-posta:</strong> <?php echo htmlspecialchars($siparis['eposta']); ?></p>
            <p><strong>Telefon:</strong> <?php echo htmlspecialchars($siparis['telefon']); ?></p>
        </div>

        <table>
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
    </div>

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
</body>
</html>

<?php $conn->close(); ?>