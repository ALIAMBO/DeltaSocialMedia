# Seni Bina Sistem SocialMedia (System Architecture)

Dokumen ini membantu mana-mana AI memahami struktur fail, pangkalan data, reka bentuk antara muka, dan cara sistem ini disusun.

## 1. Rangka Kerja & Stack Teknologi
* **Backend**: Laravel 11.x (PHP 8.2+)
* **Database**: MySQL (diurus melalui Eloquent ORM)
* **Frontend**: Blade Templates + Alpine.js + Tailwind CSS
* **Penyusun Aset**: Vite 8.x

---

## 2. Struktur Fail Utama

### A. Model Data (`app/Models/`)
* **`User.php`**: Mengurus profil, pos, pengikut, sukaan, komen, dan mesej. Mengandungi kaedah pembantu penting seperti `isFollowing(User $user)`.
* **`Profile.php`**: Maklumat tambahan profil (bio, website, lokasi, cover photo, avatar). Dihubungkan secara `hasOne` dari `User`.
* **`Post.php`**: Hantaran pengguna. Berhubung secara `hasMany` dengan `Like` dan `Comment`.
* **`Follow.php`**: Hubungan pengikut/diikuti (`follower_id`, `following_id`).
* **`Message.php`**: Sistem perbualan langsung (`sender_id`, `receiver_id`, `body`, `read_at`).
* **`Comment.php`** & **`Like.php`**: Menyimpan maklumat interaksi post.

### B. Pengawal (Controllers - `app/Http/Controllers/`)
* **`FeedController.php`**: Mengambil post daripada pengguna sendiri dan pengguna yang diikuti untuk dipaparkan di suapan utama.
* **`SearchController.php`**: Mengendalikan carian pengguna dengan mengecualikan pengguna semasa secara selamat menggunakan subquery grouped-where.
* **`FollowController.php`**: Mengendalikan tindakan follow/unfollow dan mencetuskan notifikasi.
* **`LikeController.php`** & **`CommentController.php`**: Mengendalikan sukaan/ulasan post dan menghantar pemberitahuan automatik.
* **`NotificationController.php`**: Menguruskan senarai notifikasi dan menukar status notifikasi dibaca.
* **`ChatController.php`**: Mengurus mesej chat dan bilik perbualan.
* **`ProfileController.php`**: Mengurus kemas kini maklumat profil dan hidangan gambar profil peribadi.

### C. Antara Muka (Views - `resources/views/`)
* **`layouts/app.blade.php`**: Kerangka utama sistem (termasuk skrip pencegah kelipan putih mod gelap dan pautan menu dengan lencana notifikasi).
* **`feed/`**: Paparan suapan post utama.
* **`search/results.blade.php`**: Halaman paparan carian pengguna beserta butang follow/following yang dinamik.
* **`notifications/index.blade.php`**: Dashboard pemberitahuan dengan pengkelasan ikon (heart, comment bubble, user-plus).
* **`chat/`**: Kotak mesej langsung.
* **`profile/`**: Halaman tunjuk profil dan sunting tetapan profil.

---

## 3. Sistem Laluan (Routing)
Semua laluan berdaftar di dalam [routes/web.php](file:///c:/projects/socialmedia/routes/web.php).
* **Laluan Awam**: Pelayan avatar (`/users/{user}/avatar`) dan foto muka depan (`/users/{user}/cover`).
* **Laluan Berpengesahan (auth middleware)**:
  * `/feed` (Feed utama)
  * `/search` (Carian pengguna)
  * `/posts` (Tambah & padam pos)
  * `/posts/{post}/like` (Suka pos)
  * `/posts/{post}/comments` (Tambah komen)
  * `/users/{user}/follow` (Ikuti pengguna)
  * `/chat` & `/chat/{user}` (Direct messaging)
  * `/notifications` & `/notifications/{id}/read` (Sistem notifikasi)

---

## 4. Konfigurasi Kompilasi (Vite)
Aset diurus melalui `vite.config.js` dan dipanggil menggunakan `@vite(...)` di dalam template Blade.
Setiap kali fail JS baharu dicipta untuk kegunaan Blade, ia **mesti** didaftarkan di dalam senarai input `vite.config.js` dan aset perlu dibina semula menggunakan:
```bash
npm run build
```
atau dijalankan menggunakan pelayan pembangunan hot-reload:
```bash
npm run dev
```
