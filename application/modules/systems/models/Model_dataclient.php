<?php
class Model_DataClient extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'clients';
	}
	
	function wepos_log_update($force_update = false)
	{
		
		$this->load->library('curl');
		$this->load->helper('directory');
		$this->load->helper('file');
		
		$opt_var = array(
			'merchant_key',
			'merchant_tipe',
			'merchant_cor_token',
			'merchant_acc_token',
			'merchant_mkt_token',
			'produk_key',
			'produk_nama',
			'produk_expired',
			'merchant_last_checkon',
			'is_cloud'
		);
		$get_opt = get_option_value($opt_var);
		
		$merchant_key = '';
		if(empty($get_opt['merchant_key'])){
			$get_opt['merchant_key'] = '';
			return true;
		}else{
			$merchant_key = $get_opt['merchant_key'];
		}
		if(empty($get_opt['merchant_tipe'])){
			$get_opt['merchant_tipe'] = 'retail';
		}
		if(empty($get_opt['merchant_cor_token'])){
			$get_opt['merchant_cor_token'] = '';
		}
		if(empty($get_opt['merchant_acc_token'])){
			$get_opt['merchant_acc_token'] = '';
		}
		if(empty($get_opt['merchant_mkt_token'])){
			$get_opt['merchant_mkt_token'] = '';
		}
		if(empty($get_opt['produk_nama'])){
			$get_opt['produk_nama'] = 'Gratis / Free';
		}
		if(empty($get_opt['produk_expired'])){
			$get_opt['produk_expired'] = 'unlimited';
		}
		
		$today_check = strtotime(date("d-m-Y H:i:s"));
		
		$update_last_check = false;
		if(empty($get_opt['merchant_last_checkon'])){
			$get_opt['merchant_last_checkon'] = 0;
			$update_last_check = true;
		}else{
			$merchant_last_checkon_7 = $get_opt['merchant_last_checkon'] + (ONE_DAY_UNIX*7);
			if($merchant_last_checkon_7 < $today_check){
				$update_last_check = true;
			}
		}
		
		$reset = false;
		if($force_update == true){
			$update_last_check = true;
		}
		
		if($update_last_check){
			
			$opt_var = array(
				'merchant_last_checkon' => $today_check
			);
			update_option($opt_var);
				
			if($get_opt['produk_nama'] != 'Gratis / Free'){
				
				if($get_opt['merchant_mkt_token'] < $today_check){
					
					$opt_var = array(
						'mlog_'.$merchant_key,
						'is_cloud'
					);
					$get_opt = get_option_value($opt_var);
					
					$mlog = '';
					if(empty($get_opt['mlog_'.$merchant_key])){
						$mlog = $get_opt['mlog_'.$merchant_key];
					}
					
					$resetapp = array(
						'merchant_cor_token'=> '',
						'merchant_acc_token'=> '',
						'merchant_mkt_token'=> '',
						'produk_key' 		=> 'GFR-'.strtotime(date("d-m-Y")),
						'produk_nama'		=> 'Gratis / Free',
						'produk_expired'	=> 'unlimited',
						'mlog_'.$merchant_key	=> ''
					);
					update_option($resetapp);
					
					if(!empty($mlog) AND empty($get_opt['is_cloud'])){
						$minjs_path = BASE_PATH.'/apps.min/modules'; 
						$mlog_json = json_decode($mlog);
						if(!empty($mlog_json)){
							foreach($mlog_json as $v){
								$file_minjs = $minjs_path.'/'.$v;
								@unlink($file_minjs);
							}
						}
					}
					
					$reset = true;
					$allow_reset = true;
					
				}else{
					
					if($force_update){
						
						$mktime_dc = strtotime(date("d-m-Y H:i:s"));
						
						$module_path = RESOURCES_PATH.$merchant_key;
						$file_download = RESOURCES_PATH.$merchant_key.'.zip';
						$fp = fopen ($file_download, 'w+'); 
						
						if($fp){
							$client_url = config_item('website').'/client-download?_dc='.$mktime_dc;
						
							$post_data = array(
								'merchant_key'	=> $merchant_key,
								'merchant_tipe'	=> $merchant_tipe
							);
							
							$wepos_crt = ASSETS_PATH.config_item('wepos_crt_file');
							$this->curl->create($client_url);
							$this->curl->option('connecttimeout', 600);
							$this->curl->option('RETURNTRANSFER', 1);
							$this->curl->option('SSL_VERIFYPEER', 1);
							$this->curl->option('SSL_VERIFYHOST', 2);
							//$this->curl->option('SSLVERSION', 3);
							$this->curl->option('POST', 1);
							$this->curl->option('POSTFIELDS', $post_data);
							$this->curl->option('CAINFO', $wepos_crt);
							$this->curl->option('FILE', $fp);
							$curl_ret = $this->curl->execute();
							
							$this->curl->close();
							fclose($fp);
							
							//unzip
							$zip = new ZipArchive;
				 
							if ($zip->open($file_download) === TRUE) 
							{
								if (!is_dir($module_path)) {
									@mkdir($module_path, 0777, TRUE);
								}

								$zip->extractTo($module_path);
								$zip->close();
								
								@unlink($file_download);
							}
							
							$appmin_folder = '';
							$module_files = '';
							
							//install
							$dir_mod = directory_map($module_path, 1);
							if(count($dir_mod) > 0)
							{
								foreach($dir_mod as $file_dl)
								{
									if($file_dl == 'db.sql'){
										$sql_contents = file_get_contents($module_path.'/'.$file_dl);
										$sql_contents = explode(";", $sql_contents);
										@unlink($module_path.'/'.$file_dl);
										
										//running query
										foreach($sql_contents as $query)
										{
											$query = trim($query);
											if(!empty($query)){
												@$this->db->query($query);
											}
										}
										
									}else
									if($file_dl == 'modules.file'){
										
										if(empty($get_opt['is_cloud'])){
											
											$appmod_path = APPPATH.'/modules'; 
											delete_files($appmod_path, TRUE);
											
											$module_file = $module_path.'/'.$file_dl;
											
											$zip = new ZipArchive;
											if($zip->open($module_file) === TRUE) 
											{
												if (!is_dir($appmod_path)) {
													@mkdir($appmod_path, 0777, TRUE);
												}

												$zip->extractTo($appmod_path);
												$zip->close();
											}
											
											
										}
										
										@unlink($module_path.'/'.$file_dl);
										
									}else
									if($file_dl == 'apps.min'){
										
										if(empty($get_opt['is_cloud'])){
											
											$minjs_path = BASE_PATH.'/apps.min/modules'; 
											delete_files($minjs_path, TRUE);
											
											$module_file = $module_path.'/'.$file_dl;
											
											$zip = new ZipArchive;
											if($zip->open($module_file) === TRUE) 
											{
												if (!is_dir($minjs_path)) {
													@mkdir($minjs_path, 0777, TRUE);
												}

												$zip->extractTo($minjs_path);
												$zip->close();
											}
											
											
										}
										
										@unlink($module_path.'/'.$file_dl);
										
									}else
									if($file_dl == 'application.helper'){
										
										if(empty($get_opt['is_cloud'])){
											
											$appmod_path = APPPATH.'/helpers'; 
											delete_files($appmod_path, TRUE);
											
											$module_file = $module_path.'/'.$file_dl;
											
											$zip = new ZipArchive;
											if($zip->open($module_file) === TRUE) 
											{
												if (!is_dir($appmod_path)) {
													@mkdir($appmod_path, 0777, TRUE);
												}

												$zip->extractTo($appmod_path);
												$zip->close();
											}
											
										}
										
										@unlink($module_path.'/'.$file_dl);
										
									}else
									if($file_dl == 'apps.min.helper'){
										
										if(empty($get_opt['is_cloud'])){
											
											$minjs_path = BASE_PATH.'/apps.min/helper'; 
											delete_files($minjs_path, TRUE);
											
											$module_file = $module_path.'/'.$file_dl;
											
											$zip = new ZipArchive;
											if($zip->open($module_file) === TRUE) 
											{
												if (!is_dir($minjs_path)) {
													@mkdir($minjs_path, 0777, TRUE);
												}

												$zip->extractTo($minjs_path);
												$zip->close();
											}
											
										}
										
										@unlink($module_path.'/'.$file_dl);
										
									}else
									if($file_dl == 'application.default'){
										
										if(empty($get_opt['is_cloud'])){
											
											$appcore_path = APPPATH.'/core'; 
											
											$module_file = $module_path.'/'.$file_dl;
											$new_module_file = $appcore_path.'/modules.default';
											
											//copy
											@copy($module_file, $new_module_file);
											
										}
										
										@unlink($module_path.'/'.$file_dl);
										
									}else
									if($file_dl == 'apps.min.default'){
										
										if(empty($get_opt['is_cloud'])){
											
											$appsmincore_path = BASE_PATH.'/apps.min/core';
											
											$module_file = $module_path.'/'.$file_dl;
											$new_module_file = $appsmincore_path.'/modules.default';
											
											//copy
											@copy($module_file, $new_module_file);
											
										}
										
										@unlink($module_path.'/'.$file_dl);
									}
									
								}
							}
							
							
							$filelog = array();
							$minjs_path = BASE_PATH.'/apps.min/modules'; 
							//copy module
							if (is_dir($minjs_path)){
								
								$dir_items = directory_map($minjs_path, 1);
								
								if(count($dir_items) > 0)
								{
									foreach($dir_items as $v)
									{
										$filelog[] = $v;
									}
								}
								
								@rmdir($module_path);
							}
							
							if(!empty($filelog)){
								$filelog_update = json_encode($filelog);
								$opt_var = array(
									'mlog_'.$merchant_key => $filelog_update
								);
								update_option($opt_var);
								
							}
							
						}
					}
					
				}
			}else{
			
				$reset = true;
				$allow_reset = true;
				
			}
			
			if($reset == true AND $allow_reset == true){
				
				
				if(!function_exists('doresetapp')){
					
					$resetapp = array(
						'ipserver_management_systems'=> 'https://wepos.id',
						'management_systems'=> 0,
						'use_wms'			=> 0,
						'opsi_no_print_when_payment'=> 0,
						'use_login_pin'	=> 0,
						'supervisor_pin_mode'	=> 0,
						'view_multiple_store'	=> 0,
						'autobackup_on_settlement'	=> 0,
						'must_choose_customer'	=> 0,
						'no_hold_billing'	=> 0,
						'hide_tanya_wepos'	=> 0,
						'using_item_average_as_hpp'	=> 0,
						'show_multiple_print_billing'	=> 0,
						'show_multiple_print_qc'	=> 0,
					);
					update_option($resetapp);
					
					$this->db->query('TRUNCATE TABLE '.$this->prefix.'modules');
					$this->db->query("insert  into ".$this->prefix."modules (`id`,`module_name`,`module_author`,`module_version`,`module_description`,`module_folder`,`module_controller`,`module_is_menu`,`module_breadcrumb`,`module_order`,`module_icon`,`module_shortcut_icon`,`module_glyph_icon`,`module_glyph_font`,`module_free`,`running_background`,`show_on_start_menu`,`show_on_right_start_menu`,`start_menu_path`,`start_menu_order`,`start_menu_icon`,`start_menu_glyph`,`show_on_context_menu`,`context_menu_icon`,`context_menu_glyph`,`show_on_shorcut_desktop`,`desktop_shortcut_icon`,`desktop_shortcut_glyph`,`show_on_preference`,`preference_icon`,`preference_glyph`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'Setup Aplikasi','dev@wepos.id','v.1.0','','systems','setupAplikasiFree',1,'1. Master Aplikasi>Setup Aplikasi',1,'icon-cog','icon-cog','','',1,0,1,0,'1. Master Aplikasi>Setup Aplikasi',1000,'icon-cog','',0,'icon-cog','',1,'icon-cog','',0,'icon-cog','','administrator','2018-07-10 08:52:11','administrator','2018-07-30 00:00:00',1,0),(2,'Client Info','dev@wepos.id','v.1.0.0','Client Info','systems','clientInfo',0,'1. Master Aplikasi>Client Info',1,'icon-home','icon-home','','',1,0,1,0,'1. Master Aplikasi>Client Info',1101,'icon-home','',0,'icon-home','',1,'icon-home','',1,'icon-home','','administrator','2018-07-03 07:47:08','administrator','2018-07-03 07:47:08',1,0),(3,'Client Unit','dev@wepos.id','v.1.0','','systems','DataClientUnit',1,'1. Master Aplikasi>Client Unit',1,'icon-building','icon-building','','',1,0,1,0,'1. Master Aplikasi>Client Unit',1102,'icon-building','',0,'icon-building','',1,'icon-building','',1,'icon-building','','administrator','2018-07-10 08:52:10','administrator','2018-07-30 00:00:00',1,0),(4,'Data Structure','dev@wepos.id','v.1.0','','systems','DataStructure',1,'1. Master Aplikasi>Data Structure',1,'icon-building','icon-building','','',1,0,1,0,'1. Master Aplikasi>Data Structure',1103,'icon-building','',0,'icon-building','',1,'icon-building','',1,'icon-building','','administrator','2018-07-10 08:52:11','administrator','2018-07-30 00:00:00',1,0),(5,'Role Manager','dev@wepos.id','v.1.2','Role Manager','systems','Roles',1,'1. Master Aplikasi>Role Manager',1,'icon-role-modules','icon-role-modules','','',1,0,1,0,'1. Master Aplikasi>Role Manager',1201,'icon-role-modules','',0,'icon-role-modules','',1,'icon-role-modules','',1,'icon-role-modules','','administrator','2018-07-10 08:52:15','administrator','2018-07-30 00:00:00',1,0),(6,'Data User','dev@wepos.id','v.1.0','','systems','UserData',1,'1. Master Aplikasi>Data User',1,'icon-user-data','icon-user-data','','',1,0,1,0,'1. Master Aplikasi>Data User',1203,'icon-user-data','',0,'icon-user-data','',1,'icon-user-data','',0,'icon-user-data','','administrator','2018-07-10 08:52:11','administrator','2018-07-30 00:00:00',1,0),(7,'User Profile','dev@wepos.id','v.1.0','','systems','UserProfile',1,'1. Master Aplikasi>User Profile',1,'user','user','','',1,0,1,1,'1. Master Aplikasi>User Profile',1301,'user','',1,'user','',1,'user','',1,'user','','administrator','2018-07-10 08:52:17','administrator','2018-07-30 00:00:00',1,0),(8,'Desktop Shortcuts','dev@wepos.id','v.1.0','Shortcuts Manager to Desktop','systems','DesktopShortcuts',1,'1. Master Aplikasi>Desktop Shortcuts',1,'icon-preferences','icon-preferences','','',1,0,1,1,'1. Master Aplikasi>Desktop Shortcuts',1302,'icon-preferences','',1,'icon-preferences','',1,'icon-preferences','',1,'icon-preferences','','administrator','2018-07-10 08:52:12','administrator','2018-07-30 00:00:00',1,0),(9,'QuickStart Shortcuts','dev@wepos.id','v.1.0','','systems','QuickStartShortcuts',0,'1. Master Aplikasi>QuickStart Shortcuts',1,'icon-preferences','icon-preferences','','',1,0,1,0,'1. Master Aplikasi>QuickStart Shortcuts',1303,'icon-preferences','',0,'icon-preferences','',1,'icon-preferences','',1,'icon-preferences','','administrator','2018-07-24 07:43:19','administrator','2018-07-21 09:16:19',1,0),(10,'Refresh Aplikasi','dev@wepos.id','v.1.0.0','','systems','refreshModule',0,'Refresh Aplikasi',1,'icon-refresh','icon-refresh','','',1,0,0,0,'Refresh Aplikasi',1304,'icon-refresh','',0,'icon-refresh','',1,'icon-refresh','',0,'icon-refresh','','administrator','2018-07-17 15:00:19','administrator','2018-07-17 15:00:19',1,0),(11,'Lock Screen','dev@wepos.id','v.1.0.0','User Lock Screen','systems','lockScreen',0,'LockScreen',1,'icon-grid','icon-grid','','',1,1,0,0,'LockScreen',1305,'icon-grid','',0,'icon-grid','',1,'icon-grid','',0,'icon-grid','','administrator','2018-07-17 01:40:20','administrator','2018-07-30 00:00:00',1,0),(12,'Logout','dev@wepos.id','v.1.0.0','Just Logout Module','systems','logoutModule',0,'Logout',1,'icon-grid','icon-grid','','',1,1,0,0,'Logout',1306,'icon-grid','',0,'icon-grid','',1,'icon-grid','',0,'icon-grid','','administrator','2018-07-17 01:36:16','administrator','2018-07-20 15:06:35',1,0),(13,'WePOS Update','dev@wepos.id','v.1.0.0','WePOS Update','systems','weposUpdate',0,'1. Master Aplikasi>WePOS Update',1,'icon-sync','icon-grid','','',1,0,1,0,'1. Master Aplikasi>WePOS Update',1401,'icon-sync','',0,'icon-sync','',1,'icon-sync','',1,'icon-sync','','administrator','2018-07-22 08:00:58','administrator','2018-07-22 08:00:58',1,0),(14,'Notifikasi Sistem','dev@wepos.id','v.1.0.0','Notifikasi Sistem','systems','systemNotify',0,'Notifikasi Sistem',1,'icon-info','icon-info','','',1,1,0,0,'Notifikasi Sistem',1402,'icon-info','',0,'icon-info','',0,'icon-info','',0,'icon-info','','administrator','2018-07-22 08:00:58','administrator','2018-07-22 08:00:58',1,0),(17,'Master Warehouse','dev@wepos.id','v.1.0.0','Master Warehouse','master_pos','masterStoreHouse',0,'2. Master POS>Master Warehouse',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Warehouse',2201,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 03:24:56','administrator','2018-07-21 20:05:16',1,0),(18,'Master Unit','dev@wepos.id','v.1.0.0','Master Unit','master_pos','masterUnit',0,'2. Master POS>Master Unit',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Unit',2202,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 03:25:13','administrator','2018-07-12 22:15:29',1,0),(19,'Master Supplier','dev@wepos.id','v.1.0.0','Master Supplier','master_pos','masterSupplier',0,'2. Master POS>Supplier',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Supplier',2203,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 03:25:04','administrator','2018-07-21 20:04:34',1,0),(20,'Item Category','dev@wepos.id','v.1.0.0','Item Category','master_pos','itemCategory',0,'2. Master POS>Item Category',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Item Category',2210,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-05 00:36:29','administrator','2018-07-15 20:31:54',1,0),(21,'Sub Item Category','dev@wepos.id','v.1.0.0','Sub Item Category','master_pos','itemSubCategory',0,'2. Master POS>Sub Item Category',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Sub Item Category',2221,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-12 22:16:35','administrator','2018-07-05 10:25:39',1,0),(25,'Master Item','dev@wepos.id','v.1.0.0','Data Item','master_pos','masterItemRetail',0,'2. Master POS>Master Item',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Item',2230,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-13 14:04:34','administrator','2018-07-13 14:04:34',1,0),(26,'Discount Planner','dev@wepos.id','v.1.0','Planning All discount','master_pos','discountPlannerFree',0,'2. Master POS>Discount Planner',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Discount Planner',2301,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 17:26:01','administrator','2018-07-30 00:00:00',1,0),(27,'Printer Manager','dev@wepos.id','v.1.0','Printer Manager','master_pos','masterPrinter',0,'2. Master POS>Printer Manager',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Printer Manager',2302,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 03:24:50','administrator','2018-07-21 20:06:25',1,0),(28,'Master Tipe Billing','dev@wepos.id','v.1.0.0','','master_pos','masterTipeBilling',0,'2. Master POS>Master Tipe Billing',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Tipe Billing',2309,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 17:26:54','administrator','2018-07-30 00:00:00',1,0),(30,'Master Bank','dev@wepos.id','v.1.0.0','Master Bank','master_pos','masterBank',0,'2. Master POS>Master Bank',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Bank',2304,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 03:24:53','administrator','2018-07-21 20:05:03',1,0),(33,'Warehouse Access','dev@wepos.id','v.1.0.0','Warehouse Access','master_pos','warehouseAccess',0,'2. Master POS>User Access>Warehouse Access',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>User Access>Warehouse Access',2401,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-27 19:23:32','administrator','2018-07-21 20:02:49',1,0),(34,'Printer Access','dev@wepos.id','v.1.0.0','Printer Access','master_pos','printerAccess',0,'2. Master POS>User Access>Printer Access',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>User Access>Printer Access',2402,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-03 06:43:42','administrator','2018-07-21 20:02:38',1,0),(35,'Supervisor Access','dev@wepos.id','v.1.0.0','Supervisor Access','master_pos','supervisorAccess',0,'2. Master POS>User Access>Supervisor Access',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>User Access>Supervisor Access',2403,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-11 22:53:04','administrator','2018-07-21 20:02:58',1,0),(37,'Open Cashier (Shift)','dev@wepos.id','v.1.0','','cashier','openCashierShift',0,'3. Cashier & Sales Order>Open Cashier (Shift)',7,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Sales Order>Open Cashier (Shift)',3001,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 17:28:12','administrator','2018-07-30 00:00:00',1,0),(38,'Close Cashier (Shift)','dev@wepos.id','v.1.0','','cashier','closeCashierShift',0,'3. Cashier & Sales Order>Close Cashier (Shift)',7,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Sales Order>Close Cashier (Shift)',3002,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 17:28:17','administrator','2018-07-30 00:00:00',1,0),(39,'List Open Close Cashier','dev@wepos.id','v.1.0.0','','cashier','listOpenCloseCashier',0,'3. Cashier & Sales Order>List Open Close Cashier',1,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Sales Order>List Open Close Cashier',3003,'icon-grid','',0,'icon-grid','',1,'icon-grid','',0,'icon-grid','','administrator','2018-07-20 07:59:55','administrator','2018-07-20 07:59:55',1,0),(40,'Cashier','dev@wepos.id','v.1.0','Cashier','cashier','billingCashierRetail',0,'3. Cashier & Sales Order>Cashier Retail',1,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Sales Order>Cashier Retail',3101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 03:28:03','administrator','2018-07-22 12:58:59',1,0),(46,'Cashier Receipt Setup','dev@wepos.id','v.1.0.0','Cashier Receipt Setup','cashier','cashierReceiptSetupRetail',0,'3. Cashier & Sales Order>Cashier Receipt Setup',1,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Sales Order>Cashier Receipt Setup',3301,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-11 06:13:49','administrator','2018-07-22 12:59:09',1,0),(50,'Purchase Order/Pembelian','dev@wepos.id','v.1.0.0','Purchase Order/Pembelian','purchase','purchaseOrder',0,'4. Purchase & Receive>Purchase Order/Pembelian',1,'icon-grid','icon-grid','','',1,0,1,0,'4. Purchase & Receive>Purchase Order/Pembelian',4201,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 03:27:18','administrator','2018-07-15 15:07:08',1,0),(51,'Receiving List/Penerimaan Barang','dev@wepos.id','v.1.0.0','Receiving List/Penerimaan Barang','inventory','receivingList',0,'4. Purchase & Receive>Receiving List/Penerimaan Barang',1,'icon-grid','icon-grid','','',1,0,1,0,'4. Purchase & Receive>Receiving List/Penerimaan Barang',4301,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 12:05:57','administrator','2018-07-22 13:04:22',1,0),(52,'Daftar Stok Barang','dev@wepos.id','v.1.0.0','Daftar Stok Barang','inventory','listStock',0,'5. Inventory>Daftar Stok Barang',1,'icon-grid','icon-grid','','',1,0,1,0,'5. Inventory>Daftar Stok Barang',5101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-03 06:43:42','administrator','2018-07-24 13:22:20',1,0),(57,'Stock Opname','dev@wepos.id','v.1.0.0','Module Stock Opname','inventory','stockOpname',0,'5. Inventory>Stock Opname',1,'icon-grid','icon-grid','','',1,0,1,0,'5. Inventory>Stock Opname',5401,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 12:06:05','administrator','2018-07-24 13:22:51',1,0),(76,'Closing Sales','dev@wepos.id','v.1.0.0','Closing Sales','audit_closing','closingSales',0,'8. Closing & Audit>Closing Sales',1,'icon-grid','icon-grid','','',1,0,1,0,'8. Closing & Audit>Closing Sales',8101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-03 21:43:42','administrator','2018-07-03 21:43:42',1,0),(77,'Closing Purchasing','dev@wepos.id','v.1.0.0','Closing Purchasing','audit_closing','closingPurchasing',0,'8. Closing & Audit>Closing Purchasing',1,'icon-grid','icon-grid','','',1,0,1,0,'8. Closing & Audit>Closing Purchasing',8102,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-03 21:47:56','administrator','2018-07-03 21:51:27',1,0),(79,'Auto Closing Generator','dev@wepos.id','v.1.0.0','Auto Closing Generator','monitoring','generateAutoClosing',0,'9. Sync, Backup, Generate>Auto Closing Generator',1,'icon-grid','icon-grid','','',1,0,1,0,'9. Sync, Backup, Generate>Auto Closing Generator',9102,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-03 21:43:42','administrator','2018-07-03 21:43:42',0,1),(80,'Backup Master Data','dev@wepos.id','v.1.0.0','Backup Master Data','sync_backup','syncData',0,'9. Sync, Backup, Generate>Backup Master Data',1,'icon-sync','icon-sync','','',1,0,1,0,'9. Sync, Backup, Generate>Backup Master Data',9201,'icon-sync','',0,'icon-sync','',1,'icon-sync','',1,'icon-sync','','administrator','2018-07-25 12:14:44','administrator','2018-07-26 21:05:47',1,0),(81,'Backup Data Transaksi','dev@wepos.id','v.1.0.0','Backup Data Transaksi','sync_backup','backupTrx',0,'9. Sync, Backup, Generate>Backup Data Transaksi',1,'icon-backup','icon-backup','','',1,0,1,0,'9. Sync, Backup, Generate>Backup Data Transaksi',9202,'icon-backup','',0,'icon-backup','',1,'icon-backup','',1,'icon-backup','','administrator','2018-07-25 12:17:26','administrator','2018-07-26 21:06:01',1,0),(82,'Generate Report Summary','dev@wepos.id','v.1.0.0','Generate Report Summary','generate','generateReport',0,'9. Sync, Backup, Generate>Generate Report Summary',1,'icon-generate','icon-generate','','',1,0,0,0,'9. Sync, Backup, Generate>Generate Report Summary',9301,'icon-generate','',0,'icon-generate','',1,'icon-generate','',1,'icon-generate','','administrator','2018-07-26 21:10:03','administrator','2018-07-26 21:10:03',0,1),(83,'Sync & Backup','dev@wepos.id','v.1.0.0','Sync & Backup','sync_backup','syncBackup',0,'9. Sync, Backup, Generate>Sync & Backup',1,'icon-sync','icon-sync','','',1,0,1,0,'9. Sync, Backup, Generate>Sync & Backup',9203,'icon-sync','',0,'icon-sync','',1,'icon-sync','',1,'icon-sync','','administrator','2018-07-25 12:14:44','administrator','2018-07-26 21:05:47',0,1),(84,'Sales Report','dev@wepos.id','v.1.0','Sales Report','billing','reportSales',0,'6. Reports>Sales (Billing)>Sales Report',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Billing)>Sales Report',6101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-11 01:28:24','administrator','2018-07-17 17:01:16',1,0),(87,'Sales Report (Recap)','dev@wepos.id','v.1.0.0','','billing','reportSalesRecap',0,'6. Reports>Sales (Billing)>Sales Report (Recap)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Billing)>Sales Report (Recap)',6104,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-24 16:30:29','administrator','2018-07-24 16:38:02',1,0),(88,'Sales By Discount','dev@wepos.id','v.1.0.0','Sales By Discount','billing','salesByDiscount',0,'6. Reports>Sales (Billing)>Sales By Discount',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Billing)>Sales By Discount',6105,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-15 20:43:42','administrator','2018-07-15 20:43:42',1,0),(94,'Cancel Billing Report','dev@wepos.id','v.1.0.0','','billing','reportCancelBill',0,'6. Reports>Sales (Billing)>Report Cancel Billing',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Billing)>Report Cancel Billing',6110,'icon-grid','',0,'icon-grid','',1,'icon-grid','',0,'icon-grid','','administrator','2018-07-19 09:45:34','administrator','2018-07-24 16:26:54',1,0),(96,'Sales By Item','dev@wepos.id','v.1.0.0','Sales By Item','billing','reportSalesByItem',0,'6. Reports>Sales (Item)>Sales By Item',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Item)>Sales By Item',6111,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-09 05:51:55','administrator','2018-07-17 17:47:33',1,0),(103,'Sales Profit Report','dev@wepos.id','v.1.0.0','','billing','reportSalesProfit',0,'6. Reports>Sales (Profit)>Sales Profit Report',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Profit)>Sales Profit Report',6131,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-24 16:46:57','administrator','2018-07-24 17:21:51',1,0),(106,'Sales Profit Report (Recap)','dev@wepos.id','v.1.0.0','','billing','reportSalesProfitRecap',0,'6. Reports>Sales (Profit)>Sales Profit Report (Recap)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Profit)>Sales Profit Report (Recap)',6134,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-24 16:58:17','administrator','2018-07-24 17:23:59',1,0),(107,'Sales Profit By Item','dev@wepos.id','v.1.0.0','Sales Profit By Item','billing','reportSalesProfitByItem',0,'6. Reports>Sales (Profit)>Sales Profit By Item',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Profit)>Sales Profit By Item',6135,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-24 16:53:21','administrator','2018-07-17 19:38:07',1,0),(130,'Bagi Hasil','dev@wepos.id','v.1.0.0','Bagi Hasil Detail','billing','reportSalesBagiHasil',0,'6. Reports>Sales (Bagi Hasil)>Bagi Hasil',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Bagi Hasil)>Bagi Hasil',6301,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-15 06:43:42','administrator','2018-07-15 06:43:42',1,0),(131,'Bagi Hasil (Recap)','dev@wepos.id','v.1.0.0','Bagi Hasil (Recap)','billing','reportSalesBagiHasilRecap',0,'6. Reports>Sales (Bagi Hasil)>Bagi Hasil (Recap)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Bagi Hasil)>Bagi Hasil (Recap)',6302,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-15 06:43:42','administrator','2018-07-15 06:43:42',1,0),(136,'Purchase Report','dev@wepos.id','v.1.0.0','Purchase Report','purchase','reportPurchase',0,'6. Reports>Purchase/Pembelian>Purchase Report',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Purchase/Pembelian>Purchase Report',6401,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-16 21:28:58','administrator','2018-07-09 19:08:45',1,0),(138,'Purchase Report (Recap)','dev@wepos.id','v.1.0.0','Purchase Report (Recap)','purchase','reportPurchaseRecap',0,'6. Reports>Purchase/Pembelian>Purchase Report (Recap)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Purchase/Pembelian>Purchase Report (Recap)',6403,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-17 13:23:40','administrator','2018-07-09 19:08:25',1,0),(139,'Last Purchase Price','dev@wepos.id','v.1.0.0','Last Purchase Price','purchase','reportLastPurchasePrice',0,'6. Reports>Purchase/Pembelian>Last Purchase Price',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Purchase/Pembelian>Last Purchase Price',6404,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-17 13:23:40','administrator','2018-07-09 19:08:25',1,0),(140,'Receiving Report','dev@wepos.id','v.1.0.0','Receiving Report','inventory','reportReceiving',0,'6. Reports>Receiving (In)>Receiving Report',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Receiving (In)>Receiving Report',6501,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-17 13:31:50','administrator','2018-07-09 19:00:32',1,0),(143,'Receiving Report (Recap)','dev@wepos.id','v.1.0.0','Receiving Report (Recap)','inventory','reportReceivingRecap',0,'6. Reports>Receiving (In)>Receiving Report (Recap)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Receiving (In)>Receiving Report (Recap)',6504,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-09 15:57:19','administrator','2018-07-09 19:01:16',1,0),(156,'Monitoring Stock (Actual)','dev@wepos.id','v.1.0.0','Monitoring Stock (Actual)','inventory','reportMonitoringStock',0,'6. Reports>Warehouse>Monitoring Stock (Actual)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Warehouse>Monitoring Stock (Actual)',6642,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-11 23:44:12','administrator','2018-07-18 00:45:36',1,0),(157,'Kartu Stok','dev@wepos.id','v.1.0.0','Kartu Stok','inventory','kartuStok',0,'6. Reports>Warehouse>Kartu Stock',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Warehouse>Kartu Stock',6643,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-03 06:43:42','administrator','2018-07-18 00:46:03',1,0),(176,'Product Category','dev@wepos.id','v.1.0','','master_pos','productCategory',0,'2. Master POS>Product Category',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Product Category',2101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 17:26:07','administrator','2018-07-30 00:00:00',1,0),(177,'Master Product & Package','dev@wepos.id','v.1.0','Master Product & Package','master_pos','masterProduct',0,'2. Master POS>Master Product',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Product',2102,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 17:24:38','administrator','2018-07-30 00:00:00',1,0),(184,'Pembayaran PPOB','dev@wepos.id','v.1.0.0','Pembayaran PPOB','cashier','ppob',0,'3. Cashier & Sales Order>Pembayaran PPOB',1,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Sales Order>Pembayaran PPOB',3401,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2019-04-09 08:25:57','administrator','2019-04-09 17:49:57',1,0);");
					
					$this->db->delete($this->prefix.'options',"option_var LIKE 'mlog_%'");
				 
					//copy module
					if (empty($get_opt['is_cloud'])) {
						
						$minjs_path = BASE_PATH.'/apps.min/modules'; 
						delete_files($minjs_path, TRUE);
						$zip = new ZipArchive;
						
						$apps_default = BASE_PATH.'/apps.min/core/modules.default';
						if($zip->open($apps_default) === TRUE) 
						{
							if (!is_dir($minjs_path)) {
								@mkdir($minjs_path, 0777, TRUE);
							}

							$zip->extractTo($minjs_path);
							$zip->close();
							
						}
				
						$appmod_path = APPPATH.'/modules'; 
						delete_files($appmod_path, TRUE);
						
						$zip = new ZipArchive;
						$file_default = APPPATH.'/core/modules.default';
						if($zip->open($file_default) === TRUE) 
						{
							if (!is_dir($appmod_path)) {
								@mkdir($appmod_path, 0777, TRUE);
							}

							$zip->extractTo($appmod_path);
							$zip->close();
							
						}
						
					}
					
				}else{
					doresetapp();
				}
			}
		}	
		
	}
	
	function checkClient($check = array())
	{
		extract($check);
		
		$this->load->helper('directory');
		$this->load->helper('file');
		
		if(!empty($check)){
			
			$reset = false;
			if(empty($check['merchant_xid']) AND $check['merchant_verified'] == 'unverified'){
				$reset = true;
			}
			
			if(empty($check['merchant_cor_token']) OR empty($check['merchant_acc_token']) OR empty($check['merchant_mkt_token'])){
				$reset = true;
			}
			
			if(empty($check['produk_nama']) OR empty($check['produk_expired'])){
				$reset = true;
			}else{
				if($check['produk_nama'] == 'Gratis / Free'){
					$reset = true;
				}
				
				if($check['produk_expired'] == 'unlimited'){
					$reset = true;
				}
				
			}
			
			if(empty($check['produk_nama']) OR empty($check['produk_expired'])){
				$reset = true;
			}
			
			$merchant_key = '';
			if(empty($check['$merchant_key'])){
				$reset = false;
			}else{
				$merchant_key = $check['$merchant_key'];
			}
			
			$merchant_last_check = 0;
			if(!empty($check['merchant_last_check'])){
				$merchant_last_check = $check['merchant_last_check'];
			}
			
			$today_check = strtotime(date("d-m-Y H:i:s"));
			$month_check = strtotime(date("d-m-Y H:i:s")) + (ONE_DAY_UNIX*15);
			
			$allow_reset = false;
				
			if(empty($merchant_last_check)){
				
				$allow_reset = true;
				$opt_var = array(
					'merchant_last_check' => $today_check
				);
				
				update_option($opt_var);
				
			}else{
				
				$merchant_last_check_7 = $merchant_last_check + (ONE_DAY_UNIX*7);
				if($merchant_last_check_7 < $today_check){
					$allow_reset = true;
				}else{
					if($merchant_last_check > $month_check){
						$allow_reset = true;
					}
				}
				
				if($allow_reset == true){
					
					$opt_var = array(
						'merchant_last_check' => $today_check
					);
					update_option($opt_var);
					
				}
				
			}
			
			$opt_var = array(
				'mlog_'.$merchant_key,
				'is_cloud'
			);
			$get_opt = get_option_value($opt_var);
			
			if(!empty($check['merchant_mkt_token'])){
				if($check['merchant_mkt_token'] < $today_check){
					
					$mlog = '';
					if(empty($get_opt['mlog_'.$merchant_key])){
						$mlog = $get_opt['mlog_'.$merchant_key];
					}
					
					$resetapp = array(
						'merchant_cor_token'=> '',
						'merchant_acc_token'=> '',
						'merchant_mkt_token'=> '',
						'produk_key' 		=> 'GFR-'.strtotime(date("d-m-Y")),
						'produk_nama'		=> 'Gratis / Free',
						'produk_expired'	=> 'unlimited',
						'mlog_'.$merchant_key => ''
					);
					update_option($resetapp);
					
					if(!empty($mlog) AND empty($get_opt['is_cloud'])){
						$minjs_path = BASE_PATH.'/apps.min/modules'; 
						$mlog_json = json_decode($mlog);
						if(!empty($mlog_json)){
							foreach($mlog_json as $v){
								$file_minjs = $minjs_path.'/'.$v;
								@unlink($file_minjs);
							}
						}
					}
					
					$reset = true;
					$allow_reset = true;
				}
			}
			
			if($reset == true AND $allow_reset == true){
				if(!function_exists('doresetapp')){
					
					$resetapp = array(
						'ipserver_management_systems'=> 'https://wepos.id',
						'management_systems'=> 0,
						'use_wms'			=> 0,
						'opsi_no_print_when_payment'=> 0,
						'use_login_pin'	=> 0,
						'supervisor_pin_mode'	=> 0,
						'view_multiple_store'	=> 0,
						'autobackup_on_settlement'	=> 0,
						'must_choose_customer'	=> 0,
						'no_hold_billing'	=> 0,
						'hide_tanya_wepos'	=> 0,
						'using_item_average_as_hpp'	=> 0,
						'show_multiple_print_billing'	=> 0,
						'show_multiple_print_qc'	=> 0,
					);
					update_option($resetapp);
					
					$this->db->query('TRUNCATE TABLE '.$this->prefix.'modules');
					$this->db->query("INSERT INTO ".$this->prefix."modules (`id`,`module_name`,`module_author`,`module_version`,`module_description`,`module_folder`,`module_controller`,`module_is_menu`,`module_breadcrumb`,`module_order`,`module_icon`,`module_shortcut_icon`,`module_glyph_icon`,`module_glyph_font`,`module_free`,`running_background`,`show_on_start_menu`,`show_on_right_start_menu`,`start_menu_path`,`start_menu_order`,`start_menu_icon`,`start_menu_glyph`,`show_on_context_menu`,`context_menu_icon`,`context_menu_glyph`,`show_on_shorcut_desktop`,`desktop_shortcut_icon`,`desktop_shortcut_glyph`,`show_on_preference`,`preference_icon`,`preference_glyph`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'Setup Aplikasi','dev@wepos.id','v.1.0','','systems','setupAplikasiFree',1,'1. Master Aplikasi>Setup Aplikasi',1,'icon-cog','icon-cog','','',1,0,1,0,'1. Master Aplikasi>Setup Aplikasi',1000,'icon-cog','',0,'icon-cog','',1,'icon-cog','',0,'icon-cog','','administrator','2018-07-10 08:52:11','administrator','2018-07-30 00:00:00',1,0),(2,'Client Info','dev@wepos.id','v.1.0.0','Client Info','systems','clientInfo',0,'1. Master Aplikasi>Client Info',1,'icon-home','icon-home','','',1,0,1,0,'1. Master Aplikasi>Client Info',1101,'icon-home','',0,'icon-home','',1,'icon-home','',1,'icon-home','','administrator','2018-07-03 07:47:08','administrator','2018-07-03 07:47:08',1,0),(3,'Client Unit','dev@wepos.id','v.1.0','','systems','DataClientUnit',1,'1. Master Aplikasi>Client Unit',1,'icon-building','icon-building','','',1,0,1,0,'1. Master Aplikasi>Client Unit',1102,'icon-building','',0,'icon-building','',1,'icon-building','',1,'icon-building','','administrator','2018-07-10 08:52:10','administrator','2018-07-30 00:00:00',1,0),(4,'Data Structure','dev@wepos.id','v.1.0','','systems','DataStructure',1,'1. Master Aplikasi>Data Structure',1,'icon-building','icon-building','','',1,0,1,0,'1. Master Aplikasi>Data Structure',1103,'icon-building','',0,'icon-building','',1,'icon-building','',1,'icon-building','','administrator','2018-07-10 08:52:11','administrator','2018-07-30 00:00:00',1,0),(5,'Role Manager','dev@wepos.id','v.1.2','Role Manager','systems','Roles',1,'1. Master Aplikasi>Role Manager',1,'icon-role-modules','icon-role-modules','','',1,0,1,0,'1. Master Aplikasi>Role Manager',1201,'icon-role-modules','',0,'icon-role-modules','',1,'icon-role-modules','',1,'icon-role-modules','','administrator','2018-07-10 08:52:15','administrator','2018-07-30 00:00:00',1,0),(6,'Data User','dev@wepos.id','v.1.0','','systems','UserData',1,'1. Master Aplikasi>Data User',1,'icon-user-data','icon-user-data','','',1,0,1,0,'1. Master Aplikasi>Data User',1203,'icon-user-data','',0,'icon-user-data','',1,'icon-user-data','',0,'icon-user-data','','administrator','2018-07-10 08:52:11','administrator','2018-07-30 00:00:00',1,0),(7,'User Profile','dev@wepos.id','v.1.0','','systems','UserProfile',1,'1. Master Aplikasi>User Profile',1,'user','user','','',1,0,1,1,'1. Master Aplikasi>User Profile',1301,'user','',1,'user','',1,'user','',1,'user','','administrator','2018-07-10 08:52:17','administrator','2018-07-30 00:00:00',1,0),(8,'Desktop Shortcuts','dev@wepos.id','v.1.0','Shortcuts Manager to Desktop','systems','DesktopShortcuts',1,'1. Master Aplikasi>Desktop Shortcuts',1,'icon-preferences','icon-preferences','','',1,0,1,1,'1. Master Aplikasi>Desktop Shortcuts',1302,'icon-preferences','',1,'icon-preferences','',1,'icon-preferences','',1,'icon-preferences','','administrator','2018-07-10 08:52:12','administrator','2018-07-30 00:00:00',1,0),(9,'QuickStart Shortcuts','dev@wepos.id','v.1.0','','systems','QuickStartShortcuts',0,'1. Master Aplikasi>QuickStart Shortcuts',1,'icon-preferences','icon-preferences','','',1,0,1,0,'1. Master Aplikasi>QuickStart Shortcuts',1303,'icon-preferences','',0,'icon-preferences','',1,'icon-preferences','',1,'icon-preferences','','administrator','2018-07-24 07:43:19','administrator','2018-07-21 09:16:19',1,0),(10,'Refresh Aplikasi','dev@wepos.id','v.1.0.0','','systems','refreshModule',0,'Refresh Aplikasi',1,'icon-refresh','icon-refresh','','',1,0,0,0,'Refresh Aplikasi',1304,'icon-refresh','',0,'icon-refresh','',1,'icon-refresh','',0,'icon-refresh','','administrator','2018-07-17 15:00:19','administrator','2018-07-17 15:00:19',1,0),(11,'Lock Screen','dev@wepos.id','v.1.0.0','User Lock Screen','systems','lockScreen',0,'LockScreen',1,'icon-grid','icon-grid','','',1,1,0,0,'LockScreen',1305,'icon-grid','',0,'icon-grid','',1,'icon-grid','',0,'icon-grid','','administrator','2018-07-17 01:40:20','administrator','2018-07-30 00:00:00',1,0),(12,'Logout','dev@wepos.id','v.1.0.0','Just Logout Module','systems','logoutModule',0,'Logout',1,'icon-grid','icon-grid','','',1,1,0,0,'Logout',1306,'icon-grid','',0,'icon-grid','',1,'icon-grid','',0,'icon-grid','','administrator','2018-07-17 01:36:16','administrator','2018-07-20 15:06:35',1,0),(13,'WePOS Update','dev@wepos.id','v.1.0.0','WePOS Update','systems','weposUpdate',0,'1. Master Aplikasi>WePOS Update',1,'icon-sync','icon-grid','','',1,0,1,0,'1. Master Aplikasi>WePOS Update',1401,'icon-sync','',0,'icon-sync','',1,'icon-sync','',1,'icon-sync','','administrator','2018-07-22 08:00:58','administrator','2018-07-22 08:00:58',1,0),(14,'Notifikasi Sistem','dev@wepos.id','v.1.0.0','Notifikasi Sistem','systems','systemNotify',0,'Notifikasi Sistem',1,'icon-info','icon-info','','',1,1,0,0,'Notifikasi Sistem',1402,'icon-info','',0,'icon-info','',0,'icon-info','',0,'icon-info','','administrator','2018-07-22 08:00:58','administrator','2018-07-22 08:00:58',1,0),(17,'Master Warehouse','dev@wepos.id','v.1.0.0','Master Warehouse','master_pos','masterStoreHouse',0,'2. Master POS>Master Warehouse',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Warehouse',2201,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 03:24:56','administrator','2018-07-21 20:05:16',1,0),(18,'Master Unit','dev@wepos.id','v.1.0.0','Master Unit','master_pos','masterUnit',0,'2. Master POS>Master Unit',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Unit',2202,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 03:25:13','administrator','2018-07-12 22:15:29',1,0),(19,'Master Supplier','dev@wepos.id','v.1.0.0','Master Supplier','master_pos','masterSupplier',0,'2. Master POS>Supplier',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Supplier',2203,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 03:25:04','administrator','2018-07-21 20:04:34',1,0),(20,'Item Category','dev@wepos.id','v.1.0.0','Item Category','master_pos','itemCategory',0,'2. Master POS>Item Category',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Item Category',2210,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-05 00:36:29','administrator','2018-07-15 20:31:54',1,0),(21,'Sub Item Category','dev@wepos.id','v.1.0.0','Sub Item Category','master_pos','itemSubCategory1',0,'2. Master POS>Sub Item Category',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Sub Item Category',2221,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-12 22:16:35','administrator','2018-07-05 10:25:39',1,0),(25,'Master Item','dev@wepos.id','v.1.0.0','Data Item','master_pos','masterItemRetail',0,'2. Master POS>Master Item',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Item',2230,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-13 14:04:34','administrator','2018-07-13 14:04:34',1,0),(26,'Discount Planner','dev@wepos.id','v.1.0','Planning All discount','master_pos','discountPlannerFree',0,'2. Master POS>Discount Planner',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Discount Planner',2301,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 17:26:01','administrator','2018-07-30 00:00:00',1,0),(27,'Printer Manager','dev@wepos.id','v.1.0','Printer Manager','master_pos','masterPrinter',0,'2. Master POS>Printer Manager',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Printer Manager',2302,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 03:24:50','administrator','2018-07-21 20:06:25',1,0),(28,'Master Tipe Billing','dev@wepos.id','v.1.0.0','','master_pos','masterTipeBilling',0,'2. Master POS>Master Tipe Billing',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Tipe Billing',2309,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 17:26:54','administrator','2018-07-30 00:00:00',1,0),(30,'Master Bank','dev@wepos.id','v.1.0.0','Master Bank','master_pos','masterBank',0,'2. Master POS>Master Bank',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Bank',2304,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 03:24:53','administrator','2018-07-21 20:05:03',1,0),(33,'Warehouse Access','dev@wepos.id','v.1.0.0','Warehouse Access','master_pos','warehouseAccess',0,'2. Master POS>User Access>Warehouse Access',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>User Access>Warehouse Access',2401,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-27 19:23:32','administrator','2018-07-21 20:02:49',1,0),(34,'Printer Access','dev@wepos.id','v.1.0.0','Printer Access','master_pos','printerAccess',0,'2. Master POS>User Access>Printer Access',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>User Access>Printer Access',2402,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-03 06:43:42','administrator','2018-07-21 20:02:38',1,0),(35,'Supervisor Access','dev@wepos.id','v.1.0.0','Supervisor Access','master_pos','supervisorAccess',0,'2. Master POS>User Access>Supervisor Access',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>User Access>Supervisor Access',2403,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-11 22:53:04','administrator','2018-07-21 20:02:58',1,0),(37,'Open Cashier (Shift)','dev@wepos.id','v.1.0','','cashier','openCashierShift',0,'3. Cashier & Sales Order>Open Cashier (Shift)',7,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Sales Order>Open Cashier (Shift)',3001,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 17:28:12','administrator','2018-07-30 00:00:00',1,0),(38,'Close Cashier (Shift)','dev@wepos.id','v.1.0','','cashier','closeCashierShift',0,'3. Cashier & Sales Order>Close Cashier (Shift)',7,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Sales Order>Close Cashier (Shift)',3002,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 17:28:17','administrator','2018-07-30 00:00:00',1,0),(39,'List Open Close Cashier','dev@wepos.id','v.1.0.0','','cashier','listOpenCloseCashier',0,'3. Cashier & Sales Order>List Open Close Cashier',1,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Sales Order>List Open Close Cashier',3003,'icon-grid','',0,'icon-grid','',1,'icon-grid','',0,'icon-grid','','administrator','2018-07-20 07:59:55','administrator','2018-07-20 07:59:55',1,0),(40,'Cashier','dev@wepos.id','v.1.0','Cashier','cashier','billingCashierRetail',0,'3. Cashier & Sales Order>Cashier Retail',1,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Sales Order>Cashier Retail',3101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 03:28:03','administrator','2018-07-22 12:58:59',1,0),(46,'Cashier Receipt Setup','dev@wepos.id','v.1.0.0','Cashier Receipt Setup','cashier','cashierReceiptSetupRetail',0,'3. Cashier & Sales Order>Cashier Receipt Setup',1,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Sales Order>Cashier Receipt Setup',3301,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-11 06:13:49','administrator','2018-07-22 12:59:09',1,0),(50,'Purchase Order/Pembelian','dev@wepos.id','v.1.0.0','Purchase Order/Pembelian','purchase','purchaseOrder',0,'4. Purchase & Receive>Purchase Order/Pembelian',1,'icon-grid','icon-grid','','',1,0,1,0,'4. Purchase & Receive>Purchase Order/Pembelian',4201,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 03:27:18','administrator','2018-07-15 15:07:08',1,0),(51,'Receiving List/Penerimaan Barang','dev@wepos.id','v.1.0.0','Receiving List/Penerimaan Barang','inventory','receivingList',0,'4. Purchase & Receive>Receiving List/Penerimaan Barang',1,'icon-grid','icon-grid','','',1,0,1,0,'4. Purchase & Receive>Receiving List/Penerimaan Barang',4301,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 12:05:57','administrator','2018-07-22 13:04:22',1,0),(52,'Daftar Stok Barang','dev@wepos.id','v.1.0.0','Daftar Stok Barang','inventory','listStock',0,'5. Inventory>Daftar Stok Barang',1,'icon-grid','icon-grid','','',1,0,1,0,'5. Inventory>Daftar Stok Barang',5101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-03 06:43:42','administrator','2018-07-24 13:22:20',1,0),(57,'Stock Opname','dev@wepos.id','v.1.0.0','Module Stock Opname','inventory','stockOpname',0,'5. Inventory>Stock Opname',1,'icon-grid','icon-grid','','',1,0,1,0,'5. Inventory>Stock Opname',5401,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 12:06:05','administrator','2018-07-24 13:22:51',1,0),(76,'Closing Sales','dev@wepos.id','v.1.0.0','Closing Sales','audit_closing','closingSales',0,'8. Closing & Audit>Closing Sales',1,'icon-grid','icon-grid','','',1,0,1,0,'8. Closing & Audit>Closing Sales',8101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-03 21:43:42','administrator','2018-07-03 21:43:42',1,0),(77,'Closing Purchasing','dev@wepos.id','v.1.0.0','Closing Purchasing','audit_closing','closingPurchasing',0,'8. Closing & Audit>Closing Purchasing',1,'icon-grid','icon-grid','','',1,0,1,0,'8. Closing & Audit>Closing Purchasing',8102,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-03 21:47:56','administrator','2018-07-03 21:51:27',1,0),(79,'Auto Closing Generator','dev@wepos.id','v.1.0.0','Auto Closing Generator','monitoring','generateAutoClosing',0,'9. Sync, Backup, Generate>Auto Closing Generator',1,'icon-grid','icon-grid','','',1,0,1,0,'9. Sync, Backup, Generate>Auto Closing Generator',9102,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-03 21:43:42','administrator','2018-07-03 21:43:42',1,0),(80,'Backup Master Data','dev@wepos.id','v.1.0.0','Backup Master Data','sync_backup','syncData',0,'9. Sync, Backup, Generate>Backup Master Data',1,'icon-sync','icon-sync','','',1,0,1,0,'9. Sync, Backup, Generate>Backup Master Data',9201,'icon-sync','',0,'icon-sync','',1,'icon-sync','',1,'icon-sync','','administrator','2018-07-25 12:14:44','administrator','2018-07-26 21:05:47',1,0),(81,'Backup Data Transaksi','dev@wepos.id','v.1.0.0','Backup Data Transaksi','sync_backup','backupTrx',0,'9. Sync, Backup, Generate>Backup Data Transaksi',1,'icon-backup','icon-backup','','',1,0,1,0,'9. Sync, Backup, Generate>Backup Data Transaksi',9202,'icon-backup','',0,'icon-backup','',1,'icon-backup','',1,'icon-backup','','administrator','2018-07-25 12:17:26','administrator','2018-07-26 21:06:01',1,0),(82,'Generate Report Summary','dev@wepos.id','v.1.0.0','Generate Report Summary','generate','generateReport',0,'9. Sync, Backup, Generate>Generate Report Summary',1,'icon-generate','icon-generate','','',1,0,0,0,'9. Sync, Backup, Generate>Generate Report Summary',9301,'icon-generate','',0,'icon-generate','',1,'icon-generate','',1,'icon-generate','','administrator','2018-07-26 21:10:03','administrator','2018-07-26 21:10:03',0,1),(83,'Sync & Backup','dev@wepos.id','v.1.0.0','Sync & Backup','sync_backup','syncBackup',0,'9. Sync, Backup, Generate>Sync & Backup',1,'icon-sync','icon-sync','','',1,0,1,0,'9. Sync, Backup, Generate>Sync & Backup',9203,'icon-sync','',0,'icon-sync','',1,'icon-sync','',1,'icon-sync','','administrator','2018-07-25 12:14:44','administrator','2018-07-26 21:05:47',1,0),(84,'Sales Report','dev@wepos.id','v.1.0','Sales Report','billing','reportSales',0,'6. Reports>Sales (Billing)>Sales Report',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Billing)>Sales Report',6101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-11 01:28:24','administrator','2018-07-17 17:01:16',1,0),(87,'Sales Report (Recap)','dev@wepos.id','v.1.0.0','','billing','reportSalesRecap',0,'6. Reports>Sales (Billing)>Sales Report (Recap)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Billing)>Sales Report (Recap)',6104,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-24 16:30:29','administrator','2018-07-24 16:38:02',1,0),(88,'Sales By Discount','dev@wepos.id','v.1.0.0','Sales By Discount','billing','salesByDiscount',0,'6. Reports>Sales (Billing)>Sales By Discount',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Billing)>Sales By Discount',6105,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-15 20:43:42','administrator','2018-07-15 20:43:42',1,0),(94,'Cancel Billing Report','dev@wepos.id','v.1.0.0','','billing','reportCancelBill',0,'6. Reports>Sales (Billing)>Report Cancel Billing',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Billing)>Report Cancel Billing',6110,'icon-grid','',0,'icon-grid','',1,'icon-grid','',0,'icon-grid','','administrator','2018-07-19 09:45:34','administrator','2018-07-24 16:26:54',1,0),(96,'Sales By Item','dev@wepos.id','v.1.0.0','Sales By Item','billing','reportSalesByItem',0,'6. Reports>Sales (Item)>Sales By Item',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Item)>Sales By Item',6111,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-09 05:51:55','administrator','2018-07-17 17:47:33',1,0),(103,'Sales Profit Report','dev@wepos.id','v.1.0.0','','billing','reportSalesProfit',0,'6. Reports>Sales (Profit)>Sales Profit Report',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Profit)>Sales Profit Report',6131,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-24 16:46:57','administrator','2018-07-24 17:21:51',1,0),(106,'Sales Profit Report (Recap)','dev@wepos.id','v.1.0.0','','billing','reportSalesProfitRecap',0,'6. Reports>Sales (Profit)>Sales Profit Report (Recap)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Profit)>Sales Profit Report (Recap)',6134,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-24 16:58:17','administrator','2018-07-24 17:23:59',1,0),(107,'Sales Profit By Item','dev@wepos.id','v.1.0.0','Sales Profit By Item','billing','reportSalesProfitByItem',0,'6. Reports>Sales (Profit)>Sales Profit By Item',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Profit)>Sales Profit By Item',6135,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-24 16:53:21','administrator','2018-07-17 19:38:07',1,0),(130,'Bagi Hasil','dev@wepos.id','v.1.0.0','Bagi Hasil Detail','billing','reportSalesBagiHasil',0,'6. Reports>Sales (Bagi Hasil)>Bagi Hasil',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Bagi Hasil)>Bagi Hasil',6301,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-15 06:43:42','administrator','2018-07-15 06:43:42',1,0),(131,'Bagi Hasil (Recap)','dev@wepos.id','v.1.0.0','Bagi Hasil (Recap)','billing','reportSalesBagiHasilRecap',0,'6. Reports>Sales (Bagi Hasil)>Bagi Hasil (Recap)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Bagi Hasil)>Bagi Hasil (Recap)',6302,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-15 06:43:42','administrator','2018-07-15 06:43:42',1,0),(136,'Purchase Report','dev@wepos.id','v.1.0.0','Purchase Report','purchase','reportPurchase',0,'6. Reports>Purchase/Pembelian>Purchase Report',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Purchase/Pembelian>Purchase Report',6401,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-16 21:28:58','administrator','2018-07-09 19:08:45',1,0),(138,'Purchase Report (Recap)','dev@wepos.id','v.1.0.0','Purchase Report (Recap)','purchase','reportPurchaseRecap',0,'6. Reports>Purchase/Pembelian>Purchase Report (Recap)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Purchase/Pembelian>Purchase Report (Recap)',6403,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-17 13:23:40','administrator','2018-07-09 19:08:25',1,0),(139,'Last Purchase Price','dev@wepos.id','v.1.0.0','Last Purchase Price','purchase','reportLastPurchasePrice',0,'6. Reports>Purchase/Pembelian>Last Purchase Price',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Purchase/Pembelian>Last Purchase Price',6404,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-17 13:23:40','administrator','2018-07-09 19:08:25',1,0),(140,'Receiving Report','dev@wepos.id','v.1.0.0','Receiving Report','inventory','reportReceiving',0,'6. Reports>Receiving (In)>Receiving Report',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Receiving (In)>Receiving Report',6501,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-17 13:31:50','administrator','2018-07-09 19:00:32',1,0),(143,'Receiving Report (Recap)','dev@wepos.id','v.1.0.0','Receiving Report (Recap)','inventory','reportReceivingRecap',0,'6. Reports>Receiving (In)>Receiving Report (Recap)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Receiving (In)>Receiving Report (Recap)',6504,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-09 15:57:19','administrator','2018-07-09 19:01:16',1,0),(156,'Monitoring Stock (Actual)','dev@wepos.id','v.1.0.0','Monitoring Stock (Actual)','inventory','reportMonitoringStock',0,'6. Reports>Warehouse>Monitoring Stock (Actual)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Warehouse>Monitoring Stock (Actual)',6642,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-11 23:44:12','administrator','2018-07-18 00:45:36',1,0),(157,'Kartu Stok','dev@wepos.id','v.1.0.0','Kartu Stok','inventory','kartuStok',0,'6. Reports>Warehouse>Kartu Stock',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Warehouse>Kartu Stock',6643,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-03 06:43:42','administrator','2018-07-18 00:46:03',1,0),(176,'Product Category','dev@wepos.id','v.1.0','','master_pos','productCategory',0,'2. Master POS>Product Category',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Product Category',2101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 17:26:07','administrator','2018-07-30 00:00:00',1,0),(177,'Master Product & Package','dev@wepos.id','v.1.0','Master Product & Package','master_pos','masterProduct',0,'2. Master POS>Master Product',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Product',2102,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 17:24:38','administrator','2018-07-30 00:00:00',1,0),(184,'Pembayaran PPOB','dev@wepos.id','v.1.0.0','Pembayaran PPOB','cashier','ppob',0,'3. Cashier & Sales Order>Pembayaran PPOB',1,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Sales Order>Pembayaran PPOB',3401,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2019-04-09 08:25:57','administrator','2019-04-09 17:49:57',1,0);");
					
					$this->db->delete($this->prefix.'options',"option_var LIKE 'mlog_%'");
				
					//copy module
					if (empty($get_opt['is_cloud'])) {
						
						$minjs_path = BASE_PATH.'/apps.min/modules'; 
						delete_files($minjs_path, TRUE);
						$zip = new ZipArchive;
						
						$apps_default = BASE_PATH.'/apps.min/core/modules.default';
						if($zip->open($apps_default) === TRUE) 
						{
							if (!is_dir($minjs_path)) {
								@mkdir($minjs_path, 0777, TRUE);
							}

							$zip->extractTo($minjs_path);
							$zip->close();
							
						}
				
						$appmod_path = APPPATH.'/modules'; 
						delete_files($appmod_path, TRUE);
						
						$zip = new ZipArchive;
						$file_default = APPPATH.'/core/modules.default';
						if($zip->open($file_default) === TRUE) 
						{
							if (!is_dir($appmod_path)) {
								@mkdir($appmod_path, 0777, TRUE);
							}

							$zip->extractTo($appmod_path);
							$zip->close();
							
						} 
						
					}
					
				}else{
					doresetapp();
				}
			}
			
		}
		
	}

} 