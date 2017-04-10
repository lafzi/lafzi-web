# Lafzi Web Interface

Repositori ini berisi antarmuka Lafzi dalam bentuk web seperti pada http://lafzi.apps.cs.ipb.ac.id. 
Web interface ini mengimplementasikan proses pencarian (online) sesuai bagian kiri pada diagram berikut:

![Flowchart](https://raw.githubusercontent.com/lafzi/lafzi-indexer/master/docs/flowchart.png)

Indeks yang digunakan untuk pencarian dibangun dengan script pada repository [lafzi-indexer](https://github.com/lafzi/lafzi-indexer).

Menjalankan
---

1. Clone atau download repository ini ke folder web server (htdocs atau /var/www/html)
2. Langsung kunjungi di browser

##### Optional
Anda bisa mengaktifkan index menggunakan Redis dengan langkah :
1. Mengganti value `use_redis` pada *web/search.php* dengan **true**
2. Pastikan service Redis telah aktif pada host server
3. Jalankan *redis-indexer.php* dari repository https://github.com/lafzi/lafzi-indexer pada host server

Disarankan menggunakan sistem operasi Linux karena sistem cache mengandalkan command di Linux.

Lisensi
---

GPL (GNU General Public License) v3. Anda bebas menggunakan, memodifikasi, dan mendistribusikan software ini dengan syarat tetap menjadikannya *open-source*.
