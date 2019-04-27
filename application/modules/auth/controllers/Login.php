<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends MX_Controller {
	
	function __construct(){
		parent::__construct();
		$this->load->model('mdl_login', 'm');
	}

	public function index($mkey = '')
	{
		if(!empty($_POST)){
			$this->submit();
			die();
		}
		
		if($this->session->userdata('id_user') != '' && $this->session->userdata('client_id')!=''){ redirect('backend'); }
		
		$data['title']				=	'Login | '.config_item('program_name');
		$data['meta_description'] 	=	config_item('program_name');
		$data['meta_keywords']		=	config_item('program_name');
		$data['meta_author']		=	config_item('program_author');
		$data['program_name']		=	config_item('program_name');
		
		$opt_val = array(
			'use_login_pin', 'view_multiple_store','current_date','is_cloud'
		);
		
		$get_opt = get_option_value($opt_val);
		
		$view_multiple_store = 0;
		$data_multiple_store = array();
		if(!empty($get_opt['view_multiple_store'])){
			$view_multiple_store = 1;
			$data_multiple_store = $this->m->get_masterstore();
		}
		
		$data['view_multiple_store'] = $view_multiple_store;
		$data['data_multiple_store'] = $data_multiple_store;
		
		$data['mkey'] = $mkey;
		if(empty($get_opt['is_cloud'])){
			$get_opt['is_cloud'] = 0; 
			$data['mkey'] = '';
		}else{
			$view_multiple_store = 0;
			$data_multiple_store = array();
		}
		$data['is_cloud'] = $get_opt['is_cloud'];
		
		//autodelete_print_monitoring
		$current_date = 0;
		if(!empty($get_opt['current_date'])){
			$current_date = $get_opt['current_date'];
		}
		
		$today_mktime = strtotime(date("d-m-Y"));
		if($current_date < $today_mktime){
			$update_opt = array('current_date' => $today_mktime);
			update_option($update_opt);
			$this->m->autodelete_print_monitoring();
		}
		
		if(!empty($get_opt['is_cloud'])){
			
			if($mkey == ''){
				$data['title']				=	'Merchant | '.config_item('program_name');
				$data['meta_description'] 	=	config_item('program_name');
				$data['meta_keywords']		=	config_item('program_name');
				$data['meta_author']		=	config_item('program_author');
				$data['program_name']		=	config_item('program_name');
				$this->load->view('info', $data);
			}else{
				
				$this->load->library('curl');
				$mktime_dc = strtotime(date("d-m-Y H:i:s"));
				$client_url = config_item('website').'/mkey-info?_dc='.$mktime_dc;
				
				$post_data = array(
					'merchant_key'	=> $mkey
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
				$curl_ret = $this->curl->execute();
				
				$ret_data = json_decode($curl_ret, true);
				
				
				$if_error = 0;
				if(!empty($ret_data['success'] === true)){
					$data['cloud_data'] = $ret_data['data'];
					if(!empty($get_opt['use_login_pin'])){
						$this->load->view('login-pin', $data);
					}else{
						$this->load->view('login', $data);
					}	
					
				}else{
					$if_error = 1;
				}
				
				if($if_error == 1){
					
					$data['title']				=	'Merchant | '.config_item('program_name');
					$data['meta_description'] 	=	config_item('program_name');
					$data['meta_keywords']		=	config_item('program_name');
					$data['meta_author']		=	config_item('program_author');
					$data['program_name']		=	config_item('program_name');
					$data['error'] 		= 'Merchant tidak dikenali / Salah Merchant Key!<br/>';
					$this->load->view('info', $data);
					
				}

			}
			
		}else{
			
			if(!empty($get_opt['use_login_pin'])){
				$this->load->view('login-pin', $data);
			}else{
				$this->load->view('login', $data);
			}	
			
		}
		//$this->output->enable_profiler(TRUE);
	}
		
	public function submit()
	{		
		$user_pin = $this->input->post('loginUsernamePin', true);
		$type_login = $this->input->post('type_login', true);
		$username = $this->input->post('loginUsername', true);
    	$password = $this->input->post('loginPassword', true);
		$view_multiple_store = $this->input->post('view_multiple_store', true);
		$store_data = $this->input->post('store_data', true);
		$mkey = $this->input->post('mkey', true);
		
		$conn_data = false;
		if(!empty($view_multiple_store) AND !empty($store_data) AND empty($mkey)){
			$store_data = explode("|", $store_data);
			$store_data[5] = $view_multiple_store;
			$tes_conn = @mysqli_connect($store_data[0].':'.$store_data[3], $store_data[1], $store_data[2], $store_data[4]);
			if (!$tes_conn) {
				$conn_data = false;
			}else{
				
				$this->db->close();
				
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
				$this->load->database($config);
					
				//$this->db->db_select($store_data[4]);
				$conn_data = true;
			}
			
		}
		
		if(!empty($mkey)){
			
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
			//$this->curl->option('SSLVERSION', 3);
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
						$view_multiple_store
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
					$this->load->database($config);
					
					//$this->db->db_select($store_data[4]);
					$conn_data = true;
					
				}
				
			}else{
				$conn_data = false;
			}
		}
		
		if(!empty($view_multiple_store) OR !empty($mkey)){
			$r = array();
			if ($conn_data == true){
				$r['success'] = true;
				$r['info'] = 'DB Connected';
				//$r['store_data'] = $store_data;
			}else{
				$r['success'] = false;
				$r['info'] = 'Connect DB Failed. Try Again!';
				$r['errors'] = array('reason'=>'Connect DB Failed. Try Again.');
				//$r['store_data'] = $store_data;
				die(json_encode($r));
			}
		}
		
		if($type_login == 'pin'){
			$r = $this->m->submit_pin($user_pin,$store_data, $mkey);
		}else{
			$r = $this->m->submit($username, $password, $store_data, $mkey);
		}
		
		if($r['count']==1)
        {
            $this->reg_session($r['data']);
			$r['success'] = true;
        }
        else
        {
            $r['success'] = false;
            $r['info'] = 'Login Failed. Try Again!';
			$r['errors'] = array('reason'=>'Login Failed. Try Again.');
        }
		
		die(json_encode($r));
	}
	
	public function logout()
	{
		
		$this->db->close();
		$is_cloud = $this->session->userdata('is_cloud');
		$this->unreg_session();
		
		if(!empty($is_cloud)){
			redirect('m/'.$is_cloud);
		}else{
			redirect('login');
		}
		
	}
	
	private function reg_session($d)
	{
			
		$opt_val = array(
			'timezone_default', 'view_multiple_store'
		);
		
		$get_opt = get_option_value($opt_val);
		$timezone_default = config_item('timezone_default');
		if(!empty($get_opt['timezone_default'])){
			$timezone_default = $get_opt['timezone_default'];
		}
		
		$data = array(
			'id_user'			=>	$d->id_user,
			'client_id'			=>	$d->client_id,
			'client_name'		=>	$d->client_name,
			'client_address'	=>	$d->client_address,
			'client_phone'		=>	$d->client_phone,
			'client_fax'		=>	$d->client_fax,
			'client_email'		=>	$d->client_email,
			'client_code'			=>	$d->client_code,
			'client_logo'			=>	$d->client_logo,
			'client_structure_id'	=>	$d->client_structure_id,
			'client_structure_name'	=>	$d->client_structure_name,
			'client_unit_id'		=>	$d->client_unit_id,
			'client_unit_name'	=>	$d->client_unit_name,
			'client_unit_code'	=>	$d->client_unit_code,
			'user_username'		=>	$d->user_username,
			'user_fullname'		=>	$d->user_fullname,
			'user_firstname'	=>	$d->user_firstname,
			'user_lastname'		=>	$d->user_lastname,
			'user_pin'			=>	$d->user_pin,
			'role_id'			=>	$d->role_id,
			'role_name'			=>	$d->role_name,
			'client_ip'			=>	$d->client_ip,
			'mysql_user'		=>	$d->mysql_user,
			'mysql_pass'		=>	$d->mysql_pass,
			'mysql_port'		=>	$d->mysql_port,
			'mysql_database'	=>	$d->mysql_database,
			'view_multiple_store'	=>	$d->view_multiple_store,
			'is_cloud'				=>	$d->is_cloud,
			'timezone_default'	=>	$timezone_default,
		);
		$this->session->set_userdata($data);
	}
	
	private function unreg_session()
	{
		$data = array(
			'id_user',
			'client_id',
			'client_name',
			'client_address',
			'client_phone',
			'client_fax',
			'client_email',
			'client_code',
			'client_logo',
			'client_structure_id',
			'client_structure_name',
			'client_unit_id',
			'client_unit_name',
			'client_unit_code',
			'user_username',
			'user_fullname',
			'user_firstname',
			'user_lastname',
			'user_pin',
			'role_id',
			'role_name',
			'client_ip',
			'mysql_user',
			'mysql_pass',
			'mysql_port',
			'mysql_database',
			'view_multiple_store',
			'is_cloud',
			'timezone_default'
		);
		$this->session->unset_userdata($data);
	}
	
}