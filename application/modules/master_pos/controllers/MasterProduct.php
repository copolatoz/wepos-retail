<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MasterProduct extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_masterproduct', 'm');
	}
	
	//important for service load
	function services_model_loader(){
		$this->prefix = config_item('db_prefix2');
		$dt_model = array( 'm' => '../../master_pos/models/model_masterproduct');
		return $dt_model;
	}

	public function gridData()
	{
		$this->table = $this->prefix.'product';
		$this->product_img_url = RESOURCES_URL.'product/thumb/';
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$category_id = $this->input->post('category_id');
		$keywords = $this->input->post('keywords');
		$is_active = $this->input->post('is_active');
		$by_code = $this->input->post('by_code');
		$product_type = $this->input->post('product_type');
		$from_module = $this->input->post('from_module');
		
		if(!empty($keywords)){
			$searching = $keywords;
		}
		
		
		//CHECK IN IMEI - item_kode_unik
		$selected_prodId = array();
		$selected_product_code = '';
		$selected_imei = '';
		$selected_varian = '';
		$this->db->select('a.item_id, a.kode_unik, a.varian_name, b.product_id');
		$this->db->from($this->prefix.'item_kode_unik as a');
		$this->db->join($this->prefix.'product_gramasi as b',"b.item_id = a.item_id","LEFT");
		$this->db->where("a.kode_unik = '".$searching."' AND (a.is_deleted = 0 AND a.is_active = 1) AND (a.ref_out IS NULL OR a.ref_out  = '') AND (b.is_active = 1 AND b.is_deleted = 0)");
		$get_imei = $this->db->get();
		if($get_imei->num_rows() > 0){
			$dt_imei = $get_imei->row();
			$selected_imei = $dt_imei->kode_unik;
			$selected_varian = $dt_imei->varian_name;
			
			//$selected_product_code = $dt_imei->product_code;
			//$searching = $dt_imei->product_code;
			
			foreach($get_imei->result() as $dtx){
				if(!in_array($dtx->product_id, $selected_prodId)){
					$selected_prodId[] = $dtx->product_id;
				}
			}
			
			//cek lgsg product
			$this->db->select('a.product_code, a.product_name, a.from_item, a.id_ref_item');
			$this->db->from($this->prefix.'product as a');
			$this->db->where("a.is_deleted = 0 AND a.is_active = 1");
			
			$selected_prodId_sql = '-1';
			if(!empty($selected_prodId)){
				$selected_prodId_sql = implode(",", $selected_prodId);
				$this->db->where("a.id IN (".$selected_prodId_sql.")");
			}
			$get_prod_imei = $this->db->get();
			if($get_prod_imei->num_rows() > 0){
				
				$primary_product = array();
				foreach($get_prod_imei->result() as $dtx){
					
					if(!empty($selected_varian)){
						$cek_varian = strstr($dtx->product_name, $selected_varian, true);
						if(!empty($cek_varian) AND empty($selected_product_code)){
							$selected_product_code = $dtx->product_code;
							$searching = $dtx->product_code;
						}
					}
					
					//get primary product
					if($dtx->from_item == 1 AND !empty($dtx->id_ref_item) AND empty($primary_product)){
						$primary_product =  $dtx;
					}
					
				}
				
				if(!empty($primary_product) AND empty($selected_product_code)){
					$selected_product_code = $primary_product->product_code;
					$searching = $primary_product->product_code;
				}
			}
			
		}
		
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'a.is_active',
			'has_varian_text' => 'a.has_varian'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*, b.product_category_name,  b.product_category_code, c.id as item_id, c.item_code, c.use_stok_kode_unik, d.unit_name, d.unit_code',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array(  
										array($this->prefix.'product_category as b','b.id = a.category_id','LEFT'),
										array($this->prefix.'items as c','c.id = a.id_ref_item','LEFT'),
										array($this->prefix.'unit as d','d.id = a.unit_id','LEFT'),
									) 
								),
			'where'			=> array('a.is_deleted' => 0),
			'order'			=> array('a.id' => 'DESC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		
		if(!empty($is_dropdown)){
			$params['order'] = array('a.product_desc' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(a.product_name LIKE '%".$searching."%' OR b.product_category_name LIKE '%".$searching."%' OR a.product_code LIKE '%".$searching."%' OR c.item_code LIKE '%".$searching."%')";
		}
		if(!empty($category_id)){
			$params['where'][] = "a.category_id = ".$category_id;
		}
		if(!empty($product_type)){
			$params['where'][] = "a.product_type = '".$product_type."'";
		}
		
		if(!empty($is_active)){
			
			if($is_active == 1){
				$params['where'][] = array('a.is_active' => 1);
			}
			
		}else{
			
			if(is_numeric($is_active)){
				$params['where'][] = array('a.is_active' => 0);
			}
			
		}
		
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		
		
		//cek opt
		$get_opt = get_option_value(array('hide_compliment_order'));
  		$hide_compliment_order = 0;
		if(!empty($get_opt['hide_compliment_order'])){
			$hide_compliment_order = 1;
		}
		
		//GET PROMO
		$dt_promo = array();
		$dt_promo_id = array();
		$promo_diskon_data_product = array();
		$dt_buyget = array();
		$dt_buyget_id = array();
		$promo_buyget_data_product = array();
		
		$this->db->select('*');
		$this->db->from($this->prefix.'discount');
		$this->db->where('(discount_type = 0 OR discount_type = 2) AND (is_promo = 1 OR is_buy_get = 1)');
		$this->db->where('is_active = 1');
		$this->db->where('is_deleted = 0');
		
		$today_date = date("Y-m-d H:i:s");
		$this->db->where("(discount_date_type = 'unlimited_date' OR (discount_date_type = 'limited_date' AND ('".$today_date."' BETWEEN date_start AND date_end)))");
			
		$get_promo = $this->db->get();
		
		$today_in_no = date("N");
		if($get_promo->num_rows() > 0){
			foreach($get_promo->result() as $dt){
				
				$allowed_promo = false;
				//check in day
				if($dt->discount_allow_day >= 1 AND $dt->discount_allow_day <= 7){
					if($today_in_no == $dt->discount_allow_day){
						$allowed_promo = true;
					}
				}else
				if($dt->discount_allow_day == 8){
					//weekday
					if($today_in_no >= 1 AND $today_in_no <= 5){
						$allowed_promo = true;
					}
				}else
				if($dt->discount_allow_day == 9){
					//weekend
					if($today_in_no >= 6 AND $today_in_no <= 7){
						$allowed_promo = true;
					}
				}else
				{
					//every day
					$allowed_promo = true;
				}
				
				if($allowed_promo){
					
					$allowed_time = true;
					if($dt->use_discount_time == 1){
						
						$allowed_time = false;
						
						if($dt->discount_time_end == '12:00 AM'){
							$dt->discount_time_end = '11:59 PM';
						}
						
						$time_from = date("d-m-Y")." ".$dt->discount_time_start;
						$time_till = date("d-m-Y")." ".$dt->discount_time_end;
						
						$time_from_mk = strtotime($time_from);
						$time_till_mk = strtotime($time_till);
						
						$time_now = strtotime(date("d-m-Y H:i:s"));
						
						
						
						if($time_now >= $time_from_mk AND $time_now <= $time_till_mk){
							$allowed_time = true;
						}
						
						//echo "allowed_time=".$allowed_time.", $time_from_mk=".$time_from_mk.", $time_till_mk=".$time_till_mk.", $time_now=".$time_now;
						//die();
						
					}
					
					if($allowed_time){
						if(!in_array($dt->id, $dt_promo_id) AND $dt->is_promo == 1){
							$dt_promo_id[] = $dt->id;
							$dt_promo[$dt->id] = $dt;
							
							if(empty($promo_diskon_data_product[$dt->id]) AND $dt->discount_type == 0){
								$promo_diskon_data_product[$dt->id] = array();
							}
							
						}
						if(!in_array($dt->id, $dt_buyget_id) AND $dt->is_buy_get == 1){
							$dt_buyget_id[] = $dt->id;
							$dt_buyget[$dt->id] = $dt;
							
							if(empty($promo_buyget_data_product[$dt->id])){
								$promo_buyget_data_product[$dt->id] = array();
							}
							
						}
					}
					
				}
				
			}
		}
		
		
		//DISKON PRODUCT
		$promo_diskon_product_id = array();
		$promo_diskon_product = array();
		$all_on_promo = false;
		$all_on_promo_id = 0;
		
		if(!empty($dt_promo_id)){
			$dt_promo_id_sql = implode(",", $dt_promo_id);
			$this->db->select('*');
			$this->db->from($this->prefix.'discount_product');
			$this->db->where('discount_id IN ('.$dt_promo_id_sql.')');
			$get_promo_diskon = $this->db->get();
			
			if($get_promo_diskon->num_rows() > 0){
				foreach($get_promo_diskon->result() as $dt){
					if(!in_array($dt->product_id, $promo_diskon_product_id)){
						$promo_diskon_product_id[] = $dt->product_id;
						$promo_diskon_product[$dt->product_id] = $dt->discount_id;
						
						$promo_diskon_data_product[$dt->discount_id][] = $dt->product_id;
						
					}
				}
				
			}
			
		}
		
		if(!empty($promo_diskon_data_product)){
			foreach($promo_diskon_data_product as $disc_id => $dt_prod){
				if(empty($dt_prod) AND $all_on_promo == false){
					//$all_on_promo = true;
					//$all_on_promo_id = $disc_id;
				}
			}
		}
		
		//echo '<pre>'.$all_on_promo.' == '.$all_on_promo_id;
		//print_r($promo_diskon_data_product);
		//die();
		
		//DISKON BUY & GET

		$promo_buyget_product_id = array();
		$promo_buyget_product = array();
		$data_buyget_product = array();
		
		if(!empty($dt_buyget_id)){
			$dt_buyget_id_sql = implode(",", $dt_buyget_id);
			$this->db->select('*');
			$this->db->from($this->prefix.'discount_buyget');
			$this->db->where('discount_id IN ('.$dt_buyget_id_sql.')');
			$get_promo_buyget = $this->db->get();
			
			if($get_promo_buyget->num_rows() > 0){
				foreach($get_promo_buyget->result() as $dt){
					if(!in_array($dt->buy_item, $promo_buyget_product_id)){
						$promo_buyget_product_id[] = $dt->buy_item;
						$promo_buyget_product[$dt->buy_item] = $dt->discount_id;
						$data_buyget_product[$dt->buy_item] = $dt;

					}
				}
			}
			
		}
		
		//echo 'promo_buyget_product_id = '.count($promo_buyget_product_id).'<pre>';
		//print_r($promo_buyget_product);
		//die();

		//OOO Menu/Product
		$this->db->select('*');
		$this->db->from($this->prefix.'ooo_menu');
		$this->db->where("tanggal = '".date("Y-m-d")."' AND is_deleted = 0");
		$get_ooo = $this->db->get();
		
		$ooo_menu = array();
		if($get_ooo->num_rows() > 0){
			foreach($get_ooo->result() as $dt){
				if(!in_array($dt->product_id, $ooo_menu)){
					$ooo_menu[] = $dt->product_id;
				}
			}
		}
		
		//CHECK PACKAGE ACTIVE
		$get_package_id = array();
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				if($s['product_type'] == 'package'){
					if(!in_array($s['id'], $get_package_id)){
						$get_package_id[] = $s['id'];
					}
				}
				
			}
		}
		
		if(!empty($get_package_id)){
			$get_package_id_sql = implode(",", $get_package_id);
			$this->db->select('a.*, b.is_deleted as product_deleted, b.is_active as product_active');
			$this->db->from($this->prefix.'product_package as a');
			$this->db->join($this->table.' as b',"b.id = a.product_id","LEFT");
			$this->db->where("package_id IN (".$get_package_id_sql.")");
			$get_package_detail = $this->db->get();
			if($get_package_detail->num_rows() > 0){
				foreach($get_package_detail->result() as $dt){
					
					if($dt->product_deleted == 1 OR $dt->product_active == 0 OR in_array($dt->product_id, $ooo_menu)){
						if(!in_array($dt->package_id, $ooo_menu)){
							$ooo_menu[] = $dt->package_id;
						}
						if(!in_array($dt->product_id, $ooo_menu)){
							$ooo_menu[] = $dt->product_id;
						}
					}
					
				}
			}
		}
		
		$allow_use_stok_kode_unik = array();
		$this->db->select('a.product_id');
		$this->db->from($this->prefix.'product_gramasi as a');
		$this->db->join($this->table.' as b',"b.id = a.product_id","LEFT");
		$this->db->join($this->prefix.'items as c','c.id = a.item_id','LEFT');
		$this->db->where("b.is_deleted = 0 AND (b.id_ref_item = 0 OR b.from_item = 0) AND c.use_stok_kode_unik = 1");
		$get_linkitem_detail = $this->db->get();
		if($get_linkitem_detail->num_rows() > 0){
			foreach($get_linkitem_detail->result() as $dt){
				
				if(!in_array($dt->product_id, $allow_use_stok_kode_unik)){
					$allow_use_stok_kode_unik[] = $dt->product_id;
				}
				
			}
		}
		
  		$newData = array();
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				if(empty($s['item_code'])){
					$s['item_code'] = $s['product_code'];
				}
				
				if(empty($s['product_image'])){
					$s['product_image'] = 'no-image.jpg';
				}
				if(empty($s['normal_price'])){
					$s['normal_price'] = $s['product_price'];
					$s['normal_price'] = $s['product_price'];
				}
				$s['product_image_show'] = '<img src="'.$this->product_img_url.$s['product_image'].'" style="max-width:80px; max-height:60px;"/>';
				$s['product_image_src'] = $this->product_img_url.$s['product_image'];
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				$s['has_varian_text'] = ($s['has_varian'] == '1') ? '<span style="color:green;">Ya</span>':'<span style="color:red;">Tidak</span>';
				$s['product_price_show'] = priceFormat($s['product_price']);
				$s['normal_price_show'] = priceFormat($s['normal_price']);
				$s['product_hpp_show'] = priceFormat($s['product_hpp']);
				
				$s['hide_compliment_order'] = $hide_compliment_order;
				$s['product_name_show'] = $s['product_name'];
				
				$s['product_id'] = $s['id'];
				$s['product_price_hpp'] = $s['product_hpp'];
				$s['product_normal_price'] = $s['normal_price'];
				
				//SET PROMO
				$s['promo_tipe'] = 0; //1 product, 2 buy and get
				$s['promo_id'] = 0;
				$s['is_promo'] = 0;
				$s['promo_percentage'] = 0;
				$s['promo_price'] = 0;
				$s['promo_desc'] = '';
				$no_promo = true;
				$usePromoID = 0;
				
				if(!empty($promo_diskon_product[$s['id']])){
					$usePromoID = $promo_diskon_product[$s['id']];
					$no_promo = false;
				}
				
				if($no_promo == true AND $all_on_promo){
					$usePromoID = $all_on_promo_id;
				}
				
				if(!empty($dt_promo[$usePromoID])){
					
					$s['promo_id'] = $usePromoID;
					
					$s['promo_tipe'] = 1;
					$s['is_promo'] = 1;
					$s['promo_percentage'] = $dt_promo[$usePromoID]->discount_percentage;
					$s['promo_desc'] = $dt_promo[$usePromoID]->discount_name;
					
					if($dt_promo[$usePromoID]->discount_percentage == '0.00' AND !empty($dt_promo[$usePromoID]->discount_price)){
						$s['promo_percentage'] = $dt_promo[$usePromoID]->discount_percentage;
						$promo_price = $dt_promo[$usePromoID]->discount_price;
					}else{
						$promo_price = ($s['product_price'] * ($s['promo_percentage']/100));
					}
					
					$product_price = $s['product_price'] - $promo_price;
					$s['product_price'] = $product_price;
					$s['promo_price'] = $promo_price;
					$s['promo_price_show'] = priceFormat($s['promo_price']);
					$s['product_name_show'] = $s['product_name'].' <font color="orange">Promo</font>';
					$s['product_price_show'] = '<strike>'.$s['product_price_show'].'</strike> <font color="orange">'.priceFormat($s['product_price']).'</font>';
					
				}	
				
				//BUY & GET
				$no_promo_BG = true;
				$usePromoID_BG = 0;
				if(!empty($promo_buyget_product[$s['id']])){
					$usePromoID_BG = $promo_buyget_product[$s['id']];
					$no_promo_BG = false;
				}
				
				$s['is_buyget'] = 0;
				$s['buyget_id'] = 0;
				$s['buyget_tipe'] = 0;
				$s['buyget_desc'] = 0;
				$s['buyget_buy_qty'] = 0;
				$s['buyget_qty'] = 0;
				$s['buyget_percentage'] = 0;
				$s['buyget_item'] = 0;
				if(!empty($dt_buyget[$usePromoID_BG]) AND !empty($data_buyget_product[$s['id']]) AND $s['promo_id'] == 0){
					$s['product_name_show'] = $s['product_name'].' <font color="red">BG</font>';
					$s['is_buyget'] = 1;
					$s['buyget_id'] = $dt_buyget[$usePromoID_BG]->id;
					$s['buyget_tipe'] = $data_buyget_product[$s['id']]->buyget_tipe;
					$s['buyget_desc'] = $dt_buyget[$usePromoID_BG]->discount_name;
					$s['buyget_buy_qty'] = $data_buyget_product[$s['id']]->buy_qty;
					$s['buyget_qty'] = $data_buyget_product[$s['id']]->get_qty;
					$s['buyget_percentage'] = numberFormat($data_buyget_product[$s['id']]->get_percentage,2);
					$s['buyget_item'] = $data_buyget_product[$s['id']]->get_item;
				}

				$s['is_kerjasama_text'] = ($s['is_kerjasama'] == '1') ? '<span style="color:green;">Yes</span>':'<span style="color:red;">No</span>';
				$s['total_bagi_hasil_show'] = priceFormat($s['total_bagi_hasil']);
				
				$allow_prod = true;
				if($from_module == 'cashier' AND in_array($s['id'], $ooo_menu)){
					$allow_prod = false;
				}
				
				$s['data_stok_kode_unik'] = '';
				if(in_array($s['id'], $allow_use_stok_kode_unik)){
					$s['use_stok_kode_unik'] = 1;
				}
				
				if(!empty($selected_product_code) AND $selected_product_code == $s['product_code']){
					$s['data_stok_kode_unik'] = $selected_imei;
				}
				
				if($allow_prod == true){
					array_push($newData, $s);
				}
				
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	public function gridDataReservation()
	{
		$this->table = $this->prefix.'product';
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'a.is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*, b.product_category_name, b.product_category_code, c.id as item_id, c.item_code',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'product_category as b','b.id = a.category_id','LEFT'),
										array($this->prefix.'items as c','c.id = a.id_ref_item','LEFT')
									) 
								),
			'where'			=> array('a.is_deleted' => 0),
			'order'			=> array('a.id' => 'DESC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$category_id = $this->input->post('category_id');
		$keywords = $this->input->post('keywords');
		$is_active = $this->input->post('is_active');
		$show_all_text = $this->input->post('show_all_text');
		$show_choose_text = $this->input->post('show_choose_text');
		if(!empty($keywords)){
			$searching = trim($keywords);
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('a.product_desc' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(a.product_name LIKE '%".$searching."%' OR b.product_category_name LIKE '%".$searching."%' OR a.product_code LIKE '%".$searching."%' OR c.item_code LIKE '%".$searching."%')";
		}
		if(!empty($category_id)){
			$params['where'][] = "a.category_id = ".$category_id;
		}
		
		if(!empty($is_active)){
			
			if($is_active == 1){
				$params['where'][] = array('a.is_active' => 1);
			}
			
		}else{
			
			if(is_numeric($is_active)){
				$params['where'][] = array('a.is_active' => 0);
			}
			
		}
		
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		
  		$newData = array();
		
		if(!empty($show_all_text)){
			$dt = array('id' => '-1', 'product_name' => 'Pilih Semua');
			array_push($newData, $dt);
		}else{
			if(!empty($show_choose_text)){
				$dt = array('id' => '', 'product_name' => 'Pilih Menu/Product');
				array_push($newData, $dt);
			}
		}
		
		//get option tax and service
		$opt_var = array('include_tax','include_service',
		'default_tax_percentage','default_service_percentage',
		'takeaway_no_tax','takeaway_no_service','autohold_create_billing');
		$get_opt = get_option_value($opt_var);
		$include_tax = 0;
		if(!empty($get_opt['include_tax'])){
			$include_tax = $get_opt['include_tax'];
		}
		
		$include_service = 0;
		if(!empty($get_opt['include_service'])){
			$include_service = $get_opt['include_service'];
		}
		
		$default_tax_percentage = 10;
		if(!empty($get_opt['default_tax_percentage'])){
			$default_tax_percentage = $get_opt['default_tax_percentage'];
		}		
		
		$default_service_percentage = 5;
		if(!empty($get_opt['default_service_percentage'])){
			$default_service_percentage = $get_opt['default_service_percentage'];
		}	
		
		//OOO Menu/Product
		$this->db->select('*');
		$this->db->from($this->prefix.'ooo_menu');
		$this->db->where("tanggal = '".date("Y-m-d")."' AND is_deleted = 0");
		$get_ooo = $this->db->get();
		
		$ooo_menu = array();
		if($get_ooo->num_rows() > 0){
			foreach($get_ooo->result() as $dt){
				if(!in_array($dt->product_id, $ooo_menu)){
					$ooo_menu[] = $dt->product_id;
				}
			}
		}
		
		//CHECK PACKAGE ACTIVE
		$get_package_id = array();
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				if($s['product_type'] == 'package'){
					if(!in_array($s['id'], $get_package_id)){
						$get_package_id[] = $s['id'];
					}
				}
				
			}
		}
		
		if(!empty($get_package_id)){
			$get_package_id_sql = implode(",", $get_package_id);
			$this->db->select('a.*, b.is_deleted as product_deleted, b.is_active as product_active');
			$this->db->from($this->prefix.'product_package as a');
			$this->db->join($this->table.' as b',"b.id = a.product_id","LEFT");
			$this->db->where("package_id IN (".$get_package_id_sql.")");
			$get_package_detail = $this->db->get();
			if($get_package_detail->num_rows() > 0){
				foreach($get_package_detail->result() as $dt){
					
					if($dt->product_deleted == 1 OR $dt->product_active == 0 OR in_array($dt->product_id, $ooo_menu)){
						if(!in_array($dt->package_id, $ooo_menu)){
							$ooo_menu[] = $dt->package_id;
						}
						if(!in_array($dt->product_id, $ooo_menu)){
							$ooo_menu[] = $dt->product_id;
						}
					}
					
				}
			}
		}
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				if(empty($s['normal_price'])){
					$s['normal_price'] = $s['product_price'];
					$s['normal_price'] = $s['product_price'];
				}
				
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				$s['has_varian_text'] = ($s['has_varian'] == '1') ? '<span style="color:green;">Ya</span>':'<span style="color:red;">Tidak</span>';
				$s['product_price_show'] = priceFormat($s['product_price']);
				$s['normal_price_show'] = priceFormat($s['normal_price']);
				
				$s['product_name_show'] = $s['product_name'];
				$s['product_id'] = $s['id'];
				$s['product_price_hpp'] = $s['product_hpp'];
				$s['product_normal_price'] = $s['normal_price'];
				
				$s['total_bagi_hasil_show'] = priceFormat($s['total_bagi_hasil']);
				
				$product_price = $s['product_price'];
				
				//TAX, SERVICE, TAKE AWAY & COMPLIMENT
				$include_tax = $include_tax;
				$include_service = $include_service;
				$tax_percentage = $default_tax_percentage;
				$service_percentage = $default_service_percentage;
				
				$tax_total = 0;
				$service_total = 0;
				$product_price_real = 0;
				if(!empty($include_tax) OR !empty($include_service)){
					if(!empty($include_tax) AND !empty($include_service)){
						$all_percentage = 100 + $tax_percentage + $service_percentage;
						$one_percent = $product_price / $all_percentage;
						$tax_total = priceFormat($one_percent * $tax_percentage, 0, ".", "");
						$service_total = priceFormat($one_percent * $service_percentage, 0, ".", "");
						$product_price_real = $product_price - ($tax_total + $service_total);
						
						$tax_percent = $tax_percentage/100;
						$service_percent = $service_percentage/100;
						$tax_total = priceFormat($product_price_real * $tax_percent, 0, ".", "");
						$service_total = priceFormat($product_price_real * $service_percent, 0, ".", "");
					
					}else{
						if(!empty($include_tax)){
							$all_percentage = 100 + $tax_percentage;
							$one_percent = $product_price / $all_percentage;
							$tax_total = priceFormat($one_percent * $tax_percentage, 0, ".", "");
							$product_price_real = $product_price - ($tax_total);
							
							$tax_percent = $tax_percentage/100;
							$tax_total = priceFormat($product_price_real * $tax_percent, 0, ".", "");
							
						}
						
						if(!empty($include_service)){
							$all_percentage = 100 + $service_percentage;
							$one_percent = $product_price / $all_percentage;
							$service_total = priceFormat($one_percent * $service_percentage, 0, ".", "");
							$product_price_real = $product_price - ($service_total);
							
							$service_percent = $service_percentage/100;
							$service_total = priceFormat($product_price_real * $service_percent, 0, ".", "");
							
						}
						
					}
				}else
				{
					$product_price_real = $product_price;
					$tax_percent = $tax_percentage/100;
					$service_percent = $service_percentage/100;
					$tax_total = priceFormat($product_price* $tax_percent, 0, ".", "");
					$service_total = priceFormat($product_price* $service_percent, 0, ".", "");
				}
				
				$s['tax_price'] = $tax_total;
				$s['service_price'] = $service_total;
				
				$allow_prod = true;
				if(in_array($s['id'], $ooo_menu)){
					$allow_prod = false;
				}
				
				if($allow_prod == true){
					array_push($newData, $s);
				}
				
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
		
	}
	
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'product';				
		$this->table2 = $this->prefix.'product_package';				
		$this->table_gramasi = $this->prefix.'product_gramasi';				
		$this->table_product_varian = $this->prefix.'product_varian';				
		$this->table_items = $this->prefix.'items';				
		$session_user = $this->session->userdata('user_username');
		
		$product_code = $this->input->post('product_code');
		$product_category_code = $this->input->post('product_category_code');
		if($product_code == '- AUTO -'){
			$product_code = '';
		}
		
		$product_name = $this->input->post('product_name');
		$product_chinese_name = $this->input->post('product_chinese_name');
		$product_desc = $this->input->post('product_desc');
		$product_price = $this->input->post('product_price');
		$normal_price = $this->input->post('normal_price');
		$product_hpp = $this->input->post('product_hpp');
		$category_id = $this->input->post('category_id');
		$unit_id = $this->input->post('unit_id');
		$product_type = $this->input->post('product_type');
		$old_product_type = $this->input->post('old_product_type');
		$product_image = $this->input->post('product_image');
		$product_group = $this->input->post('product_group');
		//$use_tax = $this->input->post('use_tax');
		//$use_service = $this->input->post('use_service');
		$tipe = $this->input->post('tipe');
		$from_item = $this->input->post('from_item');
		$id_ref_item = $this->input->post('id_ref_item');
		
		/*CONTENT IMAGE UPLOAD SIZE*/		
		$this->product_img_url = RESOURCES_URL.'product/';		
		$this->product_img_path_big = RESOURCES_PATH.'product/big/';
		$this->product_img_path_thumb = RESOURCES_PATH.'product/thumb/';
		$this->product_img_path_tiny = RESOURCES_PATH.'product/tiny/';
		
		$big_size_width = 1024;
		$big_size_height = 768;
		$thumb_size_width = 375;
		$thumb_size_height = 250;
		$tiny_size_width = 160;
		$tiny_size_height = 120;
		
		$opt_var = array('big_size_width','big_size_height','big_size_real',
		'thumb_size_width','thumb_size_height',
		'tiny_size_width','tiny_size_height');
		$get_opt = get_option_value($opt_var);
		
		$big_size_real = 0;
		if(!empty($get_opt['big_size_real'])){
			$big_size_real = $get_opt['big_size_real'];
		}
		if(!empty($get_opt['big_size_width'])){
			$big_size_width = $get_opt['big_size_width'];
		}
		if(!empty($get_opt['big_size_height'])){
			$big_size_height = $get_opt['big_size_height'];
		}
		if(!empty($get_opt['thumb_size_width'])){
			$thumb_size_width = $get_opt['thumb_size_width'];
		}
		if(!empty($get_opt['thumb_size_height'])){
			$thumb_size_height = $get_opt['thumb_size_height'];
		}
		if(!empty($get_opt['tiny_size_width'])){
			$tiny_size_width = $get_opt['tiny_size_width'];
		}
		if(!empty($get_opt['tiny_size_height'])){
			$tiny_size_height = $get_opt['tiny_size_height'];
		}
		
		
		$is_upload_file = false;		
		if(!empty($_FILES['upload_image']['name'])){
						
			$config['upload_path'] = $this->product_img_path_big;
			$config['allowed_types'] = 'gif|jpg|png';
			$config['max_size']	= '1024000';

			$this->load->library('upload', $config);

			if(!$this->upload->do_upload("upload_image"))
			{
				$data = $this->upload->display_errors();
				$r = array('success' => false, 'info' => $data );
				die(json_encode($r));
			}
			else
			{
				$is_upload_file = true;
				$data_upload_temp = $this->upload->data();
				$r = array('success' => true, 'info' => $data_upload_temp); 
			}
		}
		
		
		if(empty($product_name)){
			$r = array('success' => false);
			die(json_encode($r));
		}		
		
		$is_active = $this->input->post('is_active');
		if(empty($is_active)){
			$is_active = 0;
		}
		
		$use_tax = $this->input->post('use_tax');
		if(empty($use_tax)){
			$use_tax = 0;
		}
		
		$use_service = $this->input->post('use_service');
		if(empty($use_service)){
			$use_service = 0;
		}
		
		$has_varian = $this->input->post('has_varian');
		if(empty($has_varian)){
			$has_varian = 0;
		}
		
		if(empty($normal_price)){
			$normal_price = $product_price;
		}
			
		$r = '';
		if($this->input->post('form_type_masterProduct', true) == 'add')
		{
			$get_product_code = array('product_code' => '', 'product_no' => 1);
			$product_no = 1;
			
			$this->db->select("id");
			$this->db->from($this->table);
			$this->db->order_by("id", "DESC");
			$this->db->limit("1");
			$get_last_no = $this->db->get();
			if($get_last_no->num_rows() > 0){
				$get_last_db = $get_last_no->row();
				$product_no = $get_last_db->id;
				$product_no++;
			}
			
			if(empty($product_code)){
				
				//cek item code
				$get_product_code = $this->generate_product_code($tipe);
				$product_code = $get_product_code['product_code'];
				$product_no = $get_product_code['product_no'];
				
				if($product_type == 'package'){
					$product_code = 'PKT-'.$product_code;
				}else{
					$product_code = 'SKU-'.$product_code;
				}
				
			}
				
			$this->db->from($this->table);
			$this->db->where("product_code = '".$product_code."'");
			$this->db->where("is_deleted = 0");
			$get_last = $this->db->get();
			if($get_last->num_rows() > 0){
				
				//available
				$r = array('success' => false, 'info' => 'Kode sudah digunakan!'); 
				
				//suggestion
				if(!empty($product_category_code)){
					$get_product_code = $this->generate_product_code($tipe);
					$r = array('success' => false, 'info' => 'Kode sudah digunakan!<br/>Coba Kode Berikut: '.$get_product_code['product_code']); 
				}
				die(json_encode($r));
		
			}
			
			$var = array(
				'fields'	=>	array(
				    'product_code' => 	$product_code,
				    'product_no' => 	$product_no,
				    'product_name'  => 	$product_name,
				    'product_chinese_name'  => 	$product_chinese_name,
					'product_desc'	=>	$product_desc,
					'product_price'	=>	$product_price,
					'normal_price'	=>	$normal_price,
					'product_hpp'	=>	$product_hpp,
					'product_type'	=>	$product_type,
					'product_group'	=>	$product_group,
					'use_tax'		=>	$use_tax,
					'use_service'	=>	$use_service,
					'has_varian'	=>	$has_varian,
					'category_id'	=>	$category_id,
					'unit_id'		=>	$unit_id,
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'	=>	$is_active
				),
				'table'		=>  $this->table
			);				
			
			
			if($is_upload_file){
				
				if(!empty($big_size_real)){
					$var['fields']['product_image'] = $data_upload_temp['file_name'];
				}else{
					$get_file = do_thumb($data_upload_temp, $this->product_img_path_big, $this->product_img_path_big, '', $big_size_width, $big_size_height, TRUE, 'height');
					$var['fields']['product_image'] = $get_file;
				}
				
				
				
			}
			
			//SAVE
			$insert_id = false;
			$this->lib_trans->begin();
				$q = $this->m->add($var);
				$insert_id = $this->m->get_insert_id();
			$this->lib_trans->commit();			
			if($q)
			{  
				if($is_upload_file){					
					//thumb width 
					do_thumb($data_upload_temp, $this->product_img_path_big, $this->product_img_path_thumb, '', $thumb_size_width, $thumb_size_height, TRUE, 'height');
					
					//tiny
					do_thumb($data_upload_temp, $this->product_img_path_big, $this->product_img_path_tiny, '', $tiny_size_width, $tiny_size_height, TRUE, 'height');
				}
				
				$this->m->update_sales_price($insert_id);
				
				$r = array('success' => true, 'id' => $insert_id); 				
			}  
			else
			{  
				if($is_upload_file){
					//unset upload file
					@unlink($this->product_img_path_big.$data_upload_temp['file_name']);
					
				}
				
				$r = array('success' => false);
			}
      		
		}else
		if($this->input->post('form_type_masterProduct', true) == 'edit'){
			
			$set_code = false;
			if(empty($product_code)){
				
				$r = array('success' => false, 'info' => 'Kode Produk tidak boleh kosong!'); 
				die(json_encode($r));
				/*
				//cek item code
				$get_product_code = $this->generate_product_code($tipe);
				$product_code = $get_product_code['product_code'];
			
				$this->db->from($this->table);
				$this->db->where("product_code = '".$product_code."'");
				$this->db->where("is_deleted = 0");
				$get_last = $this->db->get();
				if($get_last->num_rows() > 0){
					
					$r = array('success' => false, 'info' => 'Kode Produk tidak boleh kosong!'); 
					die(json_encode($r));
			
				}else{
					$get_product_code['product_code'] = $product_code;
					$get_product_code['product_no'] = 1;
				}
				
				$set_code = false;
				*/
				
			}
			
			$var = array('fields'	=>	array(
					'product_name'	=>	$product_name,
					'product_desc'	=>	$product_desc,
					'product_price'	=>	$product_price,
					'normal_price'	=>	$normal_price,
					'use_tax'		=>	$use_tax,
					'use_service'	=>	$use_service,
					'has_varian'	=>	$has_varian,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'		=>	$is_active
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);
			
			if(!empty($from_item) AND !empty($id_ref_item)){
				
			}else{
				$var['fields']['category_id'] = $category_id;
				$var['fields']['unit_id'] = $unit_id;
				$var['fields']['product_code'] = $product_code;
				$var['fields']['product_type'] = $product_type;
			}
			
			if($set_code){
				$var['fields']['product_code'] = $product_code;
			}
						
			if($is_upload_file){
				
				if(!empty($big_size_real)){
					$var['fields']['product_image'] = $data_upload_temp['file_name'];
				}else{
					$get_file = do_thumb($data_upload_temp, $this->product_img_path_big, $this->product_img_path_big, '', $big_size_width, $big_size_height, TRUE, 'height');
					$var['fields']['product_image'] = $get_file;
				}
				
			}
			
			$id = $this->input->post('id', true);
			
			//check varian
			if(empty($has_varian)){
				$this->db->select("*");
				$this->db->from($this->table_product_varian);
				$this->db->where("product_id = ".$id." AND is_deleted = 0");
				$dt_varian = $this->db->get();
				if($dt_varian->num_rows() > 0){
					$var['fields']['has_varian'] = 1;
				}
			}
			
			//UPDATE
			$this->lib_trans->begin();
				$update = $this->m->save($var, $id);
			$this->lib_trans->commit();
			
			if($update)
			{  
				if(!empty($from_item) AND !empty($id_ref_item)){
					$update_data = array(
						"item_name" => $product_name, 
						"sales_price" => $product_price, 
						"sales_use_tax" => $use_tax
					);
					$update_items = $this->db->update($this->table_items,$update_data,"id = ".$id_ref_item);
				}
				
				if($old_product_type == 'package' AND $old_product_type != $product_type){	
					//remove all package item					
					$delete_data = array("is_deleted" => 0);
					$del_package = $this->db->update($this->table2,$delete_data,"package_id = ".$id);
				}
				
				if($old_product_type == 'item' AND $old_product_type != $product_type){	
					//remove all gramasi item					
					$delete_data = array("is_deleted" => 0);
					$del_package = $this->db->update($this->table_gramasi,$delete_data,"product_id = ".$id);
				}
				
				if($is_upload_file){					
					//thumb width 200pixel
					do_thumb($data_upload_temp, $this->product_img_path_big, $this->product_img_path_thumb, '', $thumb_size_width, $thumb_size_height, TRUE, 'height');
					
					//tiny
					do_thumb($data_upload_temp, $this->product_img_path_big, $this->product_img_path_tiny, '', $tiny_size_width, $tiny_size_height, TRUE, 'height');
					
					//unset old file
					if(!empty($product_image) AND $product_image != 'no-image.jpg'){
						@unlink($this->product_img_path_big.$product_image);
						@unlink($this->product_img_path_thumb.$product_image);
						@unlink($this->product_img_path_tiny.$product_image);
					}
				}
				
				$this->m->update_sales_price($id);
				
				$r = array('success' => true, 'id' => $id);
			}  
			else
			{  
				if($is_upload_file){
					//unset upload file
					@unlink($this->product_img_path_big.$data_upload_temp['file_name']);					
				}
				
				$r = array('success' => false);
			}
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
	public function delete()
	{
		$this->table = $this->prefix.'product';
		$this->table2 = $this->prefix.'product_package';
		$this->product_img_path_big = RESOURCES_PATH.'product/big/';
		$this->product_img_path_thumb = RESOURCES_PATH.'product/thumb/';
		$this->product_img_path_tiny = RESOURCES_PATH.'product/tiny/';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode(',', $id);
		}
		
		//Delete
		$this->db->where("id IN (".$sql_Id.")");
		$get_product = $this->db->get($this->table);
		
		$data_update = array(
			"is_deleted" => 1
		);
		$q = $this->db->update($this->table, $data_update, "id IN (".$sql_Id.")");
		
		$r = '';
		if($q)  
        {  
			if($get_product->num_rows() > 0){
							
				$all_product_package = array();
				
				foreach($get_product->result() as $dtP){
					if(!empty($dtP->product_image)){
						@unlink($this->product_img_path_big.$dtP->product_image);
						@unlink($this->product_img_path_thumb.$dtP->product_image);
						@unlink($this->product_img_path_tiny.$dtP->product_image);
					}
					
					if($dtP->product_type == 'package'){
						if(!in_array($dtP->product_id, $all_product_package)){
							$all_product_package[] = $dtP->product_id;
						}
					}
					
				}
				
				if(!empty($all_product_package)){		
					$all_product_package_txt = implode(",", $all_product_package);
					$del_package = $this->db->update($this->table2, $data_update, "package_id IN (".$all_product_package_txt.") OR product_id IN (".$all_product_package_txt.")");
				}
			}
            $r = array('success' => true); 
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Product Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function updateCode(){
		$this->table = $this->prefix.'product';	
		
		$id = $this->input->post('id');
		$product_code = $this->input->post('product_code');
		$product_category_code = $this->input->post('product_category_code');
		$tipe = $this->input->post('tipe');
		
		$r = array('success' => false, 'info' => 'Update Code Failed!'); 
		if(empty($id) OR empty($product_code) OR empty($tipe)){
			die(json_encode($r));
		}
		
		$this->db->from($this->table);
		$this->db->where("product_code = '".$product_code."' AND id != ".$id);
		$this->db->where("is_deleted = 0");
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			
			//available
			$r = array('success' => false, 'info' => 'Kode sudah digunakan!'); 
			
			//suggestion
			if(!empty($product_category_code)){
				$get_product_code = $this->generate_product_code($tipe);
				$r = array('success' => false, 'info' => 'Kode sudah digunakan!<br/>Coba Kode Berikut: '.$get_product_code['product_code']); 
			}
	
		}else{
			
			$product_code_format = '{Cat}-{ItemNo}';
			$product_no_length = 5;
			
			$repl_attr = array(
				"{Cat}"		=> $product_category_code
			);
			
			$product_code_format = strtr($product_code_format, $repl_attr);
			$get_exp = explode("{ItemNo}", $product_code_format);
			$first_format = '';
			$product_no = 0;
			if(!empty($get_exp[0])){
				$first_format = $get_exp[0];
				$first_format_length_code = strlen($first_format);
				//$get_product_no = substr($product_code, $first_format_length_code, $product_no_length);
				$get_product_no = substr($product_code, $product_no_length*-1);
				$product_no = (int) $get_product_no;
			}
			//update 
			$update_code = array('product_code' => $product_code,'product_no' => $product_no);
			
			$this->db->update($this->table, $update_code, "id = ".$id);
			$r = array('success' => true, 'product_code' => $product_code, 'product_no' => $product_no);
			
		}
		
		die(json_encode($r));
	}
	
	public function generate_product_code($tipe = ''){
		
		$this->table = $this->prefix.'product';		

		$getDate = date("ym");
		
		$product_category_code = $this->input->post('product_category_code');
		
		$product_name = $this->input->post('product_name');
		$product_sku = $this->input->post('product_sku');
		
		$opt_value = array(
			'product_code_format',
			'product_code_separator',
			'product_no_length',
			'product_sku_from_code'
		);
		
		$get_opt = get_option_value($opt_value);
		
		$product_code_format = '{Cat}-{ItemNo}';
		$product_no_length = 5;
		
		$repl_attr = array(
			"{SKU}"		=> $product_sku,
			"{Cat}"		=> $product_category_code
		);
		
		$product_code_format = strtr($product_code_format, $repl_attr);
		$get_exp = explode("{ItemNo}", $product_code_format);
		$first_format = '';
		if(!empty($get_exp[0])){
			$first_format = $get_exp[0];
			
			$this->db->from($this->table);
			$this->db->where("product_code LIKE '".$first_format."%' AND product_name = '".$product_name."'");
			$this->db->where("is_deleted = 0");
			$this->db->order_by('product_no', 'DESC');
			$this->db->order_by('product_code', 'DESC');
			$get_last = $this->db->get();
			if($get_last->num_rows() > 0){
				$data_product_code = $get_last->row();
				$first_format_length_code = strlen($first_format);
				$product_code = substr($data_product_code->product_code, $first_format_length_code, $product_no_length);
				$product_no = (int) $product_code;
				
				if(!empty($data_product_code->product_no)){
					$product_no = $data_product_code->product_no;
				}	
				$product_no++;	
		
			}else{
				
				$this->db->from($this->table);
				$this->db->where("product_code LIKE '".$first_format."%'");
				$this->db->where("is_deleted = 0");
				$this->db->order_by('product_no', 'DESC');
				$this->db->order_by('product_code', 'DESC');
				$get_last = $this->db->get();
				if($get_last->num_rows() > 0){
					$data_product_code = $get_last->row();
					$first_format_length_code = strlen($first_format);
					$product_code = substr($data_product_code->product_code, $first_format_length_code, $product_no_length);
					$product_no = (int) $product_code;
				
					if(!empty($data_product_code->product_no)){
						$product_no = $data_product_code->product_no;
					}		
					
				}else{
					$product_no = 0;
				}
				
				$product_no++;
			
			}
			
			$length_no = strlen($product_no);
			if($length_no <= $product_no_length){
				$gapTxt = $product_no_length - $length_no;
				$product_code = str_repeat("0", $gapTxt).$product_no;
			}
			
			$repl_attr = array(
				"{ItemNo}"		=> $product_code
			);
			
			$product_code_format = strtr($product_code_format, $repl_attr);
		
			
		}else
		{
			$this->db->from($this->table);
			$this->db->where("is_deleted = 0");
			$this->db->order_by('product_no', 'DESC');
			$this->db->order_by('product_code', 'DESC');
			$get_last = $this->db->get();
			if($get_last->num_rows() > 0){
				$data_product_code = $get_last->row();
				//$product_code = substr($data_product_code->product_code, 0, $product_no_length);
				$product_code = substr($data_product_code->product_code, $product_no_length*-1);
				$product_no = (int) $product_code;	
					
				if(!empty($data_product_code->product_no)){
					$product_no = $data_product_code->product_no;
				}				
			}else{
				$product_no = 0;
			}
			
			$product_no++;
			$length_no = strlen($product_no);
			if($length_no <= $product_no_length){
				$gapTxt = $product_no_length - $length_no;
				$product_code = str_repeat("0", $gapTxt).$product_no;
			}
		}
		
		$repl_productno = array(
			"{ItemNo}"		=> $product_code
		);
		
		$product_code = strtr($product_code_format, $repl_productno);	
		
		return array('product_no' => $product_no, 'product_code' => $product_code);				
	}
	
	public function importDataProduct()

	{
		$this->table = $this->prefix.'product';		
		$session_user = $this->session->userdata('user_username');
		
		$this->file_harga_menu_path = RESOURCES_PATH.'harga_menu/';
		
		$r = ''; 
		$is_upload_file = false;		
		if(!empty($_FILES['upload_file']['name'])){
						
			$config['upload_path'] = $this->file_harga_menu_path;
			$config['allowed_types'] = 'xls';
			$config['max_size']	= '1024';

			$this->load->library('upload', $config);

			if(!$this->upload->do_upload("upload_file"))
			{
				$data = $this->upload->display_errors();
				$r = array('success' => false, 'info' => $data );
				die(json_encode($r));
			}
			else
			{
				$is_upload_file = true;
				$data_upload_temp = $this->upload->data();
				
				
				// Load the spreadsheet reader library
				$this->load->library('spreadsheet_Excel_Reader');
				$xls = new Spreadsheet_Excel_Reader();
				$xls->setOutputEncoding('CP1251'); 
				$file =  $this->file_harga_menu_path.$data_upload_temp['file_name']."" ;
				$xls->read($file);
				//echo '<pre>';
				//print_r($xls->sheets);die();
				
				error_reporting(E_ALL ^ E_NOTICE);
				
				$nr_sheets = count($xls->sheets);    
				
				$this->lib_trans->begin();
				
				 
				
				//cek all menu available
				$available_id = array();
				$this->db->from($this->table);
				$get_product = $this->db->get();
				if($get_product->num_rows() > 0){
					foreach($get_product->result_array() as $dt){
						if(!in_array($dt['id'], $available_id)){
							$available_id[] = $dt['id'];
						}
					}
				}
				
				$all_new_data = array();
				$all_new_data_with_id = array();
				$all_update_data = array();
				$all_new_id = array();
				$all_update_id = array();
				for($i=0; $i<$nr_sheets; $i++) {
					//echo $xls->boundsheets[$i]['name'];
					//print_r($xls->sheets[$i]);
					
					for ($row_num = 2; $row_num <= $xls->sheets[$i]['numRows']; $row_num++) {	
						
						//echo '<pre>';
						//print_r($xls->sheets[$i]['cells'][$row_num]);
						//die();
						
						$id = $xls->sheets[$i]['cells'][$row_num][1];									
						$product_code = $xls->sheets[$i]['cells'][$row_num][2];	
						$product_name = $xls->sheets[$i]['cells'][$row_num][3];								
						$product_desc = $xls->sheets[$i]['cells'][$row_num][4];																
						$normal_price = $xls->sheets[$i]['cells'][$row_num][5];								
						$product_price = $xls->sheets[$i]['cells'][$row_num][6];														
						$product_hpp = $xls->sheets[$i]['cells'][$row_num][7];							
						$product_type = $xls->sheets[$i]['cells'][$row_num][8];									
						$product_group = $xls->sheets[$i]['cells'][$row_num][9];									
						$category_id = $xls->sheets[$i]['cells'][$row_num][10];		
						$use_tax = $xls->sheets[$i]['cells'][$row_num][11];		
						$use_service = $xls->sheets[$i]['cells'][$row_num][12];		
						$is_active = $xls->sheets[$i]['cells'][$row_num][13];		
						
						if(empty($product_type)){
							$product_type = 'item';							
						}
						
						if(empty($product_group)){
							$product_group = 'food';							
						}
						
						if(empty($is_active)){
							$is_active = 0;
						}
						if(empty($normal_price)){
							$normal_price = 0;
						}
						if(empty($product_price)){
							$product_price = 0;
						}
						if(empty($product_hpp)){
							$product_hpp = 0;
						}
						
						$update_date = date('Y-m-d H:i:s');
						
						if(!empty($product_name)){
							if(empty($id)){
								//INSERT									
								
								/*$var = array(
									'fields'	=>	array(
										'product_name'	=> 	$product_name,
										'product_desc'	=>	$product_desc,
										'product_price'	=>	$product_price,
										'normal_price'	=>	$normal_price,
										'product_hpp'	=>	$product_hpp,
										'product_type'	=>	$product_type,
										'product_group' =>	$product_group,
										'use_tax'		=>	$use_tax,
										'use_service'	=>	$use_service,
										'category_id'	=>	$category_id,
										'created'		=>	$update_date,
										'createdby'		=>	$session_user,
										'updated'		=>	$update_date,
										'updatedby'		=>	$session_user,
									),
									'table'		=>  $this->table
								);	
								
								$q = $this->m->save($var);
								*/
								
								$all_new_data[] = array(
										'product_name'	=> 	$product_name,
										'product_desc'	=>	$product_desc,
										'product_price'	=>	$product_price,
										'normal_price'	=>	$normal_price,
										'product_hpp'	=>	$product_hpp,
										'product_type'	=>	$product_type,
										'product_group' =>	$product_group,
										'use_tax'		=>	$use_tax,
										'use_service'	=>	$use_service,
										'category_id'	=>	$category_id,
										'created'		=>	$update_date,
										'createdby'		=>	$session_user,
										'updated'		=>	$update_date,
										'updatedby'		=>	$session_user,
										'is_active'		=>	$is_active,
									);
								
							}else{
								//UPDATE
								
								/*$var = array(
									'fields'	=>	array(
										'product_name'	=> 	$product_name,
										'product_desc'	=>	$product_desc,
										'product_price'	=>	$product_price,
										'normal_price'	=>	$normal_price,
										'product_hpp'	=>	$product_hpp,
										'product_type'	=>	$product_type,
										'product_group' =>	$product_group,
										'use_tax'		=>	$use_tax,
										'use_service'	=>	$use_service,
										'category_id'	=>	$category_id,
										'updated'		=>	$update_date,
										'updatedby'		=>	$session_user,
									),
									'table'			=>  $this->table,
									'primary_key'	=>  'id'
								);	
								
								$q = $this->m->save($var, $id);
								*/
								
								if(!in_array($id, $available_id)){
									//new
									if(!in_array($id, $all_new_id)){
										$all_new_id[] = $id;
										$all_new_data_with_id[] = array(
											'id'	=> 	$id,
											'product_name'	=> 	$product_name,
											'product_desc'	=>	$product_desc,
											'product_price'	=>	$product_price,
											'normal_price'	=>	$normal_price,
											'product_hpp'	=>	$product_hpp,
											'product_type'	=>	$product_type,
											'product_group' =>	$product_group,
											'use_tax'		=>	$use_tax,
											'use_service'	=>	$use_service,
											'category_id'	=>	$category_id,
											'created'		=>	$update_date,
											'createdby'		=>	$session_user,
											'updated'		=>	$update_date,
											'updatedby'		=>	$session_user,
											'is_active'		=>	$is_active,
										);
									}
								}else{
									if(!in_array($id, $all_update_id)){
										$all_update_id[] = $id;
										$all_update_data[] = array(
											'id'	=> 	$id,
											'product_name'	=> 	$product_name,
											'product_desc'	=>	$product_desc,
											'product_price'	=>	$product_price,
											'normal_price'	=>	$normal_price,
											'product_hpp'	=>	$product_hpp,
											'product_type'	=>	$product_type,
											'product_group' =>	$product_group,
											'use_tax'		=>	$use_tax,
											'use_service'	=>	$use_service,
											'category_id'	=>	$category_id,
											'updated'		=>	$update_date,
											'updatedby'		=>	$session_user,
											'is_active'		=>	$is_active,
										);
									}
								}
								
								
								
							}
						}
						
						
						
					}
					
				}   
				
				
				//all_new_data_with_id
				if(!empty($all_new_data_with_id)){
					//$q=$this->db->insert_batch($this->table, $all_new_data_with_id);
					foreach($all_new_data_with_id as $dt){
						$var = array(
							'fields'	=>	$dt,
							'table'			=>  $this->table,
							'primary_key'	=>  'id'
						);	
						
						$q = $this->m->save($var);
					}
				}
				if(!empty($all_new_data)){
					//$q=$this->db->insert_batch($this->table, $all_new_data);
					foreach($all_new_data as $dt){
						$var = array(
							'fields'	=>	$dt,
							'table'			=>  $this->table,
							'primary_key'	=>  'id'
						);	
						
						$q = $this->m->save($var);
					}
				}
				if(!empty($all_update_data)){
					//$q=$this->db->update_batch($this->table, $all_update_data, "id");
					foreach($all_update_data as $dt){
						$var = array(
							'fields'	=>	$dt,
							'table'			=>  $this->table,
							'primary_key'	=>  'id'
						);	
						
						$q = $this->m->save($var, $dt['id']);
					}
				}
				

				$this->lib_trans->commit();	
				
				if($q)
				{ 
					$r = array('success' => true); 				
				}  
				else
				{  				
					$r = array('success' => false);
				}
				
				
			}
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));	
 
	}
	
	public function print_masterProduct(){
		
		$this->table = $this->prefix.'product';
		$data_post['table'] = $this->table;
				
		$this->load->view('../../master_pos/views/print_masterProduct', $data_post);
		
	}
	
}