<?
	class CMODEL_VA_GENERATOR extends CMODEL_GENERATOR {

		protected $_upper_task 			= null;
		protected $_lower_task	 		= null;
		protected $_upper_controller 	= null;
		protected $_lower_controller 	= null;
		protected $_app_dir				= "";
		protected $_security_role_str 	= array();

		function __construct($controller,$task,$task_plural,$permissions,$app_dir=null) {
			parent::__construct($app_dir);

			$this->_upper_task 			= strtoupper($task);
			$this->_lower_task	 		= strtolower($task);
			$this->_lower_task_plural 	= strtolower($task_plural);
			$this->_upper_task_plural 	= strtoupper($task_plural);
			$this->_upper_controller 	= strtoupper($controller);
			$this->_lower_controller 	= strtolower($controller);

			$this->_app_dir			= $app_dir ? $app_dir : APPLICATION::get_main_application_directory();

			$security_role_list = array();
			foreach($permissions as $security_role)
				$security_role_list[] = $security_role;

			$this->_security_role_str = implode(",",$security_role_list);

			$this->_smarty = new CMODEL_SMARTY();
			$this->_smarty->assign("is_framework",preg_match("/framework/i",$this->_app_dir));
			$this->_smarty->assign("upper_controller",$this->_upper_controller);
			$this->_smarty->assign("lower_controller",$this->_lower_controller);
			$this->_smarty->assign("lower_task",$this->_lower_task);
			$this->_smarty->assign("upper_task",$this->_upper_task);
			$this->_smarty->assign("permissions",$this->_security_role_str);
			$this->_smarty->assign("lower_task_plural",$this->_lower_task_plural);
			$this->_smarty->assign("upper_task_plural",$this->_upper_task_plural);
			$this->_smarty->assign("small_button_class","btn btn-sm");
			$this->_smarty->registerPlugin("modifier","pretty", array($this,"get_pretty"));
			$this->_smarty->allow_php_tag();
		}

		function register_autoload() {
			spl_autoload_register(array($this, "autoload"),true,true);
		}

		function autoload($class) {

			if(preg_match("/CMODEL_/",$class)) {
				return APPLICATION::include_model($class,"complex",$this->_app_dir,array("framework"=>false,"trigger"=>false));
			}

			if(preg_match("/DBO_/",$class)) {
				return APPLICATION::include_dbo($class,$this->_app_dir,array("framework"=>false,"trigger"=>false));
			}

			if(preg_match("/DBQ_/",$class)) {
				return APPLICATION::include_dbq($class,$this->_app_dir,array("framework"=>false,"trigger"=>false));
			}
		}

		function set_frameworks($frameworks)	{

			$bootstrap2 = in_array("bootstrap2",$frameworks);
			$bootstrap3 = in_array("bootstrap3",$frameworks);

			$this->_smarty->assign("framework_bootrap2",$bootstrap2);
			$this->_smarty->assign("framework_bootrap3",$bootstrap3);

			if($bootstrap2)
				$this->_smarty->assign("small_button_class","btn btn-small");

			if($bootstrap3)
				$this->_smarty->assign("small_button_class","btn btn-default btn-sm");

			return $this;
		}

		function get_lower_controller() 		{ return $this->_lower_controller; }
		function get_upper_controller() 		{ return $this->_upper_controller; }
		function get_lower_task() 				{ return $this->_lower_task; }

		function write_file($file,$string,&$message="") {
			FILE_UTIL::put($file,$string);
			return true;
		}


		function get_pretty($model,$propercase=true) {
			$parts = array();
			foreach(explode("_",$model) as $part)
				$parts[] = $propercase ? STRING_UTIL::get_propercase($part) : $part;

			return implode(" ",$parts);
		}

		function get_hyphen($model) {
			return strtolower(str_replace("_","-",$model));
		}

		function get_abr($field) {
			return MODEL_GENERATE::get_abr($field);
		}

		function get_short_key_field_name($model) {

			APPLICATION::include_dbo($model,$this->_app_dir);

			$dbo_class = "DBO_".strtoupper($model);
			$dbo_object= new $dbo_class();

			$primary_key = get_value(array_keys($dbo_object->get_primary_keys()),0);

			$id = "";

			if($primary_key){
				$parts = explode("_",$primary_key);

				for($i=0;$i<count($parts)-1;$i++) {

					$part = get_value($parts,$i);
					$id .= substr($part,0,1);
				}

				$id = $id."id";
			}
			return $id;
		}
	}