# GiyimButik: E-Ticaret Sitesi

GiyimButik, kullanıcıların giyim ürünlerini inceleyebileceği, sepete ekleyebileceği ve satın alabileceği, yöneticilerin ise ürünleri, kullanıcıları, siparişleri ve site ayarlarını yönetebileceği bir e-ticaret web sitesi projesidir.

## Özellikler:

### Kullanıcılar için:

*   Ürünleri kategoriye göre listeleme
*   Ürün arama
*   Sepete ürün ekleme, silme ve düzenleme
*   Sipariş verme (bakiyeden ödeme)
*   Kullanıcı kaydı ve girişi
*   Profil sayfası (bilgileri görme, sipariş geçmişi, şifre değiştirme, adres ekleme, favori ürünler, ayarlar)

### Yöneticiler için:

*   Panel girişi
*   Ürün ekleme, silme ve düzenleme
*   Kategori ekleme, silme ve düzenleme
*   Kullanıcı ekleme, silme, düzenleme ve bakiye yükleme
*   Siparişleri görüntüleme, düzenleme ve iptal etme
*   Site ayarlarını (başlık, hero bölümü, footer yazısı) değiştirme
*   Panel üzerinden site içeriklerini (yazılar, resimler, buton metinleri) güncelleyebilme

### Diğer:

*   Mobil uyumlu tasarım (responsive)
*   Sepet bilgisinin local storage ve cookie'de saklanması
*   AJAX kullanımı (örneğin, sepeti güncellemek için)
*   Oturum yönetimi
*   Sipariş İptali ve Bakiye İadesi
*   Sipariş Detaylarını Görüntüleme ve Düzenleme
*   Panel üzerinden site içeriğini dinamik olarak değiştirme (metinler, resimler, butonlar)
*   Kullanıcı kaydı ve giriş formu
*   Kategorilere göre ürün listeleme
*   Bakiye sistemi ile ödeme

## Kullanılan Teknolojiler:

*   PHP (sürüm 7.4 ve üzeri önerilir)
*   MySQL
*   HTML
*   CSS
*   JavaScript
*   jQuery
*   AJAX

## Kurulum:

1.  **Veritabanını Oluşturma:**
    *   Aşağıdaki SQL kodunu kullanarak `giyimbutik` adında bir veritabanı ve gerekli tabloları oluşturun:

```sql
-- Veritabanı oluşturma (varsa önce sil)
DROP DATABASE IF EXISTS `giyimbutik`;
CREATE DATABASE `giyimbutik` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Veritabanını kullan
USE `giyimbutik`;

-- Kullanıcılar tablosu
CREATE TABLE `kullanicilar` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `kullanici_adi` VARCHAR(255) NOT NULL,
  `sifre` VARCHAR(255) NOT NULL,
  `eposta` VARCHAR(255) NOT NULL,
  `ad` VARCHAR(255) DEFAULT NULL,
  `soyad` VARCHAR(255) DEFAULT NULL,
  `telefon` VARCHAR(255) DEFAULT NULL,
  `dogum_tarihi` DATE DEFAULT NULL,
  `kayit_tarihi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `bakiye` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `yetki` INT(11) NOT NULL DEFAULT '0' COMMENT '0: Normal Kullanıcı, 1: Yönetici',
  PRIMARY KEY (`id`),
  UNIQUE KEY `kullanici_adi` (`kullanici_adi`),
  UNIQUE KEY `eposta` (`eposta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ürünler tablosu
CREATE TABLE `urunler` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `kategori_id` INT(11) DEFAULT NULL,
  `ad` VARCHAR(255) NOT NULL,
  `resim` VARCHAR(255) NOT NULL,
  `fiyat` DECIMAL(10,2) NOT NULL,
  `stok` INT(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`kategori_id`) REFERENCES `kategoriler`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Siparişler Tablosu
CREATE TABLE `siparisler` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `kullanici_id` INT(11) NOT NULL,
  `ad` VARCHAR(255) NOT NULL,
  `soyad` VARCHAR(255) NOT NULL,
  `eposta` VARCHAR(255) NOT NULL,
  `telefon` VARCHAR(255) NOT NULL,
  `toplam_tutar` DECIMAL(10,2) NOT NULL,
  `siparis_tarihi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `durum` VARCHAR(255) NOT NULL DEFAULT 'Onay Bekliyor',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`kullanici_id`) REFERENCES `kullanicilar`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sipariş Ürünleri Tablosu
