# Kas Hijau — Aplikasi Manajemen Bank Sampah Digital

Kas Hijau adalah aplikasi web **bank sampah** yang mengubah kebiasaan memilah sampah menjadi
tabungan poin: warga menyetor sampah terpilah, poin dihitung otomatis berdasarkan berat dan
tarif per kategori, lalu poin dapat dicairkan menjadi uang. Petugas mengelola antrian
verifikasi di loket, dan admin memantau seluruh sistem termasuk rekonsiliasi ledger.

| Informasi | Detail |
|---|---|
| Framework | Laravel 13 (PHP 8.3+, `declare(strict_types=1)` di semua file PHP) |
| Antarmuka | Blade Templates + Bootstrap 5.3 + Bootstrap Icons (CDN) |
| Database | MySQL (13 migrasi: 3 bawaan Laravel + 10 aplikasi, 7 tabel inti) |
| Gaya kode | PSR-12, type-safe (enum PHP 8.3), service layer + transaksi atomik |
| Bahasa UI | Indonesia penuh (termasuk pesan validasi, email, dan halaman error) |
| Status | Siap dipakai untuk demo/development; verifikasi email dinonaktifkan sesuai permintaan |

---

## Tentang Proyek Ini

Bank sampah bekerja dengan tiga aktor. **Warga** menyetor sampah terpilah — baik mengajukan
sendiri lewat aplikasi maupun dicatat petugas di loket — dan menerima poin sesuai berat tiap
jenis sampah. **Petugas** memverifikasi pengajuan setoran, memproses atau menolak pengajuan
penarikan, dan mencatat setoran fisik di loket. **Admin** mengelola master data kategori
sampah dan memantau kesehatan sistem lewat Panel Admin yang menampilkan rekonsiliasi ledger,
transaksi terbaru, serta top saldo warga.

Poin adalah aset keuangan, sehingga Kas Hijau memperlakukannya dengan disiplin pembukuan ganda
yang disederhanakan: **saldo poin tidak pernah diubah langsung**. Setiap perubahan saldo
harus melewati `PointLedgerService`, yang mencatat entri `point_ledgers` (debit/kredit) di
dalam transaksi database yang sama dengan perubahan saldo. Invarian **"saldo = rekap ledger"**
selalu terjaga, dan Panel Admin menyediakan pemeriksaan rekonsiliasi untuk membuktikannya
kapan pun.

---

## Fitur Unggulan

### Untuk Warga

- **Dashboard pribadi** — kartu saldo, statistik setoran, dan mutasi poin terakhir dalam
  sekali pandang.
- **Target tabungan poin** — tetapkan nama target (mis. *Tabungan Kompos Rumah*) beserta
  jumlah poinnya dari halaman profil; progres target tampil sebagai progress bar di dalam
  kartu saldo dashboard, lengkap dengan sisa poin yang dibutuhkan dan tanda *tercapai*.
- **Level & lencana gamifikasi** — level warga naik otomatis mengikuti total poin seumur
  hidup (Benih → Semai → Tunas → Rimbun → Lestari) dan enam lencana pencapaian (Langkah
  Pertama, Kolektor, Ahli Daur Ulang, Penabung, Panen Pertama, Setia Sebulan) plus streak
  mingguan; semuanya dihitung dari data riil tanpa tabel tambahan.
- **Ajukan setoran mandiri** — form dinamis multi-kategori (tambah/hapus baris jenis sampah)
  dengan preview poin langsung saat mengetik berat; status *Menunggu Verifikasi* dan bisa
  dibatalkan selama masih pending.
- **Tarik poin** — pilih jumlah lewat chip cepat atau isi manual, preview konversi Rupiah,
  pilih metode pembayaran (transfer/e-wallet memunculkan isian nomor rekening otomatis);
  poin ditahan (*hold*) sejak pengajuan.
- **Riwayat lengkap** — setoran, penarikan, dan mutasi poin dengan filter status + pagination
  gaya Bootstrap.
- **Grafik pribadi** — tren poin 30 hari terakhir (garis) dan komposisi kategori setoran
  (doughnut) memakai Chart.js langsung di dashboard.
- **Lonceng notifikasi in-app** — setiap setoran diverifikasi atau penarikan diproses/ditolak,
  kabar muncul di lonceng navbar (dengan titik merah berdenyut saat ada yang belum dibaca)
  dan di halaman `/notifikasi`; satu klik menandai dibaca sekaligus membuka halaman terkait.

### Untuk Petugas

- **Antrian kerja terpadu** (`/antrian`) — kartu setoran menunggu verifikasi (dengan rincian
  item) dan penarikan menunggu diproses, masing-masing berpagination sendiri.
- **Verifikasi sekali klik** — poin masuk ke saldo warga dan entri ledger tercipta otomatis.
- **Proses / Tolak penarikan** — penolakan mewajibkan alasan (tersimpan di
  `rejected_reason`) dan poin otomatis kembali ke warga (*refund*).
- **Catat setoran loket** — pilih warga + multi-kategori; setoran langsung *Selesai* dan
  poin masuk seketika.
- **Laporan setoran** (`/laporan`) — filter rentang tanggal & kategori, kartu ringkasan
  (transaksi, berat, poin, nilai Rupiah), rekap mutasi poin periode, unduh CSV siap Excel,
  dan cetak PDF lewat dialog peramban.

### Untuk Admin

- **Panel Admin** (`/admin`) — superset dari dashboard: 4 kartu statistik, aktivitas hari
  ini, **kartu Rekonsiliasi Ledger** (badge *Konsisten* atau tabel selisih), tabel setoran &
  penarikan terbaru, top 5 saldo warga, dan ringkasan antrian dengan CTA.
- **CRUD kategori sampah** — tambah/ubah via modal, hapus hanya untuk kategori yang belum
  pernah dipakai (diblokir database + tombol otomatis dinonaktifkan).
- **Bypass peran** — admin lolos semua policy (`Gate::before`) dan middleware `role`.
- **Kelola pengguna** (`/pengguna`) — buat akun warga/petugas baru, ubah peran, dan
  aktifkan/nonaktifkan akun dari tabel dengan pencarian & filter; akun nonaktif otomatis
  diblokir saat login.

### Pengalaman Pengguna

- **Dark mode** — toggle bulan/matahari di bar navbar; pakai `data-bs-theme` native Bootstrap
  5.3, tersimpan di `localStorage` (kunci `kashijau-theme`), mengikuti preferensi sistem saat
  belum dipilih, tanpa kedip saat pindah halaman.
