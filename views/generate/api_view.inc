<?
	class VIEW_GENERATE_API extends VIEW {

		public function __construct() {
			parent::__construct("generate","api")->disable_authorization();
			$this->set_form("javascript:;",false,"form-api");
		}

		public function init() {
			$this->set_var("models",CMODEL_MODEL_GENERATOR::get_cmodels());
			$this->set_var("model",$this->get("model"));
		}
	}