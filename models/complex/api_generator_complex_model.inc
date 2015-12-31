<?	
	class CMODEL_API_GENERATOR extends CMODEL_GENERATOR {
		
		protected $_model				= "";
		protected $_model_plural		= "";
		
		function __construct($dir,$model,$model_plural) {
			parent::__construct($dir);

			$this->_model 			= $model;
			$this->_model_plural	= $model_plural;		
		}
		
		function generate($override,&$messages=array()) {
			
			$dir 	= $this->get_instance_dir()."views/api/";
			$file 	= $dir.str_replace("_","",$this->_model_plural)."_view.inc";

			if(!$override && is_file($file))
				throw new Exception("The file ".$file." already exists");				

			FILE_UTIL::mkdir($dir);

			$cmodel_class = "CMODEL_".strtoupper($this->_model);
			$cmodel = $cmodel_class::create();

			$accessible_fields = $fields = [];
			foreach($cmodel->get_dbos() as $dbo)
				$fields += $dbo->get_columns();

			$accessible_fields = array_values(array_filter(array_keys($fields), function($v, $k) {
			    return !preg_match("/(".$this->_model."_id$|guid|create_date|configs|_time$)/",$v);
			}, ARRAY_FILTER_USE_BOTH));

			$this
				->assign("model_upper",strtoupper($this->_model))
				->assign("model_plural_trim",str_replace("_","",$this->_model_plural))
				->assign("model_plural_upper",strtoupper($this->_model_plural))
				->assign("model_plural_upper_trim",strtoupper(str_replace("_","",$this->_model_plural)))
				->assign("model_plural_proper",ucwords($this->_model_plural))
				->assign("model_proper",ucwords($this->_model))
				->assign("model_name",ucwords(str_replace("_"," ",$this->_model)))
				->assign("model",$this->_model)
				->assign("accessible_fields",$accessible_fields)
				->assign("has_state",in_array("state",array_keys($fields)))
				->assign("fields",array_keys($fields))
				->assign("model_id",$this->_model."_id")
				->assign("model_plural",$this->_model_plural);
	
			if(!$this->write_template(MODEL_PATH::get_assets_directory()."api.inc",$file))
				throw new Exception("Failed to generate ".$file);
				
			$messages = array("Successfully added the file ".HTML_UTIL::get_link("file:".FILE_UTIL::sanitize_file($file),FILE_UTIL::sanitize_file($file)));
		
			return true;
		}		
	}