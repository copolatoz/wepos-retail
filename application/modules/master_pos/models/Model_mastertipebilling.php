<?php
class Model_MasterTipeBilling extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'table';
	}
	
	

} 