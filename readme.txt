
WePOS - Retail v.3.42.20 (Free Version)

Cocok untuk:
Retail/Merchant/Toko, Butik, Salon (semua penjualan berbasis Retail)

Terima Kasih sudah Download dan Support WePOS.id, 
WePOS sudah dibuat sejak tahun 2015, update bertahap sesuai kebutuhan Retail 

WePOS merupakan Karya anak bangsa, support Team WePOS dengan Donasi dari rekan-rekan

#Cara Instalasi:
1. Install XAMPP 5.5.24 atau 5.6.32
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
	2. ubah text pada file .htaccess 'wepos-retail' menjadi 'nama-retail-anda'
		*jika .htaccess tidak ditemukan, ubah settingan folder anda agar dapat melihat hidden file dan ekstensi file
		*gunakan editor semisal notepad++ untuk save-as atau membuat/edit file .htaccess


4. untuk setup printer Thermal -> silahkan download extension PHP di website (login wepos.id)

5. Koneksikan dengan wepos.id -> jika mempunyai merchant key berbayar silahkan koneksikan pada module 'Client Info', aplikasi akan otomatis update sesuai varian berbayar

*Untuk Instalasi lengkap bisa baca di dokumentasi (login website, wepos.id)

#Silahkan Donasi untuk versi yang lebih baik ^^ 
#terima kasih untuk rekan-rekan yang sudah support WePOS

Team WePOS ^^

contact@wepos.id / angga.nugraha@gmail.com

*SELALU DUKUNG KARYA ANAK BANGSA!! 
*JANGAN MALU UNTUK BERTANYA - GRATIS KONSULTASI


# Update April 2019 - Retail v.3.42.20
- Menu Baru: PPOB 
- Database update
- Opsi Baru Setup Aplikasi
- Loader Notify
- Backup & Sync DB Online
- update HPP (dinamis)
- Varian Menu & Varian Item
- Upload Excel Master Item
- Product Package -> Gabungan Produk2
- Product: Item List (CutStock Otomatis / Gramasi / CoGS)
- Status Print Cashier
- Cashier Receipt New Format & Settlement
- AutoCut Stok Option: Langsung & Tidak Langsung (via Usage & Waste)
- Laporan Baru: Tax, Package, Summary Sales Report (SSR)
- Update Purchasing & Receiving -> penggunaan SN/IMEI & Stok SN/IMEI
- Update Inventory Module
- Filter Pencarian per-Item di Daftar Stok
- Warning Stok Jika Terlihat Minus (-)
- Alert jika selisih HPP < Harga Jual
- AR/Hutang on Settlement & SSR
- Update module Finance: AR (Hutang) & AP (Piutang)




