<?php
class Model_DataStructure extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'clients_structure';
	}
	
	

} 