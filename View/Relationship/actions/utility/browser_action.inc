<?
	class ACTION_UTILITY_BROWSER extends ACTION {
	
		function __construct() {
			$this->set_roles(array(SECURITY_MANAGER::ROLE_PUBLIC_USER));
		}
		
		function process() {
			
			$registry = REGISTRY::get_instance();
			$agent = $registry->get_request_parm("agent");
					
			try {
				$browscap_dir = MODEL_PATH::get_browscap_directory();
				FILE_UTIL::mkdir($browscap_dir);
				
				$browser_cap = PACKAGE_UTIL::create_browercap($browscap_dir);

				$browser = $browser_cap->getBrowser($agent,true);
				
				APPLICATION::add_notify_message("<pre>".print_r($browser,true)."</pre>");
				

			} catch(Exception $e) {}	
			
			$view = APPLICATION::get_view_instance("utility","browser");
			$view->set_agent($agent);
			$this->set_view($view);
		}
	}