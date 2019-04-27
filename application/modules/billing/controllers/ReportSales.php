<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ReportSales extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_databilling', 'm');
		$this->load->model('model_billingdetail', 'm2');
	}
	
	public function print_reportSales(){
		
		$this->table = $this->prefix.'billing';
		$this->table2 = $this->prefix.'billing_detail';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('Sesi Login sudah habis, Silahkan Login ulang!');
		}
		
		extract($_GET);

		if(empty($date_from)){ $date_from = date('Y-m-d'); }
		if(empty($date_till)){ $date_till = date('Y-m-d'); }
		
		if(empty($sorting)){
			$sorting = 'payment_date';
		}
		
		$data_post = array(
			'do'	=> '',
			'report_data'	=> array(),
			'report_place_default'	=> '',
			'report_name'	=> 'SALES REPORT',
			'date_from'	=> $date_from,
			'date_till'	=> $date_till,
			'user_fullname'	=> $user_fullname,
			'sorting'	=> $sorting,
			'diskon_sebelum_pajak_service' => 0
		);
		
		$get_opt = get_option_value(array('report_place_default','diskon_sebelum_pajak_service','cashier_max_pembulatan','cashier_pembulatan_keatas'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		
		if(!empty($get_opt['diskon_sebelum_pajak_service'])){
			$data_post['diskon_sebelum_pajak_service'] = $get_opt['diskon_sebelum_pajak_service'];
		}
		if(empty($get_opt['cashier_max_pembulatan'])){
			$get_opt['cashier_max_pembulatan'] = 0;
		}
		if(empty($get_opt['cashier_pembulatan_keatas'])){
			$get_opt['cashier_pembulatan_keatas'] = 0;
		}
		
		if(empty($date_from) OR empty($date_till)){
			die('Billing Paid Not Found!');
		}else{
				
			$mktime_dari = strtotime($date_from);
			$mktime_sampai = strtotime($date_till);
						
			$qdate_from = date("Y-m-d",strtotime($date_from));
			$qdate_till = date("Y-m-d",strtotime($date_till));
			$qdate_till_max = date("Y-m-d",strtotime($date_till)+ONE_DAY_UNIX);
			
			$add_where = "(a.payment_date >= '".$qdate_from." 00:00:00' AND a.payment_date <= '".$qdate_till_max." 23:59:59')";
			
			$this->db->select("a.*, a.id as billing_id, a.updated as billing_date, d.payment_type_name, e.bank_name");
			$this->db->from($this->table." as a");
			$this->db->join($this->prefix.'payment_type as d','d.id = a.payment_id','LEFT');
			$this->db->join($this->prefix.'bank as e','e.id = a.bank_id','LEFT');
			$this->db->where("a.billing_status", 'paid');
			$this->db->where("a.is_deleted", 0);
			$this->db->where($add_where);
			
			if(!empty($tipe)){
				if($tipe != 'null'){
					$this->db->where("a.table_id", $tipe);
				}
			}
			
			if(empty($sorting)){
				$this->db->order_by("payment_date","ASC");
			}else{
				$this->db->order_by($sorting,"ASC");
			}
			
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['report_data'] = $get_dt->result_array();				
			}
			
			//PAYMENT DATA
			$dt_payment_name = array();
			$this->db->select('*');
			$this->db->from($this->prefix.'payment_type');
			$get_dt_p = $this->db->get();
			if($get_dt_p->num_rows() > 0){
				foreach($get_dt_p->result_array() as $dtP){
					$dt_payment_name[$dtP['id']] = strtoupper($dtP['payment_type_name']);
				}
			}
			
			
			$all_bil_id = array();
			$newData = array();
			$dt_payment = array();
			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
					$s['billing_date'] = date("d-m-Y H:i",strtotime($s['created']));					
					$s['payment_date'] = date("d-m-Y H:i",strtotime($s['payment_date']));
					
					if(!in_array($s['id'], $all_bil_id)){
						$all_bil_id[] = $s['id'];
					}			
					
					$s['total_billing_awal'] = $s['total_billing'];
					
					//CHECK REAL TOTAL BILLING
					if(!empty($s['include_tax']) OR !empty($s['include_service'])){
						if(!empty($s['include_tax']) AND !empty($s['include_service'])){
						
							if($data_post['diskon_sebelum_pajak_service'] == 1){
								$get_total_billing = $s['total_billing'] / (($s['tax_percentage']+$s['service_percentage']+100)/100);
								$get_total_billing = priceFormat($get_total_billing, 0, ".", "");
								$s['total_billing'] = $get_total_billing;
							}else{
								$s['total_billing'] = $s['total_billing'] - ($s['tax_total'] + $s['service_total']);
							}
							
						}else{
							if(!empty($s['include_tax'])){
								if($data_post['diskon_sebelum_pajak_service'] == 1){
									$get_total_billing = $s['total_billing'] / (($s['tax_percentage']+100)/100);
									$get_total_billing = priceFormat($get_total_billing, 0, ".", "");
									$s['total_billing'] = $get_total_billing;
								}else{
									$s['total_billing'] = $s['total_billing'] - ($s['tax_total']);
								}
							}
							if(!empty($s['include_service'])){
								if($data_post['diskon_sebelum_pajak_service'] == 1){
									$get_total_billing = $s['total_billing'] / (($s['service_percentage']+100)/100);
									$get_total_billing = priceFormat($get_total_billing, 0, ".", "");
									$s['total_billing'] = $get_total_billing;
								}else{
									$s['total_billing'] = $s['total_billing'] - ($s['service_total']);
								}
							}
						}
					}
					
					if(!empty($s['is_compliment'])){
						$s['total_billing'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'];
						$s['service_total'] = 0;
						$s['tax_total'] = 0;
					}
					
					//diskon_sebelum_pajak_service
					if($data_post['diskon_sebelum_pajak_service'] == 0){
						$s['sub_total'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'];		
					}else{
						
						$s['sub_total'] = $s['total_billing'] - $s['discount_total'] + $s['tax_total'] + $s['service_total'];
						
						if(!empty($s['include_tax']) OR !empty($s['include_service'])){
							//CHECKING BALANCE #1
							if(empty($s['discount_total'])){
								if($s['sub_total'] != $s['total_billing_awal']){
									$s['total_billing'] = ($s['total_billing_awal'] - ($s['tax_total'] + $s['service_total']));
									$s['sub_total'] = $s['total_billing'] - $s['discount_total'] + $s['tax_total'] + $s['service_total'];
								}
							}else{
								if(($s['sub_total'] + $s['total_pembulatan']) != $s['grand_total']){
									$s['sub_total'] = ($s['grand_total']-$s['total_pembulatan'])+$s['compliment_total'];
								}
								
								$cek_total_billing = $s['sub_total'] - ($s['tax_total'] + $s['service_total']) + $s['discount_total'];
								if($s['total_billing'] != $cek_total_billing){
									$s['total_billing'] = $cek_total_billing;
								}
							}
						}
						
					}
					
					//SPLIT DISCOUNT TYPE
					if(!empty($s['discount_total']) AND $s['discount_perbilling'] == 1){
						$s['discount_billing_total'] = $s['discount_total'];
						$s['discount_total'] = 0;
					}else{
						$s['discount_billing_total'] = 0;
					}
					
					//if(!empty($s['include_tax']) OR !empty($s['include_service'])){
					//	$s['sub_total'] = $s['total_billing'];
					//}
					
					$s['grand_total'] = $s['sub_total'] + $s['total_pembulatan'];
					$s['grand_total'] -= $s['compliment_total'];
					
					//diskon_sebelum_pajak_service
					if($data_post['diskon_sebelum_pajak_service'] == 0){
						$s['grand_total'] -= $s['discount_total'];
						$s['grand_total'] -= $s['discount_billing_total'];
					}
					
					if($s['grand_total'] <= 0){
						$s['grand_total'] = 0;
					}
					
					$s['total_pembulatan_show'] = priceFormat($s['total_pembulatan']);
					
					if($s['total_pembulatan'] < 0){
						$s['total_pembulatan_show'] = "(".priceFormat($s['total_pembulatan']).")";
					}
					
					$s['sub_total_show'] = priceFormat($s['sub_total']);
					$s['grand_total_show'] = priceFormat($s['grand_total']);
					$s['total_billing_show'] = priceFormat($s['total_billing']);
					$s['total_paid_show'] = priceFormat($s['total_paid']);
					$s['tax_total_show'] = priceFormat($s['tax_total']);
					$s['service_total_show'] = priceFormat($s['service_total']);
					$s['discount_total_show'] = priceFormat($s['discount_total']);
					$s['discount_billing_total_show'] = priceFormat($s['discount_billing_total']);
					
					//DP
					$s['total_dp_show'] = priceFormat($s['total_dp']);
					/*if($s['total_cash'] == 0){
						if($s['total_credit'] > $s['total_dp']){
							$s['total_credit'] -= $s['total_dp'];
						}
					}else{
						if($s['total_cash'] > $s['total_dp']){
							$s['total_cash'] -= $s['total_dp'];
						}
					}*/
					
					$s['total_compliment'] = 0;
					$s['total_compliment_show'] = 0;

					$s['total_hpp'] = 0;
					$s['total_hpp_show'] = 0;
					$s['total_profit'] = 0;
					$s['total_profit_show'] = 0;
					
					//CARD NO 
					$card_no = '';
					if(strlen($s['card_no']) > 30){
						$card_no = $s['card_no'];
						$card_no = str_replace(";","",$card_no);
						$card_no = str_replace("?","",$card_no);
						$card_no_exp = explode("=", $card_no);
						if(!empty($card_no_exp[0])){
							$card_no = trim($card_no_exp[0]);
						}
					}else{
						$card_no = trim($s['card_no']);
					}
					
					//NOTES
					$s['payment_note'] = '';
					if(!empty($s['is_compliment']) OR !empty($s['compliment_total'])){
						$s['payment_note'] = 'COMPLIMENT';
						//$s['total_compliment'] = $s['grand_total'];
						$s['total_compliment'] = $s['compliment_total'];
						$s['total_compliment_show'] = priceFormat($s['total_compliment']);
					}else{
					
						if(!empty($s['is_half_payment'])){
							$s['payment_note'] = 'HALF PAYMENT';
						}
						
						if(strtolower($s['payment_type_name']) != 'cash'){
							$s['payment_note'] = strtoupper($s['bank_name']).' '.$card_no;
						}
					}
					
					if(!empty($s['billing_notes'])){
						if(!empty($s['payment_note'])){
							$s['payment_note'] .= '<br/>'.$s['billing_notes'];
						}else{
							$s['payment_note'] .= $s['billing_notes'];
						}
					}
					
					//if($s['billing_no'] == '1601010055'){
						//echo '<pre>';
						//print_r($s);
						//die();
					//}
										
					$newData[$s['id']] = $s;
					//array_push($newData, $s);
					
				}
			}
			
			//calc detail
			$total_hpp = array();
			$discount_item = array();
			if(!empty($all_bil_id)){
				$all_bil_id_txt = implode(",",$all_bil_id);
				$this->db->from($this->table2);
				$this->db->where('billing_id IN ('.$all_bil_id_txt.')');
				$this->db->where('is_deleted', 0);
				$get_detail = $this->db->get();
				if($get_detail->num_rows() > 0){
					foreach($get_detail->result() as $dtRow){
						
						$total_qty = $dtRow->order_qty;
						/*
						$total_qty = $dtRow->order_qty - $dtRow->retur_qty;
						if($total_qty < 0){
							$total_qty = 0;
						}*/
						
						if(empty($total_hpp[$dtRow->billing_id])){
							$total_hpp[$dtRow->billing_id] = 0;
						}
						
						$total_hpp[$dtRow->billing_id] += $dtRow->product_price_hpp * $total_qty;

						
					}
				}
			}
			
			$newData_switch = $newData;
			$newData = array();
			if(!empty($newData_switch)){
				foreach($newData_switch as $dt){
					
					if(!empty($total_hpp[$dt['billing_id']])){
						$dt['total_hpp'] = $total_hpp[$dt['billing_id']];
					}
					
					$dt['total_profit'] = $dt['total_billing']-$dt['total_hpp'];
					$dt['total_hpp_show'] = priceFormat($dt['total_hpp']);
					$dt['total_profit_show'] = priceFormat($dt['total_profit']);
					
					$newData[] = $dt;
				}
			}
	
			$data_post['report_data'] = $newData;
			$data_post['payment_data'] = $dt_payment_name;
			//$data_post['total_hpp'] = $total_hpp;
		}
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}else{
			$do = '';
		}
		
		if(empty($useview)){
			$useview = 'print_reportSales';
			$data_post['report_name'] = 'SALES REPORT';
			
			if($do == 'excel'){
				$useview = 'excel_reportSales';
			}
			
		}else{
			$useview = 'print_reportProfitSales';
			$data_post['report_name'] = 'SALES PROFIT REPORT';
			
			if($do == 'excel'){
				$useview = 'excel_reportProfitSales';
			}
			
		}
		
		$this->load->view('../../billing/views/'.$useview, $data_post);	
	}
	
	public function print_reportSalesRecap(){
		
		$this->table = $this->prefix.'billing';
		$this->table2 = $this->prefix.'billing_detail';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('Sesi Login sudah habis, Silahkan Login ulang!');
		}
		
		extract($_GET);

		if(empty($date_from)){ $date_from = date('Y-m-d'); }
		if(empty($date_till)){ $date_till = date('Y-m-d'); }
		
		if(empty($sorting)){
			$sorting = 'payment_date';
		}
		
		$data_post = array(
			'do'	=> '',
			'report_data'	=> array(),
			'report_place_default'	=> '',
			'report_name'	=> 'SALES REPORT (RECAP)',
			'date_from'	=> $date_from,
			'date_till'	=> $date_till,
			'user_fullname'	=> $user_fullname,
			'diskon_sebelum_pajak_service' => 0
		);
		
		$get_opt = get_option_value(array('report_place_default','diskon_sebelum_pajak_service','cashier_max_pembulatan','cashier_pembulatan_keatas'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		if(!empty($get_opt['diskon_sebelum_pajak_service'])){
			$data_post['diskon_sebelum_pajak_service'] = $get_opt['diskon_sebelum_pajak_service'];
		}
		if(empty($get_opt['cashier_max_pembulatan'])){
			$get_opt['cashier_max_pembulatan'] = 0;
		}
		if(empty($get_opt['cashier_pembulatan_keatas'])){
			$get_opt['cashier_pembulatan_keatas'] = 0;
		}
		
		if(empty($date_from) OR empty($date_till)){
			die('Billing Paid Not Found!');
		}else{
				
			if(empty($date_from)){ $date_from = date('Y-m-d'); }
			if(empty($date_till)){ $date_till = date('Y-m-d'); }
			
			$mktime_dari = strtotime($date_from);
			$mktime_sampai = strtotime($date_till);
						
			$qdate_from = date("Y-m-d",strtotime($date_from));
			$qdate_till = date("Y-m-d",strtotime($date_till));
			$qdate_till_max = date("Y-m-d",strtotime($date_till)+ONE_DAY_UNIX);
			
			$add_where = "(a.payment_date >= '".$qdate_from." 00:00:00' AND a.payment_date <= '".$qdate_till_max." 23:59:59')";
			
			$this->db->select("a.*, a.id as billing_id, a.updated as billing_date, d.payment_type_name, e.bank_name");
			$this->db->from($this->table." as a");
			$this->db->join($this->prefix.'payment_type as d','d.id = a.payment_id','LEFT');
			$this->db->join($this->prefix.'bank as e','e.id = a.bank_id','LEFT');
			$this->db->where("a.billing_status", 'paid');
			$this->db->where("a.is_deleted", 0);
			$this->db->where($add_where);
			
			if(!empty($tipe)){
				if($tipe != 'null'){
					$this->db->where("a.table_id", $tipe);
				}
			}
			
			if(empty($sorting)){
				$this->db->order_by("payment_date","ASC");
			}else{
				$this->db->order_by($sorting,"ASC");
			}

			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['report_data'] = $get_dt->result_array();				
			}
			
			//PAYMENT DATA
			$dt_payment_name = array();
			$this->db->select('*');
			$this->db->from($this->prefix.'payment_type');
			$get_dt_p = $this->db->get();
			if($get_dt_p->num_rows() > 0){
				foreach($get_dt_p->result_array() as $dtP){
					$dt_payment_name[$dtP['id']] = strtoupper($dtP['payment_type_name']);
				}
			}
			
			$payment_data = $dt_payment_name;
			
			
			$all_group_date = array();		  
			$all_bil_id = array();	  
			$all_bil_id_date = array();
			$newData = array();
			$dt_payment = array();
			$no_id = 1;
			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
					$s['billing_date'] = date("d-m-Y H:i",strtotime($s['created']));					
					$s['payment_date'] = date("d-m-Y H:i",strtotime($s['payment_date']));
					
					if(!in_array($s['id'], $all_bil_id)){
						$all_bil_id[] = $s['id'];
					}		
					
					$s['total_billing_awal'] = $s['total_billing'];

					
					///CHECK REAL TOTAL BILLING
					if(!empty($s['include_tax']) OR !empty($s['include_service'])){
						if(!empty($s['include_tax']) AND !empty($s['include_service'])){
						
							if($data_post['diskon_sebelum_pajak_service'] == 1){
								$get_total_billing = $s['total_billing'] / (($s['tax_percentage']+$s['service_percentage']+100)/100);
								$get_total_billing = priceFormat($get_total_billing, 0, ".", "");
								$s['total_billing'] = $get_total_billing;
							}else{
								$s['total_billing'] = $s['total_billing'] - ($s['tax_total'] + $s['service_total']);
							}
							
						}else{
							if(!empty($s['include_tax'])){
								if($data_post['diskon_sebelum_pajak_service'] == 1){
									$get_total_billing = $s['total_billing'] / (($s['tax_percentage']+100)/100);
									$get_total_billing = priceFormat($get_total_billing, 0, ".", "");
									$s['total_billing'] = $get_total_billing;
								}else{
									$s['total_billing'] = $s['total_billing'] - ($s['tax_total']);
								}
							}
							if(!empty($s['include_service'])){
								if($data_post['diskon_sebelum_pajak_service'] == 1){
									$get_total_billing = $s['total_billing'] / (($s['service_percentage']+100)/100);
									$get_total_billing = priceFormat($get_total_billing, 0, ".", "");
									$s['total_billing'] = $get_total_billing;
								}else{
									$s['total_billing'] = $s['total_billing'] - ($s['service_total']);
								}
							}
						}
					}
					
					if(!empty($s['is_compliment'])){
						$s['total_billing'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'];
						//if(!empty($s['include_tax']) OR !empty($s['include_service'])){
						//	$s['total_billing'] = $s['total_billing'];
						//}
						$s['service_total'] = 0;
						$s['tax_total'] = 0;
					}
					
					//diskon_sebelum_pajak_service
					if($data_post['diskon_sebelum_pajak_service'] == 0){
						$s['sub_total'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'];
					}else{
						$s['sub_total'] = $s['total_billing'] - $s['discount_total'] + $s['tax_total'] + $s['service_total'];
						
						if(!empty($s['include_tax']) OR !empty($s['include_service'])){
							//CHECKING BALANCE #1
							if(empty($s['discount_total'])){
								if($s['sub_total'] != $s['total_billing_awal']){
									$s['total_billing'] = ($s['total_billing_awal'] - ($s['tax_total'] + $s['service_total']));
									$s['sub_total'] = $s['total_billing'] - $s['discount_total'] + $s['tax_total'] + $s['service_total'];
								}
							}else{
								if(($s['sub_total'] + $s['total_pembulatan']) != $s['grand_total']){
									$s['sub_total'] = ($s['grand_total']-$s['total_pembulatan'])+$s['compliment_total'];
								}
								
								$cek_total_billing = $s['sub_total'] - ($s['tax_total'] + $s['service_total']) + $s['discount_total'];
								if($s['total_billing'] != $cek_total_billing){
									$s['total_billing'] = $cek_total_billing;
								}
							}
						}
						
					}
					
					
					//SPLIT DISCOUNT TYPE
					if(!empty($s['discount_total']) AND $s['discount_perbilling'] == 1){
						$s['discount_billing_total'] = $s['discount_total'];
						$s['discount_total'] = 0;
					}else{
						$s['discount_billing_total'] = 0;
					}
					
					//if(!empty($s['include_tax']) OR !empty($s['include_service'])){
					//	$s['sub_total'] = $s['total_billing'];
					//}
					
					$s['grand_total'] = $s['sub_total'] + $s['total_pembulatan'];
					
					//diskon_sebelum_pajak_service
					if($data_post['diskon_sebelum_pajak_service'] == 0){
						$s['grand_total'] -= $s['discount_total'];
						$s['grand_total'] -= $s['discount_billing_total'];
					}
					
					if($s['grand_total'] <= 0){
						$s['grand_total'] = 0;
					}
					
					
					$s['total_pembulatan_show'] = priceFormat($s['total_pembulatan']);
					
					if($s['total_pembulatan'] < 0){
						$s['total_pembulatan_show'] = "(".priceFormat($s['total_pembulatan']).")";
					}
					
					$s['sub_total_show'] = priceFormat($s['sub_total']);
					$s['grand_total_show'] = priceFormat($s['grand_total']);
					$s['total_billing_show'] = priceFormat($s['total_billing']);
					$s['total_paid_show'] = priceFormat($s['total_paid']);
					$s['tax_total_show'] = priceFormat($s['tax_total']);
					$s['service_total_show'] = priceFormat($s['service_total']);
					$s['discount_total_show'] = priceFormat($s['discount_total']);
					$s['discount_billing_total_show'] = priceFormat($s['discount_billing_total']);
					
					//DP
					$s['total_dp_show'] = priceFormat($s['total_dp']);
					/*if($s['total_cash'] == 0){
						if($s['total_credit'] > $s['total_dp']){
							$s['total_credit'] -= $s['total_dp'];
						}
					}else{
						if($s['total_cash'] > $s['total_dp']){
							$s['total_cash'] -= $s['total_dp'];
						}
					}*/

					$s['total_hpp'] = 0;
					$s['total_hpp_show'] = 0;
					$s['total_profit'] = 0;
					$s['total_profit_show'] = 0;
					
					//CARD NO 
					$card_no = '';
					if(strlen($s['card_no']) > 30){
						$card_no = $s['card_no'];
						$card_no = str_replace(";","",$card_no);
						$card_no = str_replace("?","",$card_no);
						$card_no_exp = explode("=", $card_no);
						if(!empty($card_no_exp[0])){
							$card_no = trim($card_no_exp[0]);
						}
					}else{
						$card_no = trim($s['card_no']);
					}
					
					//NOTES
					$s['payment_note'] = '';
					
					if(!empty($s['is_compliment']) OR !empty($s['compliment_total'])){
						$s['payment_note'] = 'COMPLIMENT';
						//$s['total_compliment'] = $s['grand_total'];
						$s['total_compliment'] = $s['compliment_total'];
						$s['total_compliment_show'] = priceFormat($s['total_compliment']);
					}else{
					
						if(!empty($s['is_half_payment'])){
							$s['payment_note'] = 'HALF PAYMENT';
						}
						
						if(strtolower($s['payment_type_name']) != 'cash'){
							$s['payment_note'] = strtoupper($s['bank_name']).' '.$card_no;
						}
					}
					
					//REKAP TGL
					$payment_date = date("d-m-Y",strtotime($s['payment_date']));
					if(empty($all_group_date[$payment_date])){
						$all_group_date[$payment_date] = array(
							'id'		=> $no_id, 
							'item_no'	=> $no_id, 
							'date'		=> $payment_date, 
							'qty_billing'		=> 0, 
							'total_billing'		=> 0, 
							'total_billing_show'=> 0,
							'tax_total'			=> 0, 
							'tax_total_show'	=> 0, 
							'service_total'		=> 0, 
							'service_total_show'=> 0,
							'discount_total'	=> 0, 
							'discount_total_show'=> 0, 
							'discount_billing_total'	=> 0, 
							'discount_billing_total_show'=> 0, 
							'total_dp'			=> 0, 
							'total_dp_show'		=> 0, 
							'grand_total'		=> 0, 
							'grand_total_show'	=> 0,
							'sub_total'		=> 0, 
							'sub_total_show'	=> 0,
							'total_pembulatan'		=> 0, 
							'total_pembulatan_show'	=> 0, 
							//'total_cash'		=> 0, 
							//'total_cash_show'	=> 0,
							//'total_debit'		=> 0, 
							//'total_debit_show'	=> 0,
							//'total_credit'		=> 0, 
							//'total_credit_show'	=> 0,
							'total_compliment'		=> 0, 
							'total_compliment_show'	=> 0,
							'total_hpp'			=> 0, 
							'total_hpp_show'	=> 0, 
							'total_profit'		=> 0, 
							'total_profit_show'=> 0
						);
						
						foreach($payment_data as $key_id => $dtPay){
							$all_group_date[$payment_date]['total_payment_'.$key_id] = 0;
							$all_group_date[$payment_date]['total_payment_'.$key_id.'_show'] = 0;
						}
						
						$no_id++;
					}
					
					$all_bil_id_date[$s['billing_id']] = $payment_date;
					
					$all_group_date[$payment_date]['qty_billing'] += 1;
					$all_group_date[$payment_date]['total_billing'] += $s['total_billing'];
					$all_group_date[$payment_date]['tax_total'] += $s['tax_total'];
					$all_group_date[$payment_date]['service_total'] += $s['service_total'];
					$all_group_date[$payment_date]['discount_total'] += $s['discount_total'];
					$all_group_date[$payment_date]['discount_billing_total'] += $s['discount_billing_total'];
					$all_group_date[$payment_date]['total_dp'] += $s['total_dp'];
					$all_group_date[$payment_date]['grand_total'] += $s['grand_total'];
					$all_group_date[$payment_date]['grand_total'] -= $s['compliment_total'];
					$all_group_date[$payment_date]['sub_total'] += $s['sub_total'];
					$all_group_date[$payment_date]['total_pembulatan'] += $s['total_pembulatan'];
					$all_group_date[$payment_date]['total_compliment'] += $s['compliment_total'];
					
					/* if(!empty($s['discount_total'])){
						echo '<pre>';
						print_r($s);
					} */
					
					if(!empty($s['is_compliment'])){
						$all_group_date[$payment_date]['total_compliment'] += $s['grand_total'];
					}else{
					
						/* if(!empty($s['is_half_payment'])){
							$all_group_date[$payment_date]['total_cash'] += $s['total_cash'];
							$all_group_date[$payment_date]['total_credit'] += $s['total_credit'];
						}else{
							if($s['payment_id'] == 1){
								//cash
								$all_group_date[$payment_date]['total_cash'] += $s['grand_total'];
							}else{
								$all_group_date[$payment_date]['total_credit'] += $s['grand_total'];
							}
						} */
						
						if(!empty($payment_data)){
							foreach($payment_data as $key_id => $dtPay){
						
								$tot_payment = 0;
								$tot_payment_show = 0;
								if($s['payment_id'] == $key_id){
									//$tot_payment = $s['grand_total'];
									//$tot_payment_show = $s['grand_total_show'];
									
									if($key_id == 2 OR $key_id == 3 OR $key_id == 4){
										$tot_payment = $s['total_credit'];	
									}else{
										$tot_payment = $s['total_cash'];	
									}
									
									//FIX PEMBULATAN
									/*if($tot_payment < $s['grand_total']){
										$gap = ($s['grand_total'] - $s['total_dp']) - $tot_payment;
										if($gap < 100){
											$tot_payment += $s['total_pembulatan'];
										}
									}*/
									
									$tot_payment_show = priceFormat($tot_payment);
									
									//credit half payment
									if(!empty($s['is_half_payment']) AND $key_id != 1){
										$tot_payment = $s['total_credit'];
										$tot_payment_show = priceFormat($s['total_credit']);
									}else{
										
										/*if($tot_payment <= $s['grand_total']){
											//$tot_payment_show .= '='.$tot_payment.'x'.$s['grand_total'];
											$tot_payment = $s['grand_total'];
											$tot_payment_show = priceFormat($tot_payment);
												
											if(!empty($s['discount_total'])){
												$tot_payment = $tot_payment - $s['discount_total'];
												$tot_payment_show = priceFormat($tot_payment);
											}
										}else{
											$tot_payment_show .= '='.$tot_payment.'z'.$s['grand_total'];
										}
										*/
										
										$tot_payment_show = priceFormat($tot_payment);	
									}
										
								}else{
									//cash
									if(!empty($s['is_half_payment']) AND $key_id == 1){
										$tot_payment = $s['total_cash'];
										$tot_payment_show = priceFormat($s['total_cash']);
									}
								}
						
								if(empty($grand_total_payment[$key_id])){
									$grand_total_payment[$key_id] = 0;
								}
						
								if(!empty($s['is_compliment'])){
									$tot_payment = 0;
									$tot_payment_show = 0;
								}
						
								if(!empty($s['discount_total']) AND !empty($tot_payment)){
									//$tot_payment = $tot_payment - $s['discount_total'];
									//$tot_payment_show = priceFormat($tot_payment);
								}
						
								//$grand_total_payment[$key_id] += $tot_payment;
								$all_group_date[$payment_date]['total_payment_'.$key_id] += $tot_payment;
																
							}
						}
						
					}
				
					$newData[$s['id']] = $s;
					//array_push($newData, $s);
					
				}
			}
			
			//calc detail
			$total_hpp = array();
			if(!empty($all_bil_id)){
				$all_bil_id_txt = implode(",",$all_bil_id);
				$this->db->from($this->table2);
				$this->db->where('billing_id IN ('.$all_bil_id_txt.')');
				$this->db->where('is_deleted', 0);
				$get_detail = $this->db->get();
				if($get_detail->num_rows() > 0){
					foreach($get_detail->result() as $dtRow){
			
						$total_qty = $dtRow->order_qty;
						/*
						 $total_qty = $dtRow->order_qty - $dtRow->retur_qty;
						if($total_qty < 0){
						$total_qty = 0;
						}*/
						
						if(!empty($all_bil_id_date[$dtRow->billing_id])){
							$payment_date = $all_bil_id_date[$dtRow->billing_id];
							
							if(empty($total_hpp[$payment_date])){
								$total_hpp[$payment_date] = 0;
							}
							$total_hpp[$payment_date] += $dtRow->product_price_hpp * $total_qty;
						}
			
						
					}
				}
			}
			
			$newData = array();
			if(!empty($all_group_date)){
				foreach($all_group_date as $key => $detail){
					
					$detail['total_billing_show'] = priceFormat($detail['total_billing']);
					$detail['tax_total_show'] = priceFormat($detail['tax_total']);
					$detail['service_total_show'] = priceFormat($detail['service_total']);
					$detail['grand_total_show'] = priceFormat($detail['grand_total']);
					$detail['sub_total_show'] = priceFormat($detail['sub_total']);
					$detail['total_pembulatan_show'] = priceFormat($detail['total_pembulatan']);
					
					//$detail['total_cash_show'] = priceFormat($detail['total_cash']);
					//$detail['total_credit_show'] = priceFormat($detail['total_credit']);
					
					foreach($payment_data as $key_id => $dtPay){
						$detail['total_payment_'.$key_id.'_show'] = priceFormat($detail['total_payment_'.$key_id]);
					}
					
					$detail['discount_total_show'] = priceFormat($detail['discount_total']);
					$detail['discount_billing_total_show'] = priceFormat($detail['discount_billing_total']);
					$detail['total_dp_show'] = priceFormat($detail['total_dp']);
					$detail['total_compliment_show'] = priceFormat($detail['total_compliment']);
					

					if(!empty($total_hpp[$key])){
						$detail['total_hpp'] = $total_hpp[$key];
					}

					$detail['total_profit'] = $detail['total_billing']-$detail['total_hpp'];
					$detail['total_hpp_show'] = priceFormat($detail['total_hpp']);
					$detail['total_profit_show'] = priceFormat($detail['total_profit']);
						
					
					$newData[$key] = $detail;
					
				}
			}	
			
			$newData_switch = $newData;
			$newData = array();
			if(!empty($newData_switch)){
				foreach($newData_switch as $dt){
					$newData[] = $dt;
				}
			}
			
			//echo '<pre>';
			//print_r($newData);
			//die();
			
			$data_post['report_data'] = $newData;
			$data_post['payment_data'] = $dt_payment_name;
		}
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}else{
			$do = '';
		}
		

		if(empty($useview)){
			$useview = 'print_reportSalesRecap';
			$data_post['report_name'] = 'SALES REPORT (RECAP)';
			
			if($do == 'excel'){
				$useview = 'excel_reportSalesRecap';
			}
			
		}else{
			$useview = 'print_reportProfitSalesRecap';
			$data_post['report_name'] = 'SALES PROFIT REPORT (RECAP)';
			
			if($do == 'excel'){
				$useview = 'excel_reportProfitSalesRecap';
			}
			
		}
		
		$this->load->view('../../billing/views/'.$useview, $data_post);	
	}
	
	public function print_reportSalesByCashier(){
		
		$this->table = $this->prefix.'billing';
		$this->table2 = $this->prefix.'billing_detail';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('Sesi Login sudah habis, Silahkan Login ulang!');
		}
		
		extract($_GET);

		if(empty($date_from)){ $date_from = date('Y-m-d'); }
		if(empty($date_till)){ $date_till = date('Y-m-d'); }
		
		if(empty($sorting)){
			$sorting = 'payment_date';
		}
		
		$data_post = array(
			'do'	=> '',
			'report_data'	=> array(),
			'report_place_default'	=> '',
			'report_name'	=> 'SALES REPORT BY CASHIER',
			'date_from'	=> $date_from,
			'date_till'	=> $date_till,
			'cashier_name'	=> '',
			'user_cashier'	=> $user_cashier,
			'user_fullname'	=> $user_fullname,
			'diskon_sebelum_pajak_service'	=> 0
		);
		
		$get_opt = get_option_value(array('report_place_default','diskon_sebelum_pajak_service','cashier_max_pembulatan','cashier_pembulatan_keatas'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		if(!empty($get_opt['diskon_sebelum_pajak_service'])){
			$data_post['diskon_sebelum_pajak_service'] = $get_opt['diskon_sebelum_pajak_service'];
		}
		if(empty($get_opt['cashier_max_pembulatan'])){
			$get_opt['cashier_max_pembulatan'] = 0;
		}
		if(empty($get_opt['cashier_pembulatan_keatas'])){
			$get_opt['cashier_pembulatan_keatas'] = 0;
		}
		
		if(empty($date_from) OR empty($date_till)){
			die('Billing Paid Not Found!');
		}else{
				
			if(empty($date_from)){ $date_from = date('Y-m-d'); }
			if(empty($date_till)){ $date_till = date('Y-m-d'); }
			
			$mktime_dari = strtotime($date_from);
			$mktime_sampai = strtotime($date_till);
						
			$qdate_from = date("Y-m-d",strtotime($date_from));
			$qdate_till = date("Y-m-d",strtotime($date_till));
			$qdate_till_max = date("Y-m-d",strtotime($date_till)+ONE_DAY_UNIX);
			
			$add_where = "(a.payment_date >= '".$qdate_from." 00:00:00' AND a.payment_date <= '".$qdate_till_max." 23:59:59')";
			
			$this->db->select("a.*, a.id as billing_id, a.updated as billing_date, d.payment_type_name, e.user_firstname, e.user_lastname, f.bank_name");
			$this->db->from($this->table." as a");
			$this->db->join($this->prefix.'payment_type as d','d.id = a.payment_id','LEFT');
			$this->db->join($this->prefix_apps.'users as e','e.user_username = a.updatedby','LEFT');
			$this->db->join($this->prefix.'bank as f','f.id = a.bank_id','LEFT');
			$this->db->where("a.billing_status", 'paid');
			$this->db->where("a.is_deleted", 0);
			$this->db->where($add_where);
			
			if(!empty($tipe)){
				if($tipe != 'null'){
					$this->db->where("a.table_id", $tipe);
				}
			}
			
			if(empty($sorting)){
				$this->db->order_by("payment_date","ASC");
			}else{
				$this->db->order_by($sorting,"ASC");
			}
			
			if(!empty($user_cashier) AND $user_cashier != 'All'){
				$this->db->where('a.updatedby', $user_cashier);
			}
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['report_data'] = $get_dt->result_array();
				
			}
			
			
			//PAYMENT DATA
			$dt_payment_name = array();
			$this->db->select('*');
			$this->db->from($this->prefix.'payment_type');
			$get_dt_p = $this->db->get();
			if($get_dt_p->num_rows() > 0){
				foreach($get_dt_p->result_array() as $dtP){
					$dt_payment_name[$dtP['id']] = strtoupper($dtP['payment_type_name']);
				}
			}
			
			$all_bil_id = array();
			$newData = array();
			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
					$s['billing_date'] = date("d-m-Y H:i",strtotime($s['created']));					
					$s['payment_date'] = date("d-m-Y H:i",strtotime($s['payment_date']));
				
					if(!in_array($s['id'], $all_bil_id)){
						$all_bil_id[] = $s['id'];
					}		
					
					$s['total_billing_awal'] = $s['total_billing'];

					
					//CHECK REAL TOTAL BILLING
					if(!empty($s['include_tax']) OR !empty($s['include_service'])){
						if(!empty($s['include_tax']) AND !empty($s['include_service'])){
						
							if($data_post['diskon_sebelum_pajak_service'] == 1){
								$get_total_billing = $s['total_billing'] / (($s['tax_percentage']+$s['service_percentage']+100)/100);
								$get_total_billing = priceFormat($get_total_billing, 0, ".", "");
								$s['total_billing'] = $get_total_billing;
							}else{
								$s['total_billing'] = $s['total_billing'] - ($s['tax_total'] + $s['service_total']);
							}
							
						}else{
							if(!empty($s['include_tax'])){
								if($data_post['diskon_sebelum_pajak_service'] == 1){
									$get_total_billing = $s['total_billing'] / (($s['tax_percentage']+100)/100);
									$get_total_billing = priceFormat($get_total_billing, 0, ".", "");
									$s['total_billing'] = $get_total_billing;
								}else{
									$s['total_billing'] = $s['total_billing'] - ($s['tax_total']);
								}
							}
							if(!empty($s['include_service'])){
								if($data_post['diskon_sebelum_pajak_service'] == 1){
									$get_total_billing = $s['total_billing'] / (($s['service_percentage']+100)/100);
									$get_total_billing = priceFormat($get_total_billing, 0, ".", "");
									$s['total_billing'] = $get_total_billing;
								}else{
									$s['total_billing'] = $s['total_billing'] - ($s['service_total']);
								}
							}
						}
					}
					
					if(!empty($s['is_compliment'])){
						$s['total_billing'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'];
						//if(!empty($s['include_tax']) OR !empty($s['include_service'])){
						//	$s['total_billing'] = $s['total_billing'];
						//}
						$s['service_total'] = 0;
						$s['tax_total'] = 0;
					}		
					
					//diskon_sebelum_pajak_service
					if($data_post['diskon_sebelum_pajak_service'] == 0){
						$s['sub_total'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'];
					}else{
						$s['sub_total'] = $s['total_billing'] - $s['discount_total'] + $s['tax_total'] + $s['service_total'];
						
						if(!empty($s['include_tax']) OR !empty($s['include_service'])){
							//CHECKING BALANCE #1
							if(empty($s['discount_total'])){
								if($s['sub_total'] != $s['total_billing_awal']){
									$s['total_billing'] = ($s['total_billing_awal'] - ($s['tax_total'] + $s['service_total']));
									$s['sub_total'] = $s['total_billing'] - $s['discount_total'] + $s['tax_total'] + $s['service_total'];
								}
							}else{
								if(($s['sub_total'] + $s['total_pembulatan']) != $s['grand_total']){
									$s['sub_total'] = ($s['grand_total']-$s['total_pembulatan'])+$s['compliment_total'];
								}
								
								$cek_total_billing = $s['sub_total'] - ($s['tax_total'] + $s['service_total']) + $s['discount_total'];
								if($s['total_billing'] != $cek_total_billing){
									$s['total_billing'] = $cek_total_billing;
								}
							}
						}
						
						
					}
					
					//SPLIT DISCOUNT TYPE
					if(!empty($s['discount_total']) AND $s['discount_perbilling'] == 1){
						$s['discount_billing_total'] = $s['discount_total'];
						$s['discount_total'] = 0;
					}else{
						$s['discount_billing_total'] = 0;
					}
					
					//if(!empty($s['include_tax']) OR !empty($s['include_service'])){
					//	$s['sub_total'] = $s['total_billing'];
					//}
					
					$s['grand_total'] = $s['sub_total'] + $s['total_pembulatan'];
					$s['grand_total'] -= $s['compliment_total'];
					
					//diskon_sebelum_pajak_service
					if($data_post['diskon_sebelum_pajak_service'] == 0){
						$s['grand_total'] -= $s['discount_total'];
						$s['grand_total'] -= $s['discount_billing_total'];
					}
					
					if($s['grand_total'] <= 0){
						$s['grand_total'] = 0;
					}
					
					
					$s['total_pembulatan_show'] = priceFormat($s['total_pembulatan']);
					
					if($s['total_pembulatan'] < 0){
						$s['total_pembulatan_show'] = "(".priceFormat($s['total_pembulatan']).")";
					}
					
					$s['sub_total_show'] = priceFormat($s['sub_total']);
					$s['total_billing_show'] = priceFormat($s['total_billing']);
					$s['grand_total_show'] = priceFormat($s['grand_total']);
					$s['total_paid_show'] = priceFormat($s['total_paid']);
					$s['tax_total_show'] = priceFormat($s['tax_total']);
					$s['service_total_show'] = priceFormat($s['service_total']);
					$s['discount_total_show'] = priceFormat($s['discount_total']);
					$s['discount_billing_total_show'] = priceFormat($s['discount_billing_total']);
					$s['user_fullname'] = $s['user_firstname'].' '.$s['user_lastname'];
					
					//DP
					$s['total_dp_show'] = priceFormat($s['total_dp']);
					/*if($s['total_cash'] == 0){
						if($s['total_credit'] > $s['total_dp']){
							$s['total_credit'] -= $s['total_dp'];
						}
					}else{
						if($s['total_cash'] > $s['total_dp']){
							$s['total_cash'] -= $s['total_dp'];
						}
					}*/
					
					$s['total_compliment'] = 0;
					$s['total_compliment_show'] = 0;
					
					$s['total_hpp'] = 0;
					$s['total_hpp_show'] = 0;
					$s['total_profit'] = 0;
					$s['total_profit_show'] = 0;
					
					//CARD NO 
					$card_no = '';
					if(strlen($s['card_no']) > 30){
						$card_no = $s['card_no'];
						$card_no = str_replace(";","",$card_no);
						$card_no = str_replace("?","",$card_no);
						$card_no_exp = explode("=", $card_no);
						if(!empty($card_no_exp[0])){
							$card_no = trim($card_no_exp[0]);
						}
					}else{
						$card_no = trim($s['card_no']);
					}
					
					//NOTES
					$s['payment_note'] = '';
					if(!empty($s['is_compliment']) OR !empty($s['compliment_total'])){
						$s['payment_note'] = 'COMPLIMENT';
						//$s['total_compliment'] = $s['grand_total'];
						$s['total_compliment'] = $s['compliment_total'];
						$s['total_compliment_show'] = priceFormat($s['total_compliment']);
					}else{
					
						if(!empty($s['is_half_payment'])){
							$s['payment_note'] = 'HALF PAYMENT';
						}
						
						if(strtolower($s['payment_type_name']) != 'cash'){
							$s['payment_note'] = strtoupper($s['bank_name']).' '.$card_no;
						}
					}
					
					if(!empty($s['billing_notes'])){
						if(!empty($s['payment_note'])){
							$s['payment_note'] .= '<br/>'.$s['billing_notes'];
						}else{
							$s['payment_note'] .= $s['billing_notes'];
						}
					}
												
					$newData[$s['id']] = $s;
					//array_push($newData, $s);
					
				}
			}
			
			//calc detail
			$total_hpp = array();
			if(!empty($all_bil_id)){
				$all_bil_id_txt = implode(",",$all_bil_id);
				$this->db->from($this->table2);
				$this->db->where('billing_id IN ('.$all_bil_id_txt.')');
				$this->db->where('is_deleted', 0);
				$get_detail = $this->db->get();
				if($get_detail->num_rows() > 0){
					foreach($get_detail->result() as $dtRow){
			
						$total_qty = $dtRow->order_qty;
						/*
						 $total_qty = $dtRow->order_qty - $dtRow->retur_qty;
						if($total_qty < 0){
						$total_qty = 0;
						}*/
			
						if(empty($total_hpp[$dtRow->billing_id])){
							$total_hpp[$dtRow->billing_id] = 0;
						}
			
						$total_hpp[$dtRow->billing_id] += $dtRow->product_price_hpp * $total_qty;
			
			
					}
				}
			}
			
			$newData_switch = $newData;
			$newData = array();
			if(!empty($newData_switch)){
				foreach($newData_switch as $dt){
					
					if(!empty($total_hpp[$dt['billing_id']])){
						$dt['total_hpp'] = $total_hpp[$dt['billing_id']];
					}
					
					$dt['total_profit'] = $dt['total_billing']-$dt['total_hpp'];
					$dt['total_hpp_show'] = priceFormat($dt['total_hpp']);
					$dt['total_profit_show'] = priceFormat($dt['total_profit']);
					
					$newData[] = $dt;
				}
			}
	
			$data_post['report_data'] = $newData;
			$data_post['payment_data'] = $dt_payment_name;
			
			if(!empty($user_cashier) && $user_cashier != 'null'){
				$this->db->select("user_firstname, user_lastname");
				$this->db->from($this->prefix_apps.'users');
				$this->db->where('user_username', $user_cashier);			
				$get_cashier = $this->db->get();
				if($get_cashier->num_rows() > 0){
					$dt_cashier = $get_cashier->row_array();
					$data_post['cashier_name'] = $dt_cashier['user_firstname'].' '.$dt_cashier['user_lastname'];
				}
			}
						
		}
		
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}else{
			$do = '';
		}

		if(empty($useview)){
			$useview = 'print_reportSalesByCashier';
			$data_post['report_name'] = 'SALES REPORT BY CASHIER';
			
			if($do == 'excel'){
				$useview = 'excel_reportSalesByCashier';
			}
			
		}else{
			$useview = 'print_reportProfitSalesByCashier';
			$data_post['report_name'] = 'SALES PROFIT REPORT BY CASHIER';
			
			if($do == 'excel'){
				$useview = 'excel_reportProfitSalesByCashier';
			}
			
		}
		
		$this->load->view('../../billing/views/'.$useview, $data_post);
		
	}
	
	public function print_reportSalesByShift(){
		
		$this->table = $this->prefix.'billing';
		$this->table2 = $this->prefix.'billing_detail';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('Sesi Login sudah habis, Silahkan Login ulang!');
		}
		
		extract($_GET);

		if(empty($date_from)){ $date_from = date('Y-m-d'); }
		if(empty($date_till)){ $date_till = date('Y-m-d'); }
		
		if(empty($sorting)){
			$sorting = 'payment_date';
		}
		
		$data_post = array(
			'do'	=> '',
			'report_data'	=> array(),
			'report_place_default'	=> '',
			'report_name'	=> 'SALES REPORT BY SHIFT',
			'date_from'	=> $date_from,
			'user_shift'	=> 'All Shift',
			'diskon_sebelum_pajak_service'	=> 0
			//'user_cashier'	=> $user_cashier,
			//'user_fullname'	=> $user_fullname
		);
		
		$get_opt = get_option_value(array('report_place_default','diskon_sebelum_pajak_service','cashier_max_pembulatan','cashier_pembulatan_keatas'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		if(!empty($get_opt['diskon_sebelum_pajak_service'])){
			$data_post['diskon_sebelum_pajak_service'] = $get_opt['diskon_sebelum_pajak_service'];
		}
		if(empty($get_opt['cashier_max_pembulatan'])){
			$get_opt['cashier_max_pembulatan'] = 0;
		}
		if(empty($get_opt['cashier_pembulatan_keatas'])){
			$get_opt['cashier_pembulatan_keatas'] = 0;
		}
		
		if(empty($date_from)){
			die('Billing Paid Not Found!');
		}else{
				
			if(empty($date_from)){ $date_from = date('Y-m-d'); }
			
			$mktime_dari = strtotime($date_from);
						
			$qdate_from = date("Y-m-d",strtotime($date_from));
			$qdate_till_max = date("Y-m-d",strtotime($date_from)+ONE_DAY_UNIX);
			
			//$add_where = "(a.payment_date >= '".$qdate_from." 00:00:00' AND a.payment_date <= '".$qdate_from_plus1." 23:59:59')";
			
			$where_shift_billing = '';
			if(!empty($shift_billing)){
				$skip_date = true;
				
				//get shift range
				$this->db->from($this->prefix.'open_close_shift');
				$this->db->where("user_shift",$shift_billing);
				$this->db->where("(tanggal_shift = '".$qdate_from."' OR (tipe_shift = 'close' AND tanggal_shift = '".$qdate_till_max."' 
				AND created <= '".$qdate_till_max." 23:59:59'))");
				$get_shift = $this->db->get();
				
				if($get_shift->num_rows() > 0){
					
					$data_shift = array();
					foreach($get_shift->result() as $dtS){
						if(empty($data_shift[$dtS->user_shift])){
							$data_shift[$dtS->user_shift] = array(
								'jam_from' => '',
								'jam_till' => ''
							);
						}
						
						if($dtS->tipe_shift == 'open'){
							$data_shift[$dtS->user_shift]['jam_from'] = $dtS->jam_shift;		
						}
						
						if($dtS->tipe_shift == 'close'){
							$data_shift[$dtS->user_shift]['jam_till'] = $dtS->jam_shift;
						}
						
					}
					
					if(!empty($data_shift[$shift_billing])){
						//FROM
						if(empty($data_shift[$shift_billing]['jam_from'])){
							if($shift_billing == 1){
								$data_shift[$shift_billing]['jam_from'] = '00:00'; //default													
								$qdate_till_max = date("Y-m-d",strtotime($date_from));
							}
							
							if($shift_billing == 2){
								$data_shift[$shift_billing]['jam_from'] = '00:00:00'; //default
								if(!empty($data_shift[1]['jam_till'])){
									//take from shift 1
									$data_shift[$shift_billing]['jam_from'] = $data_shift[1]['jam_till'].':59';
								}
							}
						}else{
							$data_shift[$shift_billing]['jam_from'] .= ':00';
						}
						
						//TILL
						if(empty($data_shift[$shift_billing]['jam_till'])){
							if($shift_billing == 1){
								$data_shift[$shift_billing]['jam_till'] = '23:59:59'; //default
								if(!empty($data_shift[2]['jam_from'])){
									//take from shift 2
									$data_shift[$shift_billing]['jam_till'] = $data_shift[1]['jam_from'].':00';
								}
							}
							
							if($shift_billing == 2){
								$data_shift[$shift_billing]['jam_till'] = '23:59:59'; //default
							}
							
						}else{
							$data_shift[$shift_billing]['jam_till'] .= ':00';
						}

						//$where_shift_billing = "(a.payment_date a.payment_date >= '".$qdate_from." ".$data_shift[$shift_billing]['jam_from']."'
						//AND a.payment_date <= '".$qdate_from." ".$data_shift[$shift_billing]['jam_till']."')";
						
						
					//$qdate_till_max = date("Y-m-d",strtotime($qdate_from)+ONE_DAY_UNIX);
					if($shift_billing == 1){
						$qdate_till_max = date("Y-m-d",strtotime($qdate_from));
					}else
					if($shift_billing == 2){
						$jam_shift = (int)substr($data_shift[$shift_billing]['jam_till'],0,2);
						if(strlen($jam_shift) == 1){
							//asumsi pagi
							$qdate_till_max = date("Y-m-d",strtotime($qdate_from)+ONE_DAY_UNIX);
						}else{
							$qdate_till_max = date("Y-m-d",strtotime($qdate_from));
						}
					}
					
					$where_shift_billing = "(a.payment_date >= '".$qdate_from." ".$data_shift[$shift_billing]['jam_from']."' AND a.payment_date <= '".$qdate_till_max." ".$data_shift[$shift_billing]['jam_till']."')";
					
					/*	$where_shift_billing = "(DATE_FORMAT(a.payment_date, '%Y-%m-%d') = '".$qdate_from."') 
					AND (DATE_FORMAT(a.payment_date, '%H:%i:%s') BETWEEN '".$data_shift[$shift_billing]['jam_from']."' AND '".$data_shift[$shift_billing]['jam_till']."')";*/
					}
				}else{
					
					//$where_shift_billing = "(DATE_FORMAT(a.payment_date, '%Y-%m-%d') = '".$qdate_from."')  AND (DATE_FORMAT(a.payment_date, '%H:%i:%s') BETWEEN '00:00:00' AND '24:00:00')";
					//$where_shift_billing = '(a.id = "-1")';
					$where_shift_billing = "(a.payment_date >= '".$qdate_from." 00:00:00' AND a.payment_date <= '".$qdate_till_max." 23:59:59')";
						
					
				}
				
				if($shift_billing == 1){
					$data_post['user_shift'] = 'Morning Shift';
				}else
				if($shift_billing == 2){
					$data_post['user_shift'] = 'Evening Shift';
				}
				
			}else{
				//$where_shift_billing = "(DATE_FORMAT(a.payment_date, '%Y-%m-%d') = '".$qdate_from."')  AND (DATE_FORMAT(a.payment_date, '%H:%i:%s') BETWEEN '00:00:00' AND '24:00:00')";
				$where_shift_billing = "(a.payment_date >= '".$qdate_from." 00:00:00' AND a.payment_date <= '".$qdate_till_max." 23:59:59')";
			}
			
			$this->db->select("a.*, a.id as billing_id, a.updated as billing_date, d.payment_type_name, e.user_firstname, e.user_lastname, f.bank_name");
			$this->db->from($this->table." as a");
			$this->db->join($this->prefix.'payment_type as d','d.id = a.payment_id','LEFT');
			$this->db->join($this->prefix_apps.'users as e','e.user_username = a.updatedby','LEFT');
			$this->db->join($this->prefix.'bank as f','f.id = a.bank_id','LEFT');
			$this->db->where("a.billing_status", 'paid');
			$this->db->where("a.is_deleted", 0);
			
			if(!empty($where_shift_billing)){
				$this->db->where($where_shift_billing);
			}
			
			if(!empty($tipe)){
				if($tipe != 'null'){
					$this->db->where("a.table_id", $tipe);
				}
			}
			
			if(empty($sorting)){
				$this->db->order_by("payment_date","ASC");
			}else{
				$this->db->order_by($sorting,"ASC");
			}
			
			if(!empty($add_where)){
				$this->db->where($add_where);
			}
						
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['report_data'] = $get_dt->result_array();
				
			}
			$this->db->last_query();
			
			//PAYMENT DATA
			$dt_payment_name = array();
			$this->db->select('*');
			$this->db->from($this->prefix.'payment_type');
			$get_dt_p = $this->db->get();
			if($get_dt_p->num_rows() > 0){
				foreach($get_dt_p->result_array() as $dtP){
					$dt_payment_name[$dtP['id']] = strtoupper($dtP['payment_type_name']);
				}
			}
			
			$all_bil_id = array();
			$newData = array();
			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
					$s['billing_date'] = date("d-m-Y H:i",strtotime($s['created']));					
					$s['payment_date'] = date("d-m-Y H:i",strtotime($s['payment_date']));
				
					if(!in_array($s['id'], $all_bil_id)){
						$all_bil_id[] = $s['id'];
					}		
					
					$s['total_billing_awal'] = $s['total_billing'];

					
					//CHECK REAL TOTAL BILLING
					if(!empty($s['include_tax']) OR !empty($s['include_service'])){
						if(!empty($s['include_tax']) AND !empty($s['include_service'])){
						
							if($data_post['diskon_sebelum_pajak_service'] == 1){
								$get_total_billing = $s['total_billing'] / (($s['tax_percentage']+$s['service_percentage']+100)/100);
								$get_total_billing = priceFormat($get_total_billing, 0, ".", "");
								$s['total_billing'] = $get_total_billing;
							}else{
								$s['total_billing'] = $s['total_billing'] - ($s['tax_total'] + $s['service_total']);
							}
							
						}else{
							if(!empty($s['include_tax'])){
								if($data_post['diskon_sebelum_pajak_service'] == 1){
									$get_total_billing = $s['total_billing'] / (($s['tax_percentage']+100)/100);
									$get_total_billing = priceFormat($get_total_billing, 0, ".", "");
									$s['total_billing'] = $get_total_billing;
								}else{
									$s['total_billing'] = $s['total_billing'] - ($s['tax_total']);
								}
							}
							if(!empty($s['include_service'])){
								if($data_post['diskon_sebelum_pajak_service'] == 1){
									$get_total_billing = $s['total_billing'] / (($s['service_percentage']+100)/100);
									$get_total_billing = priceFormat($get_total_billing, 0, ".", "");
									$s['total_billing'] = $get_total_billing;
								}else{
									$s['total_billing'] = $s['total_billing'] - ($s['service_total']);
								}
							}
						}
					}
					
					if(!empty($s['is_compliment'])){
						$s['total_billing'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'];
						//if(!empty($s['include_tax']) OR !empty($s['include_service'])){
						//	$s['total_billing'] = $s['total_billing'];
						//}
						$s['service_total'] = 0;
						$s['tax_total'] = 0;
					}		
					
					//diskon_sebelum_pajak_service
					if($data_post['diskon_sebelum_pajak_service'] == 0){
						$s['sub_total'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'];
					}else{
						$s['sub_total'] = $s['total_billing'] - $s['discount_total'] + $s['tax_total'] + $s['service_total'];
						
						
						if(!empty($s['include_tax']) OR !empty($s['include_service'])){
							//CHECKING BALANCE #1
							if(empty($s['discount_total'])){
								if($s['sub_total'] != $s['total_billing_awal']){
									$s['total_billing'] = ($s['total_billing_awal'] - ($s['tax_total'] + $s['service_total']));
									$s['sub_total'] = $s['total_billing'] - $s['discount_total'] + $s['tax_total'] + $s['service_total'];
								}
							}else{
								if(($s['sub_total'] + $s['total_pembulatan']) != $s['grand_total']){
									$s['sub_total'] = ($s['grand_total']-$s['total_pembulatan'])+$s['compliment_total'];
								}
								
								$cek_total_billing = $s['sub_total'] - ($s['tax_total'] + $s['service_total']) + $s['discount_total'];
								if($s['total_billing'] != $cek_total_billing){
									$s['total_billing'] = $cek_total_billing;
								}
							}
						}
						
						
					}
					
					//SPLIT DISCOUNT TYPE
					if(!empty($s['discount_total']) AND $s['discount_perbilling'] == 1){
						$s['discount_billing_total'] = $s['discount_total'];
						$s['discount_total'] = 0;
					}else{
						$s['discount_billing_total'] = 0;
					}
					
					//if(!empty($s['include_tax']) OR !empty($s['include_service'])){
					//	$s['sub_total'] = $s['total_billing'];
					//}
					
					$s['grand_total'] = $s['sub_total'] + $s['total_pembulatan'];
					$s['grand_total'] -= $s['compliment_total'];
					
					//diskon_sebelum_pajak_service
					if($data_post['diskon_sebelum_pajak_service'] == 0){
						$s['grand_total'] -= $s['discount_total'];
						$s['grand_total'] -= $s['discount_billing_total'];
					}
					
					if($s['grand_total'] <= 0){
						$s['grand_total'] = 0;
					}
					
					
					$s['total_pembulatan_show'] = priceFormat($s['total_pembulatan']);
					
					if($s['total_pembulatan'] < 0){
						$s['total_pembulatan_show'] = "(".priceFormat($s['total_pembulatan']).")";
					}
					
					$s['sub_total_show'] = priceFormat($s['sub_total']);
					$s['total_billing_show'] = priceFormat($s['total_billing']);
					$s['grand_total_show'] = priceFormat($s['grand_total']);
					$s['total_paid_show'] = priceFormat($s['total_paid']);
					$s['tax_total_show'] = priceFormat($s['tax_total']);
					$s['service_total_show'] = priceFormat($s['service_total']);
					$s['discount_total_show'] = priceFormat($s['discount_total']);
					$s['discount_billing_total_show'] = priceFormat($s['discount_billing_total']);
					$s['user_fullname'] = $s['user_firstname'].' '.$s['user_lastname'];
					
					//DP
					$s['total_dp_show'] = priceFormat($s['total_dp']);
					/*if($s['total_cash'] == 0){
						if($s['total_credit'] > $s['total_dp']){
							$s['total_credit'] -= $s['total_dp'];
						}
					}else{
						if($s['total_cash'] > $s['total_dp']){
							$s['total_cash'] -= $s['total_dp'];
						}
					}*/
					
					$s['total_compliment'] = 0;
					$s['total_compliment_show'] = 0;
					
					$s['total_hpp'] = 0;
					$s['total_hpp_show'] = 0;
					$s['total_profit'] = 0;
					$s['total_profit_show'] = 0;
					
					//CARD NO 
					$card_no = '';
					if(strlen($s['card_no']) > 30){
						$card_no = $s['card_no'];
						$card_no = str_replace(";","",$card_no);
						$card_no = str_replace("?","",$card_no);
						$card_no_exp = explode("=", $card_no);
						if(!empty($card_no_exp[0])){
							$card_no = trim($card_no_exp[0]);
						}
					}else{
						$card_no = trim($s['card_no']);
					}
					
					//NOTES
					$s['payment_note'] = '';
					if(!empty($s['is_compliment']) OR !empty($s['compliment_total'])){
						$s['payment_note'] = 'COMPLIMENT';
						//$s['total_compliment'] = $s['grand_total'];
						$s['total_compliment'] = $s['compliment_total'];
						$s['total_compliment_show'] = priceFormat($s['total_compliment']);
					}else{
					
						if(!empty($s['is_half_payment'])){
							$s['payment_note'] = 'HALF PAYMENT';
						}
						
						if(strtolower($s['payment_type_name']) != 'cash'){
							$s['payment_note'] = strtoupper($s['bank_name']).' '.$card_no;
						}
					}
					
					if(!empty($s['billing_notes'])){
						if(!empty($s['payment_note'])){
							$s['payment_note'] .= '<br/>'.$s['billing_notes'];
						}else{
							$s['payment_note'] .= $s['billing_notes'];
						}
					}
					
					$newData[$s['id']] = $s;
					//array_push($newData, $s);
					
				}
			}
			
			//calc detail
			$total_hpp = array();
			if(!empty($all_bil_id)){
				$all_bil_id_txt = implode(",",$all_bil_id);
				$this->db->from($this->table2);
				$this->db->where('billing_id IN ('.$all_bil_id_txt.')');
				$this->db->where('is_deleted', 0);
				$get_detail = $this->db->get();
				if($get_detail->num_rows() > 0){
					foreach($get_detail->result() as $dtRow){
							
						$total_qty = $dtRow->order_qty;
						/*
						 $total_qty = $dtRow->order_qty - $dtRow->retur_qty;
						if($total_qty < 0){
						$total_qty = 0;
						}*/
							
						if(empty($total_hpp[$dtRow->billing_id])){
							$total_hpp[$dtRow->billing_id] = 0;
						}
							
						$total_hpp[$dtRow->billing_id] += $dtRow->product_price_hpp * $total_qty;
							
							
					}
				}
			}
				
			$newData_switch = $newData;
			$newData = array();
			if(!empty($newData_switch)){
				foreach($newData_switch as $dt){
						
					if(!empty($total_hpp[$dt['billing_id']])){
						$dt['total_hpp'] = $total_hpp[$dt['billing_id']];
					}
						
					$dt['total_profit'] = $dt['total_billing']-$dt['total_hpp'];
					$dt['total_hpp_show'] = priceFormat($dt['total_hpp']);
					$dt['total_profit_show'] = priceFormat($dt['total_profit']);
						
					$newData[] = $dt;
				}
			}
	
			$data_post['report_data'] = $newData;
			$data_post['payment_data'] = $dt_payment_name;
			
			if(!empty($user_cashier) && $user_cashier != 'null'){
				$this->db->select("user_firstname, user_lastname");
				$this->db->from($this->prefix_apps.'users');
				$this->db->where('user_username', $user_cashier);			
				$get_cashier = $this->db->get();
				if($get_cashier->num_rows() > 0){
					$dt_cashier = $get_cashier->row_array();
					$data_post['cashier_name'] = $dt_cashier['user_firstname'].' '.$dt_cashier['user_lastname'];
				}
			}
						
		}
		
		//echo '<pre>';
		//print_r($data_post['report_data']);
		//die();
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}else{
			$do = '';
		}

		if(empty($useview)){
			$useview = 'print_reportSalesByShift';
			$data_post['report_name'] = 'SALES REPORT BY SHIFT';
			
			if($do == 'excel'){
				$useview = 'excel_reportSalesByShift';
			}
			
		}else{
			$useview = 'print_reportProfitSalesByShift';
			$data_post['report_name'] = 'SALES PROFIT REPORT BY SHIFT';
			
			if($do == 'excel'){
				$useview = 'excel_reportProfitSalesByShift';
			}
		}
		
		$this->load->view('../../billing/views/'.$useview, $data_post);
		
	}
	
	public function print_reportCancelBilling(){
		
		$this->table = $this->prefix.'billing';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('Sesi Login sudah habis, Silahkan Login ulang!');
		}
		
		extract($_GET);
		
		$data_post = array(
			'do'	=> '',
			'report_data'	=> array(),
			'report_place_default'	=> '',
			'report_name'	=> 'CANCEL BILLING REPORT',
			'date_from'	=> $date_from,
			'date_till'	=> $date_till,
			'user_fullname'	=> $user_fullname,
			'diskon_sebelum_pajak_service'	=> 0
		);
		
		$get_opt = get_option_value(array('report_place_default','diskon_sebelum_pajak_service'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		
		if(empty($date_from) OR empty($date_till)){
			die('Billing Paid Not Found!');
		}else{
				
			if(empty($date_from)){ $date_from = date('Y-m-d'); }
			if(empty($date_till)){ $date_till = date('Y-m-d'); }
		
			if(empty($sorting)){
				$sorting = 'billing_date';
			}
			
			$mktime_dari = strtotime($date_from);
			$mktime_sampai = strtotime($date_till);
						
			$qdate_from = date("Y-m-d",strtotime($date_from));
			$qdate_till = date("Y-m-d",strtotime($date_till));
			$qdate_till_max = date("Y-m-d",strtotime($date_till)+ONE_DAY_UNIX);
			
			$add_where = "(a.updated >= '".$qdate_from." 00:00:00' AND a.updated <= '".$qdate_till_max." 23:59:59')";
			
			$this->db->select("a.*, a.id as billing_id, a.updated as billing_date, d.payment_type_name, e.bank_name, f.billing_no as merge_no");
			$this->db->from($this->table." as a");
			$this->db->join($this->prefix.'payment_type as d','d.id = a.payment_id','LEFT');
			$this->db->join($this->prefix.'bank as e','e.id = a.bank_id','LEFT');
			$this->db->join($this->table.' as f','f.id = a.merge_id','LEFT');
			$this->db->where("a.billing_status", 'cancel');
			$this->db->where("a.is_deleted", 0);
			$this->db->where($add_where);
			
			if(!empty($tipe)){
				if($tipe != 'null'){
					$this->db->where("a.table_id", $tipe);
				}
			}
			
			if(empty($sorting)){
				$this->db->order_by("billing_date","ASC");
			}else{
				$this->db->order_by($sorting,"ASC");
			}
			
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['report_data'] = $get_dt->result_array();				
			}
			
			//PAYMENT DATA
			$dt_payment_name = array();
			$this->db->select('*');
			$this->db->from($this->prefix.'payment_type');
			$get_dt_p = $this->db->get();
			if($get_dt_p->num_rows() > 0){
				foreach($get_dt_p->result_array() as $dtP){
					$dt_payment_name[$dtP['id']] = strtoupper($dtP['payment_type_name']);
				}
			}
			
			
			$all_bil_id = array();
			$newData = array();
			$dt_payment = array();
			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
					$s['billing_date'] = date("d-m-Y H:i",strtotime($s['created']));					
					$s['payment_date'] = date("d-m-Y H:i",strtotime($s['payment_date']));
					
					if(!in_array($s['id'], $all_bil_id)){
						$all_bil_id[] = $s['id'];
					}		
					
					$s['total_billing_awal'] = $s['total_billing'];

					
					//CHECK REAL TOTAL BILLING
					if(!empty($s['include_tax']) OR !empty($s['include_service'])){
						if(!empty($s['include_tax']) AND !empty($s['include_service'])){
						
							if($data_post['diskon_sebelum_pajak_service'] == 1){
								$get_total_billing = $s['total_billing'] / (($s['tax_percentage']+$s['service_percentage']+100)/100);
								$get_total_billing = priceFormat($get_total_billing, 0, ".", "");
								$s['total_billing'] = $get_total_billing;
							}else{
								$s['total_billing'] = $s['total_billing'] - ($s['tax_total'] + $s['service_total']);
							}
							
						}else{
							if(!empty($s['include_tax'])){
								if($data_post['diskon_sebelum_pajak_service'] == 1){
									$get_total_billing = $s['total_billing'] / (($s['tax_percentage']+100)/100);
									$get_total_billing = priceFormat($get_total_billing, 0, ".", "");
									$s['total_billing'] = $get_total_billing;
								}else{
									$s['total_billing'] = $s['total_billing'] - ($s['tax_total']);
								}
							}
							if(!empty($s['include_service'])){
								if($data_post['diskon_sebelum_pajak_service'] == 1){
									$get_total_billing = $s['total_billing'] / (($s['service_percentage']+100)/100);
									$get_total_billing = priceFormat($get_total_billing, 0, ".", "");
									$s['total_billing'] = $get_total_billing;
								}else{
									$s['total_billing'] = $s['total_billing'] - ($s['service_total']);
								}
							}
						}
					}
					
					if(!empty($s['is_compliment'])){
						$s['total_billing'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'];
						$s['service_total'] = 0;
						$s['tax_total'] = 0;
					}	
					
					//diskon_sebelum_pajak_service
					if($data_post['diskon_sebelum_pajak_service'] == 0){
						$s['sub_total'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'];
					}else{
						$s['sub_total'] = $s['total_billing'] - $s['discount_total'] + $s['tax_total'] + $s['service_total'];
						
						
						if(!empty($s['include_tax']) OR !empty($s['include_service'])){
							//CHECKING BALANCE #1
							if(empty($s['discount_total'])){
								if($s['sub_total'] != $s['total_billing_awal']){
									$s['total_billing'] = ($s['total_billing_awal'] - ($s['tax_total'] + $s['service_total']));
									$s['sub_total'] = $s['total_billing'] - $s['discount_total'] + $s['tax_total'] + $s['service_total'];
								}
							}else{
								if(($s['sub_total'] + $s['total_pembulatan']) != $s['grand_total']){
									$s['sub_total'] = ($s['grand_total']-$s['total_pembulatan'])+$s['compliment_total'];
								}
								
								$cek_total_billing = $s['sub_total'] - ($s['tax_total'] + $s['service_total']) + $s['discount_total'];
								if($s['total_billing'] != $cek_total_billing){
									$s['total_billing'] = $cek_total_billing;
								}
							}
						}
						
						
					}
					
					//MERGE
					if(!empty($s['merge_id'])){
						$s['total_billing'] = 0;
						$s['tax_total'] = 0;
						$s['service_total'] = 0;
						$s['discount_total'] = 0;
						$s['total_dp'] = 0;
						$s['grand_total'] = 0;
						$s['total_pembulatan'] = 0;
						$s['billing_notes'] = 'Merge Billing: '.$s['merge_no'];
					}
					
					$s['grand_total'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'];
					$s['grand_total_show'] = priceFormat($s['grand_total']);
					$s['total_billing_show'] = priceFormat($s['total_billing']);
					$s['total_paid_show'] = priceFormat($s['total_paid']);
					$s['tax_total_show'] = priceFormat($s['tax_total']);
					$s['service_total_show'] = priceFormat($s['service_total']);
					$s['discount_total_show'] = priceFormat($s['discount_total']);
					
					//DP
					$s['total_dp_show'] = priceFormat($s['total_dp']);
					/*if($s['total_cash'] == 0){
						if($s['total_credit'] > $s['total_dp']){
							$s['total_credit'] -= $s['total_dp'];
						}
					}else{
						if($s['total_cash'] > $s['total_dp']){
							$s['total_cash'] -= $s['total_dp'];
						}
					}*/
					
					//CARD NO 
					$card_no = '';
					if(strlen($s['card_no']) > 30){
						$card_no = $s['card_no'];
						$card_no = str_replace(";","",$card_no);
						$card_no = str_replace("?","",$card_no);
						$card_no_exp = explode("=", $card_no);
						if(!empty($card_no_exp[0])){
							$card_no = trim($card_no_exp[0]);
						}
					}else{
						$card_no = trim($s['card_no']);
					}
					
					//NOTES
					$s['payment_note'] = '';
					if(!empty($s['is_compliment']) OR !empty($s['compliment_total'])){
						$s['payment_note'] = 'COMPLIMENT';
						//$s['total_compliment'] = $s['grand_total'];
						$s['total_compliment'] = $s['compliment_total'];
						$s['total_compliment_show'] = priceFormat($s['total_compliment']);
					}else{
					
						if(!empty($s['is_half_payment'])){
							$s['payment_note'] = 'HALF PAYMENT';
						}
						
						if(strtolower($s['payment_type_name']) != 'cash'){
							$s['payment_note'] = strtoupper($s['bank_name']).' '.$card_no;
						}
					}
					
					if(!empty($s['cancel_notes'])){
						if(!empty($s['payment_note'])){
							$s['payment_note'] .= '<br/>'.$s['cancel_notes'];
						}else{
							$s['payment_note'] .= $s['cancel_notes'];
						}
					}
										
					$newData[$s['id']] = $s;
					//array_push($newData, $s);
					
				}
			}
			
			$newData_switch = $newData;
			$newData = array();
			if(!empty($newData_switch)){
				foreach($newData_switch as $dt){
					$newData[] = $dt;
				}
			}
	
			$data_post['report_data'] = $newData;
			$data_post['payment_data'] = $dt_payment_name;
		}
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}else{
			$do = '';
		}
		
		$useview = 'print_reportCancelBilling';
		if($do == 'excel'){
			$useview = 'excel_reportCancelBilling';
		}
		
		$this->load->view('../../billing/views/'.$useview, $data_post);	
	}
	
	
}