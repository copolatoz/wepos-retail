<?php  if (!defined('BASEPATH')) exit('No direct script access allowed'); 
/**
 * Framework System Helper
 *
 * PHP version 5
 *
 * @category  CodeIgniter
 * @package   Framework System
 * @author    angga nugraha (angga.nugraha@gmail.com)
 * @version   0.1
 * Copyright (c) 2018 Angga Nugraha  (https://wepos.id)
*/

/*thumbnail*/
function do_thumb($data, $folder, $thumb_folder, $prefix_thumb = "", $limit_thumb=128, $limit_height=128, $maintain_ratio = TRUE, $master_dim = 'auto')
{
	$objCI =& get_instance();
	
	/* PATH */
	$source = $folder.$data["file_name"];
	$destination_thumb = $thumb_folder;
								
	// Permission Configuration
	chmod($source, 0777) ;
						 
	/* Resizing Processing */
	// Configuration Of Image Manipulation :: Static
	$objCI->load->library('image_lib') ;
	$img['image_library'] = 'GD2';
	$img['create_thumb']  = TRUE;
	$img['maintain_ratio']= $maintain_ratio;
	//$img['master_dim']= $master_dim;
				 
	/// Limit Width Resize
	//$limit_thumb    = 64 ;
						 
	// Size Image Limit was using (LIMIT TOP)
	$limit_use  = $data['image_width'] > $data['image_height'] ? $data['image_width'] : $data['image_height'] ;
						 
	// Percentase Resize
	if($data['image_width'] > $data['image_height']){
		if ($limit_use > $limit_thumb) {
			$percent  = $limit_thumb/$limit_use ;
		}else{
			$percent  = 1;
		}
	}else{	
		if ($limit_use > $limit_height) {
			$percent  = $limit_height/$limit_use ;
		}else{
			$percent  = 1;
		}
	}
	
						 
	//// Making THUMBNAIL ///////
	$img['width']  = $limit_use > $limit_thumb ?  $data['image_width'] * $percent : $data['image_width'] ;
	$img['height'] = $limit_use > $limit_height ?  $data['image_height'] * $percent : $data['image_height'] ;
	
	if($maintain_ratio == FALSE){
		
		if(!empty($limit_thumb)){
			$img['width'] = $limit_thumb;
		}
		
		if(!empty($limit_height)){
			$img['height'] = $limit_height;
		}
		
	}
						 
	// Configuration Of Image Manipulation :: Dynamic
	$img['thumb_marker'] = $prefix_thumb;
	$img['quality']      = '100%' ;
	$img['source_image'] = $source ;
	$img['new_image']    = $destination_thumb ;
	$img['width'] = 230;
	
	// Do Resizing
	$objCI->image_lib->initialize($img);
	$objCI->image_lib->resize();
	$objCI->image_lib->clear() ;	
									
	$img_thumb = $data["raw_name"].$prefix_thumb.$data["file_ext"];
	return $img_thumb;
}

/*OPTIONS*/
if(!function_exists('get_option_value')){
	function get_option_value($data = array(), $result = 'array'){
		$prefix = config_item('db_prefix');
		if(empty($scope)){
			$scope =& get_instance();
		}
		
		if(empty($data)){
			return false;
		}
		
		$ret_result = 'array';
		if(!empty($result)){
			if($result == 'object'){
				$ret_result = 'object';
			}
		}
		
		if(is_array($data)){
			$all_var = implode("','", $data);
		}else{
			$all_var = $data;
		}
		
		$scope->db->select("option_var, option_value");
		$scope->db->from($prefix."options");
		$scope->db->where("option_var IN ('".$all_var."')");
		$get_lap_param = $scope->db->get();
		
		$all_val = array();
		if($get_lap_param->num_rows() > 0){
			foreach($get_lap_param->result() as $dt){
				$all_val[$dt->option_var] = $dt->option_value;
			}
						
			if($ret_result == 'object'){
				$all_return = (object) $all_val; 
			}else{
				$all_return = $all_val; 
			}
			
			return $all_return;
		}else{
			return false;
		}
		
	}
	
}

if(!function_exists('get_option')){

	function get_option($data, $echoed = true){
		
		//DEFAULT
		/*$data = array(
			'var' 		=> '',
			'result'	=> 'array',
			'scope'		=> '',
			'echoed'	=> true
		);*/
		
		/*single, echoed*/
		$prefix = config_item('db_prefix');
		if(empty($scope)){
			$scope =& get_instance();
		}	
				
		$tipe = 'single';
		if(is_array($data)){
			extract($data);
			$tipe = 'data';
		}else{
			//string
			$var = $data;
		}
		
		//single condition
		if(empty($data['echoed']) AND $tipe == 'data'){
			$echoed = true;
		}
		
		$ret_result = 'array';
		if(!empty($result)){
			if($result == 'object'){
				$ret_result = 'object';
			}
		}
		
		$data_res = array();
				
		$scope->db->select('a.*');
		$scope->db->from($prefix.'options as a');		
		$scope->db->where('a.is_deleted', 0);
		$scope->db->where('a.is_active', 1);
		
		if(is_array($var)){
			$var_all = implode("','", $var);
			$scope->db->where("a.option_var IN ('".$var_all."')");
		}else{
			$scope->db->where('a.option_var', $var);	
		}
		
		$query = $scope->db->get();
		if($query->num_rows() > 0){
			
			$newData = array();
			foreach($query->result_array() as $dt){
				$newData = $dt;
			}
			
			if($tipe == 'data'){
				if($ret_result == 'object'){
					$data_res = (object) $newData; 
				}else{
					$data_res = $newData; 
				}
			}else{
				$data_res = (object) $newData; 
			}
			
		}
		
		if(!empty($data_res)){
			if($tipe == 'data'){
				return $data_res;
			}else{
				if($echoed == true){
					echo $data_res->option_value;
				}else{
					return $data_res->option_value;
				}
			}
		}
		
		return '';
	}
	
}

if(!function_exists('update_option')){

	function update_option($data = array()){
		
		//DEFAULT
		/*$data = array(
			'var' 		=> '' (var | array)
		);*/
		
		/*single, echoed*/
		$prefix = config_item('db_prefix');
		if(empty($scope)){
			$scope =& get_instance();
		}	
		
		if(empty($data)){
			return false;
		}
		
		$get_var = array();
		foreach($data as $key => $dt){
			if(!in_array($key, $get_var)){
				$get_var[] = $key;
			}
		}
				
		$option_update = array();
		$option_update_key = array();
				
		$scope->db->select('a.*');
		$scope->db->from($prefix.'options as a');		
		$scope->db->where('a.is_deleted', 0);
		$scope->db->where('a.is_active', 1);
		
		if(is_array($get_var)){
			$var_all = implode("','", $get_var);
			$scope->db->where("a.option_var IN ('".$var_all."')");
		}else{
			$scope->db->where("a.option_var != '-1' ");	//just for skip
		}
		
		$query = $scope->db->get();
		if($query->num_rows() > 0){
			
			//UPDATE OPTION
			foreach($query->result_array() as $dt){
			
				if(!in_array($dt['option_var'], $option_update_key)){
					$option_update_key[] = $dt['option_var'];
				}
				
				if(!empty($data[$dt['option_var']])){
					$option_update[] = array(
						"option_var" => $dt['option_var'],
						"option_value" => $data[$dt['option_var']]
					);
				}else{
					$option_update[] = array(
						"option_var" => $dt['option_var'],
						"option_value" => ""
					);
				}
				
			}
			
		}
		
		//UPDATE ALL
		if(!empty($option_update)){
			$scope->db->update_batch($prefix.'options', $option_update, 'option_var'); 
		}
		
		$all_insert_key = array();
		$option_insert = array();
		//INSERT ALL
		foreach($get_var as $opt_var){
			if(!in_array($opt_var, $option_update_key)){
				
				if(!in_array($opt_var, $all_insert_key)){
					$all_insert_key[] = $opt_var;
					
					//if(!empty($data[$opt_var])){
						$option_insert[] = array(
							"option_var" => $opt_var,
							"option_value" => $data[$opt_var]
						);
					//}
					
				}
				
			}
		}
		
		if(!empty($option_insert)){
			$scope->db->insert_batch($prefix.'options', $option_insert); 
		}		
		
		return true;
	}
	
}

