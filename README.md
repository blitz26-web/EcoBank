# EcoBank - Fondasi Laravel 13 (Langkah 1, 2, 3 & 4)

Fondasi aplikasi manajemen bank sampah digital **EcoBank** berbasis Laravel 13 (PHP 8.3+).

- **Langkah 1** - enam migrasi database, enam model Eloquent lengkap dengan relasinya, empat enum
  status PHP 8.3, dan `DepositService` dengan transaksi atomik.
- **Langkah 2** - `WithdrawalService` dengan pola hold saldo, `PointLedgerService` sebagai satu pintu
  mutasi poin, konfigurasi nilai tukar poin, policy per peran, serta `StoreWithdrawalRequest`.
- **Langkah 3** - autentikasi bergaya Breeze (Blade stack) dengan UI Bootstrap 5 (email reset
  kata sandi berbahasa Indonesia), middleware `role:...`, dashboard per peran, halaman profil,
  halaman error kustom, file bahasa `lang/id`, dan seeder akun demo + kategori sampah.
- **Langkah 4** - UI fungsional lengkap: form setoran dinamis multi-kategori (petugas loket &
  pengajuan mandiri warga), antrian kerja petugas dengan tombol verifikasi/proses/tolak, riwayat
  setoran/penarikan/mutasi poin dengan pagination Bootstrap, CRUD kategori sampah (admin, modal),
  dan seeder transaksi demo yang dibuat lewat Service agar ledger tetap rekonsiliasi.
- **Revisi akhir** - verifikasi email **dinonaktifkan** sesuai permintaan (registrasi langsung
  dipakai tanpa tautan verifikasi; reset kata sandi tetap berjalan normal) dan 12 akun warga
  dummy ditambahkan via `DummyUserSeeder` dengan saldo bervariasi lewat ledger.
- **Revisi UI** - **dark mode** rapih di seluruh aplikasi (Bootstrap 5.3 `data-bs-theme`, pilihan
  tersimpan di localStorage + ikuti preferensi sistem, tanpa kedip saat pindah halaman) dan
  halaman **`/admin` diperkaya menjadi Panel Admin** (rekonsiliasi ledger, transaksi terbaru,
  top saldo warga, ringkasan antrian) sehingga tidak lagi sama dengan dashboard ringkas.

## Struktur File

