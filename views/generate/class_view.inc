<?
	class VIEW_GENERATE_CLASS extends VIEW {
		
		protected $_fields 	= "";
		protected $_class 	= "";
		protected $_format 	= "P";
		
		function __construct() {
			$this->set_route("generate","class");
			$this->set_roles(array(SECURITY_MANAGER::ROLE_PUBLIC_USER));
			$this->set_form("/generate/doclass/");
		}
		
		function init() {
			$this->set_var("class",$this->_class);
			$this->set_var("fields",$this->_fields);
			$this->set_var("format",$this->_format);
		}	
		
		function set_fields($fields) { $this->_fields = $fields; }
		function set_class($class) { $this->_class = $class; }
		function set_format($format) { $this->_format = $format; }
	}
	