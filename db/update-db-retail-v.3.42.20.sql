/*

MIGRASI DATABASE

WePOS - Retail: v3.42.19 ke v3.42.20

*********************************************************************

*/

CREATE TABLE `pos_varian_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `varian_name` varchar(100) NOT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
#
CREATE TABLE `pos_product_gramasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_price` double DEFAULT '0',
  `item_qty` float DEFAULT '0',
  `createdby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  `product_varian_id` int(11) DEFAULT '0',
  `varian_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
#
CREATE TABLE `pos_item_kode_unik_log` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL,
  `kode_unik` varchar(255) DEFAULT NULL,
  `ref_in` varchar(50) DEFAULT NULL,
  `date_in` datetime DEFAULT NULL,
  `ref_out` varchar(50) DEFAULT NULL,
  `date_out` datetime DEFAULT NULL,
  `storehouse_id` smallint(6) DEFAULT NULL,
  `item_hpp` double DEFAULT '0',
  `item_sales` double DEFAULT '0',
  `log_tipe` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
#
ALTER TABLE `pos_receive_detail`
ADD `receive_det_varian_group` varchar(50) DEFAULT NULL,
ADD `receive_det_varian_name` varchar(50) DEFAULT NULL;
#
ALTER TABLE `pos_item_kode_unik`
ADD `is_deleted` tinyint(1) DEFAULT '0',
ADD `is_active` tinyint(1) DEFAULT '1';
#
insert  into `apps_modules`(`module_name`,`module_author`,`module_version`,`module_description`,`module_folder`,`module_controller`,`module_is_menu`,`module_breadcrumb`,`module_order`,`module_icon`,`module_shortcut_icon`,`module_glyph_icon`,`module_glyph_font`,`module_free`,`running_background`,`show_on_start_menu`,`show_on_right_start_menu`,`start_menu_path`,`start_menu_order`,`start_menu_icon`,`start_menu_glyph`,`show_on_context_menu`,`context_menu_icon`,`context_menu_glyph`,`show_on_shorcut_desktop`,`desktop_shortcut_icon`,`desktop_shortcut_glyph`,`show_on_preference`,`preference_icon`,`preference_glyph`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values 
(176,'Product Category','dev@wepos.id','v.1.0','','master_pos','productCategory',0,'2. Master POS>Product Category',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Product Category',2101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2019-04-10 17:26:07','administrator','2019-04-30 00:00:00',1,0),
(177,'Master Product & Package','dev@wepos.id','v.1.0','Master Product & Package','master_pos','masterProduct',0,'2. Master POS>Master Product',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Product',2102,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2019-04-10 17:24:38','administrator','2019-04-30 00:00:00',1,0),
(184,'Pembayaran PPOB','dev@wepos.id','v.1.0.0','Pembayaran PPOB','cashier','ppob',0,'3. Cashier & Sales Order>Pembayaran PPOB',1,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Sales Order>Pembayaran PPOB',3401,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2019-02-18 08:25:57','administrator','2019-02-17 17:49:57',1,0);

/*Startup-Enterprise*/
insert  into `apps_modules`(`module_name`,`module_author`,`module_version`,`module_description`,`module_folder`,`module_controller`,`module_is_menu`,`module_breadcrumb`,`module_order`,`module_icon`,`module_shortcut_icon`,`module_glyph_icon`,`module_glyph_font`,`module_free`,`running_background`,`show_on_start_menu`,`show_on_right_start_menu`,`start_menu_path`,`start_menu_order`,`start_menu_icon`,`start_menu_glyph`,`show_on_context_menu`,`context_menu_icon`,`context_menu_glyph`,`show_on_shorcut_desktop`,`desktop_shortcut_icon`,`desktop_shortcut_glyph`,`show_on_preference`,`preference_icon`,`preference_glyph`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values 
('Sales By Product Varian','dev@wepos.id','v.1.0.0','Sales By Product Varian','billing','reportSalesByMenuVarian',0,'6. Reports>Sales (Item)>Sales By Product Varian',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Item)>Sales By Product Varian',6124,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-18 08:25:57','administrator','2018-07-17 17:49:57',1,0),
('Sales Profit By Product Varian','dev@wepos.id','v.1.0.0','Sales Profit By Product Varian','billing','reportSalesProfitByMenuVarian',0,'6. Reports>Sales (Profit)>Sales Profit By Product Varian',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Profit)>Sales Profit By Product Varian',6138,'icon-grid','',0,'icon-grid','',1,'icon-grid','',0,'icon-grid','','administrator','2018-07-18 08:28:02','administrator','2018-07-17 19:37:19',1,0),
('Sales By Product Package','dev@wepos.id','v.1.0.0','Sales By Product Package','billing','reportSalesByProductPackage',0,'6. Reports>Sales (Item)>Sales By Product Package',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Item)>Sales By Product Package',6125,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2019-02-18 08:25:57','administrator','2019-02-17 17:49:57',1,0),
('Sales Profit By Product Package','dev@wepos.id','v.1.0.0','Sales Profit By Product Package','billing','reportSalesProfitByProductPackage',0,'6. Reports>Sales (Profit)>Sales Profit By Product Package',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Profit)>Sales Profit By Product Package',6139,'icon-grid','',0,'icon-grid','',1,'icon-grid','',0,'icon-grid','','administrator','2019-02-18 08:28:02','administrator','2019-02-17 19:37:19',1,0),
('Sales By Tax Service','dev@wepos.id','v.1.0.0','Sales By Tax Service','billing','reportSalesByTaxService',0,'6. Reports>Sales (Item)>Sales By Tax Service',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Item)>Sales By Tax Service',6126,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2019-02-18 08:25:57','administrator','2019-02-17 17:49:57',1,0),
('Sales Profit By Tax Service','dev@wepos.id','v.1.0.0','Sales Profit By Tax Service','billing','reportSalesProfitByTaxService',0,'6. Reports>Sales (Profit)>Sales Profit By Tax Service',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Profit)>Sales Profit By Tax Service',6140,'icon-grid','',0,'icon-grid','',1,'icon-grid','',0,'icon-grid','','administrator','2018-07-18 08:28:02','administrator','2018-07-17 19:37:19',1,0);

ALTER TABLE `pos_supplier` 
MODIFY `supplier_code` VARCHAR(20) DEFAULT NULL,
ADD `source_from` ENUM('MERCHANT','WSM') DEFAULT 'MERCHANT',
ADD `supplier_no` MEDIUMINT(9) DEFAULT 0;
#
ALTER TABLE `pos_sales` 
ADD `sales_code` VARCHAR(20) DEFAULT NULL,
ADD `sales_email` VARCHAR(50) DEFAULT NULL,
ADD `source_from` ENUM('MERCHANT','WSM') DEFAULT 'MERCHANT',
ADD `sales_no` MEDIUMINT(9) DEFAULT 0;
#
ALTER TABLE `pos_customer` 
MODIFY `customer_code` VARCHAR(20) DEFAULT NULL,
ADD `source_from` ENUM('MERCHANT','WSM','ELVO') DEFAULT 'MERCHANT',
ADD `customer_no` MEDIUMINT(9) DEFAULT 0;
#
UPDATE apps_options SET option_value = '01-03-2019', updated = '2019-03-26 00:00:01' WHERE option_var IN ('closing_sales_start_date','stock_rekap_start_date','closing_purchasing_start_date','closing_inventory_start_date','closing_accounting_start_date');
#
UPDATE apps_options SET option_value = '0', updated = '2019-03-26 00:00:01' WHERE option_var IN ('view_multiple_store');
#
UPDATE apps_options SET option_value = 'https://wepos.id', updated = '2019-03-26 00:00:01' WHERE option_var IN ('ipserver_management_systems');
#
UPDATE apps_modules SET module_name = 'Backup Master Data' WHERE module_name = 'Syncronize Master Data Store';
#
UPDATE apps_modules SET module_name = 'Backup Data Transaksi' WHERE module_name = 'Backup Transaksi Store';
#
UPDATE apps_modules SET is_active = 0 WHERE module_name = 'Sync & Backup';
#
UPDATE apps_modules SET is_active = 0, is_deleted = 0 WHERE module_name IN ('Sub Item Category 2','Sub Item Category 3','Sub Item Category 4');
#
UPDATE apps_modules SET module_name = 'Sales By Sub Category' WHERE module_name = 'Sales By Sub Category 1';
#
UPDATE apps_modules SET is_active = 0, is_deleted = 0 WHERE module_name IN ('Sales By Sub Category 2','Sales By Sub Category 3','Sales By Sub  Category 4');
#
UPDATE apps_modules SET module_name = 'SO By Sub Category' WHERE module_name = 'SO By Sub Category 1';
#
UPDATE apps_modules SET is_active = 0, is_deleted = 0 WHERE module_name IN ('SO By Sub Category 2','SO By Sub Category 3','SO By Sub Category 4');
#
UPDATE apps_modules SET module_name = 'SO Profit By Sub Category' WHERE module_name = 'SO Profit By Sub Category 1';
#
UPDATE apps_modules SET is_active = 0, is_deleted = 0 WHERE module_name IN ('SO Profit By Sub Category 2','SO Profit By Sub Category 3','SO Profit By Sub Category 4');
#
UPDATE apps_modules SET module_name = 'Sub Item Category', module_description = 'Sub Item Category', 
module_controller = 'itemSubCategory', module_breadcrumb = '2. Master POS>Sub Item Category', start_menu_path = '2. Master POS>Sub Item Category'
WHERE module_description = 'Sub Item Category 1';
#
UPDATE apps_modules SET module_controller = 'itemSubCategory'
WHERE module_controller = 'itemSubCategory1';
#
UPDATE apps_modules SET module_name = 'Sales By Sub Category', module_description = 'Sales By Sub Category', 
module_controller = 'reportSalesBySubItemCategory', module_breadcrumb = '6. Reports>Sales (Item)>Sales By Sub Category', start_menu_path = '6. Reports>Sales (Item)>Sales By Sub Category'
WHERE module_description = 'Sales By Sub Category 1';
#
DROP TABLE IF EXISTS `pos_salesorder`;
#
CREATE TABLE `pos_salesorder` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `so_number` varchar(20) NOT NULL,
  `so_date` date NOT NULL,
  `so_memo` tinytext,
  `so_status` enum('progress','done','cancel') NOT NULL DEFAULT 'progress',
  `createdby` varchar(50) NOT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `so_from` int(11) DEFAULT NULL,
  `so_customer_name` varchar(100) DEFAULT NULL,
  `so_customer_address` varchar(255) DEFAULT NULL,
  `so_customer_phone` varchar(20) DEFAULT NULL,
  `so_total_qty` float DEFAULT NULL,
  `so_sub_total` double DEFAULT '0',
  `so_discount` double DEFAULT '0',
  `so_tax` double DEFAULT '0',
  `so_shipping` double DEFAULT '0',
  `so_total_price` double DEFAULT '0',
  `so_payment` enum('cash','debit','credit','credit_ar') DEFAULT 'cash',
  `so_dp` double DEFAULT '0',
  `sales_id` mediumint(9) DEFAULT NULL,
  `sales_percentage` decimal(5,2) DEFAULT '0.00',
  `sales_price` double DEFAULT '0',
  `sales_type` char(20) DEFAULT NULL,
  `single_rate` tinyint(1) DEFAULT '0',
  `customer_id` int(11) DEFAULT '0',
  `bank_id` int(1) DEFAULT '0',
  `card_no` char(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pr_number_idx` (`so_number`)
) ENGINE=InnoDB;
#
DROP TABLE IF EXISTS `pos_salesorder_detail`;
#
CREATE TABLE `pos_salesorder_detail` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `so_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `sod_qty` float DEFAULT '0',
  `sod_status` tinyint(1) NOT NULL DEFAULT '0',
  `item_hpp` double DEFAULT '0',
  `sales_price` double DEFAULT '0',
  `sod_potongan` double DEFAULT '0',
  `sod_total` double DEFAULT NULL,
  `storehouse_id` int(11) DEFAULT NULL,
  `use_stok_kode_unik` tinyint(1) DEFAULT '0',
  `data_stok_kode_unik` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#
DROP TABLE IF EXISTS `pos_retur`;
#
CREATE TABLE `pos_retur` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `retur_number` varchar(20) NOT NULL,
  `retur_ref` enum('penjualan','penjualan_so') DEFAULT NULL,
  `retur_type` enum('barang','batal_order') NOT NULL,
  `retur_date` date NOT NULL,
  `retur_memo` tinytext,
  `total_qty` float NOT NULL DEFAULT '0',
  `total_price` double NOT NULL DEFAULT '0',
  `total_tax` double DEFAULT '0',
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `ref_no` varchar(30) DEFAULT NULL,
  `retur_status` enum('progress','done') DEFAULT 'progress',
  `storehouse_id` int(11) DEFAULT '0',
  `customer_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `retur_number_idx` (`retur_number`)
) ENGINE=InnoDB;
#
DROP TABLE IF EXISTS `pos_retur_detail`;
#
CREATE TABLE `pos_retur_detail` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `retur_id` bigint(20) NOT NULL,
  `item_product_id` int(11) NOT NULL,
  `returd_qty_before` int(11) DEFAULT NULL,
  `returd_price` double DEFAULT '0',
  `returd_hpp` double DEFAULT '0',
  `returd_tax` double DEFAULT '0',
  `returd_qty` float DEFAULT '0',
  `returd_total` double DEFAULT '0',
  `returd_ref_id` bigint(20) DEFAULT NULL,
  `returd_refd_id` bigint(20) DEFAULT NULL,
  `returd_note` varchar(255) DEFAULT NULL,
  `data_stok_kode_unik` text,
  `use_stok_kode_unik` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#