```
ecobank/
├── app/
│   ├── Enums/
│   │   ├── UserRole.php            # warga | petugas | admin
│   │   ├── DepositStatus.php       # pending | completed | cancelled
│   │   ├── WithdrawalStatus.php    # requested | processed | rejected
│   │   └── LedgerType.php          # credit | debit
│   ├── Exceptions/
│   │   └── InsufficientPointsException.php   # render 422 otomatis (JSON/redirect)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   ├── AuthenticatedSessionController.php   # login/logout
│   │   │   │   ├── RegisteredUserController.php         # registrasi (selalu warga)
│   │   │   │   ├── PasswordResetLinkController.php      # kirim tautan reset
│   │   │   │   └── NewPasswordController.php            # setel ulang kata sandi
│   │   │   ├── Controller.php                           # basis + trait authorize/validate
│   │   │   ├── DashboardController.php                  # dashboard per peran + Panel Admin (/admin)
│   │   │   ├── ProfileController.php                    # profil + ganti kata sandi
│   │   │   ├── DepositController.php                    # riwayat/buat/verifikasi/batal setoran (L4)
│   │   │   ├── WithdrawalController.php                 # ajukan/proses/tolak penarikan (L4)
│   │   │   ├── QueueController.php                      # antrian kerja petugas (L4)
│   │   │   ├── PointLedgerController.php                # riwayat mutasi poin (L4)
│   │   │   └── WasteCategoryController.php              # CRUD kategori (admin, L4)
│   │   ├── Middleware/
│   │   │   └── EnsureUserHasRole.php                    # proteksi route `role:...`
│   │   └── Requests/
│   │       ├── Auth/LoginRequest.php                    # login + rate limiter
│   │       ├── StoreWithdrawalRequest.php               # validasi form penarikan
│   │       ├── StoreDepositRequest.php                  # validasi items multi-kategori (L4)
│   │       ├── RejectWithdrawalRequest.php              # alasan penolakan wajib (L4)
│   │       └── StoreWasteCategoryRequest.php            # tambah/ubah kategori (L4)
│   ├── Models/
│   │   ├── User.php                # notifikasi reset sandi kustom (verifikasi email nonaktif)
│   │   ├── WasteCategory.php       # + helper calculatePoints()
│   │   ├── Deposit.php             # header transaksi setoran
│   │   ├── DepositItem.php         # rincian jenis sampah
│   │   ├── Withdrawal.php          # + officer_id, processed_at, rejected_reason
│   │   └── PointLedger.php         # audit trail imutabel
│   ├── Notifications/
│   │   └── ResetPasswordNotification.php        # email reset sandi (Indonesia)
│   ├── Policies/
│   │   ├── DepositPolicy.php
│   │   ├── WithdrawalPolicy.php
│   │   └── WasteCategoryPolicy.php
│   ├── Providers/
│   │   └── AppServiceProvider.php  # Gate::before + Password::defaults
│   └── Services/
│       ├── PointLedgerService.php  # satu pintu mutasi saldo + ledger
│       ├── DepositService.php      # transaksi penyetoran
│       └── WithdrawalService.php   # pola hold, proses, tolak + refund
├── bootstrap/
│   └── app.php                     # alias middleware `role` + redirect guest/user
├── config/
│   └── ecobank.php                 # point_value & batas penarikan
├── database/
│   ├── migrations/                 # 7 migrasi (000001 s.d. 000007)
│   └── seeders/
│       ├── DatabaseSeeder.php      # pemanggil semua seeder
│       ├── DemoUserSeeder.php      # akun demo 3 peran (idempotent)
│       ├── DummyUserSeeder.php     # 12 warga dummy saldo bervariasi (idempotent)
│       ├── WasteCategorySeeder.php # 6 kategori sampah (idempotent)
│       └── DemoTransactionSeeder.php # setoran & penarikan demo via Service (L4)
├── lang/
│   └── id/
│       ├── auth.php                # auth.failed, auth.throttle, dst.
│       ├── passwords.php           # pesan reset kata sandi
│       ├── pagination.php          # label paginasi
│       └── validation.php          # pesan validasi global (subset aturan)
├── resources/views/
│   ├── auth/                       # login, register, forgot/reset password
│   ├── dashboard/                  # warga (aksi cepat), petugas, admin
│   │   ├── warga.blade.php         # kartu saldo + statistik + mutasi terakhir
│   │   ├── petugas.blade.php       # antrian ringkas di dashboard
│   │   ├── admin.blade.php         # ringkasan sistem (dashboard admin, tetap ringkas)
│   │   └── admin-overview.blade.php # PANEL ADMIN (/admin): rekonsiliasi ledger,
│   │                                #   transaksi terbaru, top saldo, antrian
│   ├── deposits/                   # index (riwayat+filter) & create (form dinamis) [L4]
│   ├── withdrawals/                # index (riwayat+aksi) & create (form pengajuan) [L4]
│   ├── points/                     # index riwayat mutasi poin [L4]
│   ├── waste-categories/           # index CRUD modal kategori [L4]
│   ├── officer/                    # queue.blade.php antrian kerja petugas [L4]
│   ├── errors/                     # 403, 404, 419, 500 (Indonesia)
│   ├── layouts/                    # app (navbar) + guest (kartu tengah) + errors
│   ├── partials/                   # flash + modal-reject + theme-head/theme-toggle (dark mode)
│   ├── profile/edit.blade.php      # profil + ganti kata sandi
│   ├── vendor/notifications/       # template mail Indonesia (html + plain)
│   └── welcome.blade.php           # landing page
└── routes/
    ├── web.php                     # dashboard, profil, antrian (role), admin (role)
    └── auth.php                    # login/daftar/logout/reset (pola Breeze)
```

## Cara Integrasi ke Project Laravel

1. Pastikan project Laravel 11/12/13 dengan PHP 8.3+ sudah terinisialisasi
   (`composer create-project laravel/laravel ecobank`).
2. Salin folder `app/`, `bootstrap/`, `config/`, `database/`, `lang/`, `resources/`, dan `routes/`
   dari paket ini ke root project (struktur folder identik dengan skeleton Laravel modern).
   > `bootstrap/app.php` menimpa milik skeleton. Jika project Anda sudah mengeditnya,
   > cukup salin 3 baris di dalam `withMiddleware()` (lihat komentar di file tersebut).
