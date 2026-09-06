# Uigreenmetric — Self Assessment

Aplikasi **Self Assessment Uigreenmetric** berbasis Laravel yang digunakan untuk mendukung proses penilaian dan evaluasi secara terstruktur melalui platform berbasis web.

## 🚀 About

**Uigreenmetric Self Assessment** merupakan aplikasi web yang dikembangkan untuk membantu pengguna dalam melakukan pengisian instrumen self-assessment, pengelolaan data penilaian, serta penyajian hasil evaluasi.

Aplikasi ini dibangun menggunakan framework **Laravel** dengan database **MySQL/MariaDB**.

## ✨ Features

* 🔐 Authentication dan manajemen pengguna
* 📝 Pengisian instrumen self-assessment
* 📊 Pengelolaan indikator penilaian
* 📋 Pengelolaan data dan kategori penilaian
* 📈 Penyajian hasil assessment
* 👤 Manajemen user dan hak akses
* 🗄️ Penyimpanan data menggunakan MySQL/MariaDB
* 📱 Web-based application

## 🛠️ Tech Stack

| Technology         | Version / Description             |
| ------------------ | --------------------------------- |
| PHP                | >= 8.2                            |
| Laravel            | Laravel Framework                 |
| Database           | MySQL / MariaDB                   |
| Frontend           | Blade, CSS, JavaScript            |
| Dependency Manager | Composer                          |
| Web Server         | Apache                            |
| Development        | XAMPP / Laragon / Laravel Artisan |

## 📋 Requirements

Sebelum melakukan instalasi, pastikan sistem sudah memiliki:

* PHP >= 8.2
* Composer
* MySQL atau MariaDB
* Node.js dan NPM
* Git
* Apache/Nginx

PHP extensions yang dibutuhkan mengikuti dependency yang terdapat pada `composer.json`.

## 📥 Installation

### 1. Clone Repository

```bash
git clone https://github.com/GilangHimawan/Ui-greenmetric.git
cd Uigreenmetric
```

Ganti `USERNAME/Uigreenmetric` dengan alamat repository GitHub Anda.

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Frontend Dependencies

```bash
npm install
```

### 4. Create Environment File

Copy `.env.example` menjadi `.env`.

Windows:

```bash
copy .env.example .env
```

Linux/macOS:

```bash
cp .env.example .env
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Configure Database

Buka file `.env` dan sesuaikan konfigurasi database:

```env
APP_NAME=Uigreenmetric
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=uigreenmetric
DB_USERNAME=root
DB_PASSWORD=
```

Sesuaikan:

* `DB_DATABASE`
* `DB_USERNAME`
* `DB_PASSWORD`
* `DB_HOST`

dengan konfigurasi database lokal Anda.

### 7. Run Migration

Jika database dibuat menggunakan migration:

```bash
php artisan migrate
```

Jika tersedia seeder:

```bash
php artisan db:seed
```

atau:

```bash
php artisan migrate --seed
```

> Jika repository menggunakan database yang sudah memiliki data, gunakan database backup/import sesuai kebutuhan dan jangan menjalankan perintah yang dapat menghapus data production.

### 8. Storage Link

Jika aplikasi menggunakan Laravel Storage:

```bash
php artisan storage:link
```

### 9. Build Frontend

```bash
npm run build
```

Untuk development:

```bash
npm run dev
```

### 10. Run Application

```bash
php artisan serve
```

Aplikasi dapat diakses melalui:

```text
http://127.0.0.1:8000
```

## 📁 Project Structure

```text
Uigreenmetric/
│
├── app/
│   ├── Http/
│   ├── Models/
│   └── ...
│
├── bootstrap/
├── config/
├── database/
│   ├── migrations/
│   └── seeders/
│
├── public/
│   ├── index.php
│   ├── assets/
│   └── ...
│
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│
├── routes/
│   ├── web.php
│   └── ...
│
├── storage/
├── tests/
├── artisan
├── composer.json
├── composer.lock
├── package.json
└── .env.example
```

## 🔧 Useful Artisan Commands

Clear application cache:

```bash
php artisan optimize:clear
```

Show registered routes:

```bash
php artisan route:list
```

Run migration:

```bash
php artisan migrate
```

Create controller:

```bash
php artisan make:controller ExampleController
```

Create model:

```bash
php artisan make:model Example
```

Create migration:

```bash
php artisan make:migration create_example_table
```

Clear view cache:

```bash
php artisan view:clear
```

## 🌐 Deployment

Aplikasi dapat di-deploy ke server Apache/cPanel.

Untuk production, pastikan konfigurasi `.env` menggunakan:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
```

Database production harus dikonfigurasi sesuai database yang tersedia pada server.

PHP server harus menggunakan **PHP 8.2 atau lebih baru** sesuai kebutuhan dependency aplikasi.

## 🔒 Security

Jangan commit file `.env` ke repository GitHub.

Pastikan `.gitignore` mencakup:

```text
.env
/vendor/
/node_modules/
/public/hot
/storage/*.key
```

Untuk production:

```env
APP_DEBUG=false
```

Jangan memasukkan password database, API key, `APP_KEY`, atau credential lainnya ke dalam repository publik.

## 📌 Development Notes

Beberapa komponen aplikasi menggunakan Laravel Blade View.

Contoh pemanggilan:

```php
return view('indikator.index');
```

Laravel akan mencari:

```text
resources/views/indikator/index.blade.php
```

Perhatikan bahwa environment Linux bersifat **case-sensitive**. Oleh karena itu:

```text
indikator
```

berbeda dengan:

```text
Indikator
```

## 🤝 Contributing

Contributions, issues, dan pull requests are welcome.

Untuk perubahan besar, silakan buat issue terlebih dahulu untuk mendiskusikan perubahan yang akan dilakukan.

## 📄 License

Copyright © 2026 Poliwangi.

All rights reserved.
