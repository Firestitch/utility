<?
	class VIEW_GENERATE_SERVICE extends VIEW {

		function __construct() {
			$this->set_route("generate","service")->disable_authorization();
		}
	}
