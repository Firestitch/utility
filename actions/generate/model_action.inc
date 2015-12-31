<?

	class ACTION_GENERATE_MODEL extends ACTION_JSON {

		public function __construct() {
			parent::__construct()->disable_authorization();
		}

		function process() {

			$model 			= $this->post("model");
			$model_types 	= is_array($this->post("model_types")) ? $this->post("model_types") : array();
			$override 		= $this->post("override");
			$application	= $this->post("application");
			$extends		= $this->post("extends");
			$tablename 		= strtolower($this->post("tablename"));
			$this->_app_dir	= $application ? DIR_INSTANCE.$application."/" : APPLICATION::get_main_application_directory();
			$framework		= $application=="framework";

			$model_generator_complex_cmoddel = new CMODEL_MODEL_GENERATOR($model,$this->_app_dir,$framework,$extends);

			$messags = $warnings = array();
			if($this->is_form_valid($model,$model_types)) {
			
				if(in_array("c",$model_types)) {
					
					if(!is_file($model_generator_complex_cmoddel->get_complex_model_file()) || $override) 
						$model_generator_complex_cmoddel->generate_complex_model();
					else
						$warnings[] = "The complex model ".$model_generator_complex_cmoddel->get_complex_model_file()." already exists";
				}
				
				if(in_array("h",$model_types)) {
					
					if(!is_file($model_generator_complex_cmoddel->get_handler_model_file()) || $override) 
						$model_generator_complex_cmoddel->generate_handler_model();
					else
						$warnings[] ="The handler model ".$model_generator_complex_cmoddel->get_handler_model_file()." already exists";
				}
				
				if(in_array("s",$model_types)) {
					
					if(!is_file($model_generator_complex_cmoddel->get_simple_model_file()) || $override) 
						$model_generator_complex_cmoddel->generate_simple_model();
					else
						$warnings[] = "The simple model ".$model_generator_complex_cmoddel->get_simple_model_file()." already exists";
				}				
			}
		
			$this
				->data("warnings",array_merge($warnings,$model_generator_complex_cmoddel->get_warning_messages()))
				->data("messages",$model_generator_complex_cmoddel->get_notify_messages())
				->success();
		}

		function is_form_valid($model,$model_types) {
			
			if(is_empty($model))
				throw new Exception("The model name is invalid");
				
			if(is_empty($model_types))
				throw new Exception("Please select a model type");
			else {

				if(array_key_exists("c",$model_types)) {

					$dbo = strtoupper($model);

					$is_dbo_file = is_file($this->_app_dir."db/dbo/".strtolower($dbo)."_dbo.inc");

					if(!$is_dbo_file)
						throw new Exception("The `".strtolower($dbo)."` DBO has not been generated");
				}

				$dbq = strtolower($model);

				$is_dbq_file = is_file($this->_app_dir."db/dbq/".$dbq ."_dbq.inc");

				if(!$is_dbq_file)
					throw new Exception("The `".$dbq."` DBQ has not been generated");
			}			

			return true;
		}
	}