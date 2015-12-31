<?
	class VIEW_UTILITY_CSR extends VIEW {
	
		function __construct() {
			$this->set_route("utility","csr");
			$this->set_form("/utility/docsr/");
			$this->set_roles(array(SECURITY_MANAGER::ROLE_PUBLIC_USER));
		}
		
		function init() {
			$registry = REGISTRY::get_instance();
			$registry->get_request_parm("");
		}		
	}