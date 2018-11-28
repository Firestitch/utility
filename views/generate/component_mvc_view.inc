<?php
	
	class VIEW_GENERATE_COMPONENT_MVC extends VIEW {

		private $_controller			= "";
		private $_context 			= "";
		private $_task 				= "";
		private $_has_form			= false;
		private $_mva_components		= array("V");		
		private $_application_only		= false;
		private $_security_roles		= array();
		private $_selected_security_roles	= array();
		private $_is_mva_component		= true;		
		private $_task_name			= "";	
		private $_show_is_form			= true;	
		private $_show_view_type		= true;	
		private $_page_title			= "";	
		private $_override			= false;	
		private $_view_type			= "s";	
		private $_show_page_title		= true;	

		function __construct() {			
			$this->set_route("generate","component_mvc");
			$this->disable_authorization();
		}

		function init() {
		
			$app_dir = APPLICATION::get_main_application_directory();		
			
			$security_manager = FILE_UTIL::get_file_contents($app_dir."models/complex/acl_complex_model.inc");
					
			$has_matches = preg_match_all('/const (PERMISSION_[A-Z_]*)/', $security_manager,$matches);
			
			if($has_matches) {				
				$security_roles = get_value($matches,1);
					
				$this->_security_roles = array_combine(array_values($security_roles),$security_roles);			
			}
			
			$this->set_var("selected_security_roles",$this->_selected_security_roles);
			$this->set_var("security_roles",$this->_security_roles);
			$this->set_var("controller",$this->_controller);
			$this->set_var("context",$this->_context);
			$this->set_var("task",$this->_task);
			$this->set_var("has_form",$this->_has_form);
			$this->set_var("mva_components",$this->_mva_components);	
			$this->set_var("is_mva_component",$this->_is_mva_component);	
			$this->set_var("show_is_form",$this->_show_is_form);							
			$this->set_var("override",$this->_override);
			$this->set_var("view_type",$this->_view_type);
			$this->set_var("page_title",$this->_page_title);
			$this->set_var("show_page_title",$this->_show_page_title);
			$this->set_var("show_view_type",$this->_show_view_type);
		}
		
		function set_controller($value) 		{ $this->_controller = $value; }
		function set_context($value) 			{ $this->_context = $value; }
		function set_task($value) 			{ $this->_task = $value; }
		function set_mva_components($value) 		{ $this->_mva_components = $value; }
		function set_has_form($value) 			{ $this->_has_form = $value; }
		function set_page_title($page_title) 		{ $this->_page_title = $page_title; }
		function set_selected_security_roles($value) 	{ $this->_selected_security_roles = $value; }
		function disable_mva_component()		{ $this->_is_mva_component = false; }
		function set_override($value)			{ $this->_override = $value; }
		function hide_is_form()				{ $this->_show_is_form = false; }
		function hide_page_title()			{ $this->_show_page_title = false; }
		function hide_view_type()			{ $this->_show_view_type = false; }
		function set_view_type($value)			{ $this->_view_type = $value; }
	}
