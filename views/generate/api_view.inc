<?
	class VIEW_GENERATE_API extends VIEW {

		public function __construct() {
			parent::__construct("generate","api")->disable_authorization();
			$this->set_form("javascript:;",false,"form-api");
		}

		public function init() {

			$views = FILE_UTIL::get_directory_listing(APPLICATION::get_main_application_directory()."views/api/");

			$apis = [];
			foreach($views as $view) {
				$name = preg_replace("/_view\.inc/","",$view);
				$apis[$name] = $name;
			}

			$this->set_var("model",$this->get("model"));
			$this->set_var("models",CMODEL_MODEL_GENERATOR::get_cmodels());
			$this->set_var("apis",$apis);
		}
	}