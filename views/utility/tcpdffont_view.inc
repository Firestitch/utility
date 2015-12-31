<?
	class VIEW_UTILITY_TCPDFFONT extends VIEW {
	
		function __construct() {
			$this->set_route("utility","tcpdffont");
			$this->set_form("/utility/dotcpdffont/",true);
			$this->set_roles(array(SECURITY_MANAGER::ROLE_PUBLIC_USER));
		}
		
		function init() {
			$registry = REGISTRY::get_instance();
			$registry->get_request_parm("");
		}		
	}