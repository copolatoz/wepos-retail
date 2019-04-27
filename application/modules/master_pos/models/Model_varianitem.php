<?php
class Model_VarianItem extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'varian_item';
	}
	
	

} 