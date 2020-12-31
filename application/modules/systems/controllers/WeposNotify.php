<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class WeposNotify extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix');
		$this->prefix_pos = config_item('db_prefix2');
		$this->prefix_acc = config_item('db_prefix3');
	}

	public function DataMaster()
	{
		$this->table_items = $this->prefix_pos.'items';
		$this->table_supplier_item = $this->prefix_pos.'supplier_item';
		$this->table_discount = $this->prefix_pos.'discount';
		
		$session_client_id = $this->session->userdata('client_id');	
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			die(json_encode($r));
		}
				
		if(empty($session_client_id)){
			$r = array('success' => false, 'info' => 'Akun tidak dikenali!');
			die(json_encode($r));
		}
		
		//NO DATA
		//$this->db->query("UPDATE ".$this->prefix_pos."product SET product_no = id WHERE product_no = 0");
		//$this->db->query("UPDATE ".$this->prefix_pos."items SET item_no = id WHERE item_no = 0");
		//$this->db->query("UPDATE ".$this->prefix_pos."supplier SET supplier_no = id WHERE supplier_no = 0");
		//$this->db->query("UPDATE ".$this->prefix_pos."customer SET customer_no = id WHERE customer_no = 0");
		
		$today_mk = strtotime(date("d-m-Y"));
		$day_min15_mk = $today_mk-(15*ONE_DAY_UNIX);
		$day_min15 = date("Y-m-d", $day_min15_mk);
		
		//NON ACTIVE DISCOUNT
		$this->db->query("UPDATE ".$this->prefix_pos."discount SET is_active = 0 WHERE date_end < '".$day_min15." 00:00:00' AND is_active = 1 AND is_deleted = 0 AND discount_date_type = 'limited_date'");
		
		//LIST NO
		//if(!$this->db->field_exists('list_no', $this->prefix_pos.'product_category'))
		//{
		//	@$this->db->query("ALTER TABLE `".$this->prefix_pos.'product_category'."` ADD `list_no` int(11) DEFAULT 0;");
		//}
			
		$r = array('success' => true, 'info' => 'Master Data Selesai');
		die(json_encode($r));
		
	}

	public function Inventory()
	{
		$this->ro = $this->prefix_pos.'ro';
		$this->ro_detail = $this->prefix_pos.'ro_detail';
		$this->po = $this->prefix_pos.'po';
		$this->po_detail = $this->prefix_pos.'po_detail';
		$this->receiving = $this->prefix_pos.'receiving';
		$this->receive_detail = $this->prefix_pos.'receive_detail';
		$this->distribution = $this->prefix_pos.'distribution';
		$this->distribution_detail = $this->prefix_pos.'distribution_detail';
		$this->production = $this->prefix_pos.'production';
		$this->production_detail = $this->prefix_pos.'production_detail';
		$this->usagewaste = $this->prefix_pos.'usagewaste';
		$this->usagewaste_detail = $this->prefix_pos.'usagewaste_detail';
		$this->stock = $this->prefix_pos.'stock';
		$this->notify_log = $this->prefix_pos.'notify_log';
		$this->salesorder = $this->prefix_pos.'salesorder';
		$this->salesorder_detail = $this->prefix_pos.'salesorder_detail';
		
		
		$session_client_id = $this->session->userdata('client_id');	
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			die(json_encode($r));
		}
				
		if(empty($session_client_id)){
			$r = array('success' => false, 'info' => 'Akun tidak dikenali!');
			die(json_encode($r));
		}
		
		$all_ref = array();
		
		//GET po detail
		$all_po = array();
		$all_po_no = array();
		$all_po_status = array();
		$this->db->select("a.*, b.po_number");
		$this->db->from($this->po_detail." as a");
		$this->db->join($this->po." as b","b.id = a.po_id", "LEFT");
		$this->db->where("a.po_detail_qty > a.po_receive_qty AND a.po_receive_qty != 0");
		$this->db->where("b.po_status = 'done'");
		$get_po = $this->db->get();
		if($get_po->num_rows() > 0){
			foreach($get_po->result_array() as $dt){
				
				if(!in_array($dt['po_id'], $all_po)){
					$all_po[] = $dt['po_id'];
					$all_po_no[$dt['po_id']] = $dt['po_number'];
					$all_po_status[$dt['po_id']] = $dt['po_status'];
				}
			}
			//echo '<br/>---FOUND PO: '.count($all_po).'---<br/>';
		}
		
		//GET RL
		$all_rl = array();
		$all_receive_no = array();
		$all_receive_id = array();
		$all_rl_po = array();
		$this->db->from($this->receiving);
		$this->db->where("receive_status = 'done'");
		$get_rl = $this->db->get();
		if($get_rl->num_rows() > 0){
			foreach($get_rl->result_array() as $dt){
				$all_ref[] = $dt['receive_number'];
				$all_rl[] = $dt['receive_number'];
				$all_receive_id[] = $dt['id'];
				$all_receive_no[$dt['id']] = $dt['receive_number'];
				$all_rl_po[$dt['po_id']] = $dt['receive_number'];
			}
			//echo '<br/>---FOUND RL: '.count($all_rl).'---';
		}
		
		$all_receive_detail = array();
		$all_receive_no_detail = array();
		if(!empty($all_receive_id)){
			$all_receive_id_sql = implode(",", $all_receive_id);
			$this->db->from($this->receive_detail);
			$this->db->where("receive_id IN (".$all_receive_id_sql.")");
			$get_rld = $this->db->get();
			if($get_rld->num_rows() > 0){
				foreach($get_rld->result_array() as $dt){
					
					if($dt['receive_det_qty'] > 0){
						$all_receive_detail[$dt['id']] = $dt['item_id'];
						$all_receive_no_detail[$dt['id']] = $all_receive_no[$dt['receive_id']];
					}
					
				}
				//echo '<br/>---FOUND RL DETAIL: '.count($all_receive_detail).'---';
			}
		}
		
		
		//GET salesorder
		$all_salesorder = array();
		$all_salesorder_no = array();
		$all_salesorder_id = array();
		$all_salesorder_total = array();
		
		if ($this->db->table_exists($this->salesorder))
		{
			$this->db->from($this->salesorder);
			$this->db->where("so_status = 'done'");
			$get_so = $this->db->get();
			if($get_so->num_rows() > 0){
				foreach($get_so->result_array() as $dt){
					$all_ref[] = $dt['so_number'];
					$all_salesorder[] = $dt['so_number'];
					$all_salesorder_id[] = $dt['id'];
					$all_salesorder_no[$dt['id']] = $dt['so_number'];
					$all_salesorder_total[$dt['id']] = ($dt['so_sub_total'] - $dt['so_discount']);
					
					
				}
				//echo '<br/>---FOUND salesorder: '.count($all_salesorder).'---';
			}
		}

		
		
		$all_salesorder_detail = array();
		$all_salesorder_no_detail = array();
		$all_salesorder_total_detail = array();
		
		if ($this->db->table_exists($this->salesorder_detail))
		{
			if(!empty($all_salesorder_id)){
				$all_salesorder_id_sql = implode(",", $all_salesorder_id);
				$this->db->from($this->salesorder_detail);
				$this->db->where("so_id IN (".$all_salesorder_id_sql.")");
				$get_resd = $this->db->get();
				if($get_resd->num_rows() > 0){
					foreach($get_resd->result_array() as $dt){
						
						if($dt['sod_qty'] > 0){
							$all_salesorder_detail[$dt['id']] = $dt['item_id'];
							$all_salesorder_no_detail[$dt['id']] = $all_salesorder_no[$dt['so_id']];
							
							if(empty($all_salesorder_total_detail[$dt['so_id']])){
								$all_salesorder_total_detail[$dt['so_id']] = 0;
							}
							
							$all_salesorder_total_detail[$dt['so_id']] += ($dt['sod_total'] - $dt['sod_potongan']);
							
						}
					}
					//echo '<br/>---FOUND salesorder DETAIL: '.count($all_salesorder_detail).'---';
				}
			}
		}
		
		//check on stock
		$detail_receive_stok = array();
		$all_receive_stok = array();
		$detail_salesorder_stok = array();
		$all_salesorder_stok = array();
		if(!empty($all_ref)){
			$all_ref_sql = implode("','", $all_ref);
			$this->db->from($this->stock);
			$this->db->where("trx_ref_data IN ('".$all_ref_sql."')");
			$get_stok = $this->db->get();
			if($get_stok->num_rows() > 0){
				foreach($get_stok->result_array() as $dt){
					
					if($dt['trx_note'] == 'Receiving'){
						if(!in_array($dt['trx_ref_data'], $all_receive_stok)){
							$all_receive_stok[] = $dt['trx_ref_data'];
						}
						
						if(empty($detail_receive_stok[$dt['trx_ref_det_id']])){
							$detail_receive_stok[$dt['trx_ref_det_id']] = $dt['item_id'];
						}
					}
					
					if($dt['trx_note'] == 'Sales Order'){
						if(!in_array($dt['trx_ref_data'], $all_salesorder_stok)){
							$all_salesorder_stok[] = $dt['trx_ref_data'];
						}
						
						if(empty($detail_salesorder_stok[$dt['trx_ref_det_id']])){
							$detail_salesorder_stok[$dt['trx_ref_det_id']] = $dt['item_id'];
						}
					}
					
				}
			}
			
			//echo '<br/>---FOUND RL ON STOK: '.count($all_receive_stok).'---';
			//echo '<br/>---FOUND RL DETAIL ON STOK: '.count($detail_receive_stok).'---';
		}
		
		//NOTIFY RL --------------------------------------------------------
		$notify_text_rl = '';
		if(!empty($all_rl)){
			
			$no_err = 0;
			foreach($all_rl as $dt){
				if(!in_array($dt, $all_receive_stok)){
					$no_err++;
					
					if($no_err == 1){
						$notify_text_rl .= '<br/>---CEK RL DONE NOT IN STOK---';
					}
					
					$notify_text_rl .= '<br/>'.$dt.' --> NOT FOUND!';
					
				}
			}
			
			if($no_err == 0){
				//echo '<br/>---CEK RL DONE NOT IN STOK => AMAN!!---';
			}else{
				//echo '<br/><br/>';
			}
		}
		
		if(!empty($all_receive_detail)){
			
			$no_err = 0;
			foreach($all_receive_detail as $key => $val){
				
				if(!empty($detail_receive_stok[$key])){
					
					if($detail_receive_stok[$key] != $val){
						$no_err++;
						if($no_err == 1){
							$notify_text_rl .= '<br/><br/>---CEK RL DETAIL ITEM => STOK ITEM---';
						}
						$notify_text_rl .= '<br/>ITEM DETAIL: '.$all_receive_no_detail[$key].' / #'.$key.' --> TIDAK SAMA DENGAN DI STOK!';
						
					}
					
				}else{
					$no_err++;
					
					if($no_err == 1){
						$notify_text_rl .= '<br/><br/>---CEK RL DETAIL ITEM => STOK ITEM---';
					}
					
					$notify_text_rl .= '<br/>DETAIL: '.$all_receive_no_detail[$key].' / #'.$key.' --> TIDAK ADA DI STOK!';
					
					
				}
				
			}
			
			if($no_err == 0){
				//echo '<br/>---CEK RL DETAIL ITEM => STOK ITEM => AMAN!!---';
			}else{
				//echo '<br/><br/>';
			}
		}
		
		//NOTIFY SO ----------------------------------------------------------
		$notify_text_so = '';
		if(!empty($all_salesorder)){
			
			$no_err = 0;
			foreach($all_salesorder as $dt){
				if(!in_array($dt, $all_salesorder_stok)){
					$no_err++;
					
					if($no_err == 1){
						$notify_text_so .= '<br/>---CEK SO DONE NOT IN STOK---';
					}
					$notify_text_so .= '<br/>'.$dt.' --> NOT FOUND!';
				}
			}
			
			if($no_err == 0){
				//echo '<br/>---CEK SO DONE NOT IN STOK => AMAN!!---<br/><br/>';
			}else{
				//echo '<br/><br/>';
			}
		}
		
		
		if(!empty($all_salesorder_detail)){
			
			$no_err = 0;
			foreach($all_salesorder_detail as $key => $val){
				
				if(!empty($detail_salesorder_stok[$key])){
					
					if($detail_salesorder_stok[$key] != $val){
						$no_err++;
						if($no_err == 1){
							$notify_text_so .= '<br/><br/>---CEK SO DETAIL ITEM => STOK ITEM---';
						}
						$notify_text_so .= '<br/>ITEM DETAIL: '.$all_salesorder_no_detail[$key].' / #'.$key.' --> TIDAK SAMA DENGAN DI STOK!';
						
					}
					
				}else{
					$no_err++;
					if($no_err == 1){
						$notify_text_so .= '<br/><br/>---CEK SO DETAIL ITEM => STOK ITEM---';
					}
					$notify_text_so .= '<br/>DETAIL: '.$all_salesorder_no_detail[$key].' / #'.$key.' --> TIDAK ADA DI STOK!';
					
				}
				
			}
			
			if($no_err == 0){
				//echo '<br/>---CEK SO DETAIL ITEM => STOK ITEM => AMAN!!---<br/><br/>';
			}else{
				//echo '<br/><br/>';
			}
		}
		
		if(!empty($all_salesorder_total)){
			$notify_text_so .= '<br/>---CEK SO TOTAL => DETAIL---';
			foreach($all_salesorder_total as $salesorder_id => $total){
				
				if(!empty($all_salesorder_total_detail[$salesorder_id])){
					if($all_salesorder_total_detail[$salesorder_id] != $total){
						$notify_text_so .= '<br/>#'.$all_salesorder_no[$salesorder_id].' -> '.priceFormat($total).' != '.priceFormat($all_salesorder_total_detail[$salesorder_id]);
					}
				}
				
			}
		}
		
		$notify_text_po = '';
		if(!empty($all_po_no)){
			//echo '<br/>------CEK PROBLEM?-----------';
			foreach($all_po_no as $key => $val){
				if(!empty($all_rl_po[$key])){
					//echo '<br/>PO: '.$val.' DAN RL: '.$all_rl_po[$key].' ';
				}else{
					//echo '<br/>PO: '.$val.' ';
					if(!empty($all_po_status[$key])){
						if($all_po_status[$key] == 'done')
						$notify_text_po .= '<br/>PO: '.$key.', status = done tetapi RL tidak ada!';
					}
				}
				
			}
		}
		
		if(!empty($notify_text_po)){
			  
			$set_log = array(
				'log_date'	=> date("Y-m-d"),
				'log_type'	=> 'inventory',
				'log_info'	=> 'Inventory: PO',
				'log_data'	=> $notify_text_po,
				'createdby'	=> $session_user,
				'created'	=> date("Y-m-d H:i:s")
			);
			  
			//cek on table_notify_log
			$this->db->from($this->notify_log);
			$this->db->where("log_info = 'Inventory: PO' AND log_date = '".date("Y-m-d")."'");
			$get_log = $this->db->get();
			if($get_log->num_rows() > 0){
				//update
				$get_dt = $get_log->row();
				$get_id = $get_dt->id;
				$this->db->insert($this->notify_log, $set_log, "id = ".$get_id);
			}else{
				//insert
				$this->db->insert($this->notify_log, $set_log);
			}
				
			 
		}
		
		if(!empty($notify_text_rl)){
			  
			$set_log = array(
				'log_date'	=> date("Y-m-d"),
				'log_type'	=> 'inventory',
				'log_info'	=> 'Inventory: Receiving',
				'log_data'	=> $notify_text_rl,
				'createdby'	=> $session_user,
				'created'	=> date("Y-m-d H:i:s")
			);
			  
			//cek on table_notify_log
			$this->db->from($this->notify_log);
			$this->db->where("log_info = 'Inventory: Receiving' AND log_date = '".date("Y-m-d")."'");
			$get_log = $this->db->get();
			if($get_log->num_rows() > 0){
				//update
				$get_dt = $get_log->row();
				$get_id = $get_dt->id;
				$this->db->insert($this->notify_log, $set_log, "id = ".$get_id);
			}else{
				//insert
				$this->db->insert($this->notify_log, $set_log);
			}
				
			 
		}
		
		if(!empty($notify_text_so)){
			  
			$set_log = array(
				'log_date'	=> date("Y-m-d"),
				'log_type'	=> 'inventory',
				'log_info'	=> 'Inventory: Sales Order',
				'log_data'	=> $notify_text_so,
				'createdby'	=> $session_user,
				'created'	=> date("Y-m-d H:i:s")
			);
			  
			//cek on table_notify_log
			$this->db->from($this->notify_log);
			$this->db->where("log_info = 'Inventory: Sales Order' AND log_date = '".date("Y-m-d")."'");
			$get_log = $this->db->get();
			if($get_log->num_rows() > 0){
				//update
				$get_dt = $get_log->row();
				$get_id = $get_dt->id;
				$this->db->insert($this->notify_log, $set_log, "id = ".$get_id);
			}else{
				//insert
				$this->db->insert($this->notify_log, $set_log);
			}
				
			 
		}
		
		$r = array('success' => true, 'info' => 'Cek Inventory Selesai');
		die(json_encode($r));
		
	}

	public function Finance()
	{
		
		$this->po = $this->prefix_pos.'po';
		$this->ap = $this->prefix_acc.'account_payable';
		$this->notify_log = $this->prefix_pos.'notify_log';
		
		$session_client_id = $this->session->userdata('client_id');	
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			die(json_encode($r));
		}
				
		if(empty($session_client_id)){
			$r = array('success' => false, 'info' => 'Akun tidak dikenali!');
			die(json_encode($r));
		}
		
		//GET po
		$all_po = array();
		$all_po_no = array();
		$this->db->from($this->po);
		$this->db->where("po_status = 'done'");
		$this->db->where("po_payment = 'credit'");
		$get_po = $this->db->get();
		if($get_po->num_rows() > 0){
			foreach($get_po->result_array() as $dt){
				$all_po[$dt['id']] = $dt['po_total_price'];
				$all_po_no[$dt['id']] = $dt['po_number'];
			}
			//echo '<br/>---FOUND PO: '.count($all_po).'---';
		}
		
		//GET ap
		$all_ap = array();
		$this->db->from($this->ap);
		$this->db->where("po_id > 0");
		$this->db->where("ap_status = 'pengakuan'");
		$get_ap = $this->db->get();
		if($get_ap->num_rows() > 0){
			foreach($get_ap->result_array() as $dt){
				$all_ap[$dt['po_id']] = $dt['total_tagihan'];
				$all_ap_no[$dt['po_id']] = $dt['ap_no'];
			}
			//echo '<br/>---FOUND AP: '.count($all_ap).'---<br/>';
		}
		
		$notify_text_ap = '';
		if(!empty($all_ap)){
			foreach($all_ap as $key => $val){
				if(!empty($all_po[$key])){
					if($all_po[$key] != $val){
						$notify_text_ap .= '<br/>PO: '.$all_po_no[$key].' DAN AP: '.$all_ap_no[$key].' JUMLAH TIDAK SESUAI';
					}
				}
			}
		}
		
		
		if(!empty($notify_text_ap)){
			  
			$set_log = array(
				'log_date'	=> date("Y-m-d"),
				'log_type'	=> 'finance',
				'log_info'	=> 'Finance: Account Payable',
				'log_data'	=> $notify_text_ap,
				'createdby'	=> $session_user,
				'created'	=> date("Y-m-d H:i:s")
			);
			  
			//cek on table_notify_log
			$this->db->from($this->notify_log);
			$this->db->where("log_info = 'Finance: Account Payable' AND log_date = '".date("Y-m-d")."'");
			$get_log = $this->db->get();
			if($get_log->num_rows() > 0){
				//update
				$get_dt = $get_log->row();
				$get_id = $get_dt->id;
				$this->db->insert($this->notify_log, $set_log, "id = ".$get_id);
			}else{
				//insert
				$this->db->insert($this->notify_log, $set_log);
			}
				
			 
		}
		
		$r = array('success' => true, 'info' => 'Cek Finance Selesai');
		die(json_encode($r));
		
	}

	public function Bersihkan_data()
	{
		
		$session_client_id = $this->session->userdata('client_id');	
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			die(json_encode($r));
		}
				
		if(empty($session_client_id)){
			$r = array('success' => false, 'info' => 'Akun tidak dikenali!');
			die(json_encode($r));
		}
		
		//clean yesterday billing
		$opt_value = array(
			'reset_billing_yesterday', 'current_date', 'produk_expired',
			'nontrx_sales_auto'
		);
		
		$get_opt = get_option_value($opt_value);
		
		//autodelete_print_monitoring
		$current_date = 0;
		if(!empty($get_opt['current_date'])){
			$current_date = $get_opt['current_date'];
		}
		
		$tgl_cek_mk = strtotime(date("d-m-Y"));
		
		$today_mktime = strtotime(date("d-m-Y H:i:s"));
		$yesterday_mktime = $today_mktime - ONE_DAY_UNIX;
		if($current_date < $today_mktime){
			$update_opt = array('current_date' => $today_mktime);
			update_option($update_opt);
			
			$current_date = $today_mktime;
			$date_yesterday = date("Y-m-d", $yesterday_mktime)." 24:00:00";
			
			//print-monitoring
			$this->db->query("DELETE FROM ".$this->prefix_pos."print_monitoring WHERE print_date <= '".$date_yesterday."'");
			
			//reset-billing
			if(!empty($get_opt['reset_billing_yesterday'])){
				$this->db->query("DELETE FROM ".$this->prefix_pos."billing WHERE created <= '".$date_yesterday."'");
				$this->db->query("DELETE FROM ".$this->prefix_pos."billing_detail WHERE created <= '".$date_yesterday."'");
				$this->db->query("DELETE FROM ".$this->prefix_pos."billing_detail_split WHERE created <= '".$date_yesterday."'");
				$this->db->query("DELETE FROM ".$this->prefix_pos."billing_additional_price WHERE created <= '".$date_yesterday."'");
				$this->db->query("DELETE FROM ".$this->prefix_pos."billing_detail_timer WHERE created <= '".$date_yesterday."'");
				$this->db->query("DELETE FROM ".$this->prefix_pos."billing_log WHERE created <= '".$date_yesterday."'");
			}
			
			//next-autobackup-sales
			
			$day_min15_mk = $today_mktime-(15*ONE_DAY_UNIX);
			$day_min15 = date("Y-m-d", $day_min15_mk);
			
			//REMOVE NOTIF
			$this->db->query("DELETE FROM ".$this->prefix_pos."notify_log WHERE log_date <= '".$day_min15."'");
			
		}else{
			if($current_date <= $tgl_cek_mk){
				$update_option = array('current_date' => $tgl_cek_mk);
				update_option($update_option);
				$current_date = $tgl_cek_mk;
			}
		}
		
		//autofixing-payment
		$this->db->query("UPDATE ".$this->prefix_pos."billing SET total_credit = grand_total WHERE payment_id = 1 AND ((total_credit = 0 AND is_half_payment = 0) OR (total_credit = 0 AND total_cash = 0 AND is_half_payment = 1))");
		
		//update-2009.002
		//nontrx-realisasi vs target
		if(!empty($get_opt['nontrx_sales_auto'])){
			
			if(function_exists('realisasi_nontrx')){
				$update_realisasi = realisasi_nontrx($tgl_cek_mk);
			}
			
		}
		
		//check perpanjang berlangganan
		if(!empty($get_opt['produk_expired'])){
			if($get_opt['produk_expired'] != 'unlimited'){
				$produk_expired = strtotime($get_opt['produk_expired']." 23:59:59");
				$produk_expired_alert = $produk_expired - (7*ONE_DAY_UNIX);
				if($today_mktime >= $produk_expired_alert){
					$sisa_hari = ($produk_expired - $today_mktime)/ONE_DAY_UNIX;
					$sisa_jam = ($produk_expired - $today_mktime) % ONE_DAY_UNIX;
					
					if($sisa_hari > 3){
						$sisa_hari = ceil($sisa_hari);
						$sisa_hari_text = $sisa_hari.' Hari';
					}else{
						$sisa_hari = floor($sisa_hari);
						$sisa_jam = ceil($sisa_jam/3600);
						$sisa_hari_text = $sisa_hari.' Hari, '.$sisa_jam.' Jam';
					}
					
					if($sisa_hari >= 0){
						$r = array('success' => false, 'info' => 'Masa berlaku aplikasi WePOS.id anda akan berakhir<br/>pada tanggal: <b>'.$get_opt['produk_expired'].', <font color="red">'.$sisa_hari_text.' lagi</font></b><br/><br/>Silakan lakukan perpanjangan aplikasi</br>via website: <b>https://wepos.id</b><br/><br/>untuk pertanyaan seputar masa aktif hubungi<br/>CS: <b>0812-2254-9676</b></br>');
						die(json_encode($r));
					}
				}
			}
		}
		
		$r = array('success' => true, 'info' => 'Bersihkan Data - Selesai');
		die(json_encode($r));
		
	}

	public function Closing()
	{
		
		$session_client_id = $this->session->userdata('client_id');	
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			die(json_encode($r));
		}
				
		if(empty($session_client_id)){
			$r = array('success' => false, 'info' => 'Akun tidak dikenali!');
			die(json_encode($r));
		}
		
		
		
		$r = array('success' => true, 'info' => 'Cek Closing Selesai');
		die(json_encode($r));
		
	}
}