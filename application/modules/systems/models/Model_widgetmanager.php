<?php
class Model_WidgetManager extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'widgets';
	}

} 