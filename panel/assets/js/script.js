// Menüyü mobil cihazlarda aç/kapat
const menuToggle = document.querySelector('.menu-toggle');
const menu = document.querySelector('.menu');
const userActions = document.querySelector('.user-actions');

if (menuToggle) {
    menuToggle.addEventListener('click', () => {
        menu.classList.toggle('open');
        userActions.classList.toggle('open');
    });
}

// Giriş Yap Butonu
const loginButton = document.getElementById('login-button');
const loginModal = document.getElementById('login-modal');
const closeButton = document.querySelector('.close-button');
const loginForm = document.getElementById('login-form');

if (loginButton) {
    loginButton.addEventListener('click', () => {
        loginModal.style.display = 'block';
    });
}

if (closeButton) {
    closeButton.addEventListener('click', () => {
        loginModal.style.display = 'none';
    });
}

if (loginForm) {
    loginForm.addEventListener('submit', (event) => {
        event.preventDefault(); // Formun varsayılan gönderimini engelle

        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;

        if (username === '' || password === '') {
            alert('Lütfen tüm alanları doldurun.');
        } else {
            // AJAX ile sunucuya gönderme işlemi burada yapılacak
            // Örnek AJAX kodu:

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'login.php');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = () => {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        alert('Giriş başarılı!');
                        loginModal.style.display = 'none';
                        // Kullanıcıyı başka bir sayfaya yönlendir
                        window.location.href = 'index.php'; // Giriş yaptığı varsayılan olarak Anasayfaya yönlendiriliyor değiştirebilirsiniz.
                    } else {
                        alert('Giriş başarısız: ' + response.message);
                    }
                } else {
                    alert('Bir hata oluştu.');
                }
            };
            xhr.send('username=' + username + '&password=' + password);

        }
    });
}

// Modal pencere dışına tıklandığında kapatma
window.onclick = (event) => {
  if (event.target === loginModal) {
    loginModal.style.display = "none";
  }
};

// Alışverişe Başla Butonu (Sadece yönlendirme yapacak)
const shopNowButton = document.getElementById('shop-now-button');
if (shopNowButton) {
    shopNowButton.addEventListener('click', () => {
        window.location.href = 'urunler.php';
    });
}

// Sepete Ekle Butonları (SADECE index.php ve urunler.php sayfasındakileri seç)
if (window.location.pathname.endsWith('index.php') || window.location.pathname.endsWith('urunler.php') || window.location.pathname === "/giyim-ecommerce/" || window.location.pathname === "/giyim-ecommerce/index.php" || window.location.pathname === "/giyim-ecommerce/urunler.php" || window.location.pathname === "/") { // Eğer index.php veya urunler.php sayfasındaysak veya local sunucunuzun ana dizinindeysek
    const addToCartButtons = document.querySelectorAll('.add-to-cart-button');
    addToCartButtons.forEach(button => {
        button.addEventListener("click", event => {
          event.preventDefault();
          const productId = button.id;
          addToCart(productId);
          updateCartIcon(); // Sepet ikonunu güncelle
        });
      });
}

// Sepete ürün ekleme fonksiyonu
function addToCart(productId) {
    let cart = getCart();
    const existingProductIndex = cart.findIndex(item => item.id === productId);

    if (existingProductIndex !== -1) {
        // Ürün zaten sepette varsa, miktarını artır
        cart[existingProductIndex].quantity++;
    } else {
        // Ürün sepette yoksa, yeni ürün olarak ekle
        cart.push({ id: productId, quantity: 1 });
    }

    // Sepeti local storage'a kaydet
    localStorage.setItem('sepet', JSON.stringify(cart));
    // Sepeti cookie'ye kaydet (sunucu tarafında erişim için)
    document.cookie = "sepet=" + encodeURIComponent(JSON.stringify(cart)) + ";path=/";

}

// Sepeti alma fonksiyonu
// Local storage'da sepet varsa onu, yoksa cookie'deki sepeti, o da yoksa boş bir dizi döndürür
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

// Sepet ikonunu güncelleme fonksiyonu
function updateCartIcon() {
  const cart = getCart();
  const cartItemCount = cart.reduce((total, item) => total + item.quantity, 0);
  const cartIconLink = document.querySelector('.user-actions a[href="sepetim.php"]');
  if (cartIconLink) {
    cartIconLink.textContent = `Sepetim (${cartItemCount})`;
  }
}

// Sayfa yüklendiğinde sepet ikonunu güncelle
updateCartIcon();

