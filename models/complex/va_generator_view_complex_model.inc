<?	
	class CMODEL_VA_GENERATOR_VIEW extends CMODEL_VA_GENERATOR {
		
		protected $_template_filename 			= "default_view.inc";
		protected $_template_template_filename 	= "default_template.inc";
		
		protected $_model 						= "";
		protected $_has_form 					= false;
		protected $_page_title 					= "";
		protected $_view_type 					= VIEW::TYPE_BODY;
		protected $_method 						= "";
	
		function get_template_filename()			{ return $this->_template_filename; }
		function get_template_template_filename()	{ return $this->_template_template_filename; }
		
		function get_view_directory() 				{ return $this->_app_dir."views/".$this->_lower_controller."/"; }
		function get_view_filename()				{ return $this->_lower_task."_view.inc"; }
		function get_view_file()					{ return $this->get_view_directory().$this->get_view_filename(); }
		
		function get_view_template_directory() 		{ return $this->_app_dir."templates/".$this->_lower_controller."/"; }
		function get_view_template_filename()		{ return $this->_lower_task.".php"; }
		function get_view_template_file()			{ return $this->get_view_template_directory().$this->get_view_template_filename(); }
		
		function has_view_file() 					{ return is_file($this->get_view_file()); }
		function has_view_template_file() 			{ return is_file($this->get_view_template_file()); }
		function create_cmodel() 					{ return parent::create_cmodel($this->_model); }
		
		function set_has_form($has_form) 			{ $this->_has_form = $has_form; }
		function set_page_title($page_title) 		{ $this->_page_title = $page_title; }
		function set_view_type($view_type) 			{ $this->_view_type = $view_type; }
		function set_method($method) 				{ $this->_method = $method; }
		
		function pre_generate() {}
		
		function generate(&$messages=array()) {
			
			$this->pre_generate();

			$cmodel_state = false;
			if($this->_model)
				$cmodel_state = method_exists($this->create_cmodel(),"get_states");

			$this->_smarty->assign("cmodel_state",$cmodel_state);
			$this->_smarty->assign("has_form",$this->_has_form);
			$this->_smarty->assign("is_method_post",$this->_method=="P");
			$this->_smarty->assign("is_method_ajax",$this->_method=="A");
			$this->_smarty->assign("page_title",$this->_page_title);
			$this->_smarty->assign("is_type_blank",$this->_view_type=="L");
			$this->_smarty->assign("is_type_component",$this->_view_type==VIEW::TYPE_COMPONENT);
			$this->_smarty->assign("is_type_body",$this->_view_type==VIEW::TYPE_BODY);
			$this->_smarty->assign("is_type_popup",$this->_view_type==VIEW::TYPE_POPUP);			
			
			FILE_UTIL::mkdir($this->get_view_directory());
			FILE_UTIL::mkdir($this->get_view_template_directory());
		
			$template_file = MODEL_PATH::get_assets_directory().$this->get_template_filename();
			
			$content = $this->_smarty->fetch($template_file);
			
			$has_success = $this->write_file($this->get_view_file(),$content);

			$messages[] = "Successfully added the file ".HTML_UTIL::get_link("file:".$this->get_view_file(),$this->get_view_file());	
			
			$template_file = MODEL_PATH::get_assets_directory().$this->get_template_template_filename();
			
			$content = $this->_smarty->fetch($template_file);
			
			$has_success &= $this->write_file($this->get_view_template_file(),$content);

			$messages[] = "Successfully added the file ".HTML_UTIL::get_link("file:".$this->get_view_template_file(),$this->get_view_template_file());	
			
			return $has_success;
		}
	}
		