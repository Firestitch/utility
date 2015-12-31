<?php
	class ACTION_GENERATE_DB extends ACTION_JSON {

		public function __construct() {
			parent::__construct()->disable_authorization();
		}

		function process() {
		
			$registry 		= REGISTRY::get_instance();
			$tablename 		= strtolower($registry->get_request_parm("tablename"));
			$classname 		= strtoupper($registry->get_request_parm("classname"));
			$application	= $registry->get_request_parm("application");
			$override 		= $registry->get_request_parm("override");
			$objects 		= is_array($registry->get_request_parm("objects")) ? $registry->get_request_parm("objects") : array();
			$create_dbo 	= in_array("dbo",$objects);
			$create_dbq 	= in_array("dbq",$objects);			
			$dir			= $application ? DIR_INSTANCE.$application."/" : APPLICATION::get_main_application_directory();
			$framework		= $application=="framework";
			
			$messages = $warnings = array();
			if($this->is_form_valid($tablename,$classname,$create_dbo,$create_dbq)) {
				
				$db_generator_cmodel = new CMODEL_DB_GENERATOR($dir);
				$key_count = $db_generator_cmodel->get_key_count($tablename);
				
				if(!$key_count)
					$warnings[] = "There are no key columns for this table. If this is the intended design, please disregard this warning.";
					
				if($create_dbq) {

					$has_success = $db_generator_cmodel->create_dbq($tablename,$classname,$override,$framework);
					$dbq_file = MODEL_DB::get_dbq_file($classname,$dir);

					if($has_success)
						$messages[] = 'Successfully created the DBQ class <a href="file://'.$dbq_file.'">'.$dbq_file.'</a>';
				}

				if($create_dbo) {

					$has_success = $db_generator_cmodel->create_dbo($tablename,$classname,$override,$framework);
					
					$dbo_file = MODEL_DB::get_dbo_file($classname,$dir);

					if($has_success)
						$messages[] = 'Successfully created the DBO class <a href="file://'.$dbo_file.'">'.$dbo_file.'</a>';						
				}
			}	

			$warnings = array_merge($warnings,$db_generator_cmodel->get_warning_messages());

			$this
				->success()
				->data("warnings",$warnings)	
				->data("messages",$messages);
		}
		
		function is_form_valid($tablename,$classname,$create_dbo,$create_dbq) {
			
			if(!$create_dbq && !$create_dbo)
				throw new Exception("No classes to create");
				
			if(!$tablename )
				throw new Exception("Invalid tablename");
				
			if(!$classname)
				throw new Exception("Invalid classname");

			return true;
		}
	}
