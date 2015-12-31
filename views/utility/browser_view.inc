<?
	class VIEW_UTILITY_BROWSER extends VIEW {
		
		protected $_agent = "";
		
		function __construct() {
			$this->set_route("utility","browser");
			$this->set_form("/utility/dobrowser/");
			$this->set_roles(array(SECURITY_MANAGER::ROLE_PUBLIC_USER));
		}
		
		function init() {
			$registry = REGISTRY::get_instance();
			
			$this->set_var("agent",$this->_agent);
		}	
		
		function set_agent($agent) { $this->_agent = $agent; }
	}