// Sepet işlemleri için event listener'lar (SADECE sepetim.php sayfasındakiler için)
if (window.location.pathname.endsWith('sepetim.php') || window.location.pathname === "/giyim-ecommerce/sepetim.php") {
    // Sepetten ürün silme fonksiyonu
    function removeFromCart(productId) {
        let cart = getCart();
        const productIndex = cart.findIndex(item => item.id === productId);

        if (productIndex !== -1) {
            cart.splice(productIndex, 1);
            localStorage.setItem('sepet', JSON.stringify(cart));
            document.cookie = "sepet=" + encodeURIComponent(JSON.stringify(cart)) + ";path=/";
            updateCartIcon();
            location.reload(); // Sepet içeriğini güncellemek için sayfayı yenile (geçici çözüm, daha iyisi AJAX ile yapılabilir)
        }
    }

    // Sepet miktarını güncelleme fonksiyonu
    function updateQuantity(productId, newQuantity) {
        let cart = getCart();
        const productIndex = cart.findIndex(item => item.id === productId);

        if (productIndex !== -1) {
            cart[productIndex].quantity = parseInt(newQuantity);
            localStorage.setItem('sepet', JSON.stringify(cart));
            document.cookie = "sepet=" + encodeURIComponent(JSON.stringify(cart)) + ";path=/";
            updateCartIcon();
            location.reload();
        }
    }
    document.addEventListener('DOMContentLoaded', () => {
        const cartTable = document.querySelector('.cart-table');

        if (cartTable) {
            // Ürün silme
            cartTable.addEventListener('click', (event) => {
                const removeButton = event.target.closest('.cart-remove button');
                if (removeButton) {
                    const productId = removeButton.dataset.productId;
                    removeFromCart(productId);
                }
            });

            // Miktar değiştirme (input alanında değişiklik olduğunda)
            cartTable.addEventListener('change', (event) => {
                const quantityInput = event.target.closest('.cart-quantity input');
                if (quantityInput) {
                    const productId = quantityInput.dataset.productId;
                    const newQuantity = quantityInput.value;
                    updateQuantity(productId, newQuantity);
                }
            });
        }
    });
}

// Çıkış Yap Butonu
if (document.getElementById('logout-button')){
    document.getElementById('logout-button').addEventListener('click', (event) => {
    event.preventDefault();
    // Çıkış yapma işlemi burada gerçekleştirilecek.
    // Local Storage'ı temizle
    localStorage.removeItem('sepet');

    // Cookie'yi sil
    document.cookie = "sepet=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    // Örneğin, kullanıcıyı logout.php sayfasına yönlendirebilirsiniz.
    window.location.href = 'logout.php';
    });
}
// Profil sayfasındaki linklere tıklama olayı
document.addEventListener("DOMContentLoaded", function() {
    const profileSidebarLinks = document.querySelectorAll(".profile-sidebar a");
    const profileSections = document.querySelectorAll(".profile-section");

    profileSidebarLinks.forEach(link => {
        link.addEventListener("click", function(event) {
            event.preventDefault();

            // Tüm bölümleri gizle
            profileSections.forEach(section => {
                section.style.display = "none";
            });

            // Tüm linklerden active sınıfını kaldır
            profileSidebarLinks.forEach(l => l.classList.remove("active"));

            // Tıklanan linke active sınıfını ekle
            this.classList.add("active");

            // İlgili bölümü göster
            const targetId = this.getAttribute("data-target");
            const targetSection = document.getElementById(targetId);
            if (targetSection) {
                targetSection.style.display = "block";
                // Sayfanın en üstüne yumuşak kaydırma (smooth scroll)
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Varsayılan olarak Profil Bilgileri bölümünü göster ve linki aktif et
    const profileInfoSection = document.getElementById("profile-info");
    const profileInfoLink = document.querySelector('.profile-sidebar a[data-target="profile-info"]');

    if (profileInfoSection && profileInfoLink) {
        profileInfoSection.style.display = "block";
        profileInfoLink.classList.add("active");
    }
});

$(document).ready(function() {

    // Kullanıcı Silme
    $('#users-table').on('click', '.sil-button', function() {
        if (!confirm('Kullanıcıyı silmek istediğinize emin misiniz?')) return;

        const userId = $(this).data('id');
        const $this = $(this);

        $.ajax({
            url: 'kullanici_sil.php',
            type: 'POST',
            data: { id: userId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Kullanıcı başarıyla silindi.');
                    // Kullanıcıyı listeden kaldır veya sayfayı yenile
                    $this.closest('tr').remove(); // Butona en yakın tr elementini kaldırır
                    // location.reload(); // Sayfayı yenilemek için bu satırı da kullanabilirsiniz
                } else {
                    alert('Kullanıcı silinirken bir hata oluştu.');
                }
            },
            error: function() {
                alert('Bir hata oluştu.');
            }
        });
    });

    // Bakiye Yükle Butonu
    $('#users-table').on('click', '.bakiye-yukle-button', function() {
        const userId = $(this).data('id');
        const bakiye = prompt("Yüklenecek bakiyeyi girin:", "0.00");

        if (bakiye !== null && bakiye !== "") {
            $.ajax({
                url: 'bakiye_yukle.php',
                type: 'POST',
                data: { id: userId, bakiye: bakiye },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Bakiye başarıyla yüklendi.');
                        location.reload(); // Sayfayı yenile
                    } else {
                        alert('Hata: ' + response.message);
                    }
                },
                error: function() {
                    alert('Bir hata oluştu.');
                }
            });
        }
    });
});

// "Kaydet" butonuna tıklama olayı
$('#kaydet-button').on('click', function() {
    var degisiklikler = [];
    $('#orders-table tbody tr').each(function() {
        var siparisId = $(this).find('.siparis-durumu').data('id');
        var yeniDurum = $(this).find('.siparis-durumu').val();
        degisiklikler.push({ id: siparisId, durum: yeniDurum });
    });

    $.ajax({
        url: 'siparisleri_kaydet.php',
        type: 'POST',
        data: { siparisler: degisiklikler },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('Değişiklikler başarıyla kaydedildi.');
                location.reload(); // Sayfayı yenile
            } else {
                alert('Hata: ' + response.message);
            }
        },
        error: function() {
            alert('Bir hata oluştu.');
        }
    });
});

