# Status Projek SocialMedia

Dokumen ini merekodkan status semasa projek (tugasan selesai dan tugasan masa hadapan/belum selesai) untuk membantu mana-mana AI memahami keadaan sistem.

## 1. Tugasan Yang Telah Selesai (Completed Tasks)

### 🚀 Ciri Utama Sistem (Core Features)
* **Autentikasi Pengguna**: Sistem daftar masuk (Login) & pendaftaran (Register) menggunakan Laravel Breeze.
* **Profil Pengguna**: Bio, lokasi, laman web, avatar, dan foto muka depan.
* **Paparan Feed Utama**: Paparan feed pos daripada pengguna yang diikuti.
* **Fungsi Pos**: Pengguna boleh menulis teks dan memuat naik gambar.
* **Suka & Komen (Likes & Comments)**: Kebolehan menyukai dan memberi komen pada post orang lain serta memadam post/komen sendiri.
* **Mesej Langsung (Direct Messages)**: Chat masa nyata antara pengguna dengan status mesej dibaca/belum dibaca.
* **Carian**: Bar carian pengguna berdasarkan nama atau e-mel di bar navigasi.

### 🛠️ Pembetulan Bug Penting (Bug Fixes)
* **Vite Manifest Bug**: Memperbaiki ralat `ViteException` dengan mendaftarkan `resources/js/dark-mode.js` dalam `vite.config.js`.
* **Carian Diri Sendiri**: Membetulkan operator precedence SQL dalam `SearchController.php` untuk memastikan pengguna semasa (logged-in user) tidak muncul dalam hasil carian mereka sendiri.
* **Butang Follow Carian**: Menggantikan pepijat `auth()->user()->following->contains($user->id)` kepada `auth()->user()->isFollowing($user)` di `search/results.blade.php` bagi memaparkan status butang "Follow" atau "Following" dengan tepat.
* **Kelipan Putih Tema Gelap**: Menyelesaikan FOUC (Flash of Unstyled Content) apabila refresh halaman dalam mod gelap dengan meletakkan skrip inline penyelarasan tema secara terus di dalam `<head>`.

### 🔔 Sistem Pemberitahuan (Database Notifications) - *NEW*
* Menambah migrasi jadual `notifications`.
* Mencipta kelas notifikasi: `NewFollowNotification`, `NewLikeNotification`, dan `NewCommentNotification`.
* Melaksanakan fungsi pemberitahuan automatik dalam `FollowController`, `LikeController`, dan `CommentController`.
* Menyediakan bar navigasi dengan lencana (badge) bilangan pemberitahuan merah yang dikemas kini secara dinamik.
* Membina halaman utama `/notifications` responsif untuk melihat dan menanda notifikasi sebagai telah dibaca.

---

## 2. Tugasan Belum Selesai & Cadangan Penambahbaikan (Pending Tasks)

Berikut adalah senarai baki tugasan atau ciri masa depan mengikut susunan keutamaan:

1. **Sistem Block / Sekat Pengguna**
   * *Huraian*: Membenarkan pengguna menyekat pengguna lain daripada menghantar mesej, melihat profil, atau melihat post mereka di feed.
2. **Tetapan Privasi Post (Post Privacy)**
   * *Huraian*: Pilihan untuk menetapkan post sebagai "Awam (Public)" atau "Pengikut Sahaja (Followers Only)".
3. **Kongsian Semula Post (Post Reposting)**
   * *Huraian*: Kebolehan berkongsi semula (repost/share) hantaran pengguna lain ke halaman profil sendiri.
4. **Tanda Pagar & Sebutan (Hashtags & Mentions)**
   * *Huraian*: Menyokong sebutan `@username` dan `#hashtag` di dalam post yang secara automatik dipautkan.
5. **Kemas Kini Mesej Secara Masa Nyata (WebSockets)**
   * *Huraian*: Menggunakan Pusher/WebSockets untuk membolehkan mesej chat dan notifikasi muncul serta-merta tanpa perlu refresh halaman.
6. **Sistem Stories**
   * *Huraian*: Fungsi perkongsian gambar atau teks yang akan dipadam secara automatik selepas 24 jam.
7. **Muat Naik Video**
   * *Huraian*: Menyokong perkongsian klip video pendek dalam post.
8. **Panel Pentadbir (Admin Panel)**
   * *Huraian*: Dashboard untuk pentadbir menguruskan pengguna, memantau kandungan yang dilaporkan, dan melihat statistik sistem.
