<?php
session_start();

// Kullanıcı zaten giriş yapmışsa ve yetkiliyse, kontrol paneline yönlendir
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true && isset($_SESSION['admin_yetki']) && $_SESSION['admin_yetki'] === 1) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

// Giriş formundan gelen verileri kontrol et
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'db_baglanti.php';

    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, sifre, yetki FROM kullanicilar WHERE kullanici_adi = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        
        // Şifre kontrolü (GÜVENLİ DEĞİL - düz metin karşılaştırma)
        if ($password === $row['sifre']) {
            // Yetki kontrolü
            if ($row['yetki'] == 1) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $row['id'];
                $_SESSION['admin_yetki'] = $row['yetki'];
                header("Location: dashboard.php"); // Kontrol paneline yönlendir
                exit;
            } else {
                $error = "Bu alana erişim yetkiniz bulunmamaktadır!";
            }
        } else {
            $error = "Hatalı şifre!";
        }
    } else {
        $error = "Kullanıcı bulunamadı!";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Paneli - Giriş</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="panel-login-page">
    <header>
        <div class="container">
            <div class="logo">
                <a href="../index.php">GiyimButik</a>
            </div>
            <nav>
                <ul class="menu">
                    <li><a href="#">Yeni Gelenler</a></li>
                    <li><a href="#">Kadın</a></li>
                    <li><a href="#">Erkek</a></li>
                    <li><a href="#">Çocuk</a></li>
                    <li><a href="#">İndirim</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <div class="login-container">
        <h2>Yönetici Girişi</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form action="" method="post">
            <label for="username">Kullanıcı Adı:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Şifre:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" class="button">Giriş Yap</button>
        </form>
    </div>
    <footer>
        <div class="container">
          <p>© 2025 GiyimButik. Tüm hakları saklıdır.</p>
        </div>
      </footer>
</body>
</html>