- **Multi-akun login** (pola Google/Gmail) — tambahkan beberapa akun dalam satu browser lalu
  berpindah antar akun sekali klik dari dropdown akun di navbar, tanpa login ulang.
- **Responsif mobile-first** — toggle tema + dropdown akun selalu terlihat di bar navbar
  (tidak perlu membuka menu hamburger); menu link navigasi diciutkan rapi di ponsel.
- **Bahasa Indonesia menyeluruh** — validasi, `auth.failed`/`auth.throttle`, email reset kata
  sandi (termasuk template mail vendor), dan halaman error 403/404/419/500 kustom.
- **Notifikasi email otomatis** — warga menerima email saat setorannya diverifikasi dan saat
  pengajuan penarikan diproses atau ditolak; dikirim setelah transaksi database sukses, dan
  kegagalan SMTP tidak menggagalkan operasi (tercatat di log). Kabar yang sama masuk ke
  lonceng in-app (channel `database`).
- **Animasi & micro-interaction** — kartu dan konten muncul berurutan (reveal on scroll),
  tombol terangkat + bayangan saat hover, ikon bergoyang halus, angka statistik berhitung
  naik, progress bar terisi animatif, dropdown pop-in, dan dot notifikasi berdenyut;
  semuanya otomatis di semua halaman dan otomatis dimatikan bila pengguna memakai
  `prefers-reduced-motion`.

---

## Peran & Hak Akses

Kontrol akses berlapis: **middleware `role:...`** untuk proteksi route (coarse-grained) dan
**Policy** untuk otorisasi per objek (fine-grained). Admin selalu diloloskan.

| Ability | Warga | Petugas | Admin |
|---|:---:|:---:|:---:|
| Melihat setoran/penarikan milik sendiri | ✅ | ✅ | ✅ |
| Melihat semua setoran/penarikan | — | ✅ | ✅ |
| Membuat pengajuan setoran | ✅ | ✅ (loket) | ✅ |
| Verifikasi setoran pending | — | ✅ | ✅ |
| Membatalkan setoran pending sendiri | ✅ | — | ✅ |
| Mengajukan penarikan | ✅ | — | — |
| Memproses / menolak penarikan | — | ✅ | ✅ |
| Mengelola kategori sampah | — | — | ✅ |
| Mengakses `/antrian` | — | ✅ | ✅ |
| Mengakses `/admin` | — | — | ✅ |

---

## Cara Kerja Poin

### Rumus dan Nilai Tukar

```
poin per item = round(berat_kg × point_per_unit)      → WasteCategory::calculatePoints()
rupiah        = poin × config('kas-hijau.point_value')  → default Rp 100 / poin
```

Contoh: 1 kg Botol PET (tarif 10 poin/kg) = **10 poin = Rp 1.000**; 0,5 kg Kresek
(tarif 4 poin/kg) = 2 poin. Poin dibulatkan `round()` dan disimpan sebagai integer.
Nilai tukar dan batas penarikan diatur dari server (`.env`/config) dan **tidak pernah
diterima dari klien**, sehingga tidak bisa dimanipulasi:

```env
KAS_HIJAU_POINT_VALUE=100          # Rupiah per poin
KAS_HIJAU_MIN_WITHDRAWAL_POINTS=100  # minimum per pengajuan (0 = tanpa minimum)
KAS_HIJAU_MAX_WITHDRAWAL_POINTS=0    # maksimum per pengajuan (0 = tanpa maksimum)
```

### Alur Setoran (dua skenario)

1. **Loket (petugas/admin)** — setoran dibuat langsung berstatus *Completed*; poin masuk
   seketika dalam transaksi yang sama.
2. **Pengajuan mandiri (warga)** — setoran masuk berstatus *Pending*; poin baru masuk
   ketika petugas memverifikasi. Warga boleh membatalkan selagi pending (tanpa efek saldo,
   karena poin belum pernah dikreditkan).

### Alur Penarikan (pola HOLD)

Poin dipotong **sejak pengajuan** agar saldo yang sama tidak bisa dipakai ganda oleh beberapa
pengajuan bersamaan. Saldo dan jejak ledger-nya:

| Kejadian | Efek saldo | Entri `point_ledgers` |
|---|---|---|
| Pengajuan dibuat (*requested*) | −points_amount (debit) | Debit, referensi Withdrawal |
| Diproses (*processed*) | tidak berubah | tidak ada (perubahan status saja) |
| Ditolak (*rejected*) | +points_amount (kredit) | Kredit, referensi Withdrawal (refund) |

Saldo tidak cukup melempar `InsufficientPointsException` yang otomatis dirender menjadi HTTP
422 (JSON) atau redirect dengan pesan error berbahasa Indonesia.

---

## Arsitektur & Aturan Emas

1. **Semua mutasi saldo melalui Service Class** — `DepositService` dan `WithdrawalService`
   membungkus setiap penyetoran/penarikan dalam `DB::transaction()` dengan
   `lockForUpdate()` pada baris user/transaksi terkait untuk mencegah race condition.
2. **Imutabilitas ledger** — satu pintu mutasi saldo adalah `PointLedgerService::credit()`
   / `debit()`; model `PointLedger` memblokir `updating` dan `deleting` di level aplikasi,
   dan FK database `restrictOnDelete` menjaga riwayat keuangan tidak ikut terhapus.
   Koreksi selalu berupa **entri baru berlawanan arah**, bukan edit data lama.
3. **Poin dihitung ulang di server** — angka poin di form hanyalah preview JavaScript;
   nilai tersimpan dihitung ulang di dalam transaksi sehingga manipulasi DOM tidak
   berpengaruh. `user_id` justru `prohibited` untuk warga (anti manipulasi kredit ke akun
   lain), sementara petugas/admin wajib memilih warga tujuan.
4. **Scoping riwayat di query, bukan di Blade** — warga hanya bisa melihat datanya sendiri
   karena filter `user_id` diterapkan di controller, bukan sekadar disembunyikan di view.
5. **Registrasi publik selalu berperan warga** — mencegah privilege escalation lewat form;
   akun petugas/admin hanya dibuat oleh admin (seeder/panel).
6. **Keamanan endpoint publik** — login dibatasi 5 percobaan/menit per email+IP, endpoint
   email reset dibatasi 6/menit, respons lupa kata sandi seragam (tidak membocorkan
   keberadaan akun), kekuatan kata sandi global via `Password::defaults()` (minimal 8
   karakter, kombinasi huruf besar + kecil + angka).
7. **Gaya kode** — PSR-12 dan `declare(strict_types=1)` di seluruh file PHP; konvensi nama
   route bahasa Inggris (`dashboard`, `login`) sementara URL berbahasa Indonesia
   (`/setoran`, `/masuk`) untuk kenyamanan pengguna lokal.

