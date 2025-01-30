<?php
session_start();

// Oturumu sonlandır
session_unset();
session_destroy();

// Sepeti sıfırla (hem local storage hem de cookie)
echo "<script>
        localStorage.removeItem('sepet');
        document.cookie = 'sepet=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
      </script>";

// Kullanıcıyı ana sayfaya yönlendir
header("Location: index.php");
exit;
?>