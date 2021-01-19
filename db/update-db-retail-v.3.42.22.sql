/*

MIGRASI DATABASE: v3.42.21 ke v3.42.22

WePOS.id versi Retail
Database update: 01-01-2020

*********************************************************************

*/


UPDATE apps_options SET option_value = '3.42.22' WHERE option_var = 'wepos_version';
#
UPDATE apps_options SET option_value = 'WePOS.Retail' WHERE option_var = 'app_name';
#
UPDATE apps_options SET option_value = 'WePOS.Retail' WHERE option_var = 'app_name_short';
#
UPDATE apps_options SET option_value = '2021' WHERE option_var = 'app_release';
#
DROP TABLE pos_shift;
#
CREATE TABLE `pos_shift` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_shift` VARCHAR(100) NOT NULL,
  `jam_shift_start` VARCHAR(5) NOT NULL DEFAULT '00:00',
  `jam_shift_end` VARCHAR(5) NOT NULL DEFAULT '00:00',
  `createdby` VARCHAR(50) DEFAULT NULL,
  `created` TIMESTAMP NULL DEFAULT NULL,
  `updatedby` VARCHAR(50) DEFAULT NULL,
  `updated` TIMESTAMP NULL DEFAULT NULL,
  `is_deleted` TINYINT(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=INNODB;
#
INSERT INTO `pos_shift` (`id`, `nama_shift`, `jam_shift_start`, `jam_shift_end`, `createdby`, `created`, `updatedby`, `updated`, `is_deleted`) VALUES 
('1','Shift Default','07:00','15:00','administrator','2019-12-09 19:42:49','admin','2020-12-15 01:22:13','0'),
('2','','','','administrator','2019-12-09 19:42:49','admin','2020-12-15 01:22:13','1'),
('3','','','','administrator','2019-12-09 19:42:49','admin','2020-12-15 01:22:13','1');
#
DROP TABLE pos_shift_log;
#
CREATE TABLE `pos_shift_log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_shift` INT(11) DEFAULT NULL,
  `tanggal_shift` DATE DEFAULT NULL,
  `jam_shift_start` VARCHAR(5) DEFAULT NULL,
  `jam_shift_end` VARCHAR(5) DEFAULT NULL,
  `tanggal_jam_start` DATETIME DEFAULT NULL,
  `tanggal_jam_end` DATETIME DEFAULT NULL,
  `tipe_shift` ENUM('open','close') DEFAULT NULL,
  `status_active` TINYINT(1) DEFAULT '0',
  `createdby` VARCHAR(50) DEFAULT NULL,
  `created` TIMESTAMP NULL DEFAULT NULL,
  `updatedby` VARCHAR(50) DEFAULT NULL,
  `updated` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=INNODB;
#
DROP TABLE pos_nontrx_log;
#
CREATE TABLE `pos_nontrx_log` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `nontrx_tanggal` DATE DEFAULT NULL,
  `nontrx_tahun` MEDIUMINT(9) DEFAULT '0',
  `nontrx_bulan` TINYINT(4) DEFAULT '0',
  `nontrx_minggu` SMALLINT(6) DEFAULT '0',
  `nontrx_hari_realisasi` DOUBLE DEFAULT '0',
  `nontrx_shift1_realisasi` DOUBLE DEFAULT '0',
  `nontrx_shift2_realisasi` DOUBLE DEFAULT '0',
  `nontrx_shift3_realisasi` DOUBLE DEFAULT '0',
  `createdby` VARCHAR(50) DEFAULT NULL,
  `created` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=INNODB;
#
DROP TABLE pos_nontrx_target;
#
CREATE TABLE `pos_nontrx_target` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `is_default` TINYINT(1) DEFAULT '0',
  `nontrx_only_dinein` TINYINT(1) DEFAULT '0',
  `nontrx_tahun` MEDIUMINT(9) DEFAULT '0',
  `nontrx_bulan` TINYINT(4) DEFAULT '0',
  `nontrx_bulan_target` DOUBLE DEFAULT '0',
  `nontrx_bulan_realisasi` DOUBLE DEFAULT '0',
  `nontrx_minggu` SMALLINT(6) DEFAULT '0',
  `nontrx_minggu_target` DOUBLE DEFAULT '0',
  `nontrx_minggu_realisasi` DOUBLE DEFAULT '0',
  `nontrx_curr_minggu` MEDIUMINT(9) DEFAULT '0',
  `nontrx_hari` TINYINT(1) DEFAULT '0',
  `nontrx_hari_target` DOUBLE DEFAULT '0',
  `nontrx_hari_realisasi` DOUBLE DEFAULT '0',
  `nontrx_curr_tanggal` DATE DEFAULT NULL,
  `nontrx_shift1` TINYINT(1) DEFAULT '0',
  `nontrx_shift1_target` DOUBLE DEFAULT '0',
  `nontrx_shift1_realisasi` DOUBLE DEFAULT '0',
  `nontrx_shift2` TINYINT(1) DEFAULT '0',
  `nontrx_shift2_target` DOUBLE DEFAULT '0',
  `nontrx_shift2_realisasi` DOUBLE DEFAULT '0',
  `nontrx_shift3` TINYINT(1) DEFAULT '0',
  `nontrx_shift3_target` DOUBLE DEFAULT '0',
  `nontrx_shift3_realisasi` DOUBLE DEFAULT '0',
  `nontrx_range_sales_from` DOUBLE DEFAULT '0',
  `nontrx_range_sales_till` DOUBLE DEFAULT '0',
  `createdby` VARCHAR(50) DEFAULT NULL,
  `created` TIMESTAMP NULL DEFAULT NULL,
  `nontrx_range_jam_from` CHAR(5) DEFAULT '08:00',
  `nontrx_range_jam_till` CHAR(5) DEFAULT '22:00',
  `is_deleted` TINYINT(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=INNODB;
#
DROP TABLE pos_billing_trx;
#
CREATE TABLE `pos_billing_trx` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `billing_no` VARCHAR(20) NOT NULL,
  `table_id` MEDIUMINT(9) DEFAULT NULL,
  `table_no` CHAR(20) DEFAULT NULL,
  `billing_status` ENUM('paid','unpaid','hold','cancel') DEFAULT 'unpaid',
  `total_billing` DOUBLE DEFAULT '0',
  `total_paid` DOUBLE DEFAULT '0',
  `total_pembulatan` DOUBLE DEFAULT '0',
  `billing_notes` CHAR(100) DEFAULT NULL,
  `payment_id` TINYINT(4) NOT NULL,
  `payment_date` DATETIME DEFAULT NULL,
  `bank_id` TINYINT(4) DEFAULT NULL,
  `card_no` CHAR(50) DEFAULT NULL,
  `include_tax` TINYINT(1) DEFAULT '0',
  `tax_percentage` DECIMAL(5,2) DEFAULT '0.00' COMMENT 'will added to total',
  `tax_total` DOUBLE DEFAULT '0',
  `include_service` TINYINT(1) DEFAULT '0',
  `service_percentage` DECIMAL(5,2) DEFAULT '0.00',
  `service_total` DOUBLE DEFAULT '0',
  `discount_id` MEDIUMINT(9) DEFAULT NULL,
  `discount_notes` CHAR(100) DEFAULT NULL,
  `discount_percentage` DECIMAL(5,2) DEFAULT '0.00',
  `discount_price` DOUBLE DEFAULT '0',
  `discount_total` DOUBLE DEFAULT '0',
  `is_compliment` TINYINT(1) DEFAULT '0',
  `is_half_payment` TINYINT(1) DEFAULT '0',
  `total_cash` DOUBLE DEFAULT '0',
  `total_credit` DOUBLE DEFAULT '0',
  `total_hpp` DOUBLE DEFAULT '0',
  `total_guest` SMALLINT(6) DEFAULT '1',
  `createdby` VARCHAR(50) DEFAULT NULL,
  `created` TIMESTAMP NULL DEFAULT NULL,
  `updatedby` VARCHAR(50) DEFAULT NULL,
  `updated` TIMESTAMP NULL DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT '1',
  `is_deleted` TINYINT(1) DEFAULT '0',
  `merge_id` INT(11) DEFAULT NULL,
  `merge_main_status` TINYINT(1) DEFAULT '0',
  `split_from_id` INT(11) DEFAULT NULL,
  `takeaway_no_tax` TINYINT(1) DEFAULT '0',
  `takeaway_no_service` TINYINT(1) DEFAULT '0',
  `total_dp` DOUBLE DEFAULT '0',
  `grand_total` DOUBLE DEFAULT '0',
  `total_return` DOUBLE DEFAULT '0',
  `discount_perbilling` TINYINT(1) DEFAULT '0',
  `voucher_no` CHAR(100) DEFAULT NULL,
  `compliment_total` DOUBLE DEFAULT '0',
  `compliment_total_tax_service` DOUBLE DEFAULT '0',
  `cancel_notes` CHAR(100) DEFAULT NULL,
  `sales_id` MEDIUMINT(9) DEFAULT NULL,
  `sales_percentage` DECIMAL(5,2) DEFAULT '0.00',
  `sales_price` DOUBLE DEFAULT '0',
  `sales_type` CHAR(20) DEFAULT NULL,
  `lock_billing` TINYINT(1) DEFAULT '0',
  `qc_notes` VARCHAR(100) DEFAULT NULL,
  `storehouse_id` INT(11) DEFAULT '0',
  `is_sistem_tawar` TINYINT(1) DEFAULT '0',
  `single_rate` TINYINT(1) DEFAULT '0',
  `customer_id` INT(11) DEFAULT '0',
  `is_salesorder` TINYINT(1) DEFAULT '0',
  `billing_no_simple` VARCHAR(10) DEFAULT NULL,
  `txmark` TINYINT(1) DEFAULT '0',
  `txmark_no` VARCHAR(20) DEFAULT NULL,
  `txmark_no_simple` VARCHAR(10) DEFAULT NULL,
  `group_date` DATE DEFAULT NULL,
  `diskon_sebelum_pajak_service` TINYINT(1) DEFAULT '0',
  `shift` TINYINT(1) DEFAULT '0',
  `billing_date` DATE DEFAULT NULL,
  `billing_datetime` DATETIME DEFAULT NULL,
  `billing_time` CHAR(5) DEFAULT NULL,
  `billing_no_before` CHAR(20) DEFAULT NULL,
  `block_table` TINYINT(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `billing_no` (`billing_no`)
) ENGINE=INNODB;
#
DROP TABLE pos_billing_detail_trx;
#
CREATE TABLE `pos_billing_detail_trx` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` MEDIUMINT(9) NOT NULL,
  `order_qty` FLOAT DEFAULT '0',
  `product_price` DOUBLE DEFAULT '0',
  `product_price_hpp` DOUBLE DEFAULT '0',
  `product_normal_price` DOUBLE DEFAULT '0',
  `category_id` TINYINT(4) DEFAULT NULL,
  `billing_id` INT(11) NOT NULL,
  `order_status` ENUM('order','progress','done','cancel') DEFAULT 'order',
  `order_notes` CHAR(100) DEFAULT NULL,
  `order_day_counter` INT(11) DEFAULT NULL,
  `order_counter` SMALLINT(6) DEFAULT '0',
  `retur_type` ENUM('none','payment','menu') DEFAULT 'none',
  `retur_qty` FLOAT DEFAULT '0',
  `retur_reason` CHAR(100) DEFAULT NULL,
  `createdby` VARCHAR(50) DEFAULT NULL,
  `created` TIMESTAMP NULL DEFAULT NULL,
  `updatedby` VARCHAR(50) DEFAULT NULL,
  `updated` TIMESTAMP NULL DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT '1',
  `is_deleted` TINYINT(1) DEFAULT '0',
  `billing_id_before_merge` INT(11) DEFAULT NULL,
  `cancel_order_notes` CHAR(100) DEFAULT NULL,
  `order_qty_split` FLOAT DEFAULT '0',
  `product_price_real` DOUBLE DEFAULT '0',
  `has_varian` TINYINT(1) DEFAULT '0',
  `varian_id` MEDIUMINT(9) DEFAULT NULL,
  `product_varian_id` INT(11) DEFAULT NULL,
  `print_qc` TINYINT(1) DEFAULT '0',
  `print_order` TINYINT(1) DEFAULT '0',
  `include_tax` TINYINT(1) DEFAULT '1',
  `tax_percentage` DECIMAL(5,2) DEFAULT '0.00',
  `tax_total` DOUBLE DEFAULT '0',
  `include_service` TINYINT(1) DEFAULT '1',
  `service_percentage` DECIMAL(5,2) DEFAULT '0.00',
  `service_total` DOUBLE DEFAULT '0',
  `is_takeaway` TINYINT(1) DEFAULT '0',
  `takeaway_no_tax` TINYINT(1) DEFAULT '0',
  `takeaway_no_service` TINYINT(1) DEFAULT '0',
  `is_compliment` TINYINT(1) DEFAULT '0',
  `discount_id` MEDIUMINT(9) DEFAULT NULL,
  `discount_notes` CHAR(100) DEFAULT NULL,
  `discount_percentage` DECIMAL(5,2) DEFAULT '0.00',
  `discount_price` DOUBLE DEFAULT '0',
  `discount_total` DOUBLE DEFAULT '0',
  `is_promo` TINYINT(1) DEFAULT '0',
  `promo_id` MEDIUMINT(9) DEFAULT NULL,
  `promo_tipe` TINYINT(1) DEFAULT '0',
  `promo_desc` CHAR(100) DEFAULT NULL,
  `promo_percentage` DECIMAL(5,2) DEFAULT '0.00',
  `promo_price` DOUBLE DEFAULT '0',
  `is_kerjasama` TINYINT(1) DEFAULT '0',
  `supplier_id` INT(11) DEFAULT '0',
  `persentase_bagi_hasil` DECIMAL(5,2) DEFAULT '0.00',
  `total_bagi_hasil` DOUBLE DEFAULT '0',
  `grandtotal_bagi_hasil` DOUBLE DEFAULT '0',
  `storehouse_id` INT(11) DEFAULT '0',
  `is_buyget` TINYINT(1) DEFAULT '0',
  `buyget_id` INT(11) DEFAULT '0',
  `buyget_tipe` VARCHAR(20) DEFAULT NULL,
  `buyget_percentage` DECIMAL(5,2) DEFAULT '0.00',
  `buyget_total` DOUBLE DEFAULT '0',
  `buyget_qty` FLOAT DEFAULT '0',
  `buyget_desc` VARCHAR(100) DEFAULT '',
  `buyget_item` INT(11) DEFAULT '0',
  `free_item` TINYINT(1) DEFAULT '0',
  `ref_order_id` INT(11) DEFAULT '0',
  `use_stok_kode_unik` TINYINT(1) DEFAULT '0',
  `data_stok_kode_unik` TEXT,
  `product_type` ENUM('item','package') DEFAULT 'item',
  `package_item` TINYINT(1) DEFAULT '0',
  `diskon_sebelum_pajak_service` TINYINT(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=INNODB;
#
DROP TABLE pos_closing_sales_trx;
#
CREATE TABLE `pos_closing_sales_trx` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tanggal` DATE DEFAULT NULL,
  `qty_billing` SMALLINT(6) DEFAULT '0',
  `total_guest` SMALLINT(6) DEFAULT '0',
  `total_billing` DOUBLE DEFAULT '0',
  `tax_total` DOUBLE DEFAULT '0',
  `service_total` DOUBLE DEFAULT '0',
  `discount_total` DOUBLE DEFAULT '0',
  `total_dp` DOUBLE DEFAULT '0',
  `grand_total` DOUBLE DEFAULT '0',
  `sub_total` DOUBLE DEFAULT '0',
  `total_pembulatan` DOUBLE DEFAULT '0',
  `total_compliment` DOUBLE DEFAULT '0',
  `total_hpp` DOUBLE DEFAULT '0',
  `total_profit` DOUBLE DEFAULT '0',
  `qty_halfpayment` SMALLINT(6) DEFAULT '0',
  `total_payment_1` DOUBLE DEFAULT '0',
  `qty_payment_1` SMALLINT(6) DEFAULT '0',
  `total_payment_2` DOUBLE DEFAULT '0',
  `qty_payment_2` SMALLINT(6) DEFAULT '0',
  `total_payment_3` DOUBLE DEFAULT '0',
  `qty_payment_3` SMALLINT(6) DEFAULT '0',
  `total_payment_4` DOUBLE DEFAULT '0',
  `qty_payment_4` SMALLINT(6) DEFAULT '0',
  `createdby` VARCHAR(50) DEFAULT NULL,
  `created` TIMESTAMP NULL DEFAULT NULL,
  `updatedby` VARCHAR(50) DEFAULT NULL,
  `updated` TIMESTAMP NULL DEFAULT NULL,
  `discount_billing` DOUBLE DEFAULT '0',
  `discount_item` DOUBLE DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=INNODB;
#
DROP TABLE pos_billing_detail_gramasi;
#
CREATE TABLE `pos_billing_detail_gramasi` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `billing_id` INT(11) DEFAULT NULL,
  `billing_detail_id` INT(11) DEFAULT NULL,
  `product_id` INT(11) NOT NULL,
  `item_id` INT(11) NOT NULL,
  `item_price` DOUBLE DEFAULT '0',
  `item_qty` FLOAT DEFAULT '0',
  `createdby` VARCHAR(50) DEFAULT NULL,
  `created` TIMESTAMP NULL DEFAULT NULL,
  `updatedby` VARCHAR(50) DEFAULT NULL,
  `updated` TIMESTAMP NULL DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT '1',
  `is_deleted` TINYINT(1) DEFAULT '0',
  `product_varian_id` INT(11) DEFAULT '0',
  `varian_id` INT(11) DEFAULT '0',
  `item_hpp` DOUBLE DEFAULT '0',
  `unit_id` INT(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=INNODB;
#
DROP TABLE pos_billing_detail_package;
#
CREATE TABLE `pos_billing_detail_package` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `billing_id` INT(11) NOT NULL,
  `billing_detail_id` INT(11) DEFAULT NULL,
  `product_id` INT(11) NOT NULL,
  `product_price` DOUBLE DEFAULT NULL,
  `product_hpp` DOUBLE DEFAULT '0',
  `createdby` VARCHAR(50) DEFAULT NULL,
  `created` TIMESTAMP NULL DEFAULT NULL,
  `updatedby` VARCHAR(50) DEFAULT NULL,
  `updated` TIMESTAMP NULL DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT '1',
  `is_deleted` TINYINT(1) DEFAULT '0',
  `normal_price` DOUBLE DEFAULT '0',
  `has_varian` SMALLINT(6) DEFAULT '0',
  `product_varian_id` INT(11) DEFAULT '0',
  `varian_id` INT(11) DEFAULT '0',
  `product_qty` FLOAT DEFAULT '0',
  `product_varian_id_item` INT(11) DEFAULT '0',
  `varian_id_item` INT(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=INNODB;
#
DROP TABLE pos_product_price;
#
CREATE TABLE `pos_product_price` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL,
  `qty_from` FLOAT DEFAULT '0',
  `qty_till` FLOAT DEFAULT '0',
  `product_price` DOUBLE DEFAULT NULL,
  `createdby` VARCHAR(50) DEFAULT NULL,
  `created` TIMESTAMP NULL DEFAULT NULL,
  `updatedby` VARCHAR(50) DEFAULT NULL,
  `updated` TIMESTAMP NULL DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT '1',
  `is_deleted` TINYINT(1) DEFAULT '0',
  `product_varian_id` INT(11) DEFAULT '0',
  `varian_id` INT(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=INNODB;
#
ALTER TABLE `pos_billing`
ADD `diskon_sebelum_pajak_service` TINYINT(1) DEFAULT '0';
#
ALTER TABLE `pos_billing`
ADD `shift` TINYINT(1) DEFAULT '0';
#
ALTER TABLE `pos_billing`
ADD `billing_date` DATE DEFAULT NULL,
ADD `billing_datetime` DATETIME DEFAULT NULL,
ADD `billing_time` CHAR(5) DEFAULT NULL,
ADD `billing_no_before` CHAR(20) DEFAULT NULL;
#
ALTER TABLE `pos_billing`
ADD `block_table` TINYINT(1) DEFAULT '0';
#
ALTER TABLE `pos_billing_detail`
ADD `diskon_sebelum_pajak_service` TINYINT(1) DEFAULT '0';
#
ALTER TABLE `pos_billing_detail_split`
ADD `diskon_sebelum_pajak_service` TINYINT(1) DEFAULT '0';
#
ALTER TABLE `pos_billing_detail`
MODIFY `order_qty` FLOAT DEFAULT '0',
MODIFY `buyget_qty` FLOAT DEFAULT '0',
MODIFY `order_qty_split` FLOAT DEFAULT '0',
MODIFY `retur_qty` FLOAT DEFAULT '0';
#
ALTER TABLE `pos_closing_sales`
ADD `discount_billing` DOUBLE DEFAULT '0',
ADD `discount_item` DOUBLE DEFAULT '0';
#
INSERT INTO `apps_roles_module` (`role_id`, `module_id`, `start_menu_path`, `module_order`, `createdby`, `created`, `updatedby`, `updated`, `is_active`, `is_deleted`) VALUES
(1, 194, NULL, 0, 'admin', '2020-07-31 10:14:10', 'admin', '2020-07-31 10:14:10', 1, 0),
(2, 194, NULL, 0, 'admin', '2020-07-31 10:14:10', 'admin', '2020-07-31 10:14:10', 1, 0),
(5, 194, NULL, 0, 'admin', '2020-07-31 10:14:10', 'admin', '2020-07-31 10:14:10', 1, 0);
#
UPDATE apps_modules SET is_active = 0
WHERE module_controller IN ('billingCashierRetailApp','ppob','returPembelian','listStockImei');
#
ALTER TABLE pos_printer
MODIFY `print_method` ENUM('ESC/POS','JSPRINT','BROWSER','RAWBT') DEFAULT 'ESC/POS';
#
ALTER TABLE pos_table_inventory
MODIFY `is_active` TINYINT(1) DEFAULT '1',
ADD `total_billing` TINYINT(4) DEFAULT '0';
#
ALTER TABLE pos_ooo_menu
MODIFY `is_active` TINYINT(1) DEFAULT '1';
#
ALTER TABLE pos_floorplan
ADD `list_no` TINYINT(4) DEFAULT NULL;
#
ALTER TABLE pos_product
ADD `product_bg_color` CHAR(6) DEFAULT '000000',
ADD `product_text_color` CHAR(6) DEFAULT 'FFFFFF',
ADD `qty_unit` SMALLINT(6) DEFAULT 1,
ADD `has_list_price` TINYINT(1) DEFAULT 0;
#
ALTER TABLE pos_product_category
ADD  `list_no` INT(4) DEFAULT '0';
#
ALTER TABLE pos_product_category
ADD  `product_category_bg_color` CHAR(6) DEFAULT '000000',
ADD  `product_category_text_color` CHAR(6) DEFAULT 'FFFFFF';
#
ALTER TABLE pos_items
ADD `qty_unit` SMALLINT(6) DEFAULT 1;
#
ALTER TABLE pos_customer
MODIFY `customer_city` VARCHAR(255) DEFAULT NULL,
ADD `sales_id` INT(11) DEFAULT '0';
#
ALTER TABLE pos_open_close_shift
ADD `tanggal_jam_shift` DATETIME DEFAULT NULL;
#
UPDATE apps_options 
SET option_value = REPLACE(option_value, '{order_data}', '{template_order_data}');
#
UPDATE apps_options 
SET option_value = REPLACE(option_value, 'Tipe: {customer}', '{customer_code} / {customer}');
#
ALTER TABLE pos_product DROP INDEX item_product_idx;
#
ALTER TABLE pos_item_category DROP INDEX item_category_code;
#
ALTER TABLE pos_bank DROP INDEX bank_code_idx;
#
CREATE VIEW `pos_billing_transaksi` AS (SELECT billing_no AS no_billing,payment_date AS tanggal_billing,total_billing AS subtotal,discount_total AS diskon, service_total AS service_charge, tax_total AS pajak, grand_total FROM pos_billing_trx);