if(!function_exists('replace_to_printer_command')){
	function replace_to_printer_command($text = '', $tipe_printer = 'EPSON', $tipe_pin = 32){
		
		/*
			0,1,2,3,4,5,6,7,8,9,A,B,C,D,E,F => 16
			10,11,12,13,14,15,16,17,18,19,1A,1B,1C,1D,1E,1F
			20,21,22,23,24,25,26,27,28,29,2A,2B,2C,2D,2E,2F
			30,31,32,33,34,35,36,37,38,39,3A,3B,3C,3D,3E,3F
		*/	

		$tipe_pin = str_replace("CHAR", "", $tipe_pin);
		
		$string_to_hexa = array(
			"]\n"	=> "]", //auto trim
			"[align=0]"	=> "\x1b\x61\x00", //left
			"[align=1]"	=> "\x1b\x61\x01", //center
			"[align=2]"	=> "\x1b\x61\x02", //right
			"[size=0]"	=> "\x1d\x21\x00", //all=0
			"[size=1]"	=> "\x1d\x21\x01", //width=0, height=1
			"[size=2]"	=> "\x1d\x21\x11", //width=1, height=2
			"[size=3]"	=> "\x1d\x21\x11", //width =2, height=3
			"[set_tab1]"	=> "\x1b\x44\x04\x10\x18",
			"[set_tab2]"	=> "\x1b\x44\x07\x13",
			"[set_tab3]"	=> "\x1b\x44\x01\x13",
			"[set_tab1a]"	=> "\x1b\x44\x04\x13",
			"[set_tab1b]"	=> "\x1b\x44\x10",
			"[tab]"	=> "\x09",
			"[newline]"	=> "\x0A",
			"[fullcut]"	=> "\x1b\x69",
			"[cut]"	=> "\x1b\x6d",
			"[clear_set_tab]"	=> "\x1b\x44\x00",
			"[list_order_tipe1]"	=> "\x1b\x44\x05\x15",
			"[list_order_tipe2]"	=> "\x1b\x44\x01\x15"
		);
		
		//EPSON-DEFAULT
		//32
		if($tipe_pin == 32){
			$string_to_hexa['[set_tab1]'] = "\x1b\x44\x04\x10\x18";
			//$string_to_hexa['[set_tab1]'] = "\x1b\x44\x04\x0e\x16";
			$string_to_hexa['[set_tab2]'] = "\x1b\x44\x07\x13";
			$string_to_hexa['[set_tab3]'] = "\x1b\x44\x01\x13";
			$string_to_hexa['[set_tab1a]'] = "\x1b\x44\x04\x13";
			$string_to_hexa['[set_tab1b]'] = "\x1b\x44\x11";
			$string_to_hexa['[list_order_tipe1]'] = "\x1b\x44\x05\x15";
			$string_to_hexa['[list_order_tipe2]'] = "\x1b\x44\x01\x15";
		}
		
		//40
		if($tipe_pin == 40){
			$string_to_hexa['[set_tab1]'] = "\x1b\x44\x04\x14\x1d";
			$string_to_hexa['[set_tab2]'] = "\x1b\x44\x0F\x1b";
			$string_to_hexa['[set_tab3]'] = "\x1b\x44\x01\x1b";
			$string_to_hexa['[set_tab1a]'] = "\x1b\x44\x04\x1b";
			$string_to_hexa['[set_tab1b]'] = "\x1b\x44\x19";
			$string_to_hexa['[list_order_tipe1]'] = "\x1b\x44\x05\x1d";
			$string_to_hexa['[list_order_tipe2]'] = "\x1b\x44\x01\x1d";
		}
		
		//42
		if($tipe_pin == 42){
			$string_to_hexa['[set_tab1]'] = "\x1b\x44\x04\x16\x1f";
			$string_to_hexa['[set_tab2]'] = "\x1b\x44\x11\x1d";
			$string_to_hexa['[set_tab3]'] = "\x1b\x44\x01\x1d";
			$string_to_hexa['[set_tab1a]'] = "\x1b\x44\x04\x1d";
			$string_to_hexa['[set_tab1b]'] = "\x1b\x44\x1b";
			$string_to_hexa['[list_order_tipe1]'] = "\x1b\x44\x05\x1f";
			$string_to_hexa['[list_order_tipe2]'] = "\x1b\x44\x01\x1f";
		}
		
		//46
		if($tipe_pin == 46){
			$string_to_hexa['[set_tab1]'] = "\x1b\x44\x04\x18\x22";
			$string_to_hexa['[set_tab2]'] = "\x1b\x44\x15\x20";
			$string_to_hexa['[set_tab3]'] = "\x1b\x44\x01\x20";
			$string_to_hexa['[set_tab1a]'] = "\x1b\x44\x04\x20";
			$string_to_hexa['[set_tab1b]'] = "\x1b\x44\x1e";
			$string_to_hexa['[list_order_tipe1]'] = "\x1b\x44\x05\x22";
			$string_to_hexa['[list_order_tipe2]'] = "\x1b\x44\x01\x22";
		}
		
		//48
		if($tipe_pin == 48){
			$string_to_hexa['[set_tab1]'] = "\x1b\x44\x04\x1a\x24";
			$string_to_hexa['[set_tab2]'] = "\x1b\x44\x17\x22"; 
			$string_to_hexa['[set_tab3]'] = "\x1b\x44\x01\x22";
			$string_to_hexa['[set_tab1a]'] = "\x1b\x44\x04\x22";
			$string_to_hexa['[set_tab1b]'] = "\x1b\x44\x21";
			$string_to_hexa['[list_order_tipe1]'] = "\x1b\x44\x05\x24";
			$string_to_hexa['[list_order_tipe2]'] = "\x1b\x44\x01\x24";
		}
		
		if($tipe_printer == 'SEWOO'){
			$string_to_hexa['[set_tab1]'] .= ",x04";
			$string_to_hexa['[set_tab2]'] .= ",x03";
			$string_to_hexa['[set_tab3]'] .= ",x03";
			$string_to_hexa['[set_tab1a]'] .= ",x03";
			$string_to_hexa['[set_tab1b]'] .= ",x02";
			$string_to_hexa['[list_order_tipe1]'] .= ",x03";
			$string_to_hexa['[list_order_tipe2]'] .= ",x03";
		}
		
		if($tipe_printer == 'STAR'){
			
			$string_to_hexa['[align=0]']= "\x1b\x1d\x61\x00"; //left
			$string_to_hexa['[align=1]']= "\x1b\x1d\x61\x01"; //center
			$string_to_hexa['[align=2]']= "\x1b\x1d\x61\x02"; //right
			
			$string_to_hexa['[size=0]']	= "\x1b\x1d\x21\x00";
			$string_to_hexa['[size=1]']	= "\x1b\x1d\x21\x11";
			$string_to_hexa['[size=2]']	= "\x1b\x1d\x21\x00";
			$string_to_hexa['[size=3]']	= "\x1b\x1d\x21\x00";
		}
		
		$printerX = array('SEWOO','EPSON','STAR', 'BIRCH');
		//58mm printer china
		if(!in_array($tipe_printer, $printerX)){
			$string_to_hexa['[size=2]']	= "\x1d\x21\x10";
			$string_to_hexa['[size=3]']	= "\x1d\x21\x20";
		}
		
		
		if(!in_array($tipe_printer, array('EPSON','SEWOO'))){
			$string_to_hexa['[set_tab1]'] .= ",\x00";
			$string_to_hexa['[set_tab2]'] .= ",\x00";
			$string_to_hexa['[set_tab3]'] .= ",\x00";
			$string_to_hexa['[set_tab1a]'] .= ",\x00";
			$string_to_hexa['[set_tab1b]'] .= ",\x00";
			$string_to_hexa['[list_order_tipe1]'] .= ",\x00";
			$string_to_hexa['[list_order_tipe2]'] .= ",\x00";
		}
		
		$newText = strtr($text, $string_to_hexa);
		
		return $newText;
	}
}

