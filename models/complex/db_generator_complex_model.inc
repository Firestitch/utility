<?
	class CMODEL_DB_GENERATOR extends MODEL {

		protected $_app_dir = null;

		function __construct($app_dir=null) {
			$this->_app_dir  = $app_dir ? $app_dir : APPLICATION::get_main_application_directory();
			$this->_db_utility = DB::get_instance()->get_utility();
		}

		function create_dbo($tablename,$classname,$override=false,$framework=false) {

			$dbo_file = MODEL_DB::get_dbo_file($classname,$this->_app_dir);

			FILE_UTIL::mkdir($this->_app_dir."db/dbo/");

			$has_success = false;

			if(!is_file($dbo_file) || $override) {

				$result = $this->_db_utility->get_table_fields($tablename);

				$columns = array();
				foreach($result as $row) {
					$type 		= str_replace("unsigned","",strtolower($row["Type"]));

					$data_type 	= preg_replace("/[^a-z]+/", "$1", $type);
					$size 		= preg_replace("/[a-z]+\((\d+)\)/", "$1", $type);

					$size		= is_numeric($size) ? $size : "null";


					$not_null	= $row["Null"]=="YES" ? "true" : "false";
					$primary	= $row["Key"]=="PRI" ? "true" : "false";

					$name 		= $row["Field"];

					$columns[] = "\$this->_columns[\"".$name."\"] = new DBO_COLUMN(\"".$data_type."\",".$size.",".$not_null.",".$primary.");";
				}

				$class = "DBO_".$classname;

				if($framework)
					$class = "BASE_".$class;

				$str = 	"<?\n".
						"	class ".$class." extends DBO {\n\n";

				$str .= "		function __construct() {\n".
						"			\$this->_tablename = \"".$tablename."\";\n";

				foreach($columns as $column)
					$str .= "			".$column."\n";

				$str .=	"		}\n".
						"	}";

				$has_success = $this->write_file($dbo_file,$str);

			} else
				$this->add_warning_message("The DBO class `DBO_".$classname."` already exists");

			return $has_success;
		}

		function create_dbq($tablename,$classname,$override=false,$framework=false) {

			FILE_UTIL::mkdir($this->_app_dir."db/dbq/");

			$dbq_file = MODEL_DB::get_dbq_file($classname,$this->_app_dir);

			$has_success = false;

			if(!is_file($dbq_file) || $override) {

				$class = "DBQ_".$classname;

				if($framework)
					$class = "BASE_".$class;

				$str = 	"<?\n\n".
						"	class ".$class." extends DBQ {\n\n";

				$fields = $this->_db_utility->get_table_fields($tablename);

				$primary_keys = array();

				foreach($fields as $field) {

					$name 			= value($field,"Field");
					$name_upper 	= strtoupper($name);

					if($name=="state")
						continue;

					if(get_value($field,"Key")=="PRI")
						$primary_keys[] = '"'.$name.'"';
				}

				$primary_key = count($primary_keys)==1 ? implode(",",$primary_keys) :  "array(".implode(",",$primary_keys).")";

				$str .= "		public function __construct() {\n".
						"			parent::__construct(\"".$tablename."\", ".$primary_key.");\n".
						"		}\n".
						"	}";

				$has_success = $this->write_file($dbq_file,$str);

			} else
				$this->add_warning_message("The DBQ class `DBQ_".$classname."` already exists");

			return $has_success;
		}

		function get_key_count($tablename) {
			$fields = $this->_db_utility->get_table_fields($tablename);

			$count = 0;
			foreach($fields as $field)
				if(get_value($field,"Key")=="PRI")
					$count++;
			return $count;
		}

		function write_file($file,$string) {

			$error_message = "";
			$has_success = FILE_UTIL::put_file_contents($file,$string,$error_message);

			if(!$has_success)
				throw new Exception($error_message);

			return $has_success;
		}
	}
