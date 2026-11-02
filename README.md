# ICT Management System (IMS)

IMS adalah aplikasi operasional ICT berbasis Laravel 13 dan MySQL. Iterasi pertama menyediakan dashboard, ticketing dengan histori status, database aset, fondasi maintenance, dan monitoring layanan.

## Menjalankan aplikasi

1. Aktifkan Apache dan MySQL melalui XAMPP.
2. Pastikan database `ims` tersedia dan konfigurasi `.env` sesuai.
3. Jalankan `php artisan migrate --seed` untuk membuat data awal.
4. Buka `http://localhost/IMS/public` melalui Apache, atau jalankan `php artisan serve`.

Login demo: `admin@ims.local` / `password`.

## Modul aktif

- Dashboard operasional
- Perbaikan IT/ticketing dan riwayat status
- Peralatan/aset IT
- Fondasi jadwal maintenance
- Fondasi monitoring web/server
- Role dasar pengguna

## Scheduler monitoring

Monitoring otomatis dijalankan setiap lima menit. Pada Windows, jalankan worker scheduler:

`php artisan schedule:work`

Pengecekan manual seluruh layanan juga dapat dijalankan dengan `php artisan monitor:services`.

Maintenance CRUD, histori pemeriksaan, monitoring scheduler, serta histori uptime sudah aktif. Pengembangan berikutnya: persediaan, CCTV, topologi jaringan, SOP, campaign, KPI, RBAC granular, QR code, dan Report Center.