---

## Struktur Database

Tujuh migrasi (`2026_10_10_000001` s.d. `000007`) membangun skema berikut di atas tabel
bawaan Laravel (users, password_reset_tokens, sessions); migrasi lanjutan menambah kolom
`is_active` (2026_10_19) serta `savings_goal_*` dan tabel `notifications` (2026_10_26).

| Tabel | Isi | Kolom penting |
|---|---|---|
| `users` | Akun + saldo | `role` (enum warga/petugas/admin), `balance_points`, `phone`, `is_active`, `savings_goal_title`, `savings_goal_points` (target tabungan), `email_verified_at` |
| `waste_categories` | Master kategori sampah | `name` (unik), `point_per_unit` (poin/kg), `description` |
| `deposits` | Header setoran | `user_id`, `officer_id` (nullable), `code` (unik), `status` (pending/completed/cancelled) |
| `deposit_items` | Rincian per jenis sampah | `deposit_id`, `waste_category_id`, `weight_in_kg` decimal(8,2), `points_earned` (snapshot historis) |
| `withdrawals` | Penarikan poin | `user_id`, `officer_id`, `points_amount`, `cash_amount`, `payment_method`, `account_number`, `status` (requested/processed/rejected), `processed_at`, `rejected_reason` |
| `point_ledgers` | Audit trail imutabel | `user_id`, `type` (credit/debit), `points`, `balance_after`, `description`, `reference_type`/`reference_id` (morph ke Deposit/Withdrawal/User) |
| `notifications` | Notifikasi in-app (channel database Laravel) | `id` (uuid), `type`, `notifiable` (morph), `data` (payload JSON), `read_at` |

Catatan desain: kolom `status` bertipe string + cast ke **PHP enum** (bukan SQL ENUM) agar
type-safe di aplikasi tetapi mudah dimigrasi; `points_earned` disimpan sebagai snapshot
sehingga mengubah tarif kategori **tidak meretroaktif** setoran lama.

## Struktur Folder

```
kas-hijau/
├── app/
│   ├── Enums/                      # UserRole, DepositStatus, WithdrawalStatus, LedgerType
│   │                               #   (helper label()/badgeClass()/icon() untuk UI)
│   ├── Exceptions/                 # InsufficientPointsException (render 422 otomatis)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/               # login/daftar/reset + MultiAccountController
│   │   │   ├── DashboardController.php   # dashboard per peran + Panel Admin (/admin)
│   │   │   ├── DepositController.php     # riwayat/buat/verifikasi/batal setoran
│   │   │   ├── WithdrawalController.php  # ajukan/proses/tolak penarikan
│   │   │   ├── QueueController.php       # antrian kerja petugas
│   │   │   ├── PointLedgerController.php # riwayat mutasi poin
│   │   │   ├── NotificationController.php# lonceng + halaman /notifikasi + tandai dibaca
│   │   │   ├── ProfileController.php     # profil + target tabungan + ganti kata sandi
│   │   │   └── WasteCategoryController.php # CRUD kategori (admin)
│   │   ├── Middleware/             # EnsureUserHasRole (proteksi `role:...`)
│   │   └── Requests/               # LoginRequest, StoreDepositRequest,
│   │                               # StoreWithdrawalRequest, RejectWithdrawalRequest,
│   │                               # StoreWasteCategoryRequest
│   ├── Models/                     # 6 model + relasi typed + casts() enum
│   ├── Notifications/              # DepositVerified + WithdrawalProcessed/Rejected + ResetPassword (Indonesia)
│   ├── Policies/                   # Deposit, Withdrawal, WasteCategory
│   ├── Providers/                  # AppServiceProvider (Gate::before + Password::defaults)
│   └── Services/
│       ├── PointLedgerService.php  # satu pintu mutasi saldo + ledger
│       ├── DepositService.php      # transaksi penyetoran
│       ├── WithdrawalService.php   # pola hold, proses, tolak + refund
│       ├── GamificationService.php # level, lencana, streak mingguan (dihitung on-the-fly)
│       └── MultiAccountSession.php # daftar akun per sesi (multi-login)
├── bootstrap/app.php               # alias middleware `role` + redirect guest/user
├── config/kas-hijau.php              # point_value & batas penarikan
├── database/
│   ├── migrations/                 # 13 migrasi — 3 bawaan Laravel (0001_01_01_*: users,
│   │                               #   password_reset_tokens, sessions, cache, jobs) + 10
│   │                               #   aplikasi (kolom users + is_active + target tabungan,
│   │                               #   5 tabel, evolusi withdrawals, tabel notifications)
│   └── seeders/                    # Database, DemoUser, DummyUser, WasteCategory, DemoTransaction
├── lang/id/                        # auth, passwords, pagination, validation (Indonesia)
├── resources/views/
│   ├── auth/                       # login, register, add-account, lupa/reset kata sandi
│   ├── dashboard/                  # warga.blade / petugas.blade / admin.blade / admin-overview
│   ├── deposits/ · withdrawals/ · points/ · waste-categories/ · officer/
│   ├── notifications/              # halaman /notifikasi (daftar + tandai dibaca)
│   ├── layouts/                    # app (navbar + lonceng), guest (kartu tengah), errors
│   ├── partials/                   # flash, modal-reject, theme-head, theme-toggle, motion
│   ├── errors/                     # 403, 404, 419, 500
│   ├── profile/ · vendor/notifications/ · welcome.blade.php
└── routes/
    ├── web.php                     # dashboard, profil, setoran, penarikan, antrian, admin
    └── auth.php                    # masuk/daftar/keluar/reset + multi-akun
```

## Peta Route

