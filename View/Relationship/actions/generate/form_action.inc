<?php
	require_once(APPLICATION::get_framework_directory()."libraries/htmlform/htmlform.inc");

	class ACTION_GENERATE_FORM extends ACTION {

		function __construct() {
			$this->disable_authorization();
		}

		function process() {

			$registry_obj 	= REGISTRY::get_instance();
			$page_number 	= $registry_obj->get_request_parm("page_number");

			$upload = new UPLOAD_UTIL("excel_file");

			if($upload->is_file_upload_exist()) {

				$temp_dir = MODEL_PATH::get_temporary_directory();

				FILE_UTIL::mkdir($temp_dir);

				$upload->set_store_path($temp_dir);
				$upload->set_valid_extensions(array("xls"));
				$upload->upload();

				if(!$upload->has_error()) {

					$excel_reader = new EXCEL_READER_UTIL($upload->get_store_file());
					$excel_data = $excel_reader->get_data($page_number - 1);

					$form_name = get_value($excel_data, array(0,1));

					array_shift($excel_data);
					array_shift($excel_data);
					array_shift($excel_data);
					array_shift($excel_data);
					array_shift($excel_data);

					$form_name = str_replace(" ", "_", strtolower($form_name));

					$file = APPLICATION::get_main_application_directory()."forms/".$form_name."_htmlform.inc";

					if($this->is_form_valid($file,$form_name,$excel_data)) {

						$form_code = $this->generate_form_code($form_name, $excel_data);

						if($form_code) {

							$error_message = "";

							FILE_UTIL::put_file_contents($file,$form_code,$error_message);
							if($error_message==null)
								APPLICATION::add_notify_message("Successfully added the file `".HTML_UTIL::get_link("file:".$file,$file)."`");
							else
								APPLICATION::add_error_message($error_message." `".$file."`");
						} else
							APPLICATION::add_error_message("Invalid form code");
					}
				} else {
					APPLICATION::add_error_message("Upload error.");
				}

				FILE_UTIL::rmdir($temp_dir);

			} else
				APPLICATION::add_error_message("Please upload file.");

			$view = APPLICATION::get_view_instance("generate","form");
			$this->set_view($view);
		}

		private function is_form_valid($file,$form_name,$excel_data) {

			if(is_file($file))
				APPLICATION::add_error_message("The form file already exists");

			if(!$form_name)
				APPLICATION::add_error_message("Invalid for name");

			if(!$excel_data)
				APPLICATION::add_error_message("Invalid Excel data");

			return !APPLICATION::has_error_messages();
		}


		function generate_form_code($form_name, $data) {

			if(!$form_name || !$data)
				return false;

			$valid_rule_list = array("required","int","float","email");


			$string = 	"<?php\n\n".
					"\tclass HTMLFORM_".strtoupper($form_name)." extends HTMLFORM {\n\n".
					"\t\tpublic function __construct() {\n\n".
					"\t\t\t\$this->set_name(\"".$form_name."\");\n";

			$groups = array();

			foreach($data as $row) {

				$group 		= trim(get_value($row, 0));
				$fieldname 	= trim(get_value($row, 1));
				$label 		= trim(get_value($row, 2));
				$desc 		= trim(get_value($row, 3));
				$note 		= trim(get_value($row, 4));
				$type 		= trim(get_value($row, 5));
				$options	= trim(get_value($row, 6));
				$value 		= trim(get_value($row, 7));
				$rules 		= trim(get_value($row, 8));

				$group_var = str_replace(array("-"," "), array("_","_"), strtolower($group));
				if($group_var && !in_array($group_var, $groups)) {
					$groups[] = $group_var;
					$string .= 	"\n\n\t\t\t\$".$group_var." = new HTMLFORM_ELEMENT_GROUP(\"".$group_var."\", \"".$group."\");\n".
							"\t\t\t\$this->add(\$".$group_var.");\n\n";
				}

				$rules = explode(",", $rules);

				$valid_rules = true;

				foreach($rules as $idx=>$rule) {

					$rule = trim($rule);

					if(!$rule)
						continue;

					$valid_rules &= in_array($rule,$valid_rule_list);

					if($rule)
						$rules[$idx] = "HTMLFORM_ELEMENT::VALIDATE_".strtoupper($rule);
					else
						unset($rules[$idx]);
				}

				$rules_str = implode(",",$rules);

				if(!$valid_rules)
					$rules_str = "/* ".$rules_str." */";

				$field_group = $group_var ? ", \$".$group_var : "";

				$type = $type=="text" ? "inputbox" : $type;

				switch($type) {
					default:
						$type = "inputbox";

					case "inputbox":
					case "password":
					case "textarea":
					case "phone":
					case "postalcode":
					case "percent":
					case "date":

						$string .= "\t\t\t\$this->add_".strtolower($type)."(\"".$fieldname."\", \"".$label."\", \"".$value."\", \"".$desc."\", \"".$note."\", array(".$rules_str.")".$field_group.");\n\n";
					break;

					case "dropdown":
					case "list":
					case "radio":
					case "checkbox":
						$options = explode(",",$options);
						$options_arr = array();
						foreach($options as $option) {
							if(strpos($option,":")!==false) {
								$parts  	= explode(":", $option);
								$option	= get_value($parts, 0);
								$display 	= get_value($parts, 1);
							} else {
								$display = $option;
							}
							$options_arr[$option] = "'".$option."'=>'".$display."'";
						}
						$options_str = "array(".implode(",",$options_arr).")";

						$string .= "\t\t\t\$this->add_".strtolower($type)."(\"".$fieldname."\", \"".$label."\", ".$options_str.", \"".$value."\",  \"".$desc."\", \"".$note."\", array(".$rules_str.")".$field_group.");\n\n";
					break;

					case "button":
						$string .= "t\t\t\$this->add_".strtolower($type)."(\"".$fieldname."\", \"".$value."\", array(".$rules_str.")".$field_group.");\n";
					break;
				}

			}

			$string .= 	"\t\t}\n".
					"\t}\n";

			return $string;
		}
	}
