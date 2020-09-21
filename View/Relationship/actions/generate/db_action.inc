<?php
	class ACTION_GENERATE_DB extends ACTION_JSON {

		public function __construct() {
			parent::__construct()->disable_authorization();
		}

		function process() {

			$tablename 			= strtolower($this->post("tablename"));
			$name 				= strtoupper($this->post("name"));
			$location			= $this->post("location");
			$primary_object_id	= $this->post("primary_object_id");
			$override 			= $this->post("override");
			$objects 			= (array)$this->post("objects");
			$dir				= DIR_INSTANCE.$location.DIRECTORY_SEPARATOR;
			$framework			= $location=="framework";
			$app_dir			= APPLICATION::get_main_application_directory();

			$messages = $warnings = [];
			if($this->is_form_valid($tablename,$name)) {

				$db_generator_cmodel = new CMODEL_DB_GENERATOR($dir);
				$key_count = $db_generator_cmodel->get_key_count($tablename);

				if(!$key_count)
					$warnings[] = "There are no key columns for this table. If this is the intended design, please disregard this warning.";

				if(in_array("dbq",$objects)) {

					$has_success = $db_generator_cmodel->create_dbq($tablename,$name,$override,$framework);
					$dbq_file = MODEL_DB::get_dbq_file($name,$dir);

					if($has_success)
						$messages[] = 'Successfully created DBQ_'.strtoupper($name);
				}

				if(in_array("dbo",$objects)) {

					$has_success = $db_generator_cmodel->create_dbo($tablename,$name,$override,$framework);

					$dbo_file = MODEL_DB::get_dbo_file($name,$dir);

					if($has_success)
						$messages[] = 'Successfully created DBO_'.strtoupper($name);
				}

				$warnings = array_merge($warnings,$db_generator_cmodel->get_warning_messages());


				$model_generator_complex_cmoddel = new CMODEL_MODEL_GENERATOR($name,$app_dir,$framework,false,["primary_object_id"=>$primary_object_id]);

				if(in_array("cmodel",$objects)) {

					if(!is_file($model_generator_complex_cmoddel->get_complex_model_file()) || $override) {
						$model_generator_complex_cmoddel->generate_complex_model();

						$messages[] = 'Successfully created CMODEL_'.strtoupper($name);
					} else
						$warnings[] = "The complex model ".$model_generator_complex_cmoddel->get_complex_model_file()." already exists";
				}

				if(in_array("hmodel",$objects)) {

					if(!is_file($model_generator_complex_cmoddel->get_handler_model_file()) || $override) {
						$model_generator_complex_cmoddel->generate_handler_model();

						$messages[] = 'Successfully created HMODEL_'.strtoupper($name);
					}
					else
						$warnings[] ="The handler model ".$model_generator_complex_cmoddel->get_handler_model_file()." already exists";
				}

				$warnings = array_merge($warnings,$model_generator_complex_cmoddel->get_warning_messages());
			}

			$this
				->success()
				->data("warnings",$warnings)
				->data("messages",$messages);
		}

		function is_form_valid($tablename,$name) {

			if(!$tablename )
				throw new Exception("Invalid tablename");

			if(!$name)
				throw new Exception("Invalid name");

			return true;
		}
	}
