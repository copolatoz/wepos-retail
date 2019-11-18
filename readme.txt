
WePOS - Retail v.3.42.21 (Free Version)
Updated: 19-11-2019 00:00:00

Cocok untuk:
Retail/Toko (semua penjualan berbasis Retail)

Terima Kasih sudah Download dan Support WePOS.id, 
WePOS sudah dibuat sejak tahun 2015, update bertahap sesuai kebutuhan Retail 

untuk versi Cafe/Resto: https://github.com/copolatoz/WePOS-Free

*untuk auto update: silahkan daftarkan merchant/cafe/resto untuk mendapatkan merchant-key

#Cara Instalasi:
1. Install XAMPP 5.5.24 atau 5.6.32 (Belum support PHP > 7)
2. copy-paste folder hasil download aplikasi 'wepos-retail' ke xampp/htdocs/

	#copy dan rename file:
	1. copy index.php.org --> index.php
	2. copy .htaccess.org --> .htaccess (edit isi file, sesuaikan dengan nama folder)

	#folder /applications/config 
	1. copy app_config.php.org --> app_config.php
	2. copy config.php.org --> config.php
	3. copy database.php.org --> database.php (edit isi file, sesuaikan dengan nama database)

	#import database: db/database_wepos_free.sql
	1. akses ke http://localhost/phpmyadmin
	2. buat database baru misal: wepos-retail
	3. import db/database_wepos_free.sql


3. run di browser sesuai nama folder yang digunakan, default: http://localhost/wepos-retail

	#Mengganti URL menjadi http://localhost/nama-retail-anda
	1. ganti nama folder download 'wepos-retail' menjadi 'nama-retail-anda'
	2. ubah text pada file .htaccess "RewriteBase /retail-free/" menjadi "RewriteBase /nama-retail-anda"
		*jika .htaccess tidak ditemukan, ubah settingan folder anda agar dapat melihat hidden file dan ekstensi file
		*gunakan editor semisal notepad++ untuk save-as atau membuat/edit file .htaccess

	#Integrasi dengan WePOS.Cashier App (Android)
	1. ganti nama folder download 'wepos-retail' menjadi 'wepos'
	2. ubah isi .htaccess: "RewriteBase /retail-free/" menjadi "RewriteBase /wepos"


4. untuk setup printer Thermal -> silahkan download extension PHP di website (login wepos.id)

5. Koneksikan dengan wepos.id -> jika mempunyai merchant key berbayar silahkan koneksikan pada module 'Client Info', aplikasi akan otomatis update sesuai varian berbayar

*Untuk Instalasi lengkap bisa baca di dokumentasi (login website, wepos.id)
#Silahkan Donasi untuk versi gratisan yang lebih baik ^^ 
#terima kasih untuk rekan-rekan yang sudah support WePOS.id

Team WePOS.id ^^
contact@wepos.id 
081222549676 // 087722294411

*SELALU DUKUNG KARYA ANAK BANGSA!! 
*JANGAN MALU UNTUK BERTANYA - GRATIS KONSULTASI





