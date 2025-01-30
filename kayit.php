<?php
require_once 'db_baglanti.php'; // Veritabanı bağlantı dosyanızı ekleyin

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ad = $_POST['ad'];
    $soyad = $_POST['soyad'];
    $eposta = $_POST['eposta'];
    $kullanici_adi = $_POST['kullanici_adi'];
    $sifre = $_POST['sifre'];
    $sifre_tekrar = $_POST['sifre_tekrar'];

    // Şifreleri kontrol et
    if ($sifre !== $sifre_tekrar) {
        $error = "Şifreler uyuşmuyor.";
    } else {
        // Şifreyi hash'le (güvenlik için)
        $hashed_sifre = password_hash($sifre, PASSWORD_DEFAULT);

        // Yeni kullanıcı için yetki seviyesini belirle (0: normal kullanıcı)
        $yetki = 0;

        // Kullanıcıyı veritabanına ekle
        $stmt = $conn->prepare("INSERT INTO kullanicilar (ad, soyad, eposta, kullanici_adi, sifre, yetki) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $ad, $soyad, $eposta, $kullanici_adi, $hashed_sifre, $yetki);

        if ($stmt->execute()) {
            // Kayıt başarılı, giriş sayfasına yönlendir
            header("Location: index.php");
            exit;
        } else {
            $error = "Kayıt olurken bir hata oluştu: " . $stmt->error;
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - GiyimButik</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .register-form {
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .register-form h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .register-form label {
            display: block;
            margin-bottom: 5px;
        }
        .register-form input[type="text"],
        .register-form input[type="email"],
        .register-form input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }
        .register-form .button {
            width: 100%;
            padding: 10px;
            background-color: #d9534f;
            border: none;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }
        .register-form .button:hover {
            background-color: #c9302c;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-form">
            <h2>Kayıt Ol</h2>
            <?php if (!empty($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
            <form action="" method="post">
                <label for="ad">Adınız:</label>
                <input type="text" id="ad" name="ad" required>

                <label for="soyad">Soyadınız:</label>
                <input type="text" id="soyad" name="soyad" required>

                <label for="eposta">E-posta Adresiniz:</label>
                <input type="email" id="eposta" name="eposta" required>

                <label for="kullanici_adi">Kullanıcı Adı:</label>
                <input type="text" id="kullanici_adi" name="kullanici_adi" required>

                <label for="sifre">Şifre:</label>
                <input type="password" id="sifre" name="sifre" required>

                <label for="sifre_tekrar">Şifre Tekrar:</label>
                <input type="password" id="sifre_tekrar" name="sifre_tekrar" required>

                <button type="submit" class="button">Kayıt Ol</button>
            </form>
        </div>
    </div>
</body>
</html>