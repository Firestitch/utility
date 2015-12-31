<?
	class VIEW_GENERATE_MODEL extends VIEW {
		
		private $_model			= "";
		private $_model_types	= array("c","h");
		private $_tablename		= "";
		private $_update		= false;
		private $_override		= false;
		private $_instances		= array();
		private $_extends		= "";

		function __construct() {
			$this->set_route("generate","model");
			$this->disable_authorization();
			
			$this->_model 		= $this->get("model");
			$this->_tablename 	= $this->get("table");		
		}

		function init() {
			
			$db_utility = DB::get_instance()->get_db_utility();
			$tablename_list = $db_utility->get_table_names();
			
			$this->set_var("extends",$this->_extends);	
			$this->set_var("model",$this->_model);	
			$this->set_var("model_types",$this->_model_types);	
			$this->set_var("instances",$this->_instances);	
			$this->set_var("override",$this->_override);	
			$this->set_var("update",$this->_update);	
			$this->set_var("tablename_list",$tablename_list);	
			$this->set_var("tablename",$this->_tablename);
		}
		
		function set_model($value) 			{ $this->_model = $value; }
		function set_extends($value) 			{ $this->_extends = $value; }
		function set_model_types($value) 	{ $this->_model_types = $value; }
		function set_override($value) 		{ $this->_override = $value; }
		function set_update($value) 		{ $this->_update = $value; }
		function set_instances($value) 		{ $this->_instances= $value; }
		function set_tablename($value) 		{ $this->_tablename = $value; }
	}