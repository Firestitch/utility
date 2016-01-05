<?
	class VIEW_APPLICATION_HEADER extends VIEW {

		function __construct(){
			$this->set_route("application","header");
			$this->disable_authorization();
		}

		function init() {

			$active_object	= $this->get("active_object");
			$active_table 	= $this->get("active_table");
						
			$this->set_var("active_object",$active_object);
			$this->set_var("active_table",$active_table);
			$this->set_var("application",$this->request("application"));
		}		
	}
