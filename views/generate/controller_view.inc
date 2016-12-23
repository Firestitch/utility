<?
	class VIEW_GENERATE_CONTROLLER extends VIEW {

		function __construct() {
			$this->set_route("generate","controller")->disable_authorization();
		}
	}
