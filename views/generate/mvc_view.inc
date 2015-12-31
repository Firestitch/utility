<?php
	APPLICATION::get_view_instance("generate","component_mvc");
	
	class VIEW_GENERATE_MVC extends VIEW_GENERATE_COMPONENT_MVC {
		
		function __construct() {
			parent::__construct();
			
			$this->set_route("generate","mvc");
			$this->set_form("/generate/domvc/");
			$this->disable_authorization();
		}
	}
