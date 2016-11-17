<?
	class VIEW_GENERATE_DIRECTIVE extends VIEW {

		function __construct() {
			$this->set_route("generate","directive")->disable_authorization();
		}
	}
