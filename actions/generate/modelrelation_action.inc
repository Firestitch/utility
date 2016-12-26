<?
	class ACTION_GENERATE_MODELRELATION extends ACTION_JSON {

		function __construct() {
			parent::__construct()->disable_authorization();
		}

		function process() {

			$debug 						= false;
			$source_model 				= $this->request("source_model");
			$source_model_column 		= $this->request("source_model_column");
			$reference_model_column	 	= $this->request("reference_model_column");
			$reference_model	 		= $this->request("reference_model");
			$relationship 				= $this->request("relationship");
			$joiners 					= (array)$this->request("joiners");
			$map_child 					= $this->request("relationship")=="child";
			$map_children 				= $this->request("relationship")=="children";
			$model_suffix 				= $map_child ? "" : "s";
			$application				= $this->request("application");
			$dir						= $application ? DIR_INSTANCE.$application."/" : APPLICATION::get_main_application_directory();
			$reference_name 			= preg_replace("/_id$/","",$joiners ? $reference_model : $source_model_column);
			$plural_reference_name		= LANG_UTIL::get_plural_string($reference_name);
			$cmodel_model_file 			= $dir."models/complex/".$source_model."_complex_model.inc";
			$hmodel_model_file 			= $dir."models/handler/".$source_model."_handler_model.inc";
			$warnings 					= [];

			$reference_name_set_function	= "set_".($map_child ? $reference_name : $plural_reference_name);
			$reference_name_get_function 	= "get_".($map_child ? $reference_name : $plural_reference_name);

			$reference_key 					= $map_child ? $reference_name : $plural_reference_name;

			$reference_name_set		= "\n\n\t\tpublic function ".$reference_name_set_function."(\$value) { return \$this->data(\"".$reference_key."\",\$value); }\n";

			$where_column 			= $reference_model."s.".$reference_model_column;
			$last_table 			= $reference_model."s";
			$last_column 			= $reference_model_column;

			if($joiner=value($joiners,0))
				$where_column = $joiner["table"].".".$joiner["source_column"];

			$reference_name_get = 	"\n\t\tpublic function ".$reference_name_get_function."(\$hmodel=".($map_child ? "true" : "null").") {\n".
									"\t\t\tif(\$hmodel && !\$this->has_data(\"".$reference_key."\") && \$this->get_".$source_model_column."()) {\n".
									"\t\t\t\t\$hmodel = is_a(\$hmodel,\"HMODEL\") ? \$hmodel : HMODEL_".strtoupper($reference_model)."::create(".($map_child ? "false" : "").");\n".
									"\t\t\t\t\$this->data(\"".$reference_key."\",\$hmodel";
									foreach(array_reverse($joiners) as $joiner) {
										$reference_name_get .= "->join(\"".$last_table."\",\"".$joiner["table"]."\",\"".$last_column."\",\"".$joiner["reference_column"]."\")\n\t\t\t\t\t\t\t\t\t\t\t\t";

										$last_table = $joiner["table"];
										$last_column = $joiner["source_column"];
									}

			$reference_name_get .= "->where(\"".$where_column."\",\"=\",\$this->get_".$source_model_column."())";

			$reference_name_get .= 	"\n\t\t\t\t\t\t\t\t\t\t\t\t->".($map_child ? "get" : "gets")."());\n".
									"\t\t\t}\n".
									"\t\t\treturn ".($map_child ? "" : "(array)")."\$this->data(\"".$reference_key."\");\n".
									"\t\t}";

			if(!$reference_name)
				throw new Exception("Invalid reference name");

			$cmodel_content = FILE_UTIL::get($cmodel_model_file);

			if(preg_match("/^(.*function\s+arry.*?)(\t+return\s+\\\$arry.*)$/is",$cmodel_content,$matches))  {

				$code = "";
				if($map_child) {

					if(!preg_match("/\$arry[\"".$reference_name."\"]/is",$cmodel_content))
						$code = "\t\t\tif(\$".$reference_name."_cmodel=\$this->get_".$reference_name."(false))\n".
								"\t\t\t\t\$arry[\"".$reference_name."\"] = \$".$reference_name."_cmodel->arry();\n\n";


				} else {

					if(!preg_match("/\$arry[\"".$plural_reference_name."\"]/is",$cmodel_content))
						$code = "\t\t\tforeach(\$this->get_".$plural_reference_name."(false) as \$".$reference_name."_cmodel)\n".
								"\t\t\t\t\$arry[\"".$plural_reference_name."\"][] = \$".$reference_name."_cmodel->arry();\n\n";
				}

				if($code && !$this->has_code($cmodel_content,$code)) {

					if($debug) {
						p("CMODEL",$code);
					}

					$cmodel_content = value($matches,1).$code.value($matches,2);
				}
			} else
				$warnings[] = "The arry() function is already generated";


			if(stripos($cmodel_content,"n ".$reference_name_set_function."(")===false) {
				if(preg_match("/^(.*?[^}]+})(.*?function\s+save\(.*)$/ism",$cmodel_content,$matches)) {
					$cmodel_content = value($matches,1).$reference_name_set.$reference_name_get.value($matches,2);

					if($debug) {
						p("CMODEL SET, GET",$reference_name_set.$reference_name_get);
					}
				}
			} else
				$warnings[] = "The CMODEL_".strtoupper($source_model)."->".$reference_name_set_function."() function is already generated";

			if(!$debug) {
				try {
					FILE_UTIL::put($cmodel_model_file,$cmodel_content);
				} catch(Exception $e) {
					throw new Exception("There was a problem trying to update the complex model");
				}
			}

			$hmodel_content = FILE_UTIL::get($hmodel_model_file);

			if(stripos($hmodel_content,"load_".$plural_reference_name)===false) {

				if(preg_match("/^(.*?)(return\s+\\\$(:?".$source_model."_)?cmodels;.*)$/ism",$hmodel_content,$matches)) {

					$function 				= $map_child ? "map_child" : "map_children";
					$parent_object_function = "set_".$reference_name.($map_child ? "" : "s");
					$child_reference_column = $reference_model."s.".$reference_model_column;
					$cmodels 				= stripos($hmodel_content,'return $cmodel')===false ? '$'.$source_model.'_cmodels' : '$cmodels';
					$joins 					= [];

					$last_table = $reference_model."s";
					$last_column = $reference_model_column;
					foreach(array_reverse($joiners) as $index=>$joiner) {
						$joins[] = '["from"=>"'.$last_table.'","to"=>"'.$joiner["table"].'","from_column"=>"'.$last_column.'","to_column"=>"'.$joiner["reference_column"].'"]';
						$last_table = $joiner["table"];
						$last_column = $joiner["source_column"];
						$child_reference_column = $joiner["table"].".".$joiner["source_column"];
					}

					$joins = "[".implode(",",$joins)."]";

					$code = "\n\t\t\t".'$this->'.$function.'('.$cmodels.', $this->handler("'.$reference_name.'_handler"), "get_'.$source_model_column.'", "'.$parent_object_function.'", "'.$child_reference_column.'",'.$joins.');'."\n\n\t\t\t";

					if(!$this->has_code($hmodel_content,$code)) {

						if($debug)
							p("HMODEL MAP",$code);

						$hmodel_content = value($matches,1).$code.value($matches,2);
					}
				}

				if(preg_match("/(.*)(}[\s\n]*)$/ism",$hmodel_content,$matches)) {

					$code = "\n\t\tpublic function load_".$plural_reference_name."(\$hmodel=null) {\n".
							"\t\t\treturn \$this->handler(\"".$reference_name."_handler\",\$hmodel ? \$hmodel : HMODEL_".strtoupper($reference_model)."::create());\n".
							"\t\t}\n\t}";

					if($code && !$this->has_code($hmodel_content,$code)) {
						$hmodel_content = value($matches,1).$code;

						if($debug)
							p("HMODEL LOAD",$code);
					}
				}

				if(!$debug) {
					try {
						FILE_UTIL::put($hmodel_model_file,$hmodel_content);
					} catch(Exception $e) {
						throw new Exception("There was a problem trying to update the handler model");
					}
				}
			} else
				$warnings[] = "The HMODEL_".strtoupper($source_model)." load_".$plural_reference_name."() function is already generated";

			$this
				->data("warnings",$warnings)
				->success();
		}

		function has_code($content,$code) {

			$content 	= preg_replace("/\s/","",$content);
			$code 		= preg_replace("/\s/","",$code);

			return strpos($content,$code);
		}
	}