3. Konfigurasi database, mail, dan locale di `.env` (mail diperlukan untuk fitur
   lupa kata sandi; verifikasi email sedang nonaktif):

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_DATABASE=ecobank
   APP_LOCALE=id                 # pesan validasi/auth dalam bahasa Indonesia
   APP_FALLBACK_LOCALE=en
   MAIL_MAILER=log               # untuk development; produksi ganti smtp
   MAIL_FROM_ADDRESS=no-reply@ecobank.test
   MAIL_FROM_NAME="EcoBank"
   ```

   > Tips: dengan `MAIL_MAILER=log`, email reset kata sandi ditulis ke
   > `storage/logs/laravel.log` - mudah dites tanpa SMTP sungguhan.

4. Jalankan migrasi lalu seeder:

   ```bash
   php artisan migrate
   php artisan db:seed
   ```

   > Ingin menambah akun dummy saja tanpa menyentuh data lain?
   > `php artisan db:seed --class=DummyUserSeeder` (idempotent).

5. Atur nilai tukar poin di `.env` bila perlu (default: 1 poin = Rp 100):

   ```env
   ECOBANK_POINT_VALUE=100
   ECOBANK_MIN_WITHDRAWAL_POINTS=100
   ECOBANK_MAX_WITHDRAWAL_POINTS=0
   ```

6. (Opsional, permission granular) pasang Spatie lalu buka komentar
   `use \Spatie\Permission\Traits\HasRoles;` pada `app/Models/User.php`:

   ```bash
   composer require spatie/laravel-permission
   php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
   php artisan migrate
   ```

7. Jalankan server dan login dengan akun demo:

   ```bash
   php artisan serve
   ```

## Akun Demo (password semua akun: `password`)

| Email | Peran | Saldo awal | Keterangan |
|---|---|---|---|
| admin@ecobank.test | Administrator | 0 | Akses seluruh halaman (bypass middleware & gate) |
| petugas@ecobank.test | Petugas | 0 | Verifikasi setoran & proses penarikan |
| warga@ecobank.test | Warga | 1.500 poin | Saldo awal dicatat via entri ledger |
| sari@ecobank.test | Warga | 0 | Contoh warga tanpa saldo |
| andi@ecobank.test | Warga | 2.450 poin | Dummy (DummyUserSeeder) |
| dewi@ecobank.test | Warga | 1.830 poin | Dummy |
| agus@ecobank.test | Warga | 920 poin | Dummy |
| rina@ecobank.test | Warga | 3.750 poin | Dummy |
| joko@ecobank.test | Warga | 640 poin | Dummy |
| fitri@ecobank.test | Warga | 2.880 poin | Dummy |
| rudi@ecobank.test | Warga | 1.500 poin | Dummy |
| nina@ecobank.test | Warga | 110 poin | Dummy |
| eko@ecobank.test | Warga | 5.000 poin | Dummy |
| lina@ecobank.test | Warga | 2.210 poin | Dummy |
| doni@ecobank.test | Warga | 760 poin | Dummy |
| maya@ecobank.test | Warga | 3.400 poin | Dummy |

Verifikasi email saat ini **nonaktif**, jadi semua akun langsung bisa dipakai tanpa membuka
email. Kolom `email_verified_at` tetap ada dan diisi `now()` oleh seeder sebagai bekal bila
fitur diaktifkan kembali (lihat bagian *Verifikasi Email - Status Nonaktif* di bawah).

> Perhatian: password demo `password` HANYA untuk akun seeded (dibuat langsung oleh seeder).
> Registrasi manual wajib memenuhi kebijakan kekuatan kata sandi: minimal 8 karakter,
> kombinasi huruf besar + huruf kecil + angka (contoh: `Passw0rd123`).

Seeder bersifat **idempotent** (aman dijalankan berulang): `firstOrCreate`/`updateOrCreate`,
dan saldo awal warga hanya dikreditkan saat akun BARU dibuat. Saldo awal TIDAK di-set langsung
ke kolom `balance_points`, melainkan lewat `PointLedgerService::credit()` sehingga tercipta
entri `point_ledgers` dan saldo tetap cocok dengan ledger saat rekonsiliasi (Langkah 5).

**Transaksi demo (Langkah 4)** - `DemoTransactionSeeder` mengisi antrian & riwayat agar UI
langsung bisa diuji: 2 setoran selesai (loket), 3 setoran pending (antrian verifikasi),
1 penarikan diproses, 1 ditolak dengan refund (riwayat), dan 1 penarikan diajukan
(antrian penarikan). Semua dibuat melalui `DepositService`/`WithdrawalService` - BUKAN set
kolom manual - sehingga saldo dan ledger tetap rekonsiliasi. Saldo akhir warga demo:
1.500 + 43 (setoran loket) - 300 (diproses) - 250 (diajukan/hold) = **993 poin**
(200 poin penarikan ditolak sudah dikembalikan).

**Akun dummy (revisi akhir)** - `DummyUserSeeder` menambahkan 12 warga dummy (andi, dewi, agus,
rina, joko, fitri, rudi, nina, eko, lina, doni, maya - semuanya `@ecobank.test`) dengan saldo
110-5.000 poin agar statistik poin beredar dan daftar warga di form loket terlihat realistis.
Saldo mereka juga dikredit lewat `PointLedgerService` dengan keterangan "Saldo awal (dummy
seeder)." sehingga tetap cocok dengan rekap ledger saat rekonsiliasi.

## Verifikasi Email - Status Nonaktif

Sesuai permintaan, fitur verifikasi email dimatikan: model `User` tidak lagi mengimplementasikan
`MustVerifyEmail`, middleware `verified` dilepas dari semua route, dan controller/route/view
verifikasi telah dihapus. Konsekuensinya, registrasi baru langsung login dan bisa memakai
seluruh halaman aplikasi. Fitur lupa/reset kata sandi TIDAK terpengaruh dan tetap berjalan.

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

## Peta Route Langkah 3 & 4

| URL | Method | Middleware | Keterangan |
|---|---|---|---|
| `/` | GET | - | Landing page |
| `/masuk` | GET/POST | guest | Login (rate limit 5 percobaan/menit) |
| `/daftar` | GET/POST | guest | Registrasi (selalu berperan warga, langsung login otomatis) |
| `/lupa-kata-sandi` | GET/POST | guest | Kirim tautan reset (POST dibatasi 6/menit) |
| `/atur-ulang-kata-sandi/{token}` | GET/POST | guest | Setel ulang kata sandi (POST dibatasi 6/menit) |
| `/keluar` | POST | auth | Logout |
| `/dashboard` | GET | auth | Konten otomatis sesuai peran |
| `/profil` | GET/PATCH | auth | Edit data diri |
| `/profil/kata-sandi` | PUT | auth | Ganti kata sandi |
| `/antrian` | GET | auth + `role:petugas,admin` | Antrian kerja: verifikasi setoran & proses/tolak penarikan |
| `/admin` | GET | auth + `role:admin` | Ringkasan sistem |
| `/setoran` | GET | auth | Riwayat setoran (warga: miliknya; petugas/admin: semua) + filter status |
| `/setoran/buat` | GET | auth | Form setoran dinamis (warga: pengajuan mandiri; petugas/admin: loket) |
| `/setoran` | POST | auth | Simpan setoran via DepositService |
| `/setoran/{deposit}/verifikasi` | POST | auth + `role:petugas,admin` | Verifikasi pending (kredit poin) |
| `/setoran/{deposit}` | DELETE | auth | Batalkan setoran pending milik sendiri |
| `/penarikan` | GET | auth | Riwayat penarikan + filter status |
| `/penarikan/ajukan` | GET | auth | Form pengajuan penarikan (hanya warga) |
| `/penarikan` | POST | auth | Simpan pengajuan (pola hold) |
| `/penarikan/{withdrawal}/proses` | POST | auth + `role:petugas,admin` | Tandai selesai dibayar |
| `/penarikan/{withdrawal}/tolak` | POST | auth + `role:petugas,admin` | Tolak + refund poin (alasan wajib) |
| `/riwayat-poin` | GET | auth | Riwayat mutasi poin pribadi + filter arah |
| `/kategori-sampah` | GET/POST | auth + `role:admin` | Daftar + tambah kategori (modal) |
| `/kategori-sampah/{kategori}` | PUT/DELETE | auth + `role:admin` | Ubah / hapus kategori |

Nama route memakai konvensi Inggris (`login`, `dashboard`, ...) agar familiar bagi
pengembang Laravel, sementara URL berbahasa Indonesia untuk pengalaman pengguna lokal.

## Middleware `role` - Cara Pakai

```php
Route::middleware('auth')->group(function (): void {
    Route::middleware('role:petugas,admin')->group(function (): void {
        Route::get('/antrian', QueueController::class);
        Route::post('/setoran/{deposit}/verifikasi', [DepositController::class, 'verify']);
        Route::post('/penarikan/{withdrawal}/tolak', [WithdrawalController::class, 'reject']);
    });

    Route::middleware('role:admin')->group(function (): void {
        Route::get('/kategori-sampah', [WasteCategoryController::class, 'index']);
        Route::put('/kategori-sampah/{kategori}', [WasteCategoryController::class, 'update']);
    });
});
```

Aturan:

- Parameter menerima nilai enum `UserRole`: `warga`, `petugas`, `admin` (dipisah koma).
- Admin selalu diloloskan (super user, selaras `Gate::before()`).
- Non-sesuai mendapat HTTP 403 dengan pesan Indonesia berisi daftar peran yang diizinkan.
- Middleware ini coarse-grained; otorisasi per objek tetap memakai Policy
  (lihat Matriks Peran di bawah).

## Keputusan Desain Penting

| Keputusan | Alasan |
|---|---|
| Poin disimpan sebagai integer dan dibulatkan dengan `round()` | Sistem poin sederhana untuk redeem; contoh 1.25 kg x 10 poin/kg = 12.5 menjadi 13 poin. Terpusat di `WasteCategory::calculatePoints()`. |
| Kolom `status` bertipe string + cast ke PHP enum | Menghindari SQL ENUM yang sulit dimigrasi; type-safe di level aplikasi dan tetap fleksibel. |
| `PointLedgerService` satu pintu mutasi saldo | Semua perubahan `balance_points` hanya lewat `credit()`/`debit()`, sehingga aturan "setiap perubahan = entri ledger baru" mustahil terlewat; dipakai bersama DepositService, WithdrawalService, dan seeder. |
| Pola HOLD pada penarikan | Poin dipotong saat pengajuan (requested) agar saldo tidak bisa dipakai ganda oleh beberapa pengajuan bersamaan; penolakan otomatis mengembalikan poin lewat entri kredit. |
| Nilai tukar poin dari config server | `cash_amount` dihitung dengan `config('ecobank.point_value')`; rate tidak pernah diterima dari klien sehingga tidak bisa dimanipulasi. |
| `lockForUpdate()` pada baris user/deposit/withdrawal | Mencegah race condition: dua transaksi tidak bisa membaca saldo yang sama dan memutasi bersamaan. |
| FK `restrictOnDelete` untuk data keuangan | Riwayat setoran/penarikan/ledger tidak boleh hilang mengikuti penghapusan user; gunakan soft delete / flag aktif. |
| `PointLedger` memblokir `updating` dan `deleting` | Imutabilitas audit trail di level aplikasi; koreksi selalu berupa entri baru yang berlawanan arah. |
| `Gate::before()` untuk admin | Admin lolos semua policy tanpa mencabang `isAdmin()` di tiap method; warga & petugas tetap dibatasi method policy. |
| Registrasi publik selalu peran `warga` | Mencegah privilege escalation lewat form; akun petugas/admin hanya dibuat admin (seeder/panel). |
| Login dengan rate limiter (pola Breeze) | 5 percobaan per menit per kombinasi email+IP; menahan serangan brute force pada endpoint publik. |
| Saldo awal seeder via ledger, bukan set kolom | Menjaga invarian "saldo = rekap ledger" sehingga rekonsiliasi Langkah 5 tidak pernah gagal pada data demo. |
| Akun dummy via DummyUserSeeder | 12 warga saldo bervariasi untuk demo realistis; idempotent dan saldo tetap lewat PointLedgerService sehingga invarian ledger tidak rusak. |
| Verifikasi email DINONAKTIFKAN (permintaan user) | Registrasi langsung dipakai tanpa tautan verifikasi agar demo/development tidak bergantung konfigurasi mail. Kolom `email_verified_at` + cast tetap dipertahankan; panduan aktivasi ulang ada di bagian *Verifikasi Email - Status Nonaktif*. |
| Notifikasi reset sandi kustom (bukan dependensi terjemahan framework) | Email lupa kata sandi selalu berbahasa Indonesia deterministik, tanpa bergantung pada paket `laravel-lang` atau isi folder lang framework; kedaluwarsa token tetap dikelola framework (subclass `ResetPassword`). |
| Template mail dipublikasikan ke `resources/views/vendor/notifications` | Footer/subcopy email yang di luar kendali MailMessage ikut berbahasa Indonesia; plain-text version ikut dilokalkan. |
| `Password::defaults()` global (min 8, mixedCase, numbers) | Satu tempat mengatur kekuatan kata sandi untuk registrasi & ganti kata sandi; mudah diperketat (mis. `->uncompromised(true)`) tanpa menyentuh controller. |
| Throttle 6/menit pada endpoint email reset | Endpoint publik pengirim email dibatasi untuk mencegah mail bombing. |
| File bahasa `lang/id` + `APP_LOCALE=id` | Pesan validasi, `auth.failed`, `auth.throttle`, dan `passwords.*` berbahasa Indonesia secara global; FormRequest dengan pesan khusus tetap memakai pesannya sendiri. |
| Halaman error kustom 403/404/419/500 | HTTP 403 dari middleware `role` (mis. warga membuka `/admin`) tampil rapi berbahasa Indonesia, bukan halaman default Laravel. |
| Pesan reset password dibedakan sama (uniform response) | Tidak membocorkan keberadaan akun pada endpoint publik lupa kata sandi. |
| Evolusi skema withdrawals (migrasi 007) | `officer_id`, `processed_at`, `rejected_reason` ditambahkan untuk akuntabilitas petugas dan transparansi alasan penolakan ke warga. |
| Scoping riwayat WAJIB di query, bukan di Blade | `DepositController::index()`/`WithdrawalController::index()` memfilter `user_id` untuk warga di level query; menyembunyikan baris hanya di Blade berarti data bocor lewat URL langsung. |
| Warga `prohibited` mengirim `user_id` pada form setoran | Anti manipulasi kredit poin ke akun lain lewat POST manual; petugas/admin justru WAJIB memilih warga (rule `exists` + `where role = warga`). |
| Poin dihitung ulang di server pada penyimpanan setoran | Angka poin di form hanya preview JS (`berat x tarif`); nilai tersimpan dihitung `WasteCategory::calculatePoints()` di dalam transaksi, sehingga manipulasi DOM tidak berpengaruh. |
| Form item dinamis pakai `<template>` + index berurutan | Baris item dikloning JS dengan placeholder `__IDX__`; isian lama dipulihkan dari `old('items')` setelah error validasi agar warga tidak mengetik ulang. |
| Pagination `Paginator::useBootstrapFive()` | Markup paginasi selaras Bootstrap 5 (default Laravel bergaya Tailwind); filter status memakai `withQueryString()` agar filter tidak hilang saat pindah halaman. |
| Dua paginator satu halaman memakai `pageName` berbeda | Halaman antrian punya daftar setoran & penarikan masing-masing 10/halaman; `setoran_page` & `penarikan_page` membuat navigasi daftar A tidak mereset posisi daftar B. |
| Aksi sensitif lewat form POST + policy, bukan link GET | Verifikasi/proses/tolak/batal semua berbentuk form dengan CSRF + konfirmasi; policy (`@can` di Blade, `$this->authorize()`/`Gate::authorize()` di controller) tetap gerbang utama. |
| Hapus kategori ditolak ramah + tombol disabled di UI | FK `restrictOnDelete` blokir di database; controller menangkap `QueryException` jadi pesan Indonesia, dan tombol hapus dinonaktifkan bila `deposit_items_count > 0`. |
| `DemoTransactionSeeder` lewat Service, bukan set kolom | Data demo wajib lulus jalur yang sama dengan produksi (transaksi + row lock + entri ledger), sehingga invarian "saldo = rekap ledger" terjaga sejak data pertama. |
| Perubahan tarif kategori tidak meretroaktif setoran lama | `points_earned` tersimpan permanen di `deposit_items` (snapshot historis); mengubah `point_per_unit` hanya berlaku untuk setoran berikutnya. |

## API DepositService

```php
use App\Enums\DepositStatus;
use App\Services\DepositService;

