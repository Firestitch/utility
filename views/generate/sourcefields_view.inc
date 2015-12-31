<?
	class VIEW_GENERATE_SOURCEFIELDS extends VIEW {
		
		protected $_source_columns 		= array();
		
		function __construct() {
			$this->set_route("generate","sourcefields");
			$this->set_roles(array(SECURITY_MANAGER::ROLE_PUBLIC_USER));
			$this->type_blank();
		}
		
		function init() {
					
			$app_dir				= APPLICATION::get_main_application_directory();
			$source_model 			= $this->request("source_model");
			$source_model_column 	= $this->request("source_model_column");
			
			if($source_model)
				$source_model = $source_model;
			
			if($source_model)
				$this->_source_columns = MODEL_DB::get_dbo_columns($source_model,$app_dir);
				
			$source_model_column_list = array();
			foreach($this->_source_columns as $name=>$column)
				$source_model_column_list[$name] = $name;
				
			$this->set_var("source_model",$source_model);
			$this->set_var("source_columns",$this->_source_columns);
			$this->set_var("source_model_column_list",$source_model_column_list);
			$this->set_var("source_model_column",$source_model_column);			
		}		
	}