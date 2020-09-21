<?
	class ACTION_GENERATE_CMODEL extends ACTION_JSON {

		public function __construct() {
			parent::__construct()->disable_authorization();
		}

		function process() {

			$types			= (array)$this->post("types");
			$cmodel			= $this->post("cmodel");
			$types 			= $this->post("types");
			$application	= $this->post("application");
			$dir			= $application ? DIR_INSTANCE.$application."/" : APPLICATION::get_main_application_directory();
			$cmodel_file	= $dir."models/complex/".$cmodel."_complex_model.inc";

			$this->validate($cmodel);

			$insert_cmodel = CMODEL_MODEL_INSERT::create($cmodel_file);

			$messages = $warnings = array();

			if(in_array("image",$types)) {

				$this->validate_image($cmodel);

				$name = $this->post("image","name");
				$path = $this->post("image","path");
				$sizes = array();
				foreach(explode(",",$this->post("image","sizes")) as $size) {
					$parts = explode(":",$size);

					if(count($parts)==2)
						$sizes[] = '"'.value($parts,0).'"=>"'.value($parts,1).'"';
					else
						$sizes[] = '"'.value($parts,0).'"';
				}

				$image_code = 	"\n\n		public function get_".$name."_image(\$cache=true) {\n".
								"			if(!\$cache || !\$this->has_data(\"".$name."_image\"))\n".
								"				\$this->data(\"".$name."_image\",CMODEL_IMAGE::create(	\"".$path."\",\n".
								"																	\"".$path."\",\$this->get_guid(),\n".
								"																	\"png\",\n".								
								"																	array(".implode(",",$sizes)."),\n".
								"																	\$this->get_".$name."_time()));\n".
								"			return \$this->data(\"".$name."_image\");\n".
								"		}\n\n".
								"		public function commit_".$name."(\$file) {\n".
								"			\$this->set_".$name."_time(time());\n".
								"			\$this->get_".$name."_image(false)->purge(\$this->get_guid().\"*\")->process(\$file);\n".
								"			return \$this->save();\n".
								"		}\n\n".
								"		public function get_".$name."_image_url(\$name) { return \$this->get_".$name."_image()->url(\$name, true, CMODEL_IMAGE::BLANK_IMAGE); }\n";

				
				try {
					
					$insert_cmodel->prepend_save("n commit_".$name."(",$image_code);
					$messages[] = "Successfully inserted the image code";

				} catch(Exception $e) {
					$warnings[] = $e->getMessage();
				}
			}

			$this
				->data("warnings",$warnings)
				->data("messages",$messages)
				->success();
		}

		function validate($cmodel) {
			
			if(is_empty($cmodel))
				throw new Exception("The model is not selected");
				
			return true;
		}

		function validate_image($cmodel) {
			
			if(is_empty($this->post("image","name")))
				throw new Exception("The image is not specified");
				
			return true;
		}
	}