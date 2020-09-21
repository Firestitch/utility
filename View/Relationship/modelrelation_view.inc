<?
	class VIEW_GENERATE_MODELRELATION extends VIEW {

		protected $_reference_model 	= "";
		protected $_joiner	 			= "";
		protected $_model				= null;
		protected $_source_model_column	= "";

		function __construct() {
			$this->set_route("generate","modelrelation");
			$this->set_form("javascript:;",false,"form-relation");
			$this->set_roles(array(SECURITY_MANAGER::ROLE_PUBLIC_USER));
		}

		function init() {

			$task 			= strtolower($this->request("task"));
			$application	= strtolower($this->request("application"));
			$app_dir		= $application ? DIR_INSTANCE.$application."/" : APPLICATION::get_main_application_directory();

			$model_list = CMODEL_INSTANCE::get_cmodels($app_dir);

			$joiner_list = DB::get_instance()->get_utility()->get_table_names();

			$this->set_var("joiner",$this->_joiner);
			$this->set_var("reference_model",$this->_reference_model);
			$this->set_var("model_list",$model_list);
			$this->set_var("joiner_list",$joiner_list);
			$this->set_var("source_model_column",$this->_source_model_column);
			$this->set_var("model",$this->_model);
		}
	}