| URL | Method | Middleware | Keterangan |
|---|---|---|---|
| `/` | GET | — | Landing page |
| `/masuk` | GET/POST | guest | Login (rate limit 5 percobaan/menit) |
| `/daftar` | GET/POST | guest | Registrasi (selalu warga, langsung login otomatis) |
| `/lupa-kata-sandi` | GET/POST | guest | Kirim tautan reset (POST dibatasi 6/menit) |
| `/atur-ulang-kata-sandi/{token}` | GET/POST | guest | Setel ulang kata sandi (POST dibatasi 6/menit) |
| `/keluar` | POST | auth | Keluar dari akun aktif (otomatis pindah ke akun tersisa bila ada) |
| `/dashboard` | GET | auth | Konten otomatis sesuai peran |
| `/profil` | GET/PATCH | auth | Edit data diri |
| `/profil/target` | PATCH | auth | Simpan/hapus target tabungan poin |
| `/profil/kata-sandi` | PUT | auth | Ganti kata sandi |
| `/notifikasi` | GET | auth | Halaman daftar notifikasi (15/halaman) |
| `/notifikasi/baca-semua` | POST | auth | Tandai semua notifikasi dibaca |
| `/notifikasi/{id}/baca` | POST | auth | Tandai satu notifikasi dibaca lalu buka halaman terkait |
| `/akun/tambah` | GET/POST | auth | Tambah akun lain ke sesi (POST memakai rate limiter login) |
| `/akun/pindah` | POST | auth | Pindah ke akun terdaftar pada sesi ini |
| `/akun/keluar-semua` | POST | auth | Keluar dari semua akun (sesi diakhiri) |
| `/setoran` | GET | auth | Riwayat setoran (warga: miliknya; petugas/admin: semua) + filter status |
| `/setoran/buat` | GET | auth | Form setoran dinamis (warga: pengajuan; petugas/admin: loket) |
| `/setoran` | POST | auth | Simpan setoran via DepositService |
| `/setoran/{deposit}/verifikasi` | POST | auth + `role:petugas,admin` | Verifikasi pending (kredit poin) |
| `/setoran/{deposit}` | DELETE | auth | Batalkan setoran pending milik sendiri |
| `/penarikan` | GET | auth | Riwayat penarikan + filter status |
| `/penarikan/ajukan` | GET | auth | Form pengajuan penarikan (hanya warga) |
| `/penarikan` | POST | auth | Simpan pengajuan (pola hold) |
| `/penarikan/{withdrawal}/proses` | POST | auth + `role:petugas,admin` | Tandai selesai dibayar |
| `/penarikan/{withdrawal}/tolak` | POST | auth + `role:petugas,admin` | Tolak + refund poin (alasan wajib) |
| `/riwayat-poin` | GET | auth | Riwayat mutasi poin pribadi + filter arah |
| `/antrian` | GET | auth + `role:petugas,admin` | Antrian kerja petugas |
| `/admin` | GET | auth + `role:admin` | Panel Admin (rekonsiliasi, statistik, transaksi terbaru) |
| `/kategori-sampah` | GET/POST | auth + `role:admin` | Daftar + tambah kategori (modal) |
| `/kategori-sampah/{kategori}` | PUT/DELETE | auth + `role:admin` | Ubah / hapus kategori |

## Panduan Instalasi

### Prasyarat

- PHP 8.3+ dengan ekstensi `pdo_mysql`, `mbstring`, `openssl` (XAMPP terbaru sudah memenuhi)
- Composer, atau XAMPP + phpMyAdmin bila memakai jalur import SQL
- Project Laravel 11/12/13 yang sudah terinisialisasi

### A. Instalasi via Composer (disarankan)

1. Buat project Laravel bila belum ada:

   ```bash
   composer create-project laravel/laravel kas-hijau
   ```

2. Salin folder `app/`, `bootstrap/`, `config/`, `database/`, `lang/`, `resources/`, dan
   `routes/` dari paket ini ke root project (struktur folder identik dengan skeleton
   Laravel modern).

   > `bootstrap/app.php` menimpa milik skeleton. Bila project Anda sudah mengeditnya,
   > cukup salin isi `withMiddleware()`: alias `role`, `redirectGuestsTo()` ke route
   > `login`, dan `redirectUsersTo()` ke route `dashboard`.
   > Folder `database/migrations/` dari paket ini SUDAH memuat 3 migrasi bawaan
   > Laravel (`0001_01_01_*` pembuat tabel `users`, `password_reset_tokens`,
   > `sessions`, `cache`, `jobs`) sehingga aman menimpa seluruh folder `database/`
   > dari skeleton. JANGAN hapus file `0001_01_01_*` — migrasi aplikasi menambah
   > kolom ke tabel `users` yang dibuat oleh migrasi tersebut.

