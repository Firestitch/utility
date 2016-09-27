<?
	class ACTION_GENERATE_MODELRELATION extends ACTION {

		function __construct() {
			$this->set_roles(array(SECURITY_MANAGER::ROLE_PUBLIC_USER));
		}

		function process() {

			$source_model 				= $this->request("source_model");
			$source_model_column 		= $this->request("source_model_column");
			$source_model_column_name	= preg_replace("/_id$/","",$source_model_column);
			$reference_name_column	 	= $this->request("reference_model_column");
			$reference_model	 		= $this->request("reference_model");
			$joiner_source_column		= $this->request("joiner_source_column");
			$joiner_reference_column	= $this->request("joiner_reference_column");
			$relationship 				= $this->request("relationship");
			$joiner 					= $this->request("joiner");
			$is_relationship_one 		= $this->request("relationship")=="O";
			$is_relationship_many 		= $this->request("relationship")=="M";
			$is_relationship_many_many	= $this->request("relationship")=="N";
			$model_suffix 				= $is_relationship_one ? "" : "s";
			$application				= $this->request("application");
			$dir						= $application ? DIR_INSTANCE.$application."/" : APPLICATION::get_main_application_directory();
			$reference_name 			= $is_relationship_one ? preg_replace("/_id$/","",$source_model_column) : $reference_model;
			$reference_name 			= preg_replace("/_id$/","",$reference_name);
			$plural_reference_model		= LANG_UTIL::get_plural_string($reference_model);
			$plural_reference_name		= LANG_UTIL::get_plural_string($reference_name);
			$plural_source_model		= LANG_UTIL::get_plural_string($source_model);
			$cmodel_model_file 			= $dir."models/complex/".$source_model."_complex_model.inc";
			$hmodel_model_file 			= $dir."models/handler/".$source_model."_handler_model.inc";

			$reference_name_set_function	= "set_".($is_relationship_one ? $reference_name : $plural_reference_name);
			$reference_name_get_function 	= "get_".($is_relationship_one ? $reference_name : $plural_reference_name);

			$reference_key 					= $is_relationship_one ? $reference_name : $plural_reference_name;

			$reference_name_set		= "\n\n\t\tpublic function ".$reference_name_set_function."(\$value) { return \$this->data(\"".$reference_key."\",\$value); }\n";

			$reference_name_get = "";

			if($is_relationship_one)
				$reference_name_get = 	"\n\t\tpublic function ".$reference_name_get_function."(\$hmodel=true) {\n".
										"\t\t\tif(\$hmodel && !\$this->has_data(\"".$reference_name."\") && \$this->get_".$source_model_column."()) {\n".
										"\t\t\t\t\$hmodel = is_a(\$hmodel,\"HMODEL\") ? \$hmodel : HMODEL_".strtoupper($reference_model)."::create(false);\n".
										"\t\t\t\t\$this->data(\"".$reference_name."\",\$hmodel->get(\$this->get_".$source_model_column."()));\n".
										"\t\t\t}\n".
										"\t\t\treturn \$this->data(\"".$reference_name."\");\n".
										"\t\t}";

			if($is_relationship_many)
				$reference_name_get = "\n\t\tpublic function ".$reference_name_get_function."(\$hmodel=null) {\n".
						"\t\t\tif(\$hmodel && !\$this->has_data(\"".$reference_key."\")) {\n".
						"\t\t\t\t\$hmodel = is_a(\$hmodel,\"HMODEL\") ? \$hmodel : HMODEL_".strtoupper($reference_model)."::create();\n".
						"\t\t\t\t\$this->data(\"".$reference_key."\",\$hmodel->where(\"".$reference_name_column."\",\"=\",\$this->get_".$reference_name_column."())->gets());\n".
						"\t\t\t}\n".
						"\t\t\treturn (array)\$this->data(\"".$reference_key."\");\n\t\t}";

			elseif($is_relationship_many_many)
				$reference_name_get = "\n\t\tpublic function ".$reference_name_get_function."(\$hmodel=null) {\n".
						"\t\t\tif(\$hmodel && !\$this->has_data(\"".$reference_key."\")) {\n".
						"\t\t\t\t\$hmodel = is_a(\$hmodel,\"HMODEL\") ? \$hmodel : HMODEL_".strtoupper($reference_model)."::create();\n".
						"\t\t\t\t\$this->data(\"".$reference_key."\",\$hmodel\n".
						"\t\t\t\t\t\t\t->join(\"".$plural_reference_name."\",\"".$joiner."\",\"".$joiner_reference_column."\")\n".
						"\t\t\t\t\t\t\t->where(\"".$joiner_source_column."\",\"=\",\$this->get_".$joiner_source_column."())->gets());\n".
						"\t\t\t}\n".
						"\t\t\treturn (array)\$this->data(\"".$reference_key."\");\n\t\t}";
			try {

				if(!$reference_name)
					throw new Exception("Invalid reference name");

				$cmodel_content = FILE_UTIL::get_file_contents($cmodel_model_file);

				if(preg_match("/^(.*function\s+arry.*?)(\t+return\s+\\\$arry.*)$/is",$cmodel_content,$matches))  {

					$code = "";
					if($is_relationship_one) {

						if(!preg_match("/\$arry[\"".$reference_name."\"]/is",$cmodel_content))
							$code = "\t\t\tif(\$".$reference_name."_cmodel=\$this->get_".$reference_name."(false))\n".
									"\t\t\t\t\$arry[\"".$reference_name."\"] = \$".$reference_name."_cmodel->arry();\n\n";


					} else {

						if(!preg_match("/\$arry[\"".$plural_reference_name."\"]/is",$cmodel_content))
							$code = "\t\t\tforeach(\$this->get_".$plural_reference_name."(false) as \$".$reference_name."_cmodel)\n".
									"\t\t\t\t\$arry[\"".$plural_reference_name."\"][] = \$".$reference_name."_cmodel->arry();\n\n";
					}

					if($code && !$this->has_code($cmodel_content,$code))
						$cmodel_content = value($matches,1).$code.value($matches,2);
				}

				if(stripos($cmodel_content,"n ".$reference_name_set_function."(")===false) {

					if(preg_match("/^(.*?[^}]+})(.*?function\s+save\(.*)$/ism",$cmodel_content,$matches))
						$cmodel_content = value($matches,1).$reference_name_set.$reference_name_get.value($matches,2);
				}

				$has_success = FILE_UTIL::put_file_contents($cmodel_model_file,$cmodel_content);

				if($has_success)
					APPLICATION::add_notify_message("Successfully updated the complex model");
				else
					APPLICATION::add_error_message("There was a problem trying to update the complex model");

				$hmodel_content = FILE_UTIL::get_file_contents($hmodel_model_file);

				if(stripos($hmodel_content,"protected \$_".$reference_name."_hmodel")===false) {

					if(preg_match("/^(.*?)(return\s+\\\$".$source_model."_cmodels;.*)$/ism",$hmodel_content,$matches)) {

						$code = "";
						if($is_relationship_many_many) {

							$code = "\$this->map_many_many(\$".$source_model."_cmodels,".
														"\$this->handler(\"".$reference_name."_handler\"),".
														"\"".$source_model."\",".
														"\"".$joiner."\",".
														"\"".$joiner_source_column."\",".
														"\"".$plural_reference_model."\",".
														"\"".$joiner_reference_column."\",".
														"\"".$joiner_reference_column."\",".
														"\"".$reference_name."\");\n\n\t\t\t";

						} elseif($is_relationship_one) {
							$code = "\$this->map_one(\$".$source_model."_cmodels,\$this->handler(\"".$reference_name."_handler\"),\"".$source_model_column_name."\",\"".$source_model_column_name."\",\"".$reference_model."\");\n\n\t\t\t";
						} elseif($is_relationship_many) {
							$code = "\$this->map_many(\$".$source_model."_cmodels,\$this->handler(\"".$reference_name."_handler\"),\"".$source_model_column_name."\",\"".$source_model_column_name."\",\"".$reference_model."\");\n\n\t\t\t";
						}

						if($code && !$this->has_code($hmodel_content,$code))
							$hmodel_content = value($matches,1).$code.value($matches,2);
					}

					if(preg_match("/(.*)(}[\s\n]*)$/ism",$hmodel_content,$matches)) {

						$code = "\n\t\tpublic function load_".$plural_reference_name."(\$hmodel=null) {\n".
								"\t\t\treturn \$this->handler(\"".$reference_name."_handler\",\$hmodel ? \$hmodel : HMODEL_".strtoupper($reference_model)."::create());\n".
								"\t\t}\n\t}";

						if($code && !$this->has_code($hmodel_content,$code))
							$hmodel_content = value($matches,1).$code;
					}

					$has_success = FILE_UTIL::put_file_contents($hmodel_model_file,$hmodel_content);

					if($has_success)
						APPLICATION::add_notify_message("Successfully updated the handler model");
					else
						APPLICATION::add_error_message("There was a problem trying to update the handler model");
				} else
					APPLICATION::add_warning_message("The handler model has already been modified for this relationship");

			} catch(Exception $e) {
				APPLICATION::add_error_message($e->getMessage());
			}

			$view = APPLICATION::get_view_instance("generate","modelrelation");
			$view->set_joiner($joiner);
			$view->set_reference_model($reference_model);
			$view->set_model($source_model);
			$view->set_source_model_column($source_model_column);
			$this->set_view($view);
		}

		function has_code($content,$code) {

			$content 	= preg_replace("/\s/","",$content);
			$code 		= preg_replace("/\s/","",$code);

			return strpos($content,$code);
		}
	}