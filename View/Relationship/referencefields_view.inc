<?
	class VIEW_GENERATE_REFERENCEFIELDS extends VIEW {
		
		protected $_reference_model 		= "";
		protected $_reference_model_column	= "";
		protected $_reference_columns 		= array();
		
		function __construct() {
			$this->set_route("generate","referencefields");
			$this->set_roles(array(SECURITY_MANAGER::ROLE_PUBLIC_USER));
			$this->type_blank();
		}
		
		function init() {
			DEBUG_UTIL::enable_format_html();
			
			$registry 		= REGISTRY::get_instance();
			$app_dir		= APPLICATION::get_main_application_directory();
			$reference_model 	= $registry->get_request_parm("reference_model");
			
			if($reference_model)
				$this->_reference_model = $reference_model;
			
			if($this->_reference_model)
				$this->_reference_columns = MODEL_DB::get_dbo_columns($this->_reference_model,$app_dir);
				
			$reference_model_column_list = array();
			foreach($this->_reference_columns as $name=>$column)
				$reference_model_column_list[$name] = $name;
				
			$this->set_var("reference_model",$this->_reference_model);
			$this->set_var("reference_columns",$this->_reference_columns);
			$this->set_var("reference_model_column_list",$reference_model_column_list);
			$this->set_var("reference_model_column",$this->_reference_model_column);			
		}		
	}