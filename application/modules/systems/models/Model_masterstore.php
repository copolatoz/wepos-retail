<?php
class Model_masterstore extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'clients';
	}

} 