<?
	class CMODEL_API_GENERATOR extends CMODEL_GENERATOR {

		protected $_dir					= "";
		protected $_model				= "";
		protected $_model_plural		= "";

		function __construct($dir,$api,$model,$model_plural,$parent_model=null) {
			parent::__construct($dir);

			$this->_model 			= $model;
			$this->_api 			= $api;
			$this->_parent_model 	= $parent_model;
			$this->_model_plural	= $model_plural;
			$this->_dir 			= $this->get_instance_dir()."views/api/";
		}

		function get_file() {
			return $this->_dir.str_replace("_","",$this->_api)."_view.inc";
		}

		function append(&$messages=array()) {

			$file = $this->get_file();

			if(!is_file($file))
				throw new Exception("API not found");

			$code = FILE_UTIL::get($this->get_file());

			$regex = "/public\s+function\s+wsdl\(\)\s+{[^}]+}/is";
			if(!preg_match($regex,$code,$matches))
				throw new Exception("Failed to locate the wsdl() function");

			$wsdl = preg_replace("/\],?[^\]]*(\][^\]]*\][^\]]*)$/","],\n".$this->get_wsdl_endpoint()."\n$1",$matches[0]);

			$code = preg_replace($regex,ltrim($this->get_endpoint())."\n\n\t\t".$wsdl,$code);

			FILE_UTIL::put($file,$code);

			$messages = array("Successfully updated the file ".HTML_UTIL::get_link("file:".FILE_UTIL::sanitize_file($file),FILE_UTIL::sanitize_file($file)));
		}

		function generate($override,&$messages=array()) {

			$file = $this->get_file();

			if(!$override && is_file($file))
				throw new Exception("The file ".$file." already exists");

			$this
				->assign("endpoint",$this->get_endpoint())
				->assign("wsdl_endpoint",$this->get_wsdl_endpoint());

			if(!$this->write_template(MODEL_PATH::get_assets_directory()."api.inc",$file))
				throw new Exception("Failed to generate ".$file);

			$messages = array("Successfully added the file ".HTML_UTIL::get_link("file:".FILE_UTIL::sanitize_file($file),FILE_UTIL::sanitize_file($file)));

			return true;
		}

		function get_endpoint() {
			return $this->get(MODEL_PATH::get_assets_directory()."api_endpoint.inc");
		}

		function get_wsdl_endpoint() {
			return $this->get(MODEL_PATH::get_assets_directory()."api_wsdl_endpoint.inc");
		}

		function get($template) {

			$cmodel_class = "CMODEL_".strtoupper($this->_model);

			if(!@class_exists($cmodel_class))
				throw new Exception("Class ".$cmodel_class." does not exist");

			$cmodel = $cmodel_class::create();

			$keywords = $accessible_fields = $fields = [];
			foreach($cmodel->get_dbos() as $dbo) {
				$fields += $dbo->get_columns();

				foreach($dbo->get_columns() as $name=>$column)
					if(preg_match("/(name|description)/",$name))
						$keywords[] = ["name"=>$name,"tablename"=>$dbo->get_tablename()];
			}

			$accessible_fields = array_values(array_filter(array_keys($fields), function($v) {
			    return !preg_match("/(".$this->_model."_id$|guid|create_date|configs|_time|_url|_id$)/",$v);
			}));

			return $this
						->assign("model_upper",strtoupper($this->_model))
						->assign("model_plural_trim",str_replace("_","",$this->_model_plural))
						->assign("model_plural_upper",strtoupper($this->_model_plural))
						->assign("model_plural_upper_trim",strtoupper(str_replace("_","",$this->_model_plural)))
						->assign("model_plural_proper",ucwords($this->_model_plural))
						->assign("model_proper",ucwords($this->_model))
						->assign("model_name",ucwords(str_replace("_"," ",$this->_model)))
						->assign("model",$this->_model)
						->assign("keywords",$keywords)
						->assign("api",$this->_api)
						->assign("api_singular",rtrim($this->_api,"s"))
						->assign("dbos",$cmodel->get_dbos())
						->assign("accessible_fields",$accessible_fields)
						->assign("has_state",in_array("state",array_keys($fields)))
						->assign("fields",array_keys($fields))
						->assign("model_id",$this->_model."_id")
						->assign("model_plural",$this->_model_plural)
						->assign("parent_model",$this->_parent_model)
						->fetch($template);

		}
	}