if(!function_exists('printer_command_align_right')){
	function printer_command_align_right($text = '', $length_set = 0, $is_html = 0){
		
		$text_show = $text;
		if(!empty($length_set)){
			$length_txt = strlen($text);
			$text_show = $text;
			if($length_txt < $length_set){
				$gapTxt = $length_set - $length_txt;
				if($is_html){
					$text_show = str_repeat("&nbsp;", $gapTxt).$text_show;
				}else{
					$text_show = str_repeat(" ", $gapTxt).$text_show;
				}
											
			}
		}
		
		return $text_show;
	}
}

if(!function_exists('get_client_ip')){
	function get_client_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {   //check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {   //to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
		
		if($ip == '::1'){
			$ip = '127.0.0.1';
		}
		
        return $ip;
    } 
}

//GET STATUS CLOSING
if(!function_exists('is_closing')){

	function is_closing($data = array()){
		
		//DEFAULT
		/*$data = array(
			'xdate' 	=> '',
			'xtipe'	=> 'sales'
		);*/
		
		/*single, echoed*/
		$prefix = config_item('db_prefix2');
		if(empty($scope)){
			$scope =& get_instance();
		}	
				
		$xtipe = 'sales';
		$closing_status = 1;
		if(is_array($data)){
			extract($data);
		}else{
			//string
			$xdate = $data;
		}
		
		if(empty($xdate)){
			$xdate = date("Y-m-d");
		}
		
		$xdate = date("Y-m-d", strtotime($xdate));
				
		$scope->db->select('a.*');
		$scope->db->from($prefix.'closing as a');		
		$scope->db->where('a.tipe', $xtipe);
		$scope->db->where('a.tanggal', $xdate);
		$scope->db->where('a.closing_status', $closing_status);
		$query = $scope->db->get();
		if($query->num_rows() > 0){
			//SUDAH CLOSING
			return true;
		}
		
		//SUDAH CLOSING
		return false;
	}
	
}

