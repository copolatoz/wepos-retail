/*

MIGRASI DATABASE: v3.42.20 ke v3.42.21

WePOS.id versi Retail
Database update: 19-11-2019

*********************************************************************

*/

ALTER TABLE pos_stock_opname_detail
ADD `use_stok_kode_unik` TINYINT(1) DEFAULT '0',
ADD `data_stok_kode_unik` TEXT DEFAULT NULL;
#
CREATE TABLE `pos_stock_opname_kode_unik` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `stod_id` int(11) DEFAULT NULL,
  `varian_name` varchar(100) DEFAULT NULL,
  `kode_unik` varchar(255) DEFAULT NULL,
  `temp_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#
CREATE TABLE `pos_stock_koreksi_kode_unik` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `koreksi_id` bigint(20) DEFAULT NULL,
  `varian_name` varchar(100) DEFAULT NULL,
  `kode_unik` varchar(255) DEFAULT NULL,
  `temp_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#
CREATE TABLE `pos_receive_kode_unik` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `received_id` int(11) DEFAULT NULL,
  `varian_name` varchar(100) DEFAULT NULL,
  `kode_unik` varchar(255) DEFAULT NULL,
  `temp_id` varchar(255) DEFAULT NULL,
  `po_detail_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#
ALTER TABLE pos_item_category
MODIFY `id` INT(11) NOT NULL AUTO_INCREMENT,
MODIFY `item_category_code` CHAR(6) NOT NULL,
ADD `as_product_category` tinyint(1) DEFAULT '0';
#
ALTER TABLE pos_product_category
MODIFY `product_category_code` CHAR(6) NOT NULL,
ADD `from_item_category` INT(11) DEFAULT '0';
#
ALTER TABLE pos_item_subcategory
MODIFY `id` INT(11) NOT NULL AUTO_INCREMENT,
MODIFY `item_subcategory_code` CHAR(6) NOT NULL,
ADD `item_category_id` INT(11) NOT NULL;
#
ALTER TABLE pos_item_kode_unik
ADD `use_tax`  tinyint(1) DEFAULT '0';
#
DROP TABLE pos_item_kode_unik_log;
#
CREATE TABLE `pos_item_kode_unik_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `kode_unik_id` bigint(20) DEFAULT NULL,
  `ref_in` varchar(50) DEFAULT NULL,
  `date_in` datetime DEFAULT NULL,
  `ref_out` varchar(50) DEFAULT NULL,
  `date_out` datetime DEFAULT NULL,
  `storehouse_id` smallint(6) DEFAULT NULL,
  `item_hpp` double DEFAULT '0',
  `item_sales` double DEFAULT NULL,
  `varian_name` varchar(50) DEFAULT NULL,
  `varian_group` varchar(50) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#
ALTER TABLE pos_customer
MODIFY `customer_code` VARCHAR(20) NOT NULL,
ADD `customer_city` VARCHAR(255) NOT NULL,
ADD `limit_kredit` DOUBLE DEFAULT '0',
ADD `termin` smallint(6) DEFAULT NULL;
#
ALTER TABLE pos_supplier
MODIFY `supplier_code` VARCHAR(20) NOT NULL,
ADD `supplier_city` VARCHAR(255) NOT NULL,
ADD `supplier_termin` smallint(6) DEFAULT NULL;
#
ALTER TABLE pos_po_detail
ADD `po_detail_tax` double DEFAULT '0';
#
UPDATE apps_options 
SET option_value = REPLACE(option_value, 'NO: ', ''), 
option_value = REPLACE(option_value, 'NO:', ''), 
option_value = REPLACE(option_value, 'MEJA: ', ''), 
option_value = REPLACE(option_value, 'MEJA:', '');
#
UPDATE apps_options 
SET option_value = REPLACE(option_value, '{Cat}.{SubCat1}.{ItemNo}', '{Cat}.{SubCat}.{ItemNo}');
#
UPDATE apps_options 
SET option_value = REPLACE(option_value, 'Notes: {qc_notes}', '{qc_notes}'), 
option_value = REPLACE(option_value, 'notes: {qc_notes}', '{qc_notes}');
#
INSERT  INTO `apps_roles_module`(`role_id`,`module_id`,`start_menu_path`,`module_order`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) VALUES 
(1,190,NULL,0,'administrator','2019-04-09 16:18:38','administrator','2019-04-09 16:18:38',1,0),
(2,190,NULL,0,'administrator','2019-04-09 16:18:38','administrator','2019-04-09 16:18:38',1,0);
#
UPDATE apps_options SET option_value = '3.42.21' WHERE option_var = 'wepos_version';
#
UPDATE apps_options SET option_value = 'WePOS.Retail' WHERE option_var = 'app_name';
#
UPDATE apps_options SET option_value = 'WePOS.Retail' WHERE option_var = 'app_name_short';
#
UPDATE apps_options SET option_value = '2019' WHERE option_var = 'app_release';
#
ALTER TABLE `pos_billing` 
ADD `billing_no_simple` VARCHAR(10) DEFAULT NULL,
ADD `txmark` TINYINT(1) DEFAULT 0,
ADD `txmark_no` VARCHAR(20) DEFAULT NULL,
ADD `txmark_no_simple` VARCHAR(10) DEFAULT NULL,
ADD `group_date` DATE DEFAULT NULL;
#
ALTER TABLE `pos_salesorder`
ADD `so_termin` TINYINT(4) DEFAULT '0';
