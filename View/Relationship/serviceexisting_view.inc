<?
	class VIEW_GENERATE_SERVICEEXISTING extends VIEW {

		function __construct() {
			$this->set_route("generate","serviceexisting")->disable_authorization();

			$names = FILE_UTIL::get_directory_listing(MODEL_PATH::get_frontend_dir()."app/scripts/services/");

			$services = [];
			foreach($names as $name) {
				$name = preg_replace("/\.js/","",$name);
				$services[$name] = $name;
			}

			$this->set_var("models",CMODEL_MODEL_GENERATOR::get_cmodels());
			$this->set_var("services",$services);
		}
	}