$service = app(DepositService::class);

/* Skenario 1: petugas mencatat setoran di loket (poin langsung masuk) */
$deposit = $service->createDeposit(
    holder: $warga,
    items: [
        ['waste_category_id' => 1, 'weight_in_kg' => 2.5],
        ['waste_category_id' => 3, 'weight_in_kg' => 1.25],
    ],
    officer: $petugas,
    initialStatus: DepositStatus::Completed,
);

/* Skenario 2: warga mengajukan mandiri lalu diverifikasi petugas */
$pengajuan = $service->createDeposit(holder: $warga, items: $items); // pending
$selesai   = $service->completeDeposit($pengajuan, $petugas);        // poin masuk

/* Membatalkan pengajuan yang masih pending */
$service->cancelDeposit($pengajuan);
```

## API WithdrawalService

```php
use App\Models\Withdrawal;
use App\Services\WithdrawalService;

$service = app(WithdrawalService::class);

/* 1. Warga mengajukan penarikan: poin langsung dipotong (hold) */
$wdr = $service->requestWithdrawal(
    holder: $warga,
    pointsAmount: 500,                                   // 500 poin
    paymentMethod: Withdrawal::PAYMENT_BANK_TRANSFER,
    accountNumber: '1234567890',                         // wajib untuk non-tunai
);
// cash_amount = 500 x config('ecobank.point_value') = Rp 50.000