//GET RESETAPP
if(!function_exists('doresetapp')){

	function doresetapp($data = array()){
		
		$prefix = config_item('db_prefix');
		if(empty($scope)){
			$scope =& get_instance();
		}	
		
		$scope->load->helper('directory');
		$scope->load->helper('file');
		
		$scope->db->query('TRUNCATE TABLE '.$prefix.'modules');
		$scope->db->query("INSERT INTO ".$prefix."modules (`id`,`module_name`,`module_author`,`module_version`,`module_description`,`module_folder`,`module_controller`,`module_is_menu`,`module_breadcrumb`,`module_order`,`module_icon`,`module_shortcut_icon`,`module_glyph_icon`,`module_glyph_font`,`module_free`,`running_background`,`show_on_start_menu`,`show_on_right_start_menu`,`start_menu_path`,`start_menu_order`,`start_menu_icon`,`start_menu_glyph`,`show_on_context_menu`,`context_menu_icon`,`context_menu_glyph`,`show_on_shorcut_desktop`,`desktop_shortcut_icon`,`desktop_shortcut_glyph`,`show_on_preference`,`preference_icon`,`preference_glyph`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) VALUES (1,'Setup Aplikasi','dev@wepos.id','v.1.0','','systems','setupAplikasiFree',1,'1. Master Aplikasi>Setup Aplikasi',1,'icon-cog','icon-cog','','',1,0,1,0,'1. Master Aplikasi>Setup Aplikasi',1000,'icon-cog','',0,'icon-cog','',1,'icon-cog','',0,'icon-cog','','administrator','2018-07-10 08:52:11','administrator','2018-07-30 00:00:00',1,0),(2,'Client Info','dev@wepos.id','v.1.0.0','Client Info','systems','clientInfo',0,'1. Master Aplikasi>Client Info',1,'icon-home','icon-home','','',1,0,1,0,'1. Master Aplikasi>Client Info',1101,'icon-home','',0,'icon-home','',1,'icon-home','',1,'icon-home','','administrator','2018-07-03 07:47:08','administrator','2018-07-03 07:47:08',1,0),(3,'Client Unit','dev@wepos.id','v.1.0','','systems','DataClientUnit',1,'1. Master Aplikasi>Client Unit',1,'icon-building','icon-building','','',1,0,1,0,'1. Master Aplikasi>Client Unit',1102,'icon-building','',0,'icon-building','',1,'icon-building','',1,'icon-building','','administrator','2018-07-10 08:52:10','administrator','2018-07-30 00:00:00',1,0),(4,'Data Structure','dev@wepos.id','v.1.0','','systems','DataStructure',1,'1. Master Aplikasi>Data Structure',1,'icon-building','icon-building','','',1,0,1,0,'1. Master Aplikasi>Data Structure',1103,'icon-building','',0,'icon-building','',1,'icon-building','',1,'icon-building','','administrator','2018-07-10 08:52:11','administrator','2018-07-30 00:00:00',1,0),(5,'Role Manager','dev@wepos.id','v.1.2','Role Manager','systems','Roles',1,'1. Master Aplikasi>Role Manager',1,'icon-role-modules','icon-role-modules','','',1,0,1,0,'1. Master Aplikasi>Role Manager',1201,'icon-role-modules','',0,'icon-role-modules','',1,'icon-role-modules','',1,'icon-role-modules','','administrator','2018-07-10 08:52:15','administrator','2018-07-30 00:00:00',1,0),(6,'Data User','dev@wepos.id','v.1.0','','systems','UserData',1,'1. Master Aplikasi>Data User',1,'icon-user-data','icon-user-data','','',1,0,1,0,'1. Master Aplikasi>Data User',1203,'icon-user-data','',0,'icon-user-data','',1,'icon-user-data','',0,'icon-user-data','','administrator','2018-07-10 08:52:11','administrator','2018-07-30 00:00:00',1,0),(7,'User Profile','dev@wepos.id','v.1.0','','systems','UserProfile',1,'1. Master Aplikasi>User Profile',1,'user','user','','',1,0,1,1,'1. Master Aplikasi>User Profile',1301,'user','',1,'user','',1,'user','',1,'user','','administrator','2018-07-10 08:52:17','administrator','2018-07-30 00:00:00',1,0),(8,'Desktop Shortcuts','dev@wepos.id','v.1.0','Shortcuts Manager to Desktop','systems','DesktopShortcuts',1,'1. Master Aplikasi>Desktop Shortcuts',1,'icon-preferences','icon-preferences','','',1,0,1,1,'1. Master Aplikasi>Desktop Shortcuts',1302,'icon-preferences','',1,'icon-preferences','',1,'icon-preferences','',1,'icon-preferences','','administrator','2018-07-10 08:52:12','administrator','2018-07-30 00:00:00',1,0),(9,'QuickStart Shortcuts','dev@wepos.id','v.1.0','','systems','QuickStartShortcuts',0,'1. Master Aplikasi>QuickStart Shortcuts',1,'icon-preferences','icon-preferences','','',1,0,1,0,'1. Master Aplikasi>QuickStart Shortcuts',1303,'icon-preferences','',0,'icon-preferences','',1,'icon-preferences','',1,'icon-preferences','','administrator','2018-07-24 07:43:19','administrator','2018-07-21 09:16:19',1,0),(10,'Refresh Aplikasi','dev@wepos.id','v.1.0.0','','systems','refreshModule',0,'Refresh Aplikasi',1,'icon-refresh','icon-refresh','','',1,0,0,0,'Refresh Aplikasi',1304,'icon-refresh','',0,'icon-refresh','',1,'icon-refresh','',0,'icon-refresh','','administrator','2018-07-17 15:00:19','administrator','2018-07-17 15:00:19',1,0),(11,'Lock Screen','dev@wepos.id','v.1.0.0','User Lock Screen','systems','lockScreen',0,'LockScreen',1,'icon-grid','icon-grid','','',1,1,0,0,'LockScreen',1305,'icon-grid','',0,'icon-grid','',1,'icon-grid','',0,'icon-grid','','administrator','2018-07-17 01:40:20','administrator','2018-07-30 00:00:00',1,0),(12,'Logout','dev@wepos.id','v.1.0.0','Just Logout Module','systems','logoutModule',0,'Logout',1,'icon-grid','icon-grid','','',1,1,0,0,'Logout',1306,'icon-grid','',0,'icon-grid','',1,'icon-grid','',0,'icon-grid','','administrator','2018-07-17 01:36:16','administrator','2018-07-20 15:06:35',1,0),(13,'WePOS Update','dev@wepos.id','v.1.0.0','WePOS Update','systems','weposUpdate',0,'1. Master Aplikasi>WePOS Update',1,'icon-sync','icon-grid','','',1,0,1,0,'1. Master Aplikasi>WePOS Update',1401,'icon-sync','',0,'icon-sync','',1,'icon-sync','',1,'icon-sync','','administrator','2018-07-22 08:00:58','administrator','2018-07-22 08:00:58',1,0),(14,'Notifikasi Sistem','dev@wepos.id','v.1.0.0','Notifikasi Sistem','systems','systemNotify',0,'Notifikasi Sistem',1,'icon-info','icon-info','','',1,1,0,0,'Notifikasi Sistem',1402,'icon-info','',0,'icon-info','',0,'icon-info','',0,'icon-info','','administrator','2018-07-22 08:00:58','administrator','2018-07-22 08:00:58',1,0),(17,'Master Warehouse','dev@wepos.id','v.1.0.0','Master Warehouse','master_pos','masterStoreHouse',0,'2. Master POS>Master Warehouse',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Warehouse',2201,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 03:24:56','administrator','2018-07-21 20:05:16',1,0),(18,'Master Unit','dev@wepos.id','v.1.0.0','Master Unit','master_pos','masterUnit',0,'2. Master POS>Master Unit',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Unit',2202,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 03:25:13','administrator','2018-07-12 22:15:29',1,0),(19,'Master Supplier','dev@wepos.id','v.1.0.0','Master Supplier','master_pos','masterSupplier',0,'2. Master POS>Supplier',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Supplier',2203,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 03:25:04','administrator','2018-07-21 20:04:34',1,0),(20,'Item Category','dev@wepos.id','v.1.0.0','Item Category','master_pos','itemCategory',0,'2. Master POS>Item Category',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Item Category',2210,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-05 00:36:29','administrator','2018-07-15 20:31:54',1,0),(21,'Sub Item Category','dev@wepos.id','v.1.0.0','Sub Item Category','master_pos','itemSubCategory',0,'2. Master POS>Sub Item Category',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Sub Item Category',2221,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-12 22:16:35','administrator','2018-07-05 10:25:39',1,0),(25,'Master Item','dev@wepos.id','v.1.0.0','Data Item','master_pos','masterItemRetail',0,'2. Master POS>Master Item',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Item',2230,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-13 14:04:34','administrator','2018-07-13 14:04:34',1,0),(26,'Discount Planner','dev@wepos.id','v.1.0','Planning All discount','master_pos','discountPlannerFree',0,'2. Master POS>Discount Planner',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Discount Planner',2301,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 17:26:01','administrator','2018-07-30 00:00:00',1,0),(27,'Printer Manager','dev@wepos.id','v.1.0','Printer Manager','master_pos','masterPrinter',0,'2. Master POS>Printer Manager',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Printer Manager',2302,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 03:24:50','administrator','2018-07-21 20:06:25',1,0),(28,'Master Tipe Billing','dev@wepos.id','v.1.0.0','','master_pos','masterTipeBilling',0,'2. Master POS>Master Tipe Billing',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Tipe Billing',2309,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 17:26:54','administrator','2018-07-30 00:00:00',1,0),(30,'Master Bank','dev@wepos.id','v.1.0.0','Master Bank','master_pos','masterBank',0,'2. Master POS>Master Bank',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Bank',2304,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 03:24:53','administrator','2018-07-21 20:05:03',1,0),(33,'Warehouse Access','dev@wepos.id','v.1.0.0','Warehouse Access','master_pos','warehouseAccess',0,'2. Master POS>User Access>Warehouse Access',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>User Access>Warehouse Access',2401,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-27 19:23:32','administrator','2018-07-21 20:02:49',1,0),(34,'Printer Access','dev@wepos.id','v.1.0.0','Printer Access','master_pos','printerAccess',0,'2. Master POS>User Access>Printer Access',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>User Access>Printer Access',2402,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-03 06:43:42','administrator','2018-07-21 20:02:38',1,0),(35,'Supervisor Access','dev@wepos.id','v.1.0.0','Supervisor Access','master_pos','supervisorAccess',0,'2. Master POS>User Access>Supervisor Access',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>User Access>Supervisor Access',2403,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-11 22:53:04','administrator','2018-07-21 20:02:58',0,1),(37,'Open Cashier (Shift)','dev@wepos.id','v.1.0','','cashier','openCashierShift',0,'3. Cashier & Sales Order>Open Cashier (Shift)',7,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Sales Order>Open Cashier (Shift)',3001,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 17:28:12','administrator','2018-07-30 00:00:00',1,0),(38,'Close Cashier (Shift)','dev@wepos.id','v.1.0','','cashier','closeCashierShift',0,'3. Cashier & Sales Order>Close Cashier (Shift)',7,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Sales Order>Close Cashier (Shift)',3002,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 17:28:17','administrator','2018-07-30 00:00:00',1,0),(39,'List Open Close Cashier','dev@wepos.id','v.1.0.0','','cashier','listOpenCloseCashier',0,'3. Cashier & Sales Order>List Open Close Cashier',1,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Sales Order>List Open Close Cashier',3003,'icon-grid','',0,'icon-grid','',1,'icon-grid','',0,'icon-grid','','administrator','2018-07-20 07:59:55','administrator','2018-07-20 07:59:55',1,0),(40,'Cashier','dev@wepos.id','v.1.0','Cashier','cashier','billingCashierRetail',0,'3. Cashier & Sales Order>Cashier Retail',1,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Sales Order>Cashier Retail',3101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 03:28:03','administrator','2018-07-22 12:58:59',1,0),(46,'Cashier Receipt Setup','dev@wepos.id','v.1.0.0','Cashier Receipt Setup','cashier','cashierReceiptSetupRetail',0,'3. Cashier & Sales Order>Cashier Receipt Setup',1,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Sales Order>Cashier Receipt Setup',3301,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-11 06:13:49','administrator','2018-07-22 12:59:09',1,0),(50,'Purchase Order/Pembelian','dev@wepos.id','v.1.0.0','Purchase Order/Pembelian','purchase','purchaseOrder',0,'4. Purchase & Receive>Purchase Order/Pembelian',1,'icon-grid','icon-grid','','',1,0,1,0,'4. Purchase & Receive>Purchase Order/Pembelian',4201,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 03:27:18','administrator','2018-07-15 15:07:08',1,0),(51,'Receiving List/Penerimaan Barang','dev@wepos.id','v.1.0.0','Receiving List/Penerimaan Barang','inventory','receivingList',0,'4. Purchase & Receive>Receiving List/Penerimaan Barang',1,'icon-grid','icon-grid','','',1,0,1,0,'4. Purchase & Receive>Receiving List/Penerimaan Barang',4301,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 12:05:57','administrator','2018-07-22 13:04:22',1,0),(52,'Daftar Stok Barang','dev@wepos.id','v.1.0.0','Daftar Stok Barang','inventory','listStock',0,'5. Inventory>Daftar Stok Barang',1,'icon-grid','icon-grid','','',1,0,1,0,'5. Inventory>Daftar Stok Barang',5101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-03 06:43:42','administrator','2018-07-24 13:22:20',1,0),(57,'Stock Opname','dev@wepos.id','v.1.0.0','Module Stock Opname','inventory','stockOpname',0,'5. Inventory>Stock Opname',1,'icon-grid','icon-grid','','',1,0,1,0,'5. Inventory>Stock Opname',5401,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 12:06:05','administrator','2018-07-24 13:22:51',1,0),(76,'Closing Sales','dev@wepos.id','v.1.0.0','Closing Sales','audit_closing','closingSales',0,'8. Closing & Audit>Closing Sales',1,'icon-grid','icon-grid','','',1,0,1,0,'8. Closing & Audit>Closing Sales',8101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-03 21:43:42','administrator','2018-07-03 21:43:42',1,0),(77,'Closing Purchasing','dev@wepos.id','v.1.0.0','Closing Purchasing','audit_closing','closingPurchasing',0,'8. Closing & Audit>Closing Purchasing',1,'icon-grid','icon-grid','','',1,0,1,0,'8. Closing & Audit>Closing Purchasing',8102,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-03 21:47:56','administrator','2018-07-03 21:51:27',1,0),(79,'Auto Closing Generator','dev@wepos.id','v.1.0.0','Auto Closing Generator','monitoring','generateAutoClosing',0,'9. Sync, Backup, Generate>Auto Closing Generator',1,'icon-grid','icon-grid','','',1,0,1,0,'9. Sync, Backup, Generate>Auto Closing Generator',9102,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-03 21:43:42','administrator','2018-07-03 21:43:42',0,1),(80,'Backup Master Data','dev@wepos.id','v.1.0.0','Backup Master Data','sync_backup','syncData',0,'9. Sync, Backup, Generate>Backup Master Data',1,'icon-sync','icon-sync','','',1,0,1,0,'9. Sync, Backup, Generate>Backup Master Data',9201,'icon-sync','',0,'icon-sync','',1,'icon-sync','',1,'icon-sync','','administrator','2018-07-25 12:14:44','administrator','2018-07-26 21:05:47',1,0),(81,'Backup Data Transaksi','dev@wepos.id','v.1.0.0','Backup Data Transaksi','sync_backup','backupTrx',0,'9. Sync, Backup, Generate>Backup Data Transaksi',1,'icon-backup','icon-backup','','',1,0,1,0,'9. Sync, Backup, Generate>Backup Data Transaksi',9202,'icon-backup','',0,'icon-backup','',1,'icon-backup','',1,'icon-backup','','administrator','2018-07-25 12:17:26','administrator','2018-07-26 21:06:01',1,0),(82,'Generate Report Summary','dev@wepos.id','v.1.0.0','Generate Report Summary','generate','generateReport',0,'9. Sync, Backup, Generate>Generate Report Summary',1,'icon-generate','icon-generate','','',1,0,0,0,'9. Sync, Backup, Generate>Generate Report Summary',9301,'icon-generate','',0,'icon-generate','',1,'icon-generate','',1,'icon-generate','','administrator','2018-07-26 21:10:03','administrator','2018-07-26 21:10:03',0,1),(83,'Sync & Backup','dev@wepos.id','v.1.0.0','Sync & Backup','sync_backup','syncBackup',0,'9. Sync, Backup, Generate>Sync & Backup',1,'icon-sync','icon-sync','','',1,0,1,0,'9. Sync, Backup, Generate>Sync & Backup',9203,'icon-sync','',0,'icon-sync','',1,'icon-sync','',1,'icon-sync','','administrator','2018-07-25 12:14:44','administrator','2018-07-26 21:05:47',0,1),(84,'Sales Report','dev@wepos.id','v.1.0','Sales Report','billing','reportSales',0,'6. Reports>Sales (Billing)>Sales Report',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Billing)>Sales Report',6101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-11 01:28:24','administrator','2018-07-17 17:01:16',1,0),(87,'Sales Report (Recap)','dev@wepos.id','v.1.0.0','','billing','reportSalesRecap',0,'6. Reports>Sales (Billing)>Sales Report (Recap)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Billing)>Sales Report (Recap)',6104,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-24 16:30:29','administrator','2018-07-24 16:38:02',1,0),(88,'Sales By Discount','dev@wepos.id','v.1.0.0','Sales By Discount','billing','salesByDiscount',0,'6. Reports>Sales (Billing)>Sales By Discount',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Billing)>Sales By Discount',6105,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-15 20:43:42','administrator','2018-07-15 20:43:42',1,0),(94,'Cancel Billing Report','dev@wepos.id','v.1.0.0','','billing','reportCancelBill',0,'6. Reports>Sales (Billing)>Report Cancel Billing',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Billing)>Report Cancel Billing',6110,'icon-grid','',0,'icon-grid','',1,'icon-grid','',0,'icon-grid','','administrator','2018-07-19 09:45:34','administrator','2018-07-24 16:26:54',1,0),(96,'Sales By Item','dev@wepos.id','v.1.0.0','Sales By Item','billing','reportSalesByItem',0,'6. Reports>Sales (Item)>Sales By Item',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Item)>Sales By Item',6111,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-09 05:51:55','administrator','2018-07-17 17:47:33',1,0),(103,'Sales Profit Report','dev@wepos.id','v.1.0.0','','billing','reportSalesProfit',0,'6. Reports>Sales (Profit)>Sales Profit Report',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Profit)>Sales Profit Report',6131,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-24 16:46:57','administrator','2018-07-24 17:21:51',1,0),(106,'Sales Profit Report (Recap)','dev@wepos.id','v.1.0.0','','billing','reportSalesProfitRecap',0,'6. Reports>Sales (Profit)>Sales Profit Report (Recap)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Profit)>Sales Profit Report (Recap)',6134,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-24 16:58:17','administrator','2018-07-24 17:23:59',1,0),(107,'Sales Profit By Item','dev@wepos.id','v.1.0.0','Sales Profit By Item','billing','reportSalesProfitByItem',0,'6. Reports>Sales (Profit)>Sales Profit By Item',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Profit)>Sales Profit By Item',6135,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-24 16:53:21','administrator','2018-07-17 19:38:07',1,0),(130,'Bagi Hasil','dev@wepos.id','v.1.0.0','Bagi Hasil Detail','billing','reportSalesBagiHasil',0,'6. Reports>Sales (Bagi Hasil)>Bagi Hasil',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Bagi Hasil)>Bagi Hasil',6301,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-15 06:43:42','administrator','2018-07-15 06:43:42',1,0),(131,'Bagi Hasil (Recap)','dev@wepos.id','v.1.0.0','Bagi Hasil (Recap)','billing','reportSalesBagiHasilRecap',0,'6. Reports>Sales (Bagi Hasil)>Bagi Hasil (Recap)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Bagi Hasil)>Bagi Hasil (Recap)',6302,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-15 06:43:42','administrator','2018-07-15 06:43:42',1,0),(136,'Purchase Report','dev@wepos.id','v.1.0.0','Purchase Report','purchase','reportPurchase',0,'6. Reports>Purchase/Pembelian>Purchase Report',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Purchase/Pembelian>Purchase Report',6401,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-16 21:28:58','administrator','2018-07-09 19:08:45',1,0),(138,'Purchase Report (Recap)','dev@wepos.id','v.1.0.0','Purchase Report (Recap)','purchase','reportPurchaseRecap',0,'6. Reports>Purchase/Pembelian>Purchase Report (Recap)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Purchase/Pembelian>Purchase Report (Recap)',6403,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-17 13:23:40','administrator','2018-07-09 19:08:25',1,0),(139,'Last Purchase Price','dev@wepos.id','v.1.0.0','Last Purchase Price','purchase','reportLastPurchasePrice',0,'6. Reports>Purchase/Pembelian>Last Purchase Price',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Purchase/Pembelian>Last Purchase Price',6404,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-17 13:23:40','administrator','2018-07-09 19:08:25',0,1),(140,'Receiving Report','dev@wepos.id','v.1.0.0','Receiving Report','inventory','reportReceiving',0,'6. Reports>Receiving (In)>Receiving Report',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Receiving (In)>Receiving Report',6501,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-17 13:31:50','administrator','2018-07-09 19:00:32',1,0),(143,'Receiving Report (Recap)','dev@wepos.id','v.1.0.0','Receiving Report (Recap)','inventory','reportReceivingRecap',0,'6. Reports>Receiving (In)>Receiving Report (Recap)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Receiving (In)>Receiving Report (Recap)',6504,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-09 15:57:19','administrator','2018-07-09 19:01:16',1,0),(156,'Monitoring Stock (Actual)','dev@wepos.id','v.1.0.0','Monitoring Stock (Actual)','inventory','reportMonitoringStock',0,'6. Reports>Warehouse>Monitoring Stock (Actual)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Warehouse>Monitoring Stock (Actual)',6642,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-11 23:44:12','administrator','2018-07-18 00:45:36',1,0),(157,'Kartu Stok','dev@wepos.id','v.1.0.0','Kartu Stok','inventory','kartuStok',0,'6. Reports>Warehouse>Kartu Stock',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Warehouse>Kartu Stock',6643,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-03 06:43:42','administrator','2018-07-18 00:46:03',1,0),(176,'Product Category','dev@wepos.id','v.1.0','','master_pos','productCategory',0,'2. Master POS>Product Category',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Product Category',2101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 17:26:07','administrator','2018-07-30 00:00:00',1,0),(177,'Master Product & Package','dev@wepos.id','v.1.0','Master Product & Package','master_pos','masterProduct',0,'2. Master POS>Master Product',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Product',2102,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 17:24:38','administrator','2018-07-30 00:00:00',1,0),(184,'Pembayaran PPOB','dev@wepos.id','v.1.0.0','Pembayaran PPOB','cashier','ppob',0,'3. Cashier & Sales Order>Pembayaran PPOB',1,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Sales Order>Pembayaran PPOB',3401,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2019-04-09 08:25:57','administrator','2019-04-09 17:49:57',0,1),(190,'Cashier Apps','dev@wepos.id','v.1.0','Cashier Apps','cashier','billingCashierRetailApp',0,'3. Cashier & Sales Order>Cashier Retail (Apps)',1,'icon-grid','icon-grid','','',1,0,0,0,'3. Cashier & Sales Order>Cashier Retail (Apps)',3102,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 03:28:03','administrator','2018-07-22 12:58:59',0,1);");
			
		$scope->db->delete($prefix.'options',"option_var LIKE 'mlog_%'");
	
		$resetapp = array('use_login_pin'=>0,'supervisor_pin_mode'=>0,'management_systems'=>0,'ipserver_management_systems'=>'https://wepos.id','view_multiple_store'=>0,'use_wms'=>0,'as_server_backup'=>0,'mode_bazaar_tenant'=>0,'maxday_cashier_report'=>3,'mode_touchscreen_cashier'=>0,'table_multi_order'=>0,'mode_cashier_express'=>0,'cashier_menu_bg_text_color'=>0,'jumlah_shift'=>1,'settlement_per_shift'=>0,'nama_shift_2'=>'','jam_shift_2_start'=>'','jam_shift_2_end'=>'','nama_shift_3'=>'','jam_shift_3_start'=>'','jam_shift_3_end'=>'','autobackup_on_settlement'=>0,'hide_button_invoice'=>1,'hide_button_halfpayment'=>1,'hide_button_mergebill'=>1,'hide_button_splitbill'=>1,'hide_button_logoutaplikasi'=>1,'hide_button_downpayment'=>1,'hide_detail_taxservice'=>0,'hide_detail_takeaway'=>0,'hide_detail_compliment'=>0,'save_order_note'=>0,'no_hold_billing'=>0,'default_tipe_billing_so'=>0,'input_qty_under_zero'=>0,'input_harga_manual'=>0,'input_tanggal_manual_so'=>0,'display_kode_menu_dipencarian'=>0,'display_kode_menu_dibilling'=>0,'hide_hold_bill_yesterday'=>0,'billing_log'=>0,'cashier_credit_ar'=>0,'min_noncash'=>0,'must_choose_customer'=>0,'add_customer_on_cashier'=>0,'add_sales_on_cashier'=>0,'set_ta_table_ta'=>0,'takeaway_no_tax'=>0,'takeaway_no_service'=>0,'autocut_stok_sales_to_usage'=>0,'link_customer_dan_sales'=>0,'show_multiple_print_qc'=>0,'show_multiple_print_billing'=>0,'printMonitoring_qc'=>1,'print_qc_then_order'=>0,'print_qc_order_when_payment'=>0,'opsi_no_print_when_payment'=>0,'send_billing_to_email'=>0,'save_email_to_customer'=>0,'sms_notifikasi'=>0,'print_bill_grouping_menu'=>0,'theme_print_billing'=>0,'print_sebaris_product_name'=>0,'spv_access_active'=>'','use_approval_po'=>0,'approval_change_payment_po_done'=>0,'purchasing_request_order'=>0,'auto_add_supplier_item_when_purchasing'=>0,'auto_add_supplier_ap'=>0,'receiving_select_warehouse'=>0,'so_count_stock'=>0,'ds_count_stock'=>0,'ds_auto_terima'=>0,'hide_empty_stock_on_report'=>0,'ds_detail_show_hpp'=>0,'mode_qty_unit'=>0,'mode_harga_grosir'=>0,'use_stok_imei'=>0,'salesorder_cek_stok'=>0,'salesorder_cashier'=>0,'tandai_pajak_billing'=>0,'override_pajak_billing'=>0,'nontrx_sales_auto'=>0,'nontrx_backup_onsettlement'=>0,'nontrx_button_onoff'=>0,'nontrx_allow_zero'=>0,'allow_app_all_user'=>0,'reset_billing_yesterday'=>0,'billing_no_simple'=>0,'standalone_cashier'=>0,'opsi_no_print_settlement'=>0);
		update_option($resetapp);
		
		$opt_var = array(
			'is_cloud',
		);
		
		$get_opt = get_option_value($opt_var);
		
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
		
	}
	
}

