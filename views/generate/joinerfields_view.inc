<?
	class VIEW_GENERATE_JOINERFIELDS extends VIEW {

		protected $_joiner 			= "";
		protected $_joiner_source_column	= "";
		protected $_joiner_reference_column 	= "";
		protected $_joiner_columns 		= array();

		function __construct() {
			$this->set_route("generate","joinerfields");
			$this->set_roles(array(SECURITY_MANAGER::ROLE_PUBLIC_USER));
			$this->type_blank();
		}

		function init() {
			DEBUG_UTIL::enable_format_html();

			$registry	= REGISTRY::get_instance();
			$app_dir	= APPLICATION::get_main_application_directory();

			$joiner_columns = DB::get_instance()->get_utility()->get_table_field_names($this->post("table"));

			$this->_joiner_columns = array_combine($joiner_columns,$joiner_columns);

			$this->set_var("joiner_list",DB::get_instance()->get_utility()->get_table_names());
			$this->set_var("index",$this->post("index"));
			$this->set_var("joiner_columns",$this->_joiner_columns);
			$this->set_var("joiner_source_column",$this->_joiner_source_column);
			$this->set_var("joiner_reference_column",$this->_joiner_reference_column);
		}
	}