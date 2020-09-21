<?
	class ACTION_MAINTENANCE_CSS extends ACTION {
	
		function __construct() {
			$this->set_roles(array(SECURITY_MANAGER::ROLE_PUBLIC_USER));
		}
		
		function process() {
			
			$registry = REGISTRY::get_instance();
			$form = $registry->get_request_parm("form");
			
			$view = APPLICATION::get_view_instance("maintenance","css");
			$this->set_view($view);
		}
	}