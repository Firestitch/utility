<?
	class VIEW_GENERATE_FILTER extends VIEW {

		function __construct() {
			$this->set_route("generate","filter")->disable_authorization();
		}
	}
