<?
	class VIEW_GENERATE_SERVICEEXISTING extends VIEW {

		function __construct() {
			$this->set_route("generate","serviceexisting")->disable_authorization();

			$names = FILE_UTIL::get_directory_listing(APPLICATION::get_main_frontend_directory()."app/scripts/services/");

			$services = [];
			foreach($names as $name) {
				$name = preg_replace("/\.js/","",$name);
				$services[$name] = $name;
			}
			$this->set_var("services",$services);
		}
	}
