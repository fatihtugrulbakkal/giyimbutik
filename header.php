<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : "GiyimButik"; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="/">
                <?php
                // Veritabanı bağlantısı zaten yapılmış olmalı ($conn değişkeni mevcut)
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