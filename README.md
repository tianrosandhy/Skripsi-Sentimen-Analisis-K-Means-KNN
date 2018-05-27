# Skripsi-Sentimen-Analisis-K-Means-KNN
Ini skripsi sederhana saya (yg tentunya udah diACC) tentang metode analisis sentimen komentar dengan methode K-Means dan KNN. Contoh data kalimat yang digunakan adalah menggunakan angket evaluasi dosen di kampus. Metode ini bisa diterapkan untuk analisis sentimen dasar konteks lainnya juga.

## Cara Install
1. Clone
2. Atur koneksi database di core/credentials.php. Format credentials ada di file credentials.php.example, isinya dicopy aja jadi credentials.php. Atur koneksi databasenya disana
3. Jalankan query untuk isi databasenya di file kamus.sql. Contoh data latih yang saya gunakan di database ini ada sekitar 400 data kalimat komentar evaluasi untuk dosen. Untuk contoh kasus lain, dibuat dulu tabel dengan struktur yang sama dengan data latih lainnya
4. Aplikasi sudah bisa dijalankan~


Sistem ini dibuat masih dengan PHP Native, jadi mohon maaf kalau agak berantakan. Fungsi logika proses analisa utamanya ada di file core/function.php (single_process()). Kalau ingin dirapikan lagi masih bisa dan dipersilakan


## Metode
Metode yang digunakan untuk analisis ini adalah K-Means untuk clustering, dan KNN untuk klasifikasi. Perhitungan bobot menggunakan TF*IDF. Metode ini sangat cocok digunakan untuk analisis kalimat sederhana karena rumusnya yang tidak terlalu rumit. 

