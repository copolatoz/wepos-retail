<?php
class Model_purchasingdetail extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->prefix = config_item('db_prefix2');
		$this->table = $this->prefix.'purchasing_detail';
		$this->table_purchasing_kode_unik = $this->prefix.'purchasing_kode_unik';
		$this->table_storehouse = $this->prefix.'storehouse';
	}
	
	function purchasingDetail($purchasingDetail = '', $purchasing_id = '', $update_stok = '', $data_kodeunik = array()){
				
		$session_user = $this->session->userdata('user_username');
		$get_temp_id = $this->input->post('temp_id');
		
		$update_stock_item_unit = array();
		$all_item_updated = array();
		$all_item_updated_price = array();
		
		$from_add = false;
		//$storehouse_id = 0;
		
		//form type
		$from_type = '';
		if($update_stok == 'add'){
			$update_stok = '';
			$from_type = $update_stok;
		}
		
		if($update_stok == 'update_add'){
			$update_stok = 'update';
			$from_add = true;
		}
		
		if(!empty($purchasingDetail)){
			
			if(empty($purchasing_id)){
				$purchasing_id = -1;
				$purchasing_number = -1;
			}
			
			$purchasing_status = 'progress';
			$dt_rowguid = array();
			
			$this->db->from($this->prefix.'purchasing');
			$this->db->where("id", $purchasing_id);
			$get_rowguid = $this->db->get();
			if($get_rowguid->num_rows() > 0){
				$dt_rowguid = $get_rowguid->row_array();
				$purchasing_status = $dt_rowguid['purchasing_status'];
				$purchasing_number = $dt_rowguid['purchasing_number'];
				$storehouse_id = $dt_rowguid['storehouse_id'];
				$use_tax = $dt_rowguid['use_tax'];
			}
			
			
			$all_purchasing_det_id = array();
			$all_purchasing_det_qty = array();
			$all_purchasing_item_qty = array();
			
			$dtCurrent = array();
			$dtCurrent_qty_before = array();
			
			$this->db->from($this->prefix.'purchasing_detail');
			
			if(!empty($get_temp_id)){
				$this->db->where("temp_id = '".$get_temp_id."' AND (purchasing_id = 0 OR purchasing_id IS NULL)");
			}else{
				$this->db->where("purchasing_id", $purchasing_id);
			}
			
			$get_det = $this->db->get();
			if($get_det->num_rows() > 0){
				
				foreach($get_det->result() as $dt){
					if(!in_array($dt->id, $dtCurrent) AND $from_add == false){
						$dtCurrent[] = $dt->id;
						if($purchasing_status == 'done'){
							$dtCurrent_qty_before[$dt->id] = $dt->purchasing_detail_qty;
						}else{
							$dtCurrent_qty_before[$dt->id] = 0;
						}
						
						if(!in_array($dt->id, $all_purchasing_det_id)){
							$all_purchasing_det_id[] = $dt->id;
						}
							
						if(empty($all_purchasing_det_qty[$dt->id])){
							$all_purchasing_det_qty[$dt->id] = 0;
						}
						$all_purchasing_det_qty[$dt->id] += $dt->purchasing_detail_qty;
						
						if(empty($all_purchasing_item_qty[$dt->item_id])){
							$all_purchasing_item_qty[$dt->item_id] = 0;
						}
						$all_purchasing_item_qty[$dt->item_id] += $dt->purchasing_detail_qty;
						
					}
					
					
					
				}
			}
			
			$purchasing_date = $dt_rowguid['purchasing_date'];
			
			//GET PRIMARY HOUSE
			if(empty($storehouse_id)){
				$storehouse_id = 0;
				$opt_value = array(
					'warehouse_primary'
				);
				$get_opt = get_option_value($opt_value);
				if(!empty($get_opt['warehouse_primary'])){
					$storehouse_id = $get_opt['warehouse_primary'];
				}
				
				if(empty($storehouse_id)){
					$this->db->from($this->table_storehouse);
					$this->db->where("is_primary = 1");
					$get_primary_storehouse = $this->db->get();
					if($get_primary_storehouse->num_rows() > 0){
						$storehouse_dt = $get_primary_storehouse->row();
						$storehouse_id = $storehouse_dt->id;
					}
				}
			}
			
			if(empty($storehouse_id)){
				return false;
			}
			
			$opt_value = array(
				'using_item_average_as_hpp'
			);
			$get_opt = get_option_value($opt_value);
			
			$using_item_average_as_hpp = 0;
			if(!empty($get_opt['using_item_average_as_hpp'])){
				$using_item_average_as_hpp = $get_opt['using_item_average_as_hpp'];
			}
			
			$total_qty = 0;
			$dtNew = array();
			$dtInsert_stock = array();
			$dtInsert = array();
			$dtUpdate = array();
			
			$dtInsert_kode_unik = array();
			$dtUpdate_kode_unik = array();
			$all_unik_kode = array();
			
			if(!empty($dt_rowguid) AND !empty($purchasingDetail)){
				foreach($purchasingDetail as $dt){
					
					$dt['purchasing_id'] = $purchasing_id;
					$dt['storehouse_id'] = $storehouse_id;
					$item_id_real = $dt['item_id'];
					$temp_id = $dt['temp_id'];
					
					$purchasing_det_date = date("Y-m-d",strtotime($purchasing_date));
					
					//SURE ONLY UPDATE!
					if(($update_stok == 'update' OR $update_stok == 'rollback') AND !empty($dt['purchasing_detail_qty'])){
						
						if(empty($update_stock_item_unit[$storehouse_id])){
							$update_stock_item_unit[$storehouse_id] = array();
						}
						
						$update_stock_item_unit[$storehouse_id][] = $item_id_real;
						
						if(!in_array($item_id_real,$all_item_updated)){
							$all_item_updated[] = $item_id_real;
						}
						
						if(empty($all_item_updated_price[$item_id_real])){
							$all_item_updated_price[$item_id_real] = 0;
							$all_item_updated_price[$item_id_real] = $dt['purchasing_detail_purchase'];
						}else{
							$all_item_updated_price[$item_id_real] = ($all_item_updated_price[$item_id_real] + $dt['purchasing_detail_purchase']) / 2;
						}
						
						$all_item_updated_price[$item_id_real] = priceFormat($all_item_updated_price[$item_id_real]);
						$all_item_updated_price[$item_id_real] = numberFormat($all_item_updated_price[$item_id_real]);
						
						$dtInsert_stock[] = array(
							"item_id" => $item_id_real,
							"trx_date" => $purchasing_det_date,
							"trx_type" => 'in',
							"trx_qty" => $dt['purchasing_detail_qty'],
							"unit_id" => $dt['unit_id'],
							"trx_nominal" => $dt['purchasing_detail_purchase'],
							"storehouse_id" => $storehouse_id,
							"trx_note" => 'Purchasing',
							"trx_ref_data" => $purchasing_number,
							"trx_ref_det_id" => $dt['id'],
							"is_active" => "1"
						);
					}
					
					$total_qty += ($dt['purchasing_detail_qty']);
					
					//check if new
					$dt['purchasing_id'] = $purchasing_id;
					$purchasingd_id = $dt['id'];
					$is_new = false;
					if(strstr($purchasingd_id, 'new')){
						
						//$purchasingd_id_exp = explode("-", $dt['id']);
						//unset($purchasingd_id_exp[3]);
						//$purchasingd_id = implode("-",$purchasingd_id_exp);
						
						$purchasingd_id = $temp_id;
						unset($dt['id']);
						$is_new = true;
					}
					
					//SN/IMEI --------------
					//if($dt['use_stok_kode_unik'] == 1){
						
						if(!empty($data_kodeunik[$purchasingd_id])){
							foreach($data_kodeunik[$purchasingd_id] as $dtD){
								if(!in_array($dtD['kode_unik'], $all_unik_kode)){
									$all_unik_kode[] = $dtD['kode_unik'];
									
									$dtInsert_kode_unik[] = array(
										"item_id" => $dt['item_id'],
										"kode_unik" => $dtD['kode_unik'],
										"ref_in" => $purchasing_number,
										"date_in" => $purchasing_det_date.' '.date("H:i:s"),
										"storehouse_id" => $storehouse_id,
										"item_hpp" => $dt['purchasing_detail_purchase'],
										"varian_name" => $dtD['varian_name'],
										"varian_group" => $dtD['varian_name'],
										"use_tax" => $dtD['use_tax']
									);
									
								}
							}
						}
					//}
					
					
					if($is_new){
					
						$dtInsert[] = $dt;
						
					}else{
							
						$dtUpdate[] = $dt;
					
						if(!in_array($dt['id'], $dtNew)){
							$dtNew[] = $dt['id'];
						}
						
					}
					
					
				}
			}
			
			//delete if not exist
			$dtDelete = array();
			$delete_all = false;
			if(!empty($dtNew)){
				foreach($dtCurrent as $dtR){
					if(!in_array($dtR, $dtNew)){
						$dtDelete[] = $dtR;
					}
				}
			}else{
				//delete all
				$dtDelete = $dtCurrent;
				$delete_all = true;
			}
			
			if(!empty($dtDelete)){
				
				$allRowguid = implode("','", $dtDelete);
				$this->db->where("id IN ('".$allRowguid."')");
				$this->db->delete($this->table); 
				
				//delete SN/IMEI detail
				if($delete_all == true AND $update_stok == 'update'){
					$allRowguid = implode("','", $dtDelete);
					$this->db->where("purchasingd_id IN ('".$allRowguid."')");
					$this->db->delete($this->table_purchasing_kode_unik); 
					//return array('dtDelete' => $dtDelete);
				}
			}
			
			if(!empty($dtInsert)){
				$this->db->insert_batch($this->table, $dtInsert);
				
			}
			
			if(!empty($dtUpdate)){
				$this->db->update_batch($this->table, $dtUpdate, 'id');
				
				//update SN/IMEI detail - not effected
			}	
			
			//UPDATE DETAIL & SN/IMEI
			//update SN/IMEI detail - update purchasingd_id
			$all_temp_update = array();
			$all_purchasingd_update = array();
			$update_temp_id = array();
			$all_update_temp_id = array();
			$this->db->from($this->prefix.'purchasing_detail');
			$this->db->where("purchasing_id", $purchasing_id);
			$get_det = $this->db->get();
			if($get_det->num_rows() > 0){
				foreach($get_det->result() as $dt){
					
					if(!empty($dt->temp_id)){
						$temp_id = $dt->temp_id.'-'.$dt->item_id;
						if(!in_array($temp_id, $all_temp_update)){
							$all_temp_update[] = $temp_id;
							$all_purchasingd_update[] = $dt->id;
							$update_temp_id[$temp_id] = $dt->id;
							$all_update_temp_id[] = array(
								'temp_id'		=> $temp_id,
								'purchasingd_id'=> $dt->id
							);
						}
					}
					
				}
			}
			
			//update detail id - kode unik
			if(!empty($all_update_temp_id)){
				$this->db->update_batch($this->table_purchasing_kode_unik, $all_update_temp_id, "temp_id");
			}
			
			//remove temp id - kode unik
			if(!empty($all_purchasingd_update)){
				$all_purchasingd_update_sql = implode(",", $all_purchasingd_update);
				$all_update_temp_data = array(
					'temp_id'	=> ''
				);
				$this->db->update($this->table_purchasing_kode_unik, $all_update_temp_data, "purchasingd_id IN (".$all_purchasingd_update_sql.")");
				$this->db->update($this->table, $all_update_temp_data, "id IN (".$all_purchasingd_update_sql.")");
				
			}
			
			
			
			
			if($update_stok == 'update' OR $update_stok == 'rollback'){
				
				if($update_stok == 'rollback'){
					//DELETE ALL STOCK
					$this->db->where("trx_ref_data", $purchasing_number);
					$this->db->delete($this->prefix."stock"); 
					
					$this->db->where("ref_in", $purchasing_number);
					$this->db->delete($this->prefix."item_kode_unik"); 
					
				}else{
					
					//UPDATE STOCK TRX
					if(!empty($dtInsert_stock)){
						$this->db->insert_batch($this->prefix.'stock', $dtInsert_stock);
						
						if(!empty($dtInsert_kode_unik)){
							$this->db->insert_batch($this->prefix.'item_kode_unik', $dtInsert_kode_unik);
						}
						
					}
				}
				
				
				//ITEM AVERAGE	
				if(!empty($all_item_updated)){
					//AVERAGE Items
					$update_item_price_average = array();
					$update_item_hpp = array();
					$all_item_updated_txt = implode("','", $all_item_updated);
					$this->db->where("id IN ('".$all_item_updated_txt."')");
					$this->db->from($this->prefix.'items'); 
					$get_items = $this->db->get();
					if($get_items->num_rows() > 0){
						foreach($get_items->result() as $dt){
							
							if(!empty($all_item_updated_price[$dt->id])){
							
								if(empty($dt->item_hpp)){
									$dt->item_hpp = $dt->item_price;
								}
								
								$item_hpp = $dt->item_hpp;
								$last_in  = $all_item_updated_price[$dt->id];
								$old_last_in  = $dt->last_in;
								
								if($update_stok == 'rollback'){
									$item_hpp = ($dt->item_hpp * 2) - $all_item_updated_price[$dt->id];
									$item_hpp = priceFormat($item_hpp);
									$item_hpp = numberFormat($item_hpp);
									
									$last_in = $dt->old_last_in;
									
								}else{
									$item_hpp = ($all_item_updated_price[$dt->id] + $dt->item_hpp) / 2;
									$item_hpp = priceFormat($item_hpp);
									$item_hpp = numberFormat($item_hpp);
								}
								
								$item_price = $dt->item_price;
								if(!empty($using_item_average_as_hpp)){
									$item_price = $item_hpp;
								}
								
								$update_item_price_average[] = array(
									'id'			=> $dt->id,
									//'item_price'	=> $item_price, --> buat jual item
									'item_hpp'		=> $item_hpp,
									'last_in'		=> $all_item_updated_price[$dt->id],
									'old_last_in'	=> $old_last_in
								);
								
								$update_item_hpp[$dt->id] = array(
									'id'			=> $dt->id,
									'item_hpp'		=> $item_hpp,
									'last_in'		=> $all_item_updated_price[$dt->id],
									'old_last_in'	=> $old_last_in
								);
								
							}
							
						}
					}
					
					if(!empty($update_item_price_average)){
						$this->db->update_batch($this->prefix."items", $update_item_price_average, "id");
					}
					
					//SUPPLIER ITEM
					$supplier_id = $dt_rowguid['supplier_id'];
					if(!empty($supplier_id)){
						$update_supplier_item_price = array();
						$all_item_updated_txt = implode("','", $all_item_updated);
						$this->db->where("item_id IN ('".$all_item_updated_txt."') AND supplier_id = '".$supplier_id."'");
						$this->db->from($this->prefix.'supplier_item'); 
						$get_items = $this->db->get();
						if($get_items->num_rows() > 0){
							foreach($get_items->result() as $dt){
								
								if(!empty($all_item_updated_price[$dt->item_id])){
									
									if(empty($dt->item_hpp)){
										$dt->item_hpp = $dt->item_price;
									}
									
									$item_hpp = $dt->item_hpp;
									$last_in  = $all_item_updated_price[$dt->item_id];
									$old_last_in  = $dt->last_in;
									
									if($update_stok == 'rollback'){
										$item_hpp = ($dt->item_hpp * 2) - $all_item_updated_price[$dt->item_id];
										$item_hpp = priceFormat($item_hpp);
										$item_hpp = numberFormat($item_hpp);
										
										$last_in = $dt->old_last_in;
										
									}else{
										$item_hpp = ($all_item_updated_price[$dt->item_id] + $dt->item_hpp) / 2;
										$item_hpp = priceFormat($item_hpp);
										$item_hpp = numberFormat($item_hpp);
									}
								
									$item_price = $dt->item_price;
									if(!empty($using_item_average_as_hpp)){
										$item_price = $item_hpp;
									}
									
									$update_supplier_item_price[] = array(
										'id'			=> $dt->id,
										'item_hpp'		=> $item_hpp,
										'item_price'	=> $item_price,
										'last_in'		=> $all_item_updated_price[$dt->item_id],
										'old_last_in'	=> $old_last_in
									);
									
								}
								
							}
						}
						
						if(!empty($update_supplier_item_price)){
							$this->db->update_batch($this->prefix."supplier_item", $update_supplier_item_price, "id");
						}
					}
					
					
					//CEK GRAMASI -> ITEM
					$update_hpp_gramasi = array();
					$gramasi_perproduct = array();
					$product_varian_hpp = array();
					$get_product_hpp = array();
					if(!empty($all_item_updated)){
						
						$all_item_updated_txt = implode(",", $all_item_updated);
						
						$this->db->select("a.id as gramasi_id, a.item_id, a.item_qty, a.item_price, a.varian_id, a.product_id, 
						b.product_price, b.product_hpp, b.from_item, b.id_ref_item, 
						c.id as product_varian_id, c.product_price as product_price_varian, c.product_hpp as product_hpp_varian");
						$this->db->from($this->prefix."product_gramasi as a");
						$this->db->join($this->prefix."product as b","b.id = a.product_id","LEFT");
						$this->db->join($this->prefix."product_varian as c","c.product_id = a.product_id AND c.varian_id = a.varian_id AND c.is_deleted = 0","LEFT");
						$this->db->where("a.item_id IN (".$all_item_updated_txt.")");
						$this->db->where("a.is_deleted", 0);
						$get_all_gramasi = $this->db->get();
						if($get_all_gramasi->num_rows() > 0){
							//$update_item_hpp
							foreach($get_all_gramasi->result() as $dt){
								
								if(!empty($update_item_hpp[$dt->item_id])){
									
									$varID = $dt->product_id."_".$dt->varian_id;
									$update_item = $update_item_hpp[$dt->item_id];
									
									//UPDATE TO ALL GRAMASI - ITEM
									$update_hpp_gramasi[] = array("id" => $dt->gramasi_id, "item_price" => $update_item['item_hpp']);
									
									$gramasi_perproduct[$varID][] = array("product_id" => $dt->product_id, "item_id" => $dt->item_id, "item_qty" => $dt->item_qty, "varian_id" => $dt->varian_id, "item_hpp" => $update_item['item_hpp']);
									if($dt->from_item == 1 AND $dt->id_ref_item == $dt->item_id){
										//PRODUK -> FROM ITEM & REF_ITEM --> PASTI ITEM = PRODUCT (GRAMASI TOTAL = 0)
										$product_from_item[$varID] =  array("product_id" => $dt->product_id, "item_id" => $dt->item_id, "item_qty" => $dt->item_qty, "varian_id" => $dt->varian_id, "item_hpp" => $update_item['item_hpp']);
									}
									
									if($dt->varian_id != 0){
									
										if(empty($product_varian_hpp[$dt->product_varian_id])){
											$product_varian_hpp[$dt->product_varian_id] = 0;
										}
										
										$selisih = ($dt->item_qty*$update_item['item_hpp']) - ($dt->item_qty*$dt->item_price);
										$product_varian_hpp[$dt->product_varian_id] += ($dt->product_hpp_varian+$selisih);
										
									}else{
										
										if(empty($get_product_hpp[$dt->product_id])){
											$get_product_hpp[$dt->product_id] = array("product_hpp" => $dt->product_hpp, "old_hpp" => 0, "update_hpp" => 0);
										}
										
										$get_product_hpp[$dt->product_id]["old_hpp"] += ($dt->item_qty*$dt->item_price);
										$get_product_hpp[$dt->product_id]["update_hpp"] += ($dt->item_qty*$update_item['item_hpp']);
										
									}
								}
							}
						}
						
						//UPDATE GRAMASI
						if(!empty($update_hpp_gramasi)){
							$this->db->update_batch($this->prefix."product_gramasi", $update_hpp_gramasi, "id");
						}
						
						//UPDATE TO ALL PRODUCT = ITEM
						$update_product_id = array();
						$update_product_hpp = array();
						if(!empty($product_from_item)){
							foreach($product_from_item as $varId => $dt){
								//cek jika gramasi total == 1
								if(!empty($gramasi_perproduct[$varID])){
									if(count($gramasi_perproduct[$varID]) == 1){
										//update product hpp
										$product_hpp = $dt['item_qty'] * $dt['item_hpp'];
										$update_product_hpp[] = array("id" => $dt['product_id'],"product_hpp" => $product_hpp);
										
										if(!in_array($dt['product_id'], $update_product_id)){
											$update_product_id[] = $dt['product_id'];
										}
										
									}
								}
							}
						}
						
						//UPDATE PRODUCT HPP FROM GRAMASI ITEM
						if(!empty($get_product_hpp)){
							foreach($get_product_hpp as $prodID => $dt){
								
								$selisih = $dt['update_hpp'] - $dt['old_hpp'];
								$product_hpp = $dt['product_hpp'] + $selisih;
								
								if(!in_array($prodID, $update_product_id)){
									$update_product_id[] = $prodID;
									$update_product_hpp[] = array("id" => $prodID,"product_hpp" => $product_hpp);
								}
								
							}
						}
						
						if(!empty($update_product_hpp)){
							$this->db->update_batch($this->prefix."product", $update_product_hpp, "id");
						}
						
						//UPDATE TO ALL PRODUCT VARIAN
						$update_product_varian_hpp = array();
						if(!empty($product_varian_hpp)){
							foreach($product_varian_hpp as $pvarId => $product_hpp){
								$update_product_varian_hpp[] = array("id" => $pvarId, "product_hpp" => $product_hpp);
							}
						}
						
						if(!empty($update_product_varian_hpp)){
							$this->db->update_batch($this->prefix."product_varian", $update_product_varian_hpp, "id");
						}
						
						
						//PRODUCT -> UPDATE KE PAKET (INCLUDE PRODUCT)
						$update_hpp_product_package = array();
						$package_varian_hpp = array();
						if(!empty($update_product_id)){
							$update_product_id_sql = implode(",", $update_product_id);
							$this->db->select("a.id as product_package_id, a.package_id, a.product_qty, a.product_price, a.product_hpp, a.varian_id, a.varian_id_item, 
							b.product_hpp as update_hpp, b2.product_hpp as package_hpp,
							c.id as package_varian_id, c.product_price as package_price_varian, c.product_hpp as package_hpp_varian,
							d.id as product_varian_id, d.product_price as product_price_varian, d.product_hpp as product_hpp_varian");
							$this->db->from($this->prefix."product_package as a");
							$this->db->join($this->prefix."product as b","b.id = a.product_id","LEFT");
							$this->db->join($this->prefix."product as b2","b2.id = a.package_id","LEFT");
							$this->db->join($this->prefix."product_varian as c","c.product_id = a.package_id AND c.varian_id = a.varian_id AND c.is_deleted = 0","LEFT"); //package varian
							$this->db->join($this->prefix."product_varian as d","d.product_id = b.id AND d.varian_id = a.varian_id_item AND d.is_deleted = 0","LEFT"); //product varian
							$this->db->where("a.product_id IN ($update_product_id_sql)");
							$this->db->where("a.is_deleted", 0);
							$get_all_package = $this->db->get();
							if($get_all_package->num_rows() > 0){
								
								foreach($get_all_package->result() as $dt){
									$varID = $dt->package_id."_".$dt->varian_id;
									$update_hpp = $dt->update_hpp;
									
									if(!empty($dt->varian_id_item)){
										$update_hpp = $dt->product_hpp_varian;
									}
									
									//UPDATE TO ALL PRODUCT PACKAGE
									$update_hpp_product_package[] = array("id" => $dt->product_package_id, "product_hpp" => $update_hpp);
									
									if($dt->varian_id != 0){
										
										if(empty($package_varian_hpp[$dt->package_varian_id])){
											$package_varian_hpp[$dt->package_varian_id] = 0;
										}
										
										$selisih = ($dt->product_qty*$update_hpp) - ($dt->product_qty*$dt->product_hpp);
										$package_varian_hpp[$dt->package_varian_id] += ($dt->package_hpp_varian+$selisih);
										
									}else{
										
										if(empty($get_package_hpp[$dt->package_id])){
											$get_package_hpp[$dt->package_id] = array("product_hpp" => $dt->package_hpp, "old_hpp" => 0, "update_hpp" => 0);
										}
										
										//????
										$get_package_hpp[$dt->package_id]["old_hpp"] += ($dt->product_qty*$dt->product_hpp);
										$get_package_hpp[$dt->package_id]["update_hpp"] += ($dt->product_qty*$update_hpp);
										
									}
								}
							}
						}
						
						//UPDATE PRODUCT PACKAGE
						if(!empty($update_hpp_product_package)){
							$this->db->update_batch($this->prefix."product_package", $update_hpp_product_package, "id");
						}
						
						//UPDATE PACKAGE HPP FROM PRODUCT PACKAGE
						$update_package_id = array();
						if(!empty($get_package_hpp)){
							foreach($get_package_hpp as $prodID => $dt){
								
								$selisih = $dt['update_hpp'] - $dt['old_hpp'];
								$product_hpp = $dt['product_hpp'] + $selisih;
								
								if(!in_array($prodID, $update_product_id)){
									$update_product_id[] = $prodID;
									$update_product_hpp[] = array("id" => $prodID,"product_hpp" => $product_hpp);
								}
								
							}
						}
						
						if(!empty($update_product_hpp)){
							$this->db->update_batch($this->prefix."product", $update_product_hpp, "id");
						}
						
							
						//UPDATE TO ALL PACKAGE VARIAN
						$update_package_varian_hpp = array();
						if(!empty($package_varian_hpp)){
							foreach($package_varian_hpp as $pvarId => $product_hpp){
								$update_package_varian_hpp[] = array("id" => $pvarId, "product_hpp" => $product_hpp);
							}
						}
						
						if(!empty($update_package_varian_hpp)){
							$this->db->update_batch($this->prefix."product_varian", $update_package_varian_hpp, "id");
						}
						
						
					}
					
				}
			}
			
			return array('dtPurchasing' => $dt_rowguid, 'dtInsert' => $dtInsert, 'dtUpdate' => $dtUpdate, 'dtDelete' => $dtDelete, 
			'all_purchasing_item_qty' => $all_purchasing_item_qty, 'all_purchasing_det_qty' => $all_purchasing_det_qty, 
			'dtCurrent_qty_before' => $dtCurrent_qty_before, 'update_stock' => $update_stock_item_unit, 'purchasing_status' => $purchasing_status);
		}
	}

} 