/* 2a. Petugas menolak: poin kembali otomatis + entri kredit di ledger */
$service->rejectWithdrawal($wdr, $petugas, 'Rekening tidak atas nama pemilik.');

/* 2b. Atau diproses: ditandai selesai dibayar, saldo tidak berubah lagi */
$service->processWithdrawal($wdr, $petugas);
```

Alur saldo dan ledger penarikan:

| Kejadian | Efek saldo | Entri point_ledgers |
|---|---|---|
| Pengajuan dibuat (requested) | -points_amount (debit) | Debit, referensi Withdrawal |
| Diproses (processed) | tidak berubah | tidak ada (perubahan status saja) |
| Ditolak (rejected) | +points_amount (kredit) | Kredit, referensi Withdrawal |

Kesalahan yang ditangani rapi:

- Saldo tidak cukup -> `InsufficientPointsException` (otomatis 422 JSON atau redirect dengan pesan error).
- Metode pembayaran non-tunai tanpa nomor rekening -> `InvalidArgumentException`.
- Penarikan sudah bukan `requested` -> `RuntimeException` (anti proses ganda, dibackend row lock).

## Matriks Peran (Policy + Middleware)

Policy di `app/Policies` otomatis terdeteksi Laravel mengikuti konvensi penamaan model.
Admin lolos semua pemeriksaan berkat `Gate::before()` di `AppServiceProvider` dan bypass
di middleware `role`. Badge status/kategori di UI memakai helper enum `badgeClass()`/`icon()`.

| Ability | Warga | Petugas | Admin |
|---|---|---|---|
| Melihat setoran/penarikan milik sendiri | Ya | Ya | Ya |
| Melihat semua setoran/penarikan | - | Ya | Ya |
| Membuat pengajuan setoran | Ya | Ya (mencatat di loket) | Ya |
| Verifikasi setoran pending (`verify`) | - | Ya | Ya |
| Membatalkan setoran pending sendiri (`cancel`) | Ya | - | Ya |
| Mengajukan penarikan (`create`) | Ya | - | - |
| Memproses / menolak penarikan (`process`, `reject`) | - | Ya | Ya |
| Mengelola kategori sampah | - | - | Ya |
| Mengakses `/antrian` (middleware role) | - | Ya | Ya |
| Mengakses `/admin` (middleware role) | - | - | Ya |

Contoh pemakaian di controller:

```php
$this->authorize('verify', $deposit);                          // DepositPolicy::verify
$this->authorize('process', $withdrawal);                      // WithdrawalPolicy::process
if ($user->can('create', Withdrawal::class)) { /* warga */ }
```

## Pembagian Validasi

- `StoreWithdrawalRequest` (HTTP layer): bentuk data, daftar metode pembayaran dari
  `WithdrawalService::allowedPaymentMethods()`, batas min/max dari config, nomor rekening
  wajib untuk pembayaran non-tunai, pesan error berbahasa Indonesia.
- `WithdrawalService` (bisnis layer, di dalam `DB::transaction`): kecukupan saldo dengan row
  lock, konversi poin ke Rupiah memakai rate server, anti proses ganda.
- `StoreDepositRequest` (HTTP layer): array `items` (1-10 baris), kategori harus ada,
  berat > 0; `user_id` wajib untuk petugas/admin (harus berperan warga) dan `prohibited`
  untuk warga. `prepareForValidation()` membuang baris kosong dan menormalkan desimal koma.
- `DepositService` (bisnis layer): hitung poin ulang per item, transaksi + row lock,
  penolakan status awal tidak sah.
- `RejectWithdrawalRequest`: alasan penolakan wajib (5-255 karakter) karena tersimpan di
  `rejected_reason` dan keterangan ledger refund.
- `StoreWasteCategoryRequest`: dipakai untuk tambah (POST) dan ubah (PUT); nama unik
  mengabaikan baris yang sedang diedit; tarif poin integer 0-1.000.000.
- `LoginRequest` (HTTP layer): format email/kata sandi + rate limiter.
- Pengecekan saldo sengaja tidak diletakkan di FormRequest agar selalu dieksekusi di dalam
  transaksi database bersama debit ledger. Service tetap memvalidasi ulang semuanya
  (defense in depth) karena bisa dipanggil dari Tinker/CLI tanpa HTTP layer.

## Alur UI Langkah 4 (Skenario Uji Manual)

1. **Warga** login (`warga@ecobank.test`) -> dashboard menampilkan aksi cepat ->
   "Ajukan Setoran Mandiri" -> tambah 2 jenis sampah (baris dinamis + preview poin) ->
   kirim -> status *Menunggu Verifikasi* di Riwayat Setoran (dapat dibatalkan selama pending).
2. **Warga** -> "Tarik Poin" -> pilih jumlah (chip cepat / isi manual, preview Rupiah) ->
   pilih metode (form nomor rekening muncul otomatis untuk non-tunai) -> kirim ->
   poin terpotong (hold) dan muncul di Riwayat Penarikan dengan status *Diajukan*.
3. **Petugas** login (`petugas@ecobank.test`) -> Antrian -> kartu "Setoran Menunggu
   Verifikasi" berisi rincian item -> klik **Verifikasi** -> poin masuk ke saldo warga,
   entri ledger kredit tercipta.
4. **Petugas** -> kartu "Penarikan Menunggu Diproses" -> **Proses** (selesai dibayar) atau
   **Tolak** lewat modal berisi alasan wajib -> bila ditolak, poin otomatis kembali ke warga.
5. **Petugas** -> "Catat Setoran di Loket" -> pilih warga + multi-kategori -> simpan ->
   setoran langsung *Selesai* dan poin masuk seketika.
6. **Admin** login (`admin@ecobank.test`) -> menu **Kategori** -> tambah/ubah kategori via
   modal, hapus hanya untuk kategori yang belum dipakai -> perubahan tarif tidak
   memengaruhi setoran lama.

## Catatan spatie/laravel-permission

Kolom `users.role` dipertahankan sebagai peran sederhana untuk filter cepat, middleware `role`,
dan seed data awal, sementara Spatie akan menangani permission granular (mis. `verify-deposits`,
`manage-withdrawals`). Aktifkan trait `HasRoles` pada model `User` setelah package terpasang,
lalu sinkronkan role Spatie dengan nilai kolom `role` saat seeder dijalankan agar dua mekanisme
ini tidak saling bertentangan.

## Rencana Langkah Berikutnya

1. **Langkah 5** - Laporan Excel via `maatwebsite/excel` (riwayat setoran, mutasi poin,
   rekap kategori) dan halaman rekonsiliasi ledger untuk admin (validasi invarian
   "saldo = rekap ledger", deteksi selisih, dan koreksi berupa entri ledger baru).