CREATE TABLE `siparis_urunler` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `siparis_id` INT(11) NOT NULL,
  `urun_id` INT(11) NOT NULL,
  `urun_adi` VARCHAR(255) NOT NULL,
  `adet` INT(11) NOT NULL,
  `birim_fiyat` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`siparis_id`) REFERENCES `siparisler`(`id`),
  FOREIGN KEY (`urun_id`) REFERENCES `urunler`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Kategoriler Tablosu
CREATE TABLE `kategoriler` (
 `id` INT(11) NOT NULL AUTO_INCREMENT,
 `ad` VARCHAR(255) NOT NULL,
 `aciklama` TEXT DEFAULT NULL,
 `link` VARCHAR(255) DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Site Ayarları Tablosu
CREATE TABLE `site_ayarlari` (
`id` INT(11) NOT NULL AUTO_INCREMENT,
`anahtar` VARCHAR(255) NOT NULL UNIQUE,
`dosya` VARCHAR(255) NOT NULL,
`eski_metin` TEXT,
`yeni_metin` VARCHAR(500),
`eski_deger` TEXT,
`yeni_deger` VARCHAR(500),
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Örnek Yönetici Hesabı (Şifre: 'password123')
INSERT INTO `kullanicilar` (`kullanici_adi`, `sifre`, `eposta`, `ad`, `soyad`, `telefon`, `dogum_tarihi`, `yetki`) VALUES
('admin', '$2y$10\$Geh3i4AO.p0/cn5YO/x0i.kH.DLPB6JpSx/YYnWj0mJ15EC/6D5/y', 'admin@giyimbutik.com', 'Admin', 'Yönetici', '05551234567', '1990-01-01', 1);

-- Örnek Kategoriler
INSERT INTO `kategoriler` (`ad`, `aciklama`, `link`) VALUES
('Kadın Giyim', 'Kadınlar için giyim ürünleri', 'kadin-giyim'),
('Erkek Giyim', 'Erkekler için giyim ürünleri', 'erkek-giyim'),
('Çocuk Giyim', 'Çocuklar için giyim ürünleri', 'cocuk-giyim');

-- Unutmayın, gerçek resim dosyalarını 'assets/images' klasörüne yüklemeniz ve yollarını güncellemeniz gerekecek.
INSERT INTO `urunler` (`kategori_id`, `ad`, `resim`, `fiyat`, `stok`) VALUES
(1, 'Şık Elbise', 'assets/images/giyim1.jpg', 150.00, 10),
(2, 'Rahat Tişört', 'assets/images/giyim2.jpg', 50.00, 20),
(2, 'Modern Ceket', 'assets/images/giyim3.jpg', 250.00, 5),
(3, 'Spor Ayakkabı', 'assets/images/giyim4.jpg', 120.00, 15);

-- Örnek Site Ayarları
INSERT INTO `site_ayarlari` (`anahtar`, `dosya`, `eski_metin`, `yeni_metin`, `eski_deger`, `yeni_deger`) VALUES
('site_basligi', 'index.php', '<title>GiyimButik</title>', '<title>GiyimButik</title>', '', ''),
('hero_baslik', 'index.php', '<h2>Son Moda Trendleri Keşfedin</h2>', '<h2>Son Moda Trendleri Keşfedin</h2>', '', ''),
('hero_aciklama', 'index.php', '<p>Şıklığınızı tamamlayacak en yeni koleksiyonlar burada!</p>', '<p>Şıklığınızı tamamlayacak en yeni koleksiyonlar burada!</p>', '', ''),
('hero_resim_url', 'index.php', '', '', 'images/hero.jpg', 'images/hero.jpg'),
('alisverise_basla_yazisi', 'index.php', 'Alışverişe Başla', 'Alışverişe Başla', '', ''),
('footer_yazi', 'index.php', '<p>&copy; 2025 GiyimButik. Tüm hakları saklıdır.</p>', '<p>&copy; 2023 GiyimButik. Tüm hakları saklıdır.</p>', '', '');
