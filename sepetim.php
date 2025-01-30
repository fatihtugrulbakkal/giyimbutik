<?php
session_start();

// Veritabanı bağlantısını dahil et
require_once 'db_baglanti.php';

// Sepet bilgisini cookieden al (JSON formatından diziye çevir)
$cart = isset($_COOKIE['sepet']) ? json_decode(urldecode($_COOKIE['sepet']), true) : [];
$totalPrice = 0; // Toplam fiyatı sıfırla
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sepetim - GiyimButik</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="style.css">
    <style>
        /* Sepetim sayfası için ek stiller */
        #cart {
            padding: 40px 0;
        }

        #cart h2 {
            margin-bottom: 30px;
            font-weight: 700;
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .cart-table th, .cart-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .cart-table th {
            font-weight: 600;
        }

        .cart-product-image {
            width: 80px;
            height: 80px;
        }

        .cart-product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cart-product-name {
            font-weight: 600;
        }

        .cart-product-price {
            color: #d9534f;
        }

        .cart-quantity input {
            width: 50px;
            text-align: center;
        }

        .cart-remove button {
            background: none;
            border: none;
            color: #777;
            cursor: pointer;
            font-size: 1.2em;
        }

        .cart-total {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-bottom: 30px;
        }

        .cart-total p {
            font-size: 18px;
            font-weight: 600;
        }

        .cart-actions {
            display: flex;
            justify-content: flex-end;
        }

        .empty-cart-message {
            text-align: center;
            font-size: 18px;
            color: #777;
        }
        .button {
            background-color: #d9534f;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            border: none;
            cursor: pointer;
        }
        .button:hover {
            background-color: #c9302c;
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

    <section id="cart">
        <div class="container">
            <h2>Sepetim</h2>
            <?php if (count($cart) > 0): ?>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Ürün</th>
                            <th>Ad</th>
                            <th>Fiyat</th>
                            <th>Adet</th>
                            <th>Toplam</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($cart as $item) {
                            $productId = $item['id'];
                            $stmt = $conn->prepare("SELECT ad, resim, fiyat FROM urunler WHERE id = ?");
                            $stmt->bind_param("i", $productId);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($result->num_rows > 0) {
                                $product = $result->fetch_assoc();
                                $productName = $product['ad'];
                                $productImage = $product['resim'];
                                $productPrice = $product['fiyat'];
                                $quantity = intval($item['quantity']);
                                $itemTotalPrice = $productPrice * $quantity;
                                $totalPrice += $itemTotalPrice;

                                echo "<tr>";
                                echo "<td><div class='cart-product-image'><img src='../" . $productImage . "' alt='" . $productName . "'></div></td>";
                                echo "<td><span class='cart-product-name'>" . $productName . "</span></td>";
                                echo "<td><span class='cart-product-price'>" . $productPrice . " TL</span></td>";
                                // Input alanına min değerini ekleyin ve değeri integer olarak alın
                                echo "<td class='cart-quantity'><input type='number' min='1' value='" . $quantity . "' data-product-id='" . $productId . "'></td>";
                                echo "<td><span class='cart-product-total'>" . $itemTotalPrice . " TL</span></td>";
                                echo "<td class='cart-remove'><button data-product-id='" . $productId . "'><i class='fas fa-trash-alt'></i></button></td>";
                                echo "</tr>";
                            }
                            $stmt->close();
                        }
                        ?>
                    </tbody>
                </table>
                <div class="cart-total">
                    <p>Toplam Tutar: <span id="total-price"><?php echo $totalPrice; ?></span> TL</p>
                </div>
                <div class="cart-actions">
                    <a href="odeme.php" class="button">Ödemeye Geç</a>
                </div>
            <?php else: ?>
                <p class="empty-cart-message">Sepetiniz boş.</p>
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
            <p>&copy; 2023 GiyimButik. Tüm hakları saklıdır.</p>
        </div>
    </footer>
    <script src="script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const quantityInputs = document.querySelectorAll('.cart-quantity input');

            quantityInputs.forEach(input => {
                input.addEventListener('input', function() {
                    let newQuantity = parseInt(this.value);

                    
                    if (isNaN(newQuantity) || newQuantity < 1) {
                        newQuantity = 1;
                        this.value = newQuantity;
                    }

                    const productId = this.dataset.productId;

                    
                    updateCart(productId, newQuantity);
                });
            });

            function updateCart(productId, quantity) {
                let cart = getCart();
                const productIndex = cart.findIndex(item => item.id === productId);

                if (productIndex !== -1) {
                    cart[productIndex].quantity = quantity;

                    
                    localStorage.setItem('sepet', JSON.stringify(cart));

                    
                    document.cookie = "sepet=" + encodeURIComponent(JSON.stringify(cart)) + ";path=/";

                    
                    updateCartIcon();

                    
                    location.reload();
                }
            }

            
            function getCart() {
                const localCartStr = localStorage.getItem('sepet');
                if (localCartStr) {
                    return JSON.parse(localCartStr);
                }

                const cookieCartStr = document.cookie.replace(/(?:(?:^|.*;\s*)sepet\s*\=\s*([^;]*).*$)|^.*$/, "$1");
                if (cookieCartStr) {
                    return JSON.parse(decodeURIComponent(cookieCartStr));
                }

                return [];
            }

            
            function updateCartIcon() {
                const cart = getCart();
                const cartItemCount = cart.reduce((total, item) => total + item.quantity, 0);
                const cartIconLink = document.querySelector('.user-actions li:first-child a');
                if (cartIconLink) {
                    cartIconLink.textContent = `Sepetim (${cartItemCount})`;
                }
            }
        });
    </script>

</body>
</html>

<?php $conn->close(); ?>