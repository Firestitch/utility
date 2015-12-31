<?
	class VIEW_UTILITY_FBAUTHORIZATION extends VIEW {
		
		protected $_code 		= "";
		protected $_facebook_app_id 	= "";
		protected $_facebook_secrect 	= "";
		
		function __construct() {
			$this->set_route("utility","fbauthorization");
			$this->set_form("/utility/dofbauthorization/");
			$this->set_roles(array(SECURITY_MANAGER::ROLE_PUBLIC_USER));
		}
		
		function init() {
			$this->set_var("code",$this->_code);
			$this->set_var("facebook_app_id",$this->_facebook_app_id);
			$this->set_var("facebook_secrect",$this->_facebook_secrect);
		}	
		
		function set_code($code) 				{ $this->_code = $code; }
		function set_facebook_secrect($facebook_secrect) 	{ $this->_facebook_secrect = $facebook_secrect; }
		function set_facebook_app_id($facebook_app_id) 		{ $this->_facebook_app_id = $facebook_app_id; }
	}