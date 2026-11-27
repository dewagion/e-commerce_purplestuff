# Purple Stuff

Purple Stuff adalah aplikasi e-commerce berbasis Laravel untuk katalog merchandise, keranjang, checkout, pembayaran transfer bank, upload bukti pembayaran, pengelolaan pesanan, pengelolaan produk, banner dashboard, artist, kategori, dan notifikasi admin.

## Ringkasan Paket Perbaikan

Paket ini menjaga fitur lama tetap berjalan. Perubahan difokuskan pada keamanan, kerapian struktur, dan kebersihan paket tugas akhir.

Perubahan utama:

1. File `.env` asli tidak disertakan dalam paket bersih.
2. `.env.example` sudah diganti menjadi template aman tanpa kunci asli.
3. Closure besar pada `routes/web.php` dipindahkan ke controller.
4. Script dan style panjang pada layout dipindahkan ke partial Blade.
5. File view duplikat dan file view salah lokasi dihapus dari paket.
6. Seeder user diganti menjadi akun demo yang aman.
7. SQL demo aman disediakan di `database/sql/e_commercepurple_clean.sql`.
8. File runtime seperti session, compiled view, log, bukti pembayaran, resi, vendor, node_modules, dan `.vs` tidak perlu masuk paket final.

## Akun Demo

Jika memakai `database/sql/e_commercepurple_clean.sql` atau menjalankan seeder, akun demo yang tersedia adalah:

Admin:

- Email: `admin@example.com`
- Password: `password`

Customer:

- Email: `customer@example.com`
- Password: `password`

## Cara Instalasi Lokal

Jalankan perintah berikut dari folder project.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Sesuaikan database di file `.env`.

```env
DB_DATABASE=e_commercepurple
DB_USERNAME=root
DB_PASSWORD=
```

Import database demo:

```bash
mysql -u root -p e_commercepurple < database/sql/e_commercepurple_clean.sql
```

Buat storage link:

```bash
php artisan storage:link
```

Jalankan aplikasi:

```bash
php artisan serve
```

Jika ingin memakai asset Vite:

```bash
npm run build
```

## Catatan Konfigurasi Layanan

Secara default, email memakai `MAIL_MAILER=log`. Artinya email tidak benar-benar dikirim, tetapi dicatat di log. Ini aman untuk demo tugas akhir.

Jika ingin fitur ongkir Biteship berjalan, isi variabel berikut pada `.env`:

```env
BITESHIP_API_KEY=
BITESHIP_BASE_URL=https://api.biteship.com/v1
BITESHIP_ORIGIN_POSTAL_CODE=80582
```

Jika ingin push notification admin berjalan, isi konfigurasi Firebase pada `.env`.

## Pemeriksaan Teknis

Paket ini telah diperiksa dengan:

```bash
php -l
php artisan route:list
Blade compile check
```

Hasil pemeriksaan:

- Sintaks PHP valid.
- Route tetap 79.
- Route utama tetap tersedia.
- Blade berhasil dikompilasi secara sintaks.

## Catatan Keamanan

Jangan membagikan file `.env` asli. Jika file `.env` sebelumnya sudah pernah terkirim, rotasi kredensial berikut:

1. APP_KEY jika project dipakai ulang di lingkungan baru.
2. Password database jika database terbuka.
3. SMTP username dan password.
4. Biteship API key.
5. Firebase private key, web key, dan VAPID key.

## Struktur Penting

- `app/Http/Controllers` berisi logic request pelanggan dan admin.
- `app/Models` berisi model database.
- `app/Services` berisi logic pendukung seperti cart persistence, Biteship, Firebase, dan expiry order.
- `resources/views` berisi tampilan Blade.
- `routes/web.php` berisi definisi route yang sudah diringkas.
- `database/sql/e_commercepurple_clean.sql` berisi database demo yang sudah disanitasi.
