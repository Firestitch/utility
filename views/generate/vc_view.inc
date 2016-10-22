<?
	class VIEW_GENERATE_VC extends VIEW {

		function __construct() {
			$this->set_route("generate","vc")->disable_authorization();
		}
	}
