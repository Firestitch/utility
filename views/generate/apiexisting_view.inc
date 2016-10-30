<?
	class VIEW_GENERATE_APIEXISTING extends VIEW {

		public function __construct() {
			parent::__construct("generate","apiexisting")->disable_authorization();
			$this->set_form("javascript:;",false,"form-api");
		}

		public function init() {
			$this->set_var("model",$this->get("model"));
		}
	}