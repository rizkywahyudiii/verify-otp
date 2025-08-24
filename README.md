# Laravel OTP Verification System

Sistem verifikasi OTP menggunakan Laravel 12 dengan fitur register, login, dan verifikasi email menggunakan kode OTP.

## Fitur

- ✅ **Register**: Pendaftaran user dengan nama, email, dan password
- ✅ **OTP Verification**: Verifikasi email menggunakan kode OTP 6 digit
- ✅ **Login**: Login hanya untuk user yang sudah diverifikasi
- ✅ **Dashboard**: Halaman dashboard sederhana untuk user yang sudah login
- ✅ **Email OTP**: Pengiriman kode OTP via email
- ✅ **Security**: Middleware auth untuk proteksi halaman

## Struktur Database

### Tabel `users`
- `id` - Primary key
- `name` - Nama user
- `email` - Email user (unique)
- `password` - Password yang di-hash
- `is_verified` - Status verifikasi (default: false)
- `email_verified_at` - Timestamp verifikasi email
- `remember_token` - Token untuk remember me
- `created_at`, `updated_at` - Timestamps

### Tabel `otp_codes`
- `id` - Primary key
- `user_id` - Foreign key ke tabel users
- `otp_code` - Kode OTP 6 digit
- `expires_at` - Waktu expired OTP
- `created_at`, `updated_at` - Timestamps

## Instalasi

1. **Clone repository**
   ```bash
   git clone <repository-url>
   cd Verify-OTP
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Setup environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Konfigurasi database di `.env`**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=verify_otp
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Konfigurasi email di `.env`**
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.mailtrap.io
   MAIL_PORT=2525
   MAIL_USERNAME=your_mailtrap_username
   MAIL_PASSWORD=your_mailtrap_password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS="noreply@example.com"
   MAIL_FROM_NAME="${APP_NAME}"
   ```

6. **Jalankan migration**
   ```bash
   php artisan migrate
   ```

7. **Jalankan server**
   ```bash
   php artisan serve
   ```

## Routes

| Method | URL | Name | Description |
|--------|-----|------|-------------|
| GET | `/register` | `register.show` | Form register |
| POST | `/register` | `register` | Proses register |
| GET | `/verify-otp` | `verify-otp.show` | Form verifikasi OTP |
| POST | `/verify-otp` | `verify-otp` | Proses verifikasi OTP |
| GET | `/login` | `login.show` | Form login |
| POST | `/login` | `login` | Proses login |
| POST | `/logout` | `logout` | Logout |
| GET | `/dashboard` | `dashboard` | Dashboard (protected) |

## Alur Kerja

1. **Register**
   - User mengisi form register
   - Sistem membuat user baru dengan `is_verified = false`
   - Sistem generate OTP 6 digit dan simpan ke database
   - Sistem kirim email OTP ke user
   - Redirect ke halaman verifikasi OTP

2. **Verifikasi OTP**
   - User memasukkan email dan kode OTP
   - Sistem validasi OTP (tidak expired dan benar)
   - Jika valid, update `is_verified = true`
   - Hapus OTP yang sudah digunakan
   - Redirect ke halaman login

3. **Login**
   - User login dengan email dan password
   - Sistem cek apakah user sudah diverifikasi
   - Jika belum verifikasi, redirect ke halaman verifikasi OTP
   - Jika sudah verifikasi, login berhasil dan redirect ke dashboard

4. **Dashboard**
   - Halaman yang dilindungi middleware `auth`
   - Menampilkan informasi user yang sudah login

## Testing

Untuk testing, Anda bisa menggunakan Mailtrap atau mengubah konfigurasi email di `.env`:

```env
MAIL_MAILER=log
```

Ini akan menyimpan email di `storage/logs/laravel.log` untuk testing lokal.

## Keamanan

- Password di-hash menggunakan bcrypt
- OTP expired setelah 10 menit
- OTP dihapus setelah digunakan
- Middleware auth untuk proteksi halaman
- CSRF protection pada semua form
- Validasi input pada semua form

## Troubleshooting

1. **Email tidak terkirim**
   - Pastikan konfigurasi SMTP sudah benar
   - Cek log Laravel di `storage/logs/laravel.log`
   - Gunakan Mailtrap untuk testing

2. **Database error**
   - Pastikan database sudah dibuat
   - Jalankan `php artisan migrate:fresh` untuk reset database

3. **OTP tidak valid**
   - Pastikan OTP belum expired (10 menit)
   - Pastikan email yang dimasukkan benar
   - Cek apakah OTP sudah digunakan

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
