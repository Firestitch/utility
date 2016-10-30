<?
	class VIEW_GENERATE_SERVICEEXISTING extends VIEW {

		function __construct() {
			$this->set_route("generate","serviceexisting")->disable_authorization();
		}
	}
