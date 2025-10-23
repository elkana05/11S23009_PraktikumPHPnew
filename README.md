# Latihan PHP: Aplikasi Todolist

Aplikasi Todolist sederhana yang dibangun menggunakan PHP native dengan database PostgreSQL.

## Fitur

- Menambah, melihat, mengubah status, dan menghapus todo.
- Melihat detail todo.
- Validasi judul todo agar tidak duplikat.
- Filter todo berdasarkan status (Semua, Selesai, Belum Selesai).
- Pencarian todo yang terintegrasi dengan filter.
- Mengurutkan todo dengan _drag-and-drop_ yang persisten.

## Persiapan

1. Pastikan Anda memiliki PHP dan PostgreSQL terinstal.
2. Buat database di PostgreSQL (contoh: `todolist_db`).
3. Jalankan skrip SQL yang ada di `database.sql` untuk membuat tabel `todos` dan _trigger_-nya.
4. Sesuaikan konfigurasi koneksi database di `config/database.php`.

## Menjalankan Aplikasi

php -S localhost:8000 -t public
