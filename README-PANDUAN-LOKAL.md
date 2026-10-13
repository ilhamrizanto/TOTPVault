# Panduan Penggunaan TOTPVault di Lokal PC & Deployment VPS

Proyek ini telah disiapkan dan dirapikan di:
**`C:\Users\TECNO\Desktop\Project\TOTPVault`**

Semua dependensi lokal (PHP 8.3 dengan ekstensi OpenSSL & PDO MySQL, MariaDB portabel mandiri, database, skema, tabel, dan router lokal) telah dikonfigurasi dan siap pakai (*ready to use*).

---

## 1. Menjalankan di Lokal PC (1-Klik)

Untuk menjalankan server di PC lokal:
1. Masuk ke folder `C:\Users\TECNO\Desktop\Project\TOTPVault`
2. Klik ganda (**Double click**) file:
   👉 **`start-local.bat`**
3. Skrip otomatis:
   * Menyalakan MariaDB lokal di port `3306` (data tersimpan rapi di folder `mariadb/data`).
   * Menyalakan server web PHP di `http://localhost:8000`.
   * Membuka peramban (browser) otomatis ke `http://localhost:8000`.

Untuk menghentikan server jika sudah selesai dipakai:
* Klik ganda: 👉 **`stop-local.bat`**

---

## 2. Cara Login di PC Lokal (Mode Pengujian)

Di mode pengujian lokal (`APP_ENV=development` di `.env`):
1. Buka `http://localhost:8000`
2. Masukkan alamat email Anda (contoh: `admin@bps.go.id`) pada form **Sign in to TOTPVault**.
3. Klik tombol **Send link**.
4. Sistem lokal akan **langsung otomatis memasukkan Anda ke Dashboard** (*instant login* tanpa memerlukan API MailerSend pihak ketiga).

---

## 3. Cara Menggunakan untuk Akun SSO BPS

### A. Menambahkan Token Baru
1. Di Dashboard, klik tombol **Add token** (+).
2. Isi kolom:
   * **Name**: Nama akun (misal: `[PPK] Akun SSO Pak Budi` atau `[Operator] SIMPEG Satker`)
   * **Issuer**: `BPS RI`
   * **Secret**: Masukkan kode Base32 dari layar aktivasi 2FA SSO BPS.  
     *(Atau klik **Scan QR**, lalu paste/drag gambar QR Code 2FA SSO BPS langsung ke browser)*.
3. Klik **Save token**.
4. Token akan muncul di dashboard dengan hitungan mundur 30 detik. Klik angka untuk menyalin (*copy*) kode OTP 6-digit.

### B. Membagikan (*Token Sharing*) ke Rekan Kerja
1. Pada kartu token yang ingin dibagikan, klik ikon **Share / Manage shares**.
2. Masukkan email rekan kerja di satker (misal: `rekan.kerja@bps.go.id`).
3. Tentukan hak akses:
   * **View only** (Disarankan): Rekan kerja hanya bisa melihat kode OTP yang berputar dan menyalin kodenya. Mereka tidak bisa melihat seed/secret aslinya.
   * **Can edit**: Boleh mengubah nama token.
4. Klik **Share**.
5. Saat rekan kerja login ke portal dengan email mereka, token tersebut langsung muncul di layar mereka. Anda bisa mencabut aksesnya (*Unshare*) kapan pun dibutuhkan.

---

## 4. Panduan Deploy ke VPS di Masa Mendatang

Ketika Anda siap mendeploy aplikasi ini ke VPS (Ubuntu/Debian):

### Opsi A: Menggunakan Docker Compose (Paling Mudah & Bersih)
Di dalam repo ini sudah tersedia konfigurasi Docker resmi:
```bash
# 1. Di VPS Anda:
git clone https://github.com/token2/TOTPVault.git
cd TOTPVault

# 2. Buat konfigurasi .env
cp .env.example .env

# 3. Edit .env (sesuaikan domain/IP VPS dan generate ENCRYPTION_KEY 32-byte)
nano .env

# 4. Jalankan stack (Web + MariaDB)
docker compose up -d
```

### Opsi B: Menggunakan Web Server VPS Tradisional (Nginx/Apache + PHP + MySQL)
1. Pasang paket `php8.2-fpm php8.2-mysql php8.2-curl php8.2-mbstring mariadb-server`.
2. Buat database:
   ```sql
   CREATE DATABASE totpvault CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Impor skema:
   ```bash
   mysql -u user -p totpvault < schema.sql
   ```
4. Arahkan DocumentRoot web server ke folder proyek TOTPVault.
5. Salin `.env` dan atur `APP_ENV=production`, `APP_URL=https://totp.satker-anda.go.id`, dan `DB_HOST=localhost`.

---

## 5. Ringkasan File Penting

* **`start-local.bat`** : Peluncur 1-klik lokal.
* **`stop-local.bat`** : Penghenti server lokal.
* **`router.php`** : Router perantara untuk web server bawaan PHP lokal.
* **`.env`** : Pengaturan konfigurasi koneksi database, enkripsi, dan lingkungan.
* **`config/config.php`** : Loader konfigurasi aplikasi (mendukung membaca `.env`).
* **`schema.sql`** : Struktur tabel database.
* **`mariadb/data/`** : Tempat penyimpanan berkas database lokal Anda.
