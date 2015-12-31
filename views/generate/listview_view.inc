<?	
	class VIEW_GENERATE_LISTVIEW extends VIEW {
	
		const FORMAT_SKIP 					= "S";
		const FORMAT_AJAX					= "A";
		const FORMAT_POPUP 					= "U";
		const FORMAT_PAGE					= "P";
		const FORMAT_INLINE					= "I";

		private $_model 					= "";
		private $_task	 					= "";
		private $_controller 				= "";
		private $_relation_field			= "";
		private $_list_body					= "";
		private $_override					= false;
		private $_security_roles 			= array();
		private $_selected_frameworks		= array();
		private $_selected_security_roles	= array();
		private $_format					= array("list"=>self::FORMAT_AJAX,"view"=>self::FORMAT_AJAX);
		private $_view_settings				= array();
		private $_list_options				= array();
		private $_list_table_classes		= array("table","table-bordered","table-striped");

		function get_format_name_list() 	{ return array(self::FORMAT_AJAX=>"ajax",self::FORMAT_POPUP=>"popup",self::FORMAT_PAGE=>"page"); }
		
		function __construct() {
			$this->set_route("generate","listview");
			$this->set_form("javascript:;",false,"form-list-view");
			$this->disable_authorization();
			
			$this->set_view("mvc",APPLICATION::get_view_instance("generate","component_mvc"));
			$this->get_view("mvc")->disable_mva_component();
			$this->get_view("mvc")->hide_is_form();
			$this->get_view("mvc")->hide_view_type();
			
			$registry 		= REGISTRY::get_instance();
			$this->_model 		= $registry->get_request_parm("o");

			$this->set_task($this->_model);
			
			$registry->set_parm("active_object",$this->_model);
			$registry->set_parm("active_table",$registry->get_request_parm("t"));

			$list_view = JSON_UTIL::decode(COOKIE_UTIL::get("list-view"));

			$this->_selected_frameworks = get_value($list_view,"frameworks");
		}

		function init() {
		
			$app_dir = APPLICATION::get_main_application_directory();		
			
			$security_manager = FILE_UTIL::get_file_contents($app_dir."system/managers/security_manager.inc");
					
			$has_matches = preg_match_all('/const (ROLE_[A-Z_]*)/', $security_manager,$matches);
			
			if($has_matches) {				
				$security_roles = get_value($matches,1);
					
				$this->_security_roles = array_combine(array_values($security_roles),$security_roles);			
			}

			$frameworks = array("bootstrap2"=>"Bootstrap 2.x",
								"bootstrap3"=>"Bootstrap 3.x");
			
			$this->set_var("frameworks",$frameworks);			
			$this->set_var("selected_frameworks",$this->_selected_frameworks);			
			$this->set_var("list_table_classes",$this->_list_table_classes);
			$this->set_var("list_body",$this->_list_body);
			$this->set_var("controller",$this->_controller);
			$this->set_var("format",$this->_format);
			$this->set_var("relation_field",$this->_relation_field);
			$this->set_var("security_roles",$this->_security_roles);
			$this->set_var("selected_security_roles",$this->_selected_security_roles);
			$this->set_var("view_settings",$this->_view_settings);
			$this->set_var("task",$this->_task);
			$this->set_var("override",$this->_override);
			$this->set_var("list_options",$this->_list_options);
			$this->set_var("model",$this->_model);
		}
		
		function set_list_table_classes($value) 				{ $this->_list_table_classes = $value; }
		function set_controller($value) 				{ $this->_controller = $value; }
		function set_list_body($value) 					{ $this->_list_body = $value; }
		function set_relation_field($value) 			{ $this->_relation_field = $value; }
		function set_view_settings($value) 				{ $this->_view_settings = $value; }
		function set_task($value) 						{ $this->_task = $value; }
		function set_selected_security_roles($value) 	{ $this->_selected_security_roles = $value; }	
		function set_selected_frameworks($value) 		{ $this->_selected_frameworks = $value; }	
		function set_format($value) 					{ $this->_format = $value; }	
		function set_override($value) 					{ $this->_override = $value; }
		function set_list_options($value) 				{ $this->_list_options = $value; }
		function set_model($value) 						{ $this->_model = $value; }		
	}
