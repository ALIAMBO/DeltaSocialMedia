# Panduan Agen AI (AI Assistant Guidelines)

Selamat datang! Sila ikuti panduan berikut apabila membantu tugasan pembangunan dalam projek ini:

## 1. Memahami Keadaan Projek
Sebelum memulakan sebarang tugasan pengekodan, kajian, atau perbincangan, anda **WAJIB** membaca dokumen berikut untuk mengetahui status sistem semasa:
* **Status Semasa Tugasan**: Rujuk [project_status.md](file:///c:/projects/socialmedia/.agents/project_status.md) untuk melihat tugasan yang telah selesai dan tugasan yang belum selesai.
* **Seni Bina Kod**: Rujuk [system_architecture.md](file:///c:/projects/socialmedia/.agents/system_architecture.md) untuk memahami reka bentuk pangkalan data, model, controller, routing, dan integrasi aset CSS/JS.

## 2. Bahasa Komunikasi
* Gunakan **Bahasa Melayu Malaysia** yang profesional dan mesra untuk berinteraksi dengan pengguna, kecuali jika diminta sebaliknya.

## 3. Garis Panduan Pengekodan (Coding Rules)
* **Kompilasi Aset (Vite)**: Sekiranya anda menambah atau mengubah fail CSS/JS luaran, pastikan fail tersebut berdaftar di `vite.config.js` dan jalankan `npm run build` selepas pengubahsuaian selesai.
* **Inisialisasi Tema Gelap (Dark Mode)**: Untuk mengelakkan kelipan unread (FOUC), pastikan semua halaman menggunakan skrip inisialisasi inline di bahagian `<head>` susun atur utama (layouts).
* **Integriti Komen**: Kekalkan semua komen sedia ada yang tidak berkaitan dengan perubahan anda bagi mengelakkan kemerosotan dokumentasi kod.
* **Kemas Kini Dokumentasi (README.md)**: Apabila sesuatu ciri (feature) telah dikemas kini atau ditambah baik, pastikan anda turut mengemas kini fail [README.md](file:///c:/projects/socialmedia/README.md) projek bagi mencerminkan perubahan tersebut.
