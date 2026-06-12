# 🛒 POS Sistema - To'liq Yangilangan

Zamonaviy va funksionalli POS (Point of Sale) sistema, barcha talablaringizga mos keladi.

## 🎯 Asosiy Xususiyatlar

### 📦 Mahsulotlar Boshqaruvi
- ✅ Mahsulot qo'shish, tahrirlash, o'chirish
- ✅ Bir mahsulot uchun 4 ta shtrix-kod (13 xonali)
- ✅ Olingan narx va sotuv narxi
- ✅ Avtomatik son kamayish
- ✅ Kam son ogohlantirish (< 5 dona)

### 💰 Savdo
- ✅ Shtrix-kod yoki nom orqali qidirish
- ✅ Savatchaga qo'shish
- ✅ Naqd/karta to'lov
- ✅ Avtomatik foyda hisoblash
- ✅ PDF chek yaratish va chop etish

### 📊 Hisobotlar va Tahlil
- ✅ Eng ko'p foyda beruvchi mahsulotlar
- ✅ Kunlik va oylik savdo
- ✅ Kalendar ko'rinishida oylik savdolar
- ✅ Excel va PDF eksport

### 🔐 Xavfsizlik
- ✅ Admin va kassir rollari
- ✅ Sessiya boshqaruvi
- ✅ Xavfsiz API

## 🚀 O'rnatish

### 1. Tizim talablari
- PHP 7.4+ yoki 8.0+
- MySQL 5.7+ yoki 8.0+
- Web server (Apache/Nginx)

### 2. Ma'lumotlar bazasini sozlash
```bash
# MySQL'ga ulaning
mysql -u root -p

# Ma'lumotlar bazasini yarating
CREATE DATABASE pos_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Konfiguratsiya
`config.php` faylini o'zgartiring:
```php
$host = "localhost";
$user = "your_username";     
$pass = "your_password";        
$db   = "pos_system";
```

### 4. O'rnatish
```bash
# Web server orqali setup.php'ni ishga tushiring
http://localhost/pos_system/setup.php
```

## 📁 Fayl tuzilishi

```
pos_system/
├── config.php              # Ma'lumotlar bazasi konfiguratsiyasi
├── database_schema.sql     # Ma'lumotlar bazasi sxemasi
├── setup.php              # O'rnatish skripti
├── login.php              # Kirish sahifasi
├── logout.php             # Chiqish skripti
├── api.php                # API endpoint'lar
├── admin.php              # Admin panel
├── cashier.php            # Kassir sahifasi
└── README.md              # Bu fayl
```

## 🔑 Demo ma'lumotlar

### Admin
- **Login:** admin
- **Parol:** admin123

### Kassir
- **Login:** cashier
- **Parol:** admin123

## 📱 Foydalanish

### 1. Kirish
- `login.php` sahifasiga kiring
- Demo ma'lumotlar bilan tizimga kiring

### 2. Admin Panel
- Mahsulotlar boshqaruvi
- Savdolar tahlili
- Hisobotlar va grafiklar
- Kalendar ko'rinish

### 3. Kassir Sahifasi
- Mahsulot qidirish
- Savatchaga qo'shish
- Savdoni yakunlash
- Chek yaratish

## 🔧 API Endpoint'lar

### Mahsulotlar
- `GET /api.php?action=products` - Barcha mahsulotlar
- `GET /api.php?action=product&barcode=1234567890123` - Shtrix-kod orqali
- `POST /api.php?action=products` - Yangi mahsulot
- `PUT /api.php?action=products&id=1` - Mahsulotni tahrirlash
- `DELETE /api.php?action=products&id=1` - Mahsulotni o'chirish

### Savdolar
- `GET /api.php?action=sales` - Barcha savdolar
- `GET /api.php?action=sales&month=2025-01` - Oylik savdolar
- `POST /api.php?action=sales` - Yangi savdo

### Tahlil
- `GET /api.php?action=analytics` - Barcha tahlil ma'lumotlari

## 🎨 Texnologiyalar

### Frontend
- **HTML5** - Struktura
- **Tailwind CSS** - Stillar
- **jQuery** - JavaScript kutubxonasi
- **Chart.js** - Grafiklar
- **FullCalendar** - Kalendar
- **jsPDF** - PDF yaratish

### Backend
- **PHP** - Server-side logika
- **MySQL** - Ma'lumotlar bazasi
- **RESTful API** - Ma'lumotlar almashinuvi

## 📊 Ma'lumotlar bazasi sxemasi

### Asosiy jadvallar
- `products` - Mahsulotlar
- `product_barcodes` - Mahsulot shtrix-kodlari
- `sales` - Savdolar
- `sale_items` - Savdo elementlari
- `users` - Foydalanuvchilar

## 🔄 Yangilanishlar

### v2.0 (Hozirgi versiya)
- ✅ Zamonaviy UI/UX
- ✅ Ko'p shtrix-kod qo'llab-quvvatlash
- ✅ Real-time tahlil
- ✅ Kalendar ko'rinish
- ✅ PDF chek yaratish
- ✅ Xavfsizlik yaxshilanishlari

## 🛠 Xatoliklarni tuzatish

### PHP xatoliklari
```bash
# PHP versiyasini tekshiring
php -v

# PHP xatoliklarini ko'rsatish
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Ma'lumotlar bazasi xatoliklari
```bash
# MySQL ulanishini tekshiring
mysql -u your_user -p your_database

# Jadvallarni tekshiring
SHOW TABLES;
```

## 📞 Yordam

Agar muammolar bo'lsa:
1. `setup.php` ni qayta ishga tushiring
2. Ma'lumotlar bazasi ulanishini tekshiring
3. PHP xatoliklarini ko'ring
4. Web server log'larini tekshiring

## 📄 Litsenziya

Bu loyiha MIT litsenziyasi ostida tarqatiladi.

---

**🎉 POS Sistema tayyor! Endi savdolaringizni boshqarish oson!** 