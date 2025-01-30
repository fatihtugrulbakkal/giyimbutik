<?php
session_start();

// Kullanıcı giriş ve yetki kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || $_SESSION['admin_yetki'] !== 1) {
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim.']);
    exit;
}

require_once 'db_baglanti.php';

$message = ''; // Varsayılan mesaj
$error = false; // Hata durumu

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id']) && isset($_POST['durum'])) {
    $siparisId = intval($_POST['id']);
    $yeniDurum = htmlspecialchars($_POST['durum']);

    // Sipariş bilgilerini al
    $durumStmt = $conn->prepare("SELECT durum, kullanici_id, toplam_tutar FROM siparisler WHERE id = ?");
    $durumStmt->bind_param("i", $siparisId);
    $durumStmt->execute();
    $durumResult = $durumStmt->get_result();

    if ($durumResult->num_rows == 1) {
        $row = $durumResult->fetch_assoc();
        $eskiDurum = $row['durum'];
        $kullaniciId = $row['kullanici_id'];
        $toplamTutar = $row['toplam_tutar'];
    } else {
        $message = 'Sipariş bulunamadı.';
        $error = true;
        $durumStmt->close();
        $conn->close();
    }

    $durumStmt->close();

    // İptal durumunda bakiye iadesi
    if ($yeniDurum == 'İptal Edildi' && $eskiDurum != 'İptal Edildi') {
        $bakiyeStmt = $conn->prepare("SELECT bakiye FROM kullanicilar WHERE id = ?");
        $bakiyeStmt->bind_param("i", $kullaniciId);
        $bakiyeStmt->execute();
        $bakiyeResult = $bakiyeStmt->get_result();
        
        if ($bakiyeResult->num_rows == 1) {
            $bakiyeRow = $bakiyeResult->fetch_assoc();
            $bakiye = $bakiyeRow['bakiye'];
            $bakiyeStmt->close();

            $yeniBakiye = $bakiye + $toplamTutar;

            $guncelleBakiyeStmt = $conn->prepare("UPDATE kullanicilar SET bakiye = ? WHERE id = ?");
            $guncelleBakiyeStmt->bind_param("di", $yeniBakiye, $kullaniciId);
            if (!$guncelleBakiyeStmt->execute()) {
                $message = 'Bakiye güncellenirken hata oluştu.';
                $error = true;
            }
            $guncelleBakiyeStmt->close();
        } else {
            $message = 'Kullanıcı bakiyesi alınamadı.';
            $error = true;
        }
    }

    // Sipariş durumunu güncelle
    if (!$error) {
        $stmt = $conn->prepare("UPDATE siparisler SET durum = ? WHERE id = ?");
        $stmt->bind_param("si", $yeniDurum, $siparisId);
        if ($stmt->execute()) {
            $message = 'Sipariş durumu başarıyla güncellendi.';
        } else {
            $message = 'Sipariş durumu güncellenirken hata oluştu.';
            $error = true;
        }
        $stmt->close();
    }
    $conn->close();
} else {
    $message = 'Geçersiz istek. ID ve durum parametreleri eksik.';
    $error = true;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sipariş Durumu Güncelle</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
            margin: 0 0 20px;
            font-size: 24px;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-size: 16px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .button {
            display: inline-block;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            font-weight: bold;
            border-radius: 5px;
            transition: background-color 0.3s;
            text-align: center;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Sipariş Durumu Güncelle</h2>
        <div class="message <?php echo $error ? 'error' : 'success'; ?>">
            <?php echo $message; ?>
        </div>
        <a href="javascript:history.back()" class="button">Geri Dön</a>
    </div>
</body>
</html>
