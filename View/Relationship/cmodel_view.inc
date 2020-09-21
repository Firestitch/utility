<?
	class VIEW_GENERATE_CMODEL extends VIEW {
		
		private $_cmodels		= array();
		
		function __construct() {
			$this->set_route("generate","cmodel")->disable_authorization();	
		}

		function init() {

			$application	= strtolower($this->request("application"));
			$app_dir		= $application ? DIR_INSTANCE.$application."/" : APPLICATION::get_main_application_directory();
			
			$this->_cmodels = CMODEL_INSTANCE::get_cmodels($app_dir);
			
			$this->set_var("cmodels",$this->_cmodels);
			$this->set_var("model",$this->get("model"));
		}
	}