//GET STATUS CLOSING
if(!function_exists('wepos_log_update')){
	function wepos_log_update($force_update = false){
		
		if(empty($scope)){
			$scope =& get_instance();
		}
			
		$scope->load->library('curl');
		$scope->load->helper('directory');
		$scope->load->helper('file');
		
		$opt_var = array('merchant_key','merchant_tipe','merchant_cor_token','merchant_acc_token','merchant_mkt_token','produk_key','produk_nama','produk_expired','merchant_last_checkon','is_cloud');
		$get_opt = get_option_value($opt_var);
		
		$merchant_key = '';
		if(empty($get_opt['merchant_key'])){
			$get_opt['merchant_key'] = '';
			return true;
		}else{
			$merchant_key = $get_opt['merchant_key'];
		}
		if(empty($get_opt['merchant_cor_token'])){
			$get_opt['merchant_cor_token'] = '';
		}
		if(empty($get_opt['merchant_tipe'])){
			$get_opt['merchant_tipe'] = 'retail';
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
		
			if($get_opt['produk_nama'] != 'Gratis / Free' OR !empty($merchant_key)){
				
				if($get_opt['merchant_mkt_token'] < $today_check AND !empty($get_opt['merchant_mkt_token'])){
					
					$opt_var = array(
						'mlog_'.$merchant_key,
						'is_cloud'
					);
					$get_opt = get_option_value($opt_var);
					
					$mlog = '';
					if(empty($get_opt['mlog_'.$merchant_key])){
						$mlog = $get_opt['mlog_'.$merchant_key];
					}
					
					$resetapp = array('merchant_cor_token'=>'','merchant_acc_token'=>'','merchant_mkt_token'=>'','produk_key'=>'GFR-'.strtotime(date("d-m-Y")),'produk_nama'=>'Gratis / Free','produk_expired'=>'unlimited','mlog_'.$merchant_key=>'');
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
							$scope->curl->create($client_url);
							$scope->curl->option('connecttimeout', 600);
							$scope->curl->option('RETURNTRANSFER', 1);
							$scope->curl->option('SSL_VERIFYPEER', 1);
							$scope->curl->option('SSL_VERIFYHOST', 2);
							//$scope->curl->option('SSLVERSION', 3);
							$scope->curl->option('POST', 1);
							$scope->curl->option('POSTFIELDS', $post_data);
							$scope->curl->option('CAINFO', $wepos_crt);
							$scope->curl->option('FILE', $fp);
							$curl_ret = $scope->curl->execute();
							
							$scope->curl->close();
							fclose($fp);
							
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
							
							$dir_mod = directory_map($module_path, 1);
							if(count($dir_mod) > 0)
							{
								foreach($dir_mod as $file_dl)
								{
									if($file_dl == 'db.sql'){
										$sql_contents = file_get_contents($module_path.'/'.$file_dl);
										$sql_contents = explode(";", $sql_contents);
										@unlink($module_path.'/'.$file_dl);
										
										foreach($sql_contents as $query)
										{
											$query = trim($query);
											if(!empty($query)){
												@$scope->db->query($query);
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
									if($file_dl == 'application.libraries'){
										
										if(empty($get_opt['is_cloud'])){
											
											$appmod_path = APPPATH.'/libraries'; 
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
									if($file_dl == 'application.core'){
										
										if(empty($get_opt['is_cloud'])){
											
											$minjs_path = APPPATH.'/core'; 
											//unlink($minjs_path.'/MY_Controller.php');
											//unlink($minjs_path.'/MY_Loader.php');
											//unlink($minjs_path.'/MY_Router.php');
											//unlink($minjs_path.'/MY_Model.php');
											$module_file = $module_path.'/'.$file_dl;
											
											$zip = new ZipArchive;
											if($zip->open($module_file) === TRUE) 
											{
												$zip->extractTo($minjs_path);
												$zip->close();
											}
											
										}
										
										@unlink($module_path.'/'.$file_dl);
										
									}else
									if($file_dl == 'apps.min.core'){
										
										if(empty($get_opt['is_cloud'])){
											
											$minjs_path = BASE_PATH.'/apps.min/core'; 
											//unlink($minjs_path.'/application.min.js');
											$module_file = $module_path.'/'.$file_dl;
											
											$zip = new ZipArchive;
											if($zip->open($module_file) === TRUE) 
											{
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
												
												$swfile_1 = $minjs_path.'/sw-wepos.js';
												$swfile_1_copy = BASE_PATH.'/sw-wepos.js';
												if(file_exists($swfile_1)){
													@copy($swfile_1, $swfile_1_copy);
												}
												
												$swfile_2 = $minjs_path.'/manifest.json';
												$swfile_2_copy = BASE_PATH.'/manifest.json';
												if(file_exists($swfile_2)){
													@copy($swfile_2, $swfile_2_copy);
												}
												
											}
											
										}
										
										@unlink($module_path.'/'.$file_dl);
										
									}else
									if($file_dl == 'application.default'){
										
										if(empty($get_opt['is_cloud'])){
											
											$appcore_path = APPPATH.'/core'; 
											
											$module_file = $module_path.'/'.$file_dl;
											$new_module_file = $appcore_path.'/modules.default';
											
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
							
							return "force_update";
							
						}
					}
					
				}
			}else{
				
				$reset = true;
				$allow_reset = true;
				
			}
			
			if($reset == true AND $allow_reset == true){
				
				doresetapp();
				return "allow_reset";
				
			}
			
		}
		
	}
}

//GET STATUS CLOSING
if(!function_exists('empty_value_printer_text')){

	function empty_value_printer_text($Receipt_layout = '', $tipe = ''){
		if(empty($tipe)){
			return $Receipt_layout;
		}
		
		$Receipt_layout_exp = explode("\n", $Receipt_layout);
		$new_layout = array();
		if(!empty($Receipt_layout_exp)){
			foreach($Receipt_layout_exp as $dt){
				
				if(strstr($dt, $tipe)){
					if(strstr($dt, '{hide_empty}')){
						
					}else{
						$new_layout[] = $dt;
					}
				}else{
					$new_layout[] = $dt;
				}
				
			}
		}
		
		$new_layout_txt = $Receipt_layout;
		if(!empty($new_layout)){
			$new_layout_txt = implode("\n", $new_layout);
		}
		
		return $new_layout_txt;
	}
}


if(!function_exists('printing_process')){

	function printing_process($data_printer = array(), $print_content = '', $do = 'print', $custom_print = ''){
		
		if(!empty($custom_print)){
			include 'custom_print_'.$custom_print.'.php';
			//die();
			
			if($custom_print == 'email' AND !empty($return_data)){
				return $return_data;
			}
			
		}else{
			include 'default_print.php';
		}
		
	}
}


if(!function_exists('printing_process_error')){

	function printing_process_error($error = ''){
		
		include 'default_error_print.php';
		
	}
}


if(!function_exists('cek_server_backup')){
	function cek_server_backup($get_opt = array()) {
		if(!empty($get_opt)){
		   if(empty($get_opt['as_server_backup'])){
				$get_opt_var = array('as_server_backup');
				$get_opt = get_option_value($get_opt_var);
		   }
		}else{
			$get_opt_var = array('as_server_backup');
			$get_opt = get_option_value($get_opt_var);
		}

		if(!empty($get_opt['as_server_backup'])){
			$r = array('success' => false, 'info' => 'Tidak dapat melakukan Transaksi, Aplikasi WePOS ini di set sebagai Server Backup!');
			die(json_encode($r));
		}
    } 
}


if(!function_exists('check_report_jam_operasional')){
	function check_report_jam_operasional($get_opt = array(), $mktime_dari = 0, $mktime_sampai = 0) {
		
		if(!empty($get_opt)){
		   if(empty($get_opt['jam_operasional_from']) OR empty($get_opt['jam_operasional_to'])){
				$get_opt_var = array('jam_operasional_from','jam_operasional_to','jam_operasional_extra');
				$get_opt = get_option_value($get_opt_var);
		   }
		}else{
			$get_opt_var = array('jam_operasional_from','jam_operasional_to','jam_operasional_extra');
			$get_opt = get_option_value($get_opt_var);
		}
		
		$date_from = date("d-m-Y",$mktime_dari);
		$date_till = date("d-m-Y",$mktime_sampai);
			
		//update report = jam_operasional
		if(empty($get_opt['jam_operasional_from'])){
			$jam_operasional_from = '07:00:01';
		}else{
			$jam_operasional_from = $get_opt['jam_operasional_from'].':01';
		}
		if(empty($get_opt['jam_operasional_to'])){
			$jam_operasional_to = '23:00:00';
		}else{
			$jam_operasional_to = $get_opt['jam_operasional_to'].':00';
		}
		if(empty($get_opt['jam_operasional_extra'])){
			$jam_operasional_extra = 0;
		}else{
			$jam_operasional_extra = $get_opt['jam_operasional_extra']*3600;
		}
		$jam_operasional_from = strtotime($date_from." ".$jam_operasional_from);
		$jam_operasional_to = strtotime($date_till." ".$jam_operasional_to)+$jam_operasional_extra;
		
		$qdate_from = date("Y-m-d H:i:s",$jam_operasional_from);
		$qdate_till = date("Y-m-d H:i:s",strtotime($date_till));
		$qdate_till_max = date("Y-m-d H:i:s",$jam_operasional_to);
		
		$data_return = array('qdate_from' => $qdate_from, 'qdate_till' => $qdate_till, 'qdate_till_max' => $qdate_till_max);
		return $data_return;
	}
}

if(!function_exists('check_maxview_cashierReport')){
	function check_maxview_cashierReport($get_opt = array(), $mktime_dari = 0, $mktime_sampai = 0) {
		
		if(empty($scope)){
			$scope =& get_instance();
		}
		
		$role_id = $scope->session->userdata('role_id');	
		
		if(!empty($get_opt)){
		   if(empty($get_opt['maxday_cashier_report']) OR empty($get_opt['jam_operasional_from']) OR empty($get_opt['jam_operasional_to'])){
				$get_opt_var = array('role_id_kasir','maxday_cashier_report','jam_operasional_from','jam_operasional_to','jam_operasional_extra');
				$get_opt = get_option_value($get_opt_var);
		   }
		}else{
			$get_opt_var = array('role_id_kasir','maxday_cashier_report','jam_operasional_from','jam_operasional_to','jam_operasional_extra');
			$get_opt = get_option_value($get_opt_var);
		}

		$maxday_cashier_report = 1;
		if(!empty($get_opt['maxday_cashier_report'])){
			$maxday_cashier_report = $get_opt['maxday_cashier_report'];
		}
		$role_id_kasir = explode(",",$get_opt['role_id_kasir']);
		
		$is_kasir = false;
		if(in_array($role_id, $role_id_kasir)){
			//kasir
			$is_kasir = true;
		}
		
		if($role_id == 1 OR $role_id == 2){
			$is_kasir = false;
		}
		
		
		if($is_kasir == true){
			
			if(!empty($mktime_dari) OR !empty($mktime_sampai)){
				
				$todaydate = strtotime(date("d-m-Y"));
				$todaydate_maxView = $todaydate - (ONE_DAY_UNIX*$maxday_cashier_report);
				if($mktime_dari < $todaydate_maxView OR $mktime_sampai < $todaydate_maxView){
					echo 'Silahkan Pilih Tanggal Laporan<br/>
					Maksimal Lihat Laporan s/d Tanggal: <b>'.date("d-m-Y", $todaydate_maxView).'</b>';
					die();
				}
				
			}else{
				echo 'Silahkan Pilih Tanggal Laporan';
				die();
			}
		}
		
		$data_return = check_report_jam_operasional($get_opt, $mktime_dari, $mktime_sampai);
		return $data_return;
		//echo $role_id.' = '.$is_kasir.'<pre>'; print_r($role_id_kasir);die();
		
    } 
}

if(!function_exists('no2alphabet')){
	function no2alphabet($no = 1) {
		if($no == 0){
			$no = 1;
		}
		
		$huruf = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		return $huruf[$no];
	}
}

if(!function_exists('realisasi_nontrx')){
	function realisasi_nontrx($tgl_cek_mk = 0) {
		
		if(empty($scope)){
			$scope =& get_instance();
		}
		
		if(empty($tgl_cek_mk)){
			$tgl_cek_mk = strtotime(date("d-m-Y"));
		}
		
		$bulan = date("n",$tgl_cek_mk);
		$bulan2 = date("m",$tgl_cek_mk);
		$billingno_prefix = date("ymd",$tgl_cek_mk);
		$billingno_prefix_bulan = date("ym",$tgl_cek_mk);
		$tahun = date("Y",$tgl_cek_mk);
		$hari_no = date("N",$tgl_cek_mk);
		
		$prefix = config_item('db_prefix2');
		$table_billing = $prefix.'billing';
		$table_nontrx_target = $prefix.'nontrx_target';
		
		//nontrx - fixdate
		$scope->db->from($table_nontrx_target);
		$scope->db->where("nontrx_bulan = ".$bulan);
		$scope->db->where("is_deleted = 0");
		$get_nontrx_target = $scope->db->get();
		if($get_nontrx_target->num_rows() > 0){
			
			$data_nontrx = $get_nontrx_target->row();
			if($data_nontrx->nontrx_curr_tanggal != date("Y-m-d", $tgl_cek_mk)){
				$update_tanggal = array(
					'nontrx_curr_tanggal'	=> date("Y-m-d", $tgl_cek_mk)
				);
				$scope->db->update($table_nontrx_target,$update_tanggal,"nontrx_bulan = ".$bulan." AND is_deleted = 0");
				
			}
		}
		
		//hari
		$nontrx_hari_realisasi = 0;
		$total_billing_hari = 0;
		$scope->db->select("SUM(1) as total_billing, SUM(tax_total) as nontrx_hari_realisasi");
		$scope->db->from($table_billing);
		$scope->db->where("billing_no LIKE '".$billingno_prefix."%'");
		$scope->db->where("billing_status", 'paid');
		$scope->db->where("is_deleted = 0");
		$scope->db->where("txmark = 1");
		$get_billing = $scope->db->get();
		if($get_billing->num_rows() > 0){
			$data_nontrx = $get_billing->row();
			$nontrx_hari_realisasi = $data_nontrx->nontrx_hari_realisasi;
			$total_billing_hari = $data_nontrx->total_billing;
		}
		
		//minggu
		$nontrx_minggu_realisasi = 0;
		$total_billing_minggu = 0;
		if($hari_no == 1){
			$nontrx_minggu_realisasi = $nontrx_hari_realisasi;
		}else{
			
			$last_day = $tgl_cek_mk;
			for($i=0;$i<=6;$i++){
				$last_day = $tgl_cek_mk - ($i*86400);
				if(date("N", $last_day) == 1){
					$i = 7;
				}
			}
			
			$date_week_from = date("Y-m-d", $last_day);
			$billing_no_from = date("ymd", $last_day);
			$date_week_till = date("Y-m-d", $tgl_cek_mk);
			$billing_no_till = date("ymd", ($tgl_cek_mk+86400));
			$scope->db->select("SUM(1) as total_billing, SUM(tax_total) as nontrx_minggu_realisasi");
			$scope->db->from($table_billing);
			$scope->db->where("billing_no >= '".$billing_no_from."0001' AND billing_no < '".$billing_no_till."0001'");
			$scope->db->where("billing_status", 'paid');
			$scope->db->where("is_deleted = 0");
			$scope->db->where("txmark = 1");
			$get_billing = $scope->db->get();
			if($get_billing->num_rows() > 0){
				$data_nontrx = $get_billing->row();
				$nontrx_minggu_realisasi = $data_nontrx->nontrx_minggu_realisasi;
				$total_billing_minggu = $data_nontrx->total_billing;
			}
		}
		
		//bulan
		$nontrx_bulan_realisasi = 0;
		$total_billing_bulan = 0;
		$scope->db->select("SUM(1) as total_billing, SUM(tax_total) as nontrx_bulan_realisasi");
		$scope->db->from($table_billing);
		$scope->db->where("billing_no LIKE '".$billingno_prefix_bulan."%'");
		$scope->db->where("billing_status", 'paid');
		$scope->db->where("is_deleted = 0");
		$scope->db->where("txmark = 1 AND (txmark_no IS NOT NULL OR txmark_no != '')");
		$get_billing = $scope->db->get();
		if($get_billing->num_rows() > 0){
			$data_nontrx = $get_billing->row();
			$nontrx_bulan_realisasi = $data_nontrx->nontrx_bulan_realisasi;
			$total_billing_bulan = $data_nontrx->total_billing;
		}
		
		$update_realisasi = array(
			'nontrx_bulan_realisasi'	=> $nontrx_bulan_realisasi,
			'nontrx_minggu_realisasi'	=> $nontrx_minggu_realisasi,
			'nontrx_hari_realisasi'		=> $nontrx_hari_realisasi,
		);
		$scope->db->update($table_nontrx_target,$update_realisasi,"nontrx_bulan = ".$bulan." AND is_deleted = 0");
		
		return $update_realisasi;
	}
}
?>