DROP TABLE IF EXISTS `pos_notify_log`;
#
CREATE TABLE `pos_notify_log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `log_date` DATE DEFAULT NULL,
  `log_type` ENUM('master_data','inventory','sales','finance','accounting','app') DEFAULT NULL,
  `log_info` VARCHAR(255) DEFAULT NULL,
  `log_data` MEDIUMTEXT NOT NULL,
  `createdby` VARCHAR(50) DEFAULT NULL,
  `created` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=INNODB;
#
DROP TABLE IF EXISTS `acc_autoposting_detail`;
#
CREATE TABLE `acc_autoposting_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `autoposting_id` int(11) DEFAULT NULL,
  `rek_id_debet` int(11) NOT NULL,
  `rek_id_kredit` int(11) DEFAULT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#
DROP TABLE IF EXISTS `acc_periode_laporan`;
#
CREATE TABLE `acc_periode_laporan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kd_periode_laporan` varchar(5) NOT NULL DEFAULT '',
  `ket_periode_laporan` varchar(50) NOT NULL,
  `kd_periode_kalender` varchar(5) NOT NULL,
  `nama_bulan_kalender` varchar(50) NOT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#
insert  into `acc_periode_laporan`(`id`,`kd_periode_laporan`,`ket_periode_laporan`,`kd_periode_kalender`,`nama_bulan_kalender`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values 
(1,'01','Januari','01','Januari','administrator','2014-10-21 17:29:20','administrator','2014-10-21 17:31:02',0,0),
(2,'02','Februari','02','Februari','administrator','2014-10-21 17:31:30','administrator','2014-10-21 17:31:30',0,0),
(3,'03','Maret','03','Maret','administrator','2014-10-21 17:32:04','administrator','2014-10-21 17:32:04',0,0),
(4,'04','April','04','April','administrator','2014-10-21 17:32:18','administrator','2014-10-21 17:32:18',0,0),
(5,'05','Mei','05','Mei','administrator','2014-10-21 17:32:33','administrator','2014-10-21 17:32:33',0,0),
(6,'06','Juni','06','Juni','administrator','2014-10-21 17:32:58','administrator','2014-10-21 17:32:58',0,0),
(7,'07','Juli','07','Juli','administrator','2014-10-21 17:33:14','administrator','2014-10-21 17:33:14',0,0),
(8,'08','Agustus','08','Agustus','administrator','2014-10-21 17:34:56','administrator','2014-10-21 17:34:56',0,0),
(9,'09','September','09','September','administrator','2014-10-21 17:35:11','administrator','2014-10-21 17:35:11',0,0),
(10,'10','Oktober','10','Oktober','administrator','2014-10-21 17:35:30','administrator','2014-10-21 17:35:30',0,0),
(11,'11','November','11','November','administrator','2014-10-21 17:35:46','administrator','2014-10-21 17:35:46',0,0),
(12,'12','Desember','12','Desember','administrator','2014-10-21 17:36:01','administrator','2014-10-21 17:36:01',0,0);
#
DELETE FROM `apps_options` WHERE option_var IN ('wepos_version','store_connected_name','store_connected_email','as_server_backup','use_wms','opsi_no_print_when_payment','using_item_average_as_hpp');
#
INSERT  INTO `apps_options`(`option_var`,`option_value`,`option_description`,`created`,`createdby`,`updated`,`updatedby`,`is_active`,`is_deleted`) VALUES 
('store_connected_name','',NULL,'2019-03-26 00:00:01','administrator',NULL,NULL,'1','0'),
('store_connected_email','',NULL,'2019-03-26 00:00:01','administrator',NULL,NULL,'1','0'),
('as_server_backup','0',NULL,'2019-03-26 00:00:01','administrator',NULL,NULL,1,0),
('use_wms','0',NULL,'2019-03-01 00:00:07','administrator',NULL,NULL,1,0),
('opsi_no_print_when_payment','0',NULL,'2019-03-26 00:00:01','administrator',NULL,NULL,1,0),
('using_item_average_as_hpp','1',NULL,'2019-03-26 00:00:01','administrator',NULL,NULL,1,0),
('wepos_version','3.42.20',NULL,'2019-03-26 00:00:01','administrator',NULL,NULL,1,0);
#
insert  into `pos_payment_type`(`id`,`payment_type_name`,`payment_type_desc`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values 
(4,'AR / Piutang','Paid By AR','administrator','2019-03-26 03:32:50','administrator','2019-03-26 03:32:50',1,0);

#
ALTER TABLE `pos_usagewaste` 
ADD `uw_sales` TINYINT(1) DEFAULT 0,
MODIFY `uw_memo` tinytext;
#
ALTER TABLE `acc_penerimaan_kas` 
ADD `km_atasnama` varchar(100) DEFAULT NULL,
ADD `km_tipe` enum('umum','salesorder','sales') DEFAULT 'umum',
ADD `ref_id` int(11) DEFAULT NULL;
#
ALTER TABLE `acc_pengeluaran_kas` 
ADD `kk_atasnama` varchar(100) DEFAULT NULL,
ADD `kk_tipe` enum('umum','retur_dp','retur_sales') DEFAULT 'umum',
ADD `ref_id` int(11) DEFAULT NULL;
#
DROP TABLE IF EXISTS `pos_tutup_buku`;
#
DROP TABLE IF EXISTS `pos_storehouse_item`;
#
DROP TABLE IF EXISTS `pos_item_subcategory2`;
#
DROP TABLE IF EXISTS `pos_item_subcategory3`;
#
DROP TABLE IF EXISTS `pos_item_subcategory4`;
#
ALTER TABLE `pos_storehouse_users`
MODIFY `is_retail_warehouse` tinyint(1) DEFAULT '0';
#
ALTER TABLE `apps_clients` 
MODIFY `id` tinyint(4) NOT NULL AUTO_INCREMENT,
MODIFY `client_code` varchar(50) NOT NULL,
MODIFY `client_name` char(100) NOT NULL,
MODIFY `client_address` char(150) DEFAULT NULL,
MODIFY `city_id` tinyint(4) DEFAULT NULL,
MODIFY `province_id` tinyint(4) DEFAULT NULL,
MODIFY `client_postcode` char(5) DEFAULT NULL,
MODIFY `country_id` tinyint(4) DEFAULT NULL,
MODIFY `client_phone` char(20) DEFAULT NULL,
MODIFY `client_fax` char(20) DEFAULT NULL,
MODIFY `client_email` char(50) DEFAULT NULL,
MODIFY `client_logo` char(50) DEFAULT NULL,
MODIFY `client_website` char(50) DEFAULT NULL,
MODIFY `client_notes` char(100) DEFAULT NULL,
MODIFY `createdby` char(50) DEFAULT NULL,
MODIFY `created` timestamp NULL DEFAULT NULL,
MODIFY `updatedby` char(50) DEFAULT NULL;
#
ALTER TABLE `apps_clients_structure`
MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT,
MODIFY `client_structure_name` char(100) NOT NULL,
MODIFY `client_structure_notes` char(100) DEFAULT NULL,
MODIFY `client_structure_parent` smallint(6) DEFAULT '0',
MODIFY `client_structure_order` smallint(6) DEFAULT '0',
MODIFY `role_id` smallint(6) DEFAULT NULL,
MODIFY `client_id` tinyint(4) DEFAULT NULL,
MODIFY `client_unit_id` tinyint(4) DEFAULT NULL,
MODIFY `createdby` char(50) DEFAULT NULL,
MODIFY `updatedby` char(50) DEFAULT NULL;
#
ALTER TABLE `apps_clients_unit`
MODIFY `id` tinyint(4) NOT NULL AUTO_INCREMENT,
MODIFY `client_unit_name` char(50) NOT NULL,
MODIFY `client_id` tinyint(4) NOT NULL,
MODIFY `createdby` char(50) DEFAULT NULL,
MODIFY `updatedby` char(50) DEFAULT NULL;
#
ALTER TABLE `apps_modules_method`
MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT,
MODIFY `method_function` char(100) NOT NULL,
MODIFY `module_id` smallint(6) NOT NULL,
MODIFY `method_description` char(100) DEFAULT NULL,
MODIFY `createdby` char(50) DEFAULT NULL,
MODIFY `updatedby` char(50) DEFAULT NULL;
#
ALTER TABLE `apps_modules_preload`
MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT,
MODIFY `preload_filename` char(50) NOT NULL,
MODIFY `preload_folderpath` char(100) DEFAULT NULL,
MODIFY `module_id` smallint(6) NOT NULL,
MODIFY `preload_description` char(100) DEFAULT NULL,
MODIFY `createdby` char(50) DEFAULT NULL,
MODIFY `updatedby` char(50) DEFAULT NULL;
#
ALTER TABLE `apps_options` 
MODIFY `option_value` mediumtext NOT NULL;
#
ALTER TABLE `apps_roles_module`
MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT,
MODIFY `role_id` smallint(6) NOT NULL,
MODIFY `module_id` smallint(6) NOT NULL,
MODIFY `start_menu_path` char(100) DEFAULT NULL,
MODIFY `module_order` smallint(6) DEFAULT '0',
MODIFY `createdby` char(50) DEFAULT NULL,
MODIFY `updatedby` char(50) DEFAULT NULL;
#
ALTER TABLE `apps_roles_widget`
MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT,
MODIFY `role_id` smallint(6) NOT NULL,
MODIFY `widget_id` smallint(6) NOT NULL,
MODIFY `createdby` char(50) DEFAULT NULL,
MODIFY `updatedby` char(50) DEFAULT NULL;
#
ALTER TABLE `apps_supervisor`
MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT,
MODIFY `user_id` smallint(6) NOT NULL,
MODIFY `createdby` char(50) DEFAULT NULL,
MODIFY `updatedby` char(50) DEFAULT NULL;
#
ALTER TABLE `apps_supervisor_access`
MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT,
MODIFY `supervisor_id` smallint(6) NOT NULL,
MODIFY `supervisor_access` char(50) NOT NULL,
MODIFY `createdby` char(50) DEFAULT NULL,
MODIFY `updatedby` char(50) DEFAULT NULL;
#
ALTER TABLE `apps_supervisor_log` 
MODIFY `supervisor_id` smallint(6) NOT NULL,
MODIFY `supervisor_access` char(100) DEFAULT NULL;
#
ALTER TABLE `apps_users`
MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT,
MODIFY `user_username` char(50) NOT NULL,
MODIFY `user_password` char(64) NOT NULL,
MODIFY `role_id` smallint(6) NOT NULL,
MODIFY `user_firstname` char(50) NOT NULL,
MODIFY `user_lastname` char(50) DEFAULT NULL,
MODIFY `user_email` char(50) DEFAULT NULL,
MODIFY `user_phone` char(50) DEFAULT NULL,
MODIFY `user_mobile` char(50) DEFAULT NULL,
MODIFY `user_address` char(100) DEFAULT NULL,
MODIFY `client_id` tinyint(4) NOT NULL DEFAULT '1',
MODIFY `client_structure_id` smallint(6) NOT NULL,
MODIFY `avatar` char(255) DEFAULT NULL,
MODIFY `createdby` char(50) DEFAULT NULL,
MODIFY `updatedby` char(50) DEFAULT NULL;
#
ALTER TABLE `apps_users_desktop`
MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT,
MODIFY `user_id` smallint(6) NOT NULL,
MODIFY `wallpaper` char(50) NOT NULL DEFAULT 'default.jpg',
MODIFY `wallpaper_id` tinyint(4) NOT NULL DEFAULT '1',
MODIFY `createdby` char(50) DEFAULT NULL,
MODIFY `updatedby` char(50) DEFAULT NULL;
#
ALTER TABLE `apps_users_quickstart`
MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT,
MODIFY `user_id` smallint(6) NOT NULL,
MODIFY `module_id` smallint(6) NOT NULL,
MODIFY `createdby` char(50) DEFAULT NULL,
MODIFY `updatedby` char(50) DEFAULT NULL;
#
ALTER TABLE `apps_users_shortcut`
MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT,
MODIFY `user_id` smallint(6) NOT NULL,
MODIFY `module_id` smallint(6) NOT NULL,
MODIFY `createdby` char(50) DEFAULT NULL,
MODIFY `updatedby` char(50) DEFAULT NULL;
#
ALTER TABLE `apps_widgets`
MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT,
MODIFY `widget_name` char(50) NOT NULL,
MODIFY `widget_author` char(50) DEFAULT NULL,
MODIFY `widget_version` char(10) DEFAULT NULL,
MODIFY `widget_description` char(100) DEFAULT NULL,
MODIFY `widget_controller` char(50) NOT NULL,
MODIFY `widget_order` smallint(6) DEFAULT '0',
MODIFY `createdby` char(50) DEFAULT NULL,
MODIFY `updatedby` char(50) DEFAULT NULL;
#
ALTER TABLE `pos_billing_log` 
MODIFY `log_data` mediumtext NOT NULL;
#
ALTER TABLE `pos_item_category`
MODIFY `item_category_code` char(6) DEFAULT NULL;
#
ALTER TABLE `pos_item_subcategory1`
RENAME TO `pos_item_subcategory`;
#
ALTER TABLE `pos_item_subcategory` 
CHANGE COLUMN `item_subcategory1_name` `item_subcategory_name` varchar(100) NOT NULL,
CHANGE COLUMN `item_subcategory1_code` `item_subcategory_code` char(10) NOT NULL,
CHANGE COLUMN `item_subcategory1_desc` `item_subcategory_desc` varchar(100) DEFAULT NULL;
#
DROP TABLE IF EXISTS `pos_item_subcategory2`;
DROP TABLE IF EXISTS `pos_item_subcategory3`;
DROP TABLE IF EXISTS `pos_item_subcategory4`;
#
ALTER TABLE `pos_items` 
CHANGE COLUMN `subcategory1_id` `subcategory_id` SMALLINT(6) NULL,
DROP `subcategory2_id`,
DROP `subcategory3_id`;
#
ALTER TABLE `pos_items` 
DROP `subcategory4_id`;
#
ALTER TABLE `pos_product`
ADD `product_code` varchar(100) DEFAULT NULL,
ADD `product_no` smallint(6) DEFAULT '0',
ADD `unit_id` int(11) DEFAULT '0';
#
ALTER TABLE `pos_product_category`
ADD `product_category_code` char(6) DEFAULT NULL;
#
ALTER TABLE `pos_product_package`
ADD `product_qty` float DEFAULT '1',
ADD `product_varian_id_item` int(11) DEFAULT '0',
ADD `varian_id_item` int(11) DEFAULT '0';
#
ALTER TABLE `pos_stock_opname`
MODIFY `createdby` varchar(50) NOT NULL,
MODIFY `updated` datetime DEFAULT NULL;
#
UPDATE pos_product SET product_code = CONCAT('P',(category_id*1000)+id) WHERE (product_code IS NULL OR product_code = '');
#
UPDATE pos_product_category SET product_category_code = CONCAT('C',(100)+id) WHERE (product_category_code IS NULL OR product_category_code = '');
#
ALTER TABLE `pos_product` 
ADD UNIQUE KEY `item_product_idx` (`product_code`);
#
UPDATE pos_items SET item_code = CONCAT('I',(category_id*1000)+id) WHERE (item_code IS NULL OR item_code = '');
#
ALTER TABLE `pos_unit`
ADD UNIQUE KEY `satuan_code_idx` (`unit_code`);
#
INSERT INTO `apps_roles_module` (`role_id`, `module_id`, `start_menu_path`, `module_order`, `createdby`, `created`, `updatedby`, `updated`, `is_active`, `is_deleted`) VALUES
(5, 184, NULL, 0, 'admin', '2018-09-04 10:14:10', 'admin', '2018-09-04 10:14:10', 1, 0),
(1, 176, NULL, 0, 'admin', '2018-09-04 10:14:10', 'admin', '2018-09-04 10:14:10', 1, 0),
(2, 176, NULL, 0, 'admin', '2018-09-04 10:14:10', 'admin', '2018-09-04 10:14:10', 1, 0),
(1, 177, NULL, 0, 'admin', '2018-09-04 10:14:10', 'admin', '2018-09-04 10:14:10', 1, 0),
(2, 177, NULL, 0, 'admin', '2018-09-04 10:14:10', 'admin', '2018-09-04 10:14:10', 1, 0),
(1, 184, NULL, 0, 'admin', '2018-09-04 10:14:10', 'admin', '2018-09-04 10:14:10', 1, 0),
(2, 184, NULL, 0, 'admin', '2018-09-04 10:14:10', 'admin', '2018-09-04 10:14:10', 1, 0);
#
UPDATE apps_options 
SET option_value = REPLACE(option_value, 'NO: ', ''), 
option_value = REPLACE(option_value, 'NO:', ''), 
option_value = REPLACE(option_value, 'MEJA: ', ''), 
option_value = REPLACE(option_value, 'MEJA:', ''), 
option_value = REPLACE(option_value, 'TIPE: ', ''), 
option_value = REPLACE(option_value, 'TIPE:', '');
#
ALTER TABLE `pos_product` 
MODIFY `product_group` enum('food','beverage','other') DEFAULT 'other';
#
UPDATE `pos_product` SET `product_group` = 'other';
#
ALTER TABLE `pos_closing_sales` 
ADD `total_payment_4` double DEFAULT '0',
ADD `qty_payment_4` smallint(6) DEFAULT '0';