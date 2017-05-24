<?
	class VIEW_GENERATE_DB extends VIEW {

		private $_classname 	= "";
		private $_tablename 	= "";
		private $_states 	= "";
		private $_create_dbq 	= false;
		private $_create_dbo	= true;
		private $_override	= true;

		function __construct() {
			$this->set_route("generate","db");
			$this->disable_authorization();

			$this->_classname 	= $this->get("model");
			$this->_tablename 	= $this->get("table");
		}

		function init() {

			$dbq_dir = APPLICATION::get_main_application_directory()."db/dbq/";

			$dbq_filenames = FILE_UTIL::get_directory_listing($dbq_dir);

			$db_utility = DB::get_instance()->get_db_utility();
			$tablename_list = $db_utility->get_table_names();

			$sql = "SELECT `table_name` FROM `information_schema`.`columns` WHERE `table_schema` = '".DB::get_instance()->get_db_name()."' AND `column_name` = 'state'";
			$state_column_tables = ARRAY_UTIL::get_list_from_array(DB::get_instance()->_select($sql),"table_name");

			$this->set_var("tablename_list",$tablename_list);
			$this->set_var("classname",$this->_classname);
			$this->set_var("tablename",$this->_tablename);
			$this->set_var("states",$this->_states);
			$this->set_var("create_dbq",$this->_create_dbq);
			$this->set_var("create_dbo",$this->_create_dbo);
			$this->set_var("override",$this->_override);
			$this->set_var("state_column_tables",$state_column_tables);
		}

		function set_classname($value) 	{ $this->_classname = $value; }
		function set_states($value) 	{ $this->_states = $value; }
		function set_tablename($value) 	{ $this->_tablename = $value; }
		function set_create_dbq($value) { $this->_create_dbq = $value; }
		function set_create_dbo($value) { $this->_create_dbo = $value; }
		function set_override($value) 	{ $this->_override = $value; }
	}