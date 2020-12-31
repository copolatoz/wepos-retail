<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Rawbt extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix');
		$this->prefix_pos = config_item('db_prefix2');
		$this->load->model('model_billingcashierprint', 'mprint');
				
	}

	public function index()
	{
		$bill_no = $this->input->post_get('bill_no');
		$date = $this->input->post_get('date');
		
		$bill_no = str_replace(".txt","",$bill_no);
		$date = str_replace(".txt","",$date);
		if(!empty($date)){
			$this->printSettlement($date);
		}else{
			$this->doPrint($bill_no);
		}
		
	}

	public function doPrint($url_trx = '', $mkey = '')
	{
		header('Content-Type: text/plain;');
		
		$url_trx = str_replace("trx-","",$url_trx);
		$url_trx = str_replace(".txt","",$url_trx);
		$get_data = explode("-",$url_trx);
		
		if(empty($get_data[0])){
			die();
		}
		
		//update-2009.001
		$mkey_data = array();
		if(!empty($mkey)){
			
			$mkey = str_replace(".txt","",$mkey);
			$mkey_conn = $this->getStoreMkey($mkey);
			
			if(!empty($mkey_conn['success'])){
				$mkey_data = $mkey_conn['data'];
			}else{
				die();
			}
			
		}else{
			$mkey = '';
		}
		
		$billing_no = $get_data[0];
		$tipe = $get_data[1];
		$id = $get_data[2];
		$is_void = $get_data[3];
		$void_id = $get_data[4];
		$order_detail_id = $get_data[5];
		
		$dtParams = array(
			'tipe' 		 => $tipe,
			'id' 		 => $id,
			'rawbt_print'=> true,
		);
		
		//get monitoring settlement
		$this->table_print_monitoring = $this->prefix_pos.'print_monitoring';
		$this->db->select("*");
		$this->db->from($this->table_print_monitoring);
		$this->db->where("tipe = 'billing' AND billing_no = '".$billing_no."'");
		$this->db->order_by("id","DESC");
		$get_data_print = $this->db->get();
		$receiptTxt = '';
		
		if($get_data_print->num_rows() > 0){
			$dt_monitoring = $get_data_print->row_array();
			$receiptTxt = $dt_monitoring['receiptTxt'];
			$printer_type = $dt_monitoring['tipe_printer'];
			$printer_pin = $dt_monitoring['tipe_pin'];
			//echo $receiptTxt;die();
			$print_content = replace_to_printer_command($receiptTxt, $printer_type, $printer_pin);
			echo $print_content;
		}else{
			echo '';
		}
	}

	public function testPrinter($printSetting = '', $mkey = '')
	{
		header('Content-Type: text/plain;');
		
		$printSetting = str_replace(".txt","",$printSetting);
		if(empty($printSetting)){
			die();
		}
		
		//update-2009.001
		$mkey_data = array();
		if(!empty($mkey)){
			
			$mkey = str_replace(".txt","",$mkey);
			$mkey_conn = $this->getStoreMkey($mkey);
			
			if(!empty($mkey_conn['success'])){
				$mkey_data = $mkey_conn['data'];
			}else{
				die();
			}
			
		}else{
			$mkey = '';
		}
		
		$dtParams = array(
			'do_print' 		=> true,
			'printSetting' 	=> $printSetting,
			//'return_data' 	=> true,
			'rawbt_print'	=> true,
			'mkey'	=> $mkey,
			'mkey_data'	=> $mkey_data
		);
		
		$this->mprint->testPrinter($dtParams);
		
	}

	public function printSettlement($url_trx = '', $mkey = '')
	{
		
		header('Content-Type: text/plain;');
		
		$url_trx = str_replace("settlement-","",$url_trx);
		$url_trx = str_replace(".txt","",$url_trx);
		$get_data = explode("-",$url_trx);
		
		if(empty($get_data[0])){
			die();
		}
		
		//update-2009.001
		$mkey_data = array();
		if(!empty($mkey)){
			
			$mkey = str_replace(".txt","",$mkey);
			$mkey_conn = $this->getStoreMkey($mkey);
			
			if(!empty($mkey_conn['success'])){
				$mkey_data = $mkey_conn['data'];
			}else{
				die();
			}
			
		}else{
			$mkey = '';
		}
		
		$get_date = $get_data[0];
		$reprint = $get_data[1];
		$show_txmark = $get_data[2];
		$pershift = $get_data[3];
		
		$dtParams = array(
			'get_date' => $get_date,
			'reprint' => $reprint,
			'show_txmark' => $show_txmark,
			'pershift' => $pershift,
			'rawbt_print'	=> true
		);
		
		
		//get monitoring settlement
		$this->table_print_monitoring = $this->prefix_pos.'print_monitoring';
		$this->db->select("*");
		$this->db->from($this->table_print_monitoring);
		$this->db->where("tipe = 'settlement' AND billing_no = '".$get_date."'");
		$this->db->order_by("id","DESC");
		$get_data_print = $this->db->get();
		$receiptTxt = '';
		
		if($get_data_print->num_rows() > 0){
			$dt_monitoring = $get_data_print->row_array();
			$receiptTxt = $dt_monitoring['receiptTxt'];
			$printer_type = $dt_monitoring['tipe_printer'];
			$printer_pin = $dt_monitoring['tipe_pin'];
			//echo $receiptTxt;die();
			$print_content = replace_to_printer_command($receiptTxt, $printer_type, $printer_pin);
			echo $print_content;
		}else{
			echo '';
		}
		
	}
	
	//update-2009.001
	public function getStoreMkey($mkey = ''){
		$this->load->library('curl');
		$mktime_dc = strtotime(date("d-m-Y H:i:s"));
		$client_url = config_item('website').'/mkey-info?_dc='.$mktime_dc;
		
		$post_data = array(
			'merchant_key'	=> $mkey,
			'is_login'	=> true
		);
		
		$wepos_crt = ASSETS_PATH.config_item('wepos_crt_file');
		$this->curl->create($client_url);
		$this->curl->option('connecttimeout', 600);
		$this->curl->option('RETURNTRANSFER', 1);
		$this->curl->option('SSL_VERIFYPEER', 1);
		$this->curl->option('SSL_VERIFYHOST', 2);
		$this->curl->option('POST', 1);
		$this->curl->option('POSTFIELDS', $post_data);
		$this->curl->option('CAINFO', $wepos_crt);
		
		
		$curl_ret = $this->curl->execute();
		
		$ret_data = json_decode($curl_ret, true);
		
		$conn_data = false;
		
		if(!empty($ret_data['success'] === true)){
			if(!empty($ret_data['data'])){
				$this->db->close();
				
				$store_data = array(
					$ret_data['data']['merchant_host'],
					$ret_data['data']['merchant_user'],
					$ret_data['data']['merchant_accesspw'],
					$ret_data['data']['merchant_port'],
					$ret_data['data']['merchant_db'],
					1
				);
				
				$config = array();
				$config['hostname'] = $store_data[0];
				$config['username'] = $store_data[1];
				$config['password'] = $store_data[2];
				$config['port'] 	= $store_data[3];
				$config['database'] = $store_data[4];
				$config['dbdriver'] = 'mysqli';
				$config['dbprefix'] = '';
				$config['pconnect'] = FALSE;
				$config['db_debug'] = (ENVIRONMENT !== 'production');
				$config['cache_on'] = FALSE;
				$config['cachedir'] = '';
				$config['char_set'] = 'utf8';
				$config['dbcollat'] = 'utf8_general_ci';
				$config['swap_pre'] = '';
				$config['encrypt'] = FALSE;
				$config['compress'] = FALSE;
				$config['stricton'] = FALSE;
				$config['failover'] = array();
				//$this->load->database($config);
				
				$DB2 = $this->load->database($config, TRUE);
				$this->db = $DB2;
				
				$conn_data = true;
				
			}
		}
		
		$ret_data['conn_data'] = $conn_data;
		return $ret_data;
	}
}