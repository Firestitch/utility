<?
	class ACTION_GENERATE_CLASS extends ACTION {
	
		function __construct() {
			$this->set_roles(array(SECURITY_MANAGER::ROLE_PUBLIC_USER));
		}
		
		function process() {
			
			$registry = REGISTRY::get_instance();
			$fields = str_replace(array("\r","\n"),array("",","),$registry->get_request_parm("fields"));
			$format = $registry->get_request_parm("format");
			
			$variables = explode(",",$fields);
			
			$class = "";
			
			if($format=="P") {

				$class = "class CLASSNAME {\n\n";
				
				$construct_variables = array();
				
				foreach($variables as $variable) {
					$class .= "\tprotected \$_".$variable.str_repeat("\t",$this->get_tab_count($variable))."= \"\";\n";
					$construct_variables[] = "\$".$variable.'=""';
				}

				$class .= "\n\tpublic function __construct(".implode(",",$construct_variables).") {\n";
				
				foreach($variables as $variable)
					$class .= "\t\t\$this->_".$variable." = \$".$variable.";\n";

				$class .= "\t}\n\n";

				foreach($variables as $variable)
					$class .= "\tpublic function set_".$variable."(\$".$variable.")". str_repeat("\t",$this->get_tab_count($variable))."{ \$this->_".$variable." = \$".$variable."; }\n";

				$class .= "\n";

				foreach($variables as $variable)
					$class .= "\tpublic function get_".$variable."()".str_repeat("\t",$this->get_tab_count($variable))."{ return \$this->_".$variable."; }\n";

				$class .= "}";
			
			} elseif($format=="J") {

				$class = "var Classname = Class.create({\n";

				$class .= "\n\tinit: function() {\n";
				
				foreach($variables as $variable)
					$class .= "\t\tthis._".$variable." = \"\"\n";

				$class .= "\t},\n\n";

				foreach($variables as $variable)
					$class .= "\tset_".$variable.": function(".$variable.")".str_repeat("\t",$this->get_tab_count($variable))."{ this._".$variable." = ".$variable."; },\n";

				$class .= "\n";

				foreach($variables as $index=>$variable)
					$class .= "\tget_".$variable.": function()".str_repeat("\t",$this->get_tab_count($variable))."{ return this._".$variable."; }".(count($variables)>($index + 1)? "," : "")."\n";

				$class .= "});";
			}	
			
			
			$view = APPLICATION::get_view_instance("generate","class");
			$view->set_fields($fields);
			$view->set_class($class);
			$view->set_format($format);
			$this->set_view($view);
		}
		
		function get_tab_count($var) {	
			return $var ? round(12/strlen($var)) : 0;
		}
	}