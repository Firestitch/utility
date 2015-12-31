<?
	class CMODEL_VA_GENERATOR_ACTION_ORDER extends CMODEL_VA_GENERATOR_ACTION {
		
		protected $_format 		= "";
		protected $_model		= "";
		protected $_relation_field	= "";
		
		function __construct($controller,$task,$format,$security_roles,$app_dir,$relation_field) {
			parent::__construct($controller,$task,$security_roles,$app_dir);
			$this->_format = $format;
			$this->_relation_field = $relation_field;
		}
		
		function generate(&$messages=array()) {
						
			$this->_smarty->assign("pretty_model",$this->get_pretty($this->_model,false));
			$this->_smarty->assign("upper_model",strtoupper($this->_model));
			$this->_smarty->assign("upper_model",strtoupper($this->_model));
			$this->_smarty->assign("lower_models",LANG_UTIL::get_plural_string(strtolower($this->_model)));
			$this->_smarty->assign("id",$this->get_short_key_field_name($this->_model));
			$this->_smarty->assign("relation_field_abr",self::get_abr($this->_relation_field));
			$this->_smarty->assign("relation_field",$this->_relation_field);
			
			parent::generate($messages);
		}
		
		function get_template_filename() 	{ return "order_action.inc"; }
		function get_action_filename() 		{ return $this->_lower_task."order_action.inc"; }
		function set_model($model) 		{ $this->_model = $model; }
				
		function has_priority_column() {
		
			$columns = MODEL_DB::get_dbo_columns($this->_model,$this->_app_dir);
			return array_key_exists("priority",$columns);
		}
	}
		