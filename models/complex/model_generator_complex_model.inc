<?
	class CMODEL_MODEL_GENERATOR extends MODEL {

		protected $_app_dir 		= null;
		protected $_lower_model 	= null;
		protected $_upper_model 	= null;
		protected $_tablename 		= null;
		protected $_framework 		= null;
		protected $_smarty	 		= null;
		protected $_extends	 		= null;

		function __construct($model,$app_dir=null,$framework=false,$extends="") {
			$this->_extends 		= strtoupper($extends);
			$this->_lower_model 	= strtolower($model);
			$this->_upper_model 	= strtoupper($model);
			$this->_framework 		= $framework;
			$this->_app_dir  		= $app_dir ? $app_dir : APPLICATION::get_main_application_directory();

			$this->_smarty = new CMODEL_SMARTY();
			$this->_smarty->assign("upper_model",$this->_upper_model);
			$this->_smarty->assign("lower_model",$this->_lower_model);
			$this->_smarty->assign("lower_models",LANG_UTIL::get_plural_string($this->_lower_model));
		}

		function register_autoload() {
			spl_autoload_register(array($this, "autoload"),true,true);
		}

		function autoload($class) {

			if(preg_match("/CMODEL_/",$class)) {
				return APPLICATION::include_model($class,"complex",$this->_app_dir,array("trigger"=>false,"framework"=>false));
			}

			if(preg_match("/DBO_/",$class)) {
				return APPLICATION::include_dbo($class,$this->_app_dir,array("framework"=>false,"trigger"=>false));
			}

			if(preg_match("/DBQ_/",$class)) {
				return APPLICATION::include_dbq($class,$this->_app_dir,array("framework"=>false,"trigger"=>false));
			}
		}

		function init() {

			$this->register_autoload();

			$dbo = self::get_dbo($this->_lower_model);

			$this->_smarty->assign("columns",$dbo->get_columns());
		}

		function generate_complex_model() {

			$this->init();

			$dbo_class = "DBO_".$this->_upper_model;
			$dbo = $dbo_class::create();

			$columns = array();
			foreach($dbo->get_columns() as $name=>$column)
				$columns[$name] = $column;

			$parent_id = current(array_keys($dbo->get_primary_keys()));
			$extend_id = "";

			if($this->_extends) {
				$extend_class = "DBO_".strtoupper($this->_extends);
				$extend_dbo = $extend_class::create();
				$extend_id = current(array_keys($extend_dbo->get_primary_keys()));
			}

			$has_guid 			= array_key_exists("guid",$columns);
			$has_state 			= array_key_exists("state",$columns);
			$has_create_date	= array_key_exists("create_date",$columns);

			$dbq_class 	= "DBQ_".$this->_upper_model;
			$dbq 		= $dbq_class::create();

			$refl = new ReflectionClass($dbq);
			$object_consts = array_keys($refl->getConstants());

			$refl = new ReflectionClass("DBQ");
			$dbq_consts = array_keys($refl->getConstants());

			$diff_consts = array_diff($object_consts,$dbq_consts);

			$consts = array();
			foreach($diff_consts as $const) {
				$field = strtolower(get_value(explode("_",$const),0));
				$consts[] = array("const"=>$const,"field"=>$field);
			}

			$dbo = self::get_dbo($this->_lower_model);

			$extends = "CMODEL";

			if($this->_extends)
				$extends .= "_".$this->_extends;

			$this->_smarty->assign("extends",$extends);
			$this->_smarty->assign("extended",!!$this->_extends);
			$this->_smarty->assign("extend_id",$extend_id);
			$this->_smarty->assign("has_multiple_keys",count($dbo->get_primary_keys())>1);
			$this->_smarty->assign("keys",array_keys($dbo->get_primary_keys()));
			$this->_smarty->assign("primary_key",value(array_keys($dbo->get_primary_keys()),0));
			$this->_smarty->assign("framework",$this->_framework);
			$this->_smarty->assign("has_guid",$has_guid);
			$this->_smarty->assign("has_state",$has_state);
			$this->_smarty->assign("id",MODEL_GENERATE::get_abr($this->_lower_model)."id");
			$this->_smarty->assign("has_create_date",$has_create_date);
			$this->_smarty->assign("consts",$consts);
			$this->_smarty->allow_php_tag();

			return $this->generate_model("complex");
		}

		function generate_handler_model() {

			$this->init();

			$cmodel = "CMODEL_".$this->_upper_model;
			$dbos 	= array_values($cmodel::create()->get_dbos());

			$parent_dbo = current($dbos);
			reset($dbos);

			$parent_id = current(array_keys($parent_dbo->get_primary_keys()));
			reset($dbos);

			$extend_primary_id = null;
			$fields = array();
			foreach($dbos as $index=>$dbo) {

				$tablenames[] = $dbo->get_tablename();

				if(!$index) {
					$extend_primary_id = value(array_keys($dbo->get_primary_keys()),0);
				}

				foreach($dbo->get_columns() as $name=>$column)
					if(!$column->is_primary() && preg_match("/(^state$|_id$|guid)/",$name))
						$fields[$name] = $dbo->get_tablename().".".$name;
			}

			$this->_smarty->assign("select_fields",'"'.implode('.*","',$tablenames).'.*"');
			$this->_smarty->assign("extends",$this->_extends);
			$this->_smarty->assign("extend_primary_id",$extend_primary_id);
			$this->_smarty->assign("extend_tablename",value($tablenames,0));
			$this->_smarty->assign("tablename",value($tablenames,count($tablenames) - 1));
			$this->_smarty->assign("fields",$fields);
			$this->_smarty->assign("has_state",array_key_exists("state",$fields));
			$this->_smarty->assign("framework",$this->_framework);

			return $this->generate_model("handler");
		}

		function generate_simple_model() {
			return $this->generate_model("simple");
		}

		function get_dbo($model) {
			$dbo_class = "DBO_".strtoupper($model);
			return new $dbo_class();
		}

		function generate_model($model_type) {

			FILE_UTIL::mkdir($this->get_model_directory($model_type));

			$template_file = MODEL_PATH::get_assets_directory().$model_type."_model.inc";

			$content = $this->_smarty->fetch($template_file);

			return $this->write_file($this->get_model_file($model_type),$content);
		}

		function get_model_file($model_type) {
			return FILE_UTIL::sanitize_file($this->_app_dir."models/".$model_type."/".$this->_lower_model."_".$model_type."_model.inc");
		}

		function get_complex_model_file() 	{ return $this->get_model_file("complex"); }
		function get_handler_model_file() 	{ return $this->get_model_file("handler"); }
		function get_simple_model_file() 	{ return $this->get_model_file("simple"); }

		function get_model_directory($model_type) {
			return $this->_app_dir."models/".$model_type."/";
		}

		function write_file($file,$string) {

			$error_message = "";
			$has_success = FILE_UTIL::put_file_contents($file,$string,$error_message);

			if($error_message==null)
				$this->add_notify_message('Successfully added the file <a href="'.$file.'">'.$file.'</a>');
			else
				throw new Exception($error_message." ".$file);

			return $has_success;
		}
	}