3. Konfigurasi database, mail, dan locale di `.env`:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_DATABASE=kas_hijau_db
   APP_LOCALE=id
   APP_FALLBACK_LOCALE=en
   MAIL_MAILER=log                  # development; produksi ganti smtp
   MAIL_FROM_ADDRESS=no-reply@kashijau.test
   MAIL_FROM_NAME="Kas Hijau"
   KAS_HIJAU_POINT_VALUE=100
   KAS_HIJAU_MIN_WITHDRAWAL_POINTS=100
   KAS_HIJAU_MAX_WITHDRAWAL_POINTS=0
   ```

   > Dengan `MAIL_MAILER=log`, email reset kata sandi ditulis ke
   > `storage/logs/laravel.log` — mudah dites tanpa SMTP sungguhan.

4. Jalankan migrasi lalu seeder:

   ```bash
   php artisan migrate
   php artisan db:seed
   ```

   > Seeder idempotent (aman dijalankan berulang). Ingin menambah akun dummy saja?
   > `php artisan db:seed --class=DummyUserSeeder`.

5. Jalankan server dan login dengan akun demo:

   ```bash
   php artisan serve
   ```

6. *(Opsional, permission granular)* pasang Spatie lalu tambahkan trait `HasRoles`
   pada `app/Models/User.php`:

   ```php
   use Spatie\Permission\Traits\HasRoles;

   class User extends Authenticatable
   {
       use HasFactory, Notifiable, HasRoles;
   ```

   ```bash
   composer require spatie/laravel-permission
   php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
   php artisan migrate
   ```

### B. Alternatif Cepat: Import SQL via phpMyAdmin (XAMPP)

Bila `php artisan` sulit dijalankan di lingkungan Anda, data dummy lengkap tersedia sebagai
file **`kas-hijau-dummy-data.sql`** yang menyertai paket ini:

1. Pastikan tabel sudah dibuat (jalankan `php artisan migrate` sekali, atau buat database
   `kas_hijau_db` lalu migrasi). Import SQL ini TIDAK membuat tabel — hanya mengisi data.
2. Buka **phpMyAdmin → database `kas_hijau_db` → tab Import** → pilih
   `kas-hijau-dummy-data.sql` → **Import**. File memakai `TRUNCATE` + `FOREIGN_KEY_CHECKS=0`
   sehingga aman di-import ulang kapan pun (data dikembalikan ke kondisi awal).
3. Isi file: 16 users (password `password`), 6 kategori sampah, 4 setoran, 6 item setoran,
   3 penarikan (proses/tolak+refund/ajukan), dan 19 entri ledger kronologis — identik dengan
   hasil `php artisan db:seed`, termasuk invarian **saldo = rekap ledger**.
4. Dua query verifikasi rekonsiliasi tersedia di bagian akhir file untuk dicek setelah import.

## Akun Demo

**Password seluruh akun seeded: `password`** (hanya untuk akun hasil seeder/import SQL —
registrasi manual wajib memenuhi kebijakan kekuatan kata sandi, contoh: `Passw0rd123`).

| Email | Peran | Saldo awal | Keterangan |
|---|---|---|---|
| admin@kashijau.test | Administrator | 0 | Akses seluruh halaman (bypass middleware & gate) |
| petugas@kashijau.test | Petugas | 0 | Verifikasi setoran & proses penarikan |
| warga@kashijau.test | Warga | 1.500 poin | Saldo akhir **993** setelah transaksi demo |
| sari@kashijau.test | Warga | 0 | Saldo akhir **50** setelah transaksi demo |
| andi@kashijau.test | Warga | 2.450 poin | Dummy (DummyUserSeeder) |
| dewi@kashijau.test | Warga | 1.830 poin | Dummy |
| agus@kashijau.test | Warga | 920 poin | Dummy |
| rina@kashijau.test | Warga | 3.750 poin | Dummy |
| joko@kashijau.test | Warga | 640 poin | Dummy |
| fitri@kashijau.test | Warga | 2.880 poin | Dummy |
| rudi@kashijau.test | Warga | 1.500 poin | Dummy |
| nina@kashijau.test | Warga | 110 poin | Dummy |
| eko@kashijau.test | Warga | 5.000 poin | Dummy |
| lina@kashijau.test | Warga | 2.210 poin | Dummy |
| doni@kashijau.test | Warga | 760 poin | Dummy |
| maya@kashijau.test | Warga | 3.400 poin | Dummy |

Verifikasi email saat ini **nonaktif**, jadi semua akun langsung bisa dipakai tanpa membuka
email. Kolom `email_verified_at` tetap ada dan diisi `now()` oleh seeder sebagai bekal bila
fitur diaktifkan kembali (lihat bagian di bawah).

**Transaksi demo** — `DemoTransactionSeeder` mengisi antrian & riwayat agar UI langsung bisa
diuji: 2 setoran selesai (loket), 3 setoran pending (antrian verifikasi), 1 penarikan
diproses, 1 ditolak dengan refund, dan 1 penarikan diajukan (hold). Semua dibuat melalui
`DepositService`/`WithdrawalService` — bukan set kolom manual — sehingga saldo dan ledger
tetap rekonsiliasi. Saldo akhir warga demo: 1.500 + 43 (setoran loket) − 300 (diproses)
− 250 (diajukan/hold) = **993 poin** (200 poin penarikan ditolak sudah dikembalikan).

## Fitur Multi-Akun Login

Satu browser bisa memuat beberapa akun Kas Hijau sekaligus (pola Google/Gmail). Daftar akun
disimpan di sesi server — **hanya ID user, tanpa kredensial** — sedangkan akun aktif tetap
ditangani guard `web` bawaan Laravel, sehingga middleware `role`, policy, dan scoping
otomatis mengikuti akun yang sedang aktif.

Cara pakai:

1. Login seperti biasa → klik **dropdown akun di bar navbar** (ikon orang, selalu terlihat).
2. Pilih **"Tambah akun lain"** → isi email + kata sandi akun tujuan (rate limit sama dengan
   halaman login: 5 percobaan/menit) → akun itu langsung menjadi aktif.
3. Kapan pun ingin berpindah: buka dropdown → bagian **"Berpindah ke akun"** → klik akun
   tujuan (sekali klik, tanpa mengisi kredensial lagi).
4. **"Keluar dari akun ini"** mengeluarkan akun aktif saja; bila masih ada akun tersisa di
   sesi, akun pertama langsung diaktifkan otomatis. **"Keluar dari semua akun"** mengakhiri
   seluruh sesi (muncul hanya bila akun terdaftar lebih dari satu).

Catatan keamanan: berpindah akun tidak meminta ulang kata sandi karena seluruh akun dalam
daftar dianggap sudah terautentikasi pada perangkat yang sama — persis perilaku multi-akun
Google/Gmail. Daftar hilang otomatis saat sesi berakhir/logout semua, dan pindah ke ID yang
tidak terdaftar di sesi ditolak di backend.

## Verifikasi Email — Status Nonaktif

Sesuai permintaan, fitur verifikasi email dimatikan: model `User` tidak lagi mengimplementasikan
`MustVerifyEmail`, middleware `verified` dilepas dari semua route, dan controller/route/view
verifikasi telah dihapus bersih (bukan sekadar dimatikan). Konsekuensinya, registrasi baru
langsung login dan bisa memakai seluruh halaman aplikasi. Fitur lupa/reset kata sandi TIDAK
terpengaruh dan tetap berjalan.

Bila suatu saat ingin diaktifkan kembali (pola Breeze):

1. Tambahkan `implements \Illuminate\Contracts\Auth\MustVerifyEmail` pada model `User`
   beserta override `sendEmailVerificationNotification()`.
2. Buat ulang tiga controller verifikasi (`EmailVerificationPromptController`,
   `EmailVerificationNotificationController`, `VerifyEmailController`) beserta route
   `verification.notice` / `verification.verify` / `verification.send` di `routes/auth.php`
   (grup middleware `auth`).
3. Bungkus grup route terautentikasi di `routes/web.php` dengan middleware `verified`.
4. Tambahkan view `resources/views/auth/verify-email.blade.php` dan (opsional) notifikasi
   kustom `VerifyEmailNotification` berbahasa Indonesia.
5. Pastikan konfigurasi mail di `.env` valid (lihat tips `MAIL_MAILER=log` di atas).

## Keputusan Desain Penting

| Keputusan | Alasan |
|---|---|
| Poin disimpan sebagai integer dan dibulatkan dengan `round()` | Sistem poin sederhana untuk redeem; contoh 1.25 kg × 10 poin/kg = 12.5 menjadi 13 poin. Terpusat di `WasteCategory::calculatePoints()`. |
| Kolom `status` bertipe string + cast ke PHP enum | Menghindari SQL ENUM yang sulit dimigrasi; type-safe di level aplikasi dan tetap fleksibel. |
| `PointLedgerService` satu pintu mutasi saldo | Semua perubahan `balance_points` hanya lewat `credit()`/`debit()`, sehingga aturan "setiap perubahan = entri ledger baru" mustahil terlewat; dipakai bersama DepositService, WithdrawalService, dan seeder. |
| Pola HOLD pada penarikan | Poin dipotong saat pengajuan (requested) agar saldo tidak bisa dipakai ganda oleh beberapa pengajuan bersamaan; penolakan otomatis mengembalikan poin lewat entri kredit. |
| Nilai tukar poin dari config server | `cash_amount` dihitung dengan `config('kas-hijau.point_value')`; rate tidak pernah diterima dari klien sehingga tidak bisa dimanipulasi. |
| `lockForUpdate()` pada baris user/deposit/withdrawal | Mencegah race condition: dua transaksi tidak bisa membaca saldo yang sama dan memutasi bersamaan. |
| FK `restrictOnDelete` untuk data keuangan | Riwayat setoran/penarikan/ledger tidak boleh hilang mengikuti penghapusan user; gunakan soft delete / flag aktif. |
| `PointLedger` memblokir `updating` dan `deleting` | Imutabilitas audit trail di level aplikasi; koreksi selalu berupa entri baru yang berlawanan arah. |
| `Gate::before()` untuk admin | Admin lolos semua policy tanpa mencabang `isAdmin()` di tiap method; warga & petugas tetap dibatasi method policy. |
| Registrasi publik selalu peran `warga` | Mencegah privilege escalation lewat form; akun petugas/admin hanya dibuat admin (seeder/panel). |
| Login dengan rate limiter (pola Breeze) | 5 percobaan per menit per kombinasi email+IP; menahan serangan brute force pada endpoint publik. |
| Saldo awal seeder via ledger, bukan set kolom | Menjaga invarian "saldo = rekap ledger" sehingga rekonsiliasi tidak pernah gagal pada data demo. |
| Verifikasi email DINONAKTIFKAN (permintaan user) | Registrasi langsung dipakai tanpa tautan verifikasi agar demo/development tidak bergantung konfigurasi mail; kolom `email_verified_at` + cast tetap dipertahankan sebagai bekal aktivasi ulang. |
| Notifikasi reset sandi kustom | Email lupa kata sandi selalu berbahasa Indonesia deterministik, tanpa bergantung paket terjemahan framework; kedaluwarsa token tetap dikelola framework (subclass `ResetPassword`). |
| Template mail dipublikasikan ke `resources/views/vendor/notifications` | Footer/subcopy email di luar kendali MailMessage ikut berbahasa Indonesia; plain-text version ikut dilokalkan. |
| `Password::defaults()` global (min 8, mixedCase, numbers) | Satu tempat mengatur kekuatan kata sandi untuk registrasi & ganti kata sandi; mudah diperketat tanpa menyentuh controller. |
| Throttle 6/menit pada endpoint email reset | Endpoint publik pengirim email dibatasi untuk mencegah mail bombing. |
| File bahasa `lang/id` + `APP_LOCALE=id` | Pesan validasi, `auth.failed`, `auth.throttle`, dan `passwords.*` berbahasa Indonesia secara global; FormRequest dengan pesan khusus tetap memakai pesannya sendiri. |
| Halaman error kustom 403/404/419/500 | HTTP 403 dari middleware `role` (mis. warga membuka `/admin`) tampil rapi berbahasa Indonesia, bukan halaman default Laravel. |
| Pesan reset password dibedakan sama (uniform response) | Tidak membocorkan keberadaan akun pada endpoint publik lupa kata sandi. |
| Evolusi skema withdrawals (migrasi 007) | `officer_id`, `processed_at`, `rejected_reason` ditambahkan untuk akuntabilitas petugas dan transparansi alasan penolakan ke warga. |
| Scoping riwayat WAJIB di query, bukan di Blade | Menyembunyikan baris hanya di Blade berarti data bocor lewat URL langsung; filter `user_id` diterapkan di level controller. |
| Warga `prohibited` mengirim `user_id` pada form setoran | Anti manipulasi kredit poin ke akun lain lewat POST manual; petugas/admin justru WAJIB memilih warga (rule `exists` + `where role = warga`). |
| Poin dihitung ulang di server pada penyimpanan setoran | Angka poin di form hanya preview JS; nilai tersimpan dihitung `WasteCategory::calculatePoints()` di dalam transaksi, sehingga manipulasi DOM tidak berpengaruh. |
| Form item dinamis pakai `<template>` + index berurutan | Baris item dikloning JS dengan placeholder `__IDX__`; isian lama dipulihkan dari `old('items')` setelah error validasi agar warga tidak mengetik ulang. |
| Pagination `Paginator::useBootstrapFive()` | Markup paginasi selaras Bootstrap 5; filter status memakai `withQueryString()` agar filter tidak hilang saat pindah halaman. |
| Dua paginator satu halaman memakai `pageName` berbeda | Halaman antrian punya daftar setoran & penarikan masing-masing 10/halaman; `setoran_page` & `penarikan_page` membuat navigasi daftar A tidak mereset posisi daftar B. |
| Aksi sensitif lewat form POST + policy, bukan link GET | Verifikasi/proses/tolak/batal semua berbentuk form dengan CSRF + konfirmasi; policy tetap gerbang utama. |
| Hapus kategori ditolak ramah + tombol disabled di UI | FK `restrictOnDelete` blokir di database; controller menangkap `QueryException` jadi pesan Indonesia, dan tombol hapus dinonaktifkan bila kategori pernah dipakai. |
| `DemoTransactionSeeder` lewat Service, bukan set kolom | Data demo wajib lulus jalur yang sama dengan produksi (transaksi + row lock + entri ledger), sehingga invarian "saldo = rekap ledger" terjaga sejak data pertama. |
| Perubahan tarif kategori tidak meretroaktif setoran lama | `points_earned` tersimpan permanen di `deposit_items` (snapshot historis); mengubah `point_per_unit` hanya berlaku untuk setoran berikutnya. |
| Dark mode via `data-bs-theme` Bootstrap 5.3 | Nol CSS replika per komponen — utilitas `-subtle` dan `bg-body` otomatis adaptif; preferensi di localStorage tanpa perubahan database. |
| Multi-akun menyimpan ID user saja di sesi | Tidak ada kredensial di sesi; akun aktif tetap milik guard `web` sehingga seluruh lapisan otorisasi bekerja tanpa penyesuaian; pindah ke ID di luar daftar ditolak backend. |

## Contoh Pemakaian Service

Semua operasi keuangan bisa dipanggil dari Tinker/CLI/pekerjaan latar dengan jaminan
transaksi yang sama seperti lewat HTTP:

```php
use App\Enums\DepositStatus;
use App\Models\Withdrawal;
use App\Services\DepositService;
use App\Services\WithdrawalService;

/* ---------- DepositService ---------- */
$deposits = app(DepositService::class);

// Skenario 1: petugas mencatat setoran di loket (poin langsung masuk)
$deposit = $deposits->createDeposit(
    holder: $warga,
    items: [
        ['waste_category_id' => 1, 'weight_in_kg' => 2.5],
        ['waste_category_id' => 3, 'weight_in_kg' => 1.25],
    ],
    officer: $petugas,
    initialStatus: DepositStatus::Completed,
);

// Skenario 2: warga mengajukan mandiri lalu diverifikasi petugas
$pengajuan = $deposits->createDeposit(holder: $warga, items: $items); // pending
$selesai   = $deposits->completeDeposit($pengajuan, $petugas);        // poin masuk

// Membatalkan pengajuan yang masih pending
$deposits->cancelDeposit($pengajuan);

/* ---------- WithdrawalService ---------- */
$withdrawals = app(WithdrawalService::class);

// 1. Warga mengajukan: poin langsung dipotong (hold)
$wdr = $withdrawals->requestWithdrawal(
    holder: $warga,
    pointsAmount: 500,                                   // 500 poin
    paymentMethod: Withdrawal::PAYMENT_BANK_TRANSFER,
    accountNumber: '1234567890',                         // wajib untuk non-tunai
);
// cash_amount = 500 × config('kas-hijau.point_value') = Rp 50.000

// 2a. Petugas menolak: poin kembali otomatis + entri kredit di ledger
$withdrawals->rejectWithdrawal($wdr, $petugas, 'Rekening tidak atas nama pemilik.');

// 2b. Atau diproses: ditandai selesai dibayar, saldo tidak berubah lagi
$withdrawals->processWithdrawal($wdr, $petugas);
```

Kesalahan yang ditangani rapi:

- Saldo tidak cukup → `InsufficientPointsException` (otomatis 422 JSON atau redirect dengan
  pesan error berbahasa Indonesia).
- Metode pembayaran non-tunai tanpa nomor rekening → `InvalidArgumentException`.
- Penarikan sudah bukan `requested` → `RuntimeException` (anti proses ganda, dibackend row
  lock).

## Pembagian Validasi

| Lapisan | Yang divalidasi |
|---|---|
| `StoreDepositRequest` (HTTP) | Array `items` (1-10 baris), kategori harus ada, berat > 0; `user_id` wajib untuk petugas/admin (harus berperan warga) dan `prohibited` untuk warga; `prepareForValidation()` membuang baris kosong dan menormalkan desimal koma. |
| `DepositService` (bisnis, dalam transaksi) | Hitung poin ulang per item, row lock, penolakan status awal tidak sah. |
| `StoreWithdrawalRequest` (HTTP) | Daftar metode pembayaran dari service, batas min/max dari config, nomor rekening wajib untuk non-tunai, pesan error Indonesia. |
| `WithdrawalService` (bisnis, dalam transaksi) | Kecukupan saldo dengan row lock, konversi poin → Rupiah memakai rate server, anti proses ganda. |
| `RejectWithdrawalRequest` | Alasan penolakan wajib (5-255 karakter) — tersimpan di `rejected_reason` dan keterangan ledger refund. |
| `StoreWasteCategoryRequest` | Nama unik (mengabaikan baris yang diedit), tarif poin integer 0-1.000.000. |
| `LoginRequest` | Format email/kata sandi + rate limiter 5 percobaan/menit. |

Pengecekan saldo sengaja TIDAK diletakkan di FormRequest agar selalu dieksekusi di dalam
transaksi database bersama debit ledger. Service tetap memvalidasi ulang semuanya
(*defense in depth*) karena bisa dipanggil dari Tinker/CLI tanpa HTTP layer.

## Skenario Uji Manual

1. **Warga** (`warga@kashijau.test`) → Dashboard → aksi cepat → **Ajukan Setoran Mandiri** →
   tambah 2 jenis sampah (baris dinamis + preview poin) → kirim → status *Menunggu
   Verifikasi* di Riwayat Setoran (dapat dibatalkan selama pending).
2. **Warga** → **Tarik Poin** → pilih jumlah (chip cepat / manual, preview Rupiah) → pilih
   metode (nomor rekening muncul otomatis untuk non-tunai) → kirim → poin terpotong (hold)
   dan muncul di Riwayat Penarikan status *Diajukan*.
3. **Petugas** (`petugas@kashijau.test`) → Antrian → kartu *Setoran Menunggu Verifikasi* →
   **Verifikasi** → poin masuk ke saldo warga, entri ledger kredit tercipta.
4. **Petugas** → kartu *Penarikan Menunggu Diproses* → **Proses** (selesai dibayar) atau
   **Tolak** lewat modal (alasan wajib) → bila ditolak, poin otomatis kembali ke warga.
5. **Petugas** → **Catat Setoran di Loket** → pilih warga + multi-kategori → simpan →
   setoran langsung *Selesai*, poin masuk seketika.
6. **Admin** (`admin@kashijau.test`) → menu **Kategori** → tambah/ubah via modal; hapus hanya
   untuk kategori yang belum dipakai → buka **Admin** (`/admin`) → cek kartu Rekonsiliasi
   Ledger (badge *Konsisten*) dan Top Saldo.
7. **Multi-akun** → dari akun mana pun, klik dropdown akun di navbar → **Tambah akun lain**
   (`petugas@kashijau.test` / `password`) → kini aktif sebagai petugas → dropdown → klik
   warga di *Berpindah ke akun* → kembali jadi warga tanpa login ulang → coba **Keluar dari
   akun ini** → otomatis pindah ke akun tersisa.
8. **Dark mode** → klik ikon bulan di navbar → seluruh halaman (termasuk modal, dropdown,
   halaman error) ikut gelap → refresh halaman → tema tetap tersimpan.

## Catatan spatie/laravel-permission

Kolom `users.role` dipertahankan sebagai peran sederhana untuk filter cepat, middleware
`role`, dan seed data awal, sementara Spatie akan menangani permission granular (mis.
`verify-deposits`, `manage-withdrawals`). Aktifkan trait `HasRoles` pada model `User`
setelah package terpasang, lalu sinkronkan role Spatie dengan nilai kolom `role` saat
seeder dijalankan agar dua mekanisme ini tidak saling bertentangan.

## Riwayat Pengembangan

| Tahap | Hasil |
|---|---|
| Langkah 1 | 6 migrasi, 6 model + relasi, 4 enum PHP 8.3, `DepositService` (transaksi atomik). |
| Langkah 2 | `WithdrawalService` (pola hold), `PointLedgerService`, config nilai tukar, 3 policy, `StoreWithdrawalRequest`. |
| Langkah 3 | Auth gaya Breeze (Blade + Bootstrap 5), middleware `role:...`, dashboard per peran, profil, halaman error kustom, `lang/id`, seeder akun + kategori. |
| Langkah 4 | UI fungsional penuh: setoran dinamis, antrian petugas, riwayat berfilter + pagination, CRUD kategori, seeder transaksi demo via Service. |
| Revisi | Verifikasi email dinonaktifkan (hapus bersih) + 12 akun warga dummy berledger. |
| Revisi data | `kas-hijau-dummy-data.sql` — dump MySQL idempotent identik dengan seeder untuk import phpMyAdmin. |
| Revisi UI | Dark mode `data-bs-theme` + Panel Admin `/admin` (rekonsiliasi, transaksi terbaru, top saldo, antrian). |
| Revisi terakhir | Navbar: toggle tema + dropdown akun pindah ke bar atas (di luar menu collapse); dropdown akun mendukung multi-login (tambah/pindah/keluar semua). |
| Fitur baru | Grafik Chart.js di dashboard (tren 30 hari, komposisi kategori, warga teraktif), laporan setoran CSV/PDF (`/laporan`), notifikasi email otomatis (verifikasi setoran, penarikan diproses/ditolak), dan panel kelola pengguna (`/pengguna`) dengan kolom `is_active`. |
| Fitur baru II | Target tabungan poin (progres di dashboard), gamifikasi level & lencana (`GamificationService`), lonceng notifikasi in-app + halaman `/notifikasi` (channel database), dan animasi interaktif di semua halaman (reveal on scroll, hover lift tombol/kartu, count-up, progress bar animatif) via partial `motion`. |

## Rencana Pengembangan Berikutnya

1. **QR code setoran** — kode QR per pengajuan setoran yang dipindai petugas di loket
   untuk mempercepat verifikasi.
2. **Histori harga kategori** — nilai tukar poin per kategori dapat berubah per periode
   dengan jejak historis yang tetap teraudit.
3. **Ekspor laporan terjadwal** — kirim CSV/PDF rekap berkala (mingguan/bulanan) via email.
4. **Permintaan penjemputan** — warga mengajukan penjemputan sampah dengan jadwal dan status.
5. **REST API + aplikasi mobile** (Sanctum) atau integrasi notifikasi WhatsApp.

Fitur yang sudah terealisasi dari rencana sebelumnya: target tabungan poin, gamifikasi
(level & lencana), dan notifikasi in-app (lonceng database).

## Troubleshooting

| Gejala | Penyebab & solusi |
|---|---|
| Semua tabel kosong di phpMyAdmin | Seeder belum pernah dijalankan di lingkungan Anda. Jalankan `php artisan migrate` lalu `php artisan db:seed`, atau import `kas-hijau-dummy-data.sql` lewat tab Import phpMyAdmin (tabel harus sudah dibuat oleh migrasi). |
| `SQLSTATE[42S02]` — 1146 Table 'users' doesn't exist saat `php artisan migrate` | Tabel `users` dibuat oleh migrasi BAWAAN Laravel (`0001_01_01_000000_create_users_table.php`), bukan oleh migrasi aplikasi. File bawaan itu hilang dari `database/migrations/` (biasanya karena folder `database/` skeleton tertimpa seluruhnya). Salin kembali 3 file `0001_01_01_*` dari skeleton Laravel baru (`composer create-project laravel/laravel tmp` lalu salin `tmp/database/migrations/0001_01_01_*.php`), atau pakai paket ini yang sudah menyertakannya — lalu `php artisan migrate:fresh --seed`. |
| `SQLSTATE[42S21]` — 1060 Duplicate column name 'phone' saat `php artisan migrate` | Database Anda sudah memuat tabel `users` lama (kolom `phone` dsb. sudah ada), tetapi tabel `migrations` kosong/tidak sinkron sehingga Laravel mencoba menambah kolom yang sama lagi. Solusi terbersih untuk development/demo: `php artisan migrate:fresh --seed` — menghapus SEMUA tabel lalu migrasi + seeder dari nol (data demo terisi otomatis). Alternatif: hapus semua tabel di phpMyAdmin terlebih dulu, baru jalankan `php artisan migrate`. Jika tabel & data Anda sudah lengkap dan benar, migrasi tidak perlu dijalankan ulang sama sekali — cukup lanjut memakai aplikasi. |
| Login gagal padahal email benar | Pastikan memakai password demo `password` untuk akun seeded. Akun hasil registrasi manual memakai password Anda sendiri. Terkena rate limit? Tunggu 1 menit (5 percobaan/menit). |
| Pesan "Akun ini sedang dinonaktifkan" saat login akun dummy | Struktur database tidak termutakhir: kolom `is_active` (migrasi 2026_10_19) belum ada di tabel `users`, biasanya karena kode diperbarui tanpa menjalankan migrasi, dan import SQL berhenti di `TRUNCATE notifications`. Jalankan `php artisan migrate` terlebih dulu, lalu import ulang `kas-hijau-dummy-data.sql` (versi terbaru sudah mengisi `is_active` = 1 untuk semua akun). Solusi cepat tanpa import ulang: `UPDATE users SET is_active = 1;` di phpMyAdmin. Versi aplikasi terbaru hanya memblokir login bila akun memang dinonaktifkan lewat menu Pengguna. |
| Email reset tidak terkirim | `MAIL_MAILER=log` menulis email ke `storage/logs/laravel.log`, bukan mengirim sungguhan. Ini perilaku normal untuk development. |
| Warga membuka `/admin` mendapat 403 | Memang disengaja — halaman error 403 kustom menampilkan daftar peran yang diizinkan. Login sebagai admin atau petugas sesuai halaman tujuan. |
| Saldo tampak tidak cocok dengan mutasi | Cek Panel Admin (`/admin`) → kartu Rekonsiliasi Ledger. Bila pakai import SQL, pastikan import file terbaru (sudah memuat 19 entri ledger kronologis) dan jangan edit `balance_points` manual — selalu lewat service/ledger. |
| Tombol hapus kategori nonaktif | Kategori pernah dipakai di setoran; FK `restrictOnDelete` melarang agar riwayat poin historis tetap konsisten. |
| Tema kembali terang di browser lain | Preferensi tema disimpan per-browser di `localStorage` (kunci `kashijau-theme`), bukan di database. |
| Daftar akun multi-login hilang | Daftar hidup di sesi server — hilang setelah logout semua, session expired, atau `php artisan session:table`/ganti driver sesi. Tambah ulang akun via "Tambah akun lain". |
