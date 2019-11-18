<?php
class Model_purchasing extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		
		$this->prefix = config_item('db_prefix2');
		$this->table = $this->prefix.'purchasing';
		$this->table_detail = $this->prefix.'purchasing_detail';
		$this->table_account_payable = $this->prefix.'account_payable';
		$this->table_supplier = $this->prefix.'supplier';
	}
	
	function update_status_Purchasing($purchasing_id = ''){
		
		if(empty($purchasing_id)){
			return false;
		}
		
		//CEK Current Purchasing Detail
		$not_done = false;
		$this->db->from($this->table_detail);
		$this->db->where("purchasing_id = '".$purchasing_id."'");
		$get_detail = $this->db->get();
		if($get_detail->num_rows() > 0){
			foreach($get_detail->result() as $det){
				if($det->purchasing_detail_qty > 0){
					$not_done = true;
				}
			}
		}
		
		
		$status = 'done';
		if($not_done){
			$status = 'progress';
		}
		
		$dt_update = array('purchasing_status'  => $status);
		$update = $this->db->update($this->table, $dt_update, "id = '".$purchasing_id."'");
		
		return $update;
		
		
	}
	
	

} 