<?
	class CMODEL_VA_GENERATOR_VIEW_LIST_COMPONENT extends CMODEL_VA_GENERATOR_VIEW_LIST {
		
		protected $_model				= "";
		protected $_format				= "";
		protected $_list_table_classes	= array();
		protected $_view_format			= "";
		
		function __construct($controller,$task,$task_plural,$format,$security_roles,$app_dir,$is_search_form,$view_format,$relation_field,$list_table_classes) {
			$this->_base_task 	= $task;			
			$this->_list_table_classes = $list_table_classes;
			$task = $task."list";
			parent::__construct($controller,$task,$task_plural,$format,$security_roles,$app_dir,$is_search_form,$view_format,$relation_field);
		}		
	
		function pre_generate() {
				
			parent::pre_generate();
			
			$id = $this->get_id();
			
			$headings = $get_functions = array();
			
			$columns = $this->get_dbo_columns();

			$name_field = array();
			
			foreach($columns as $name=>$column) {
			
				if(!in_array($name,array("guid","create_date","modified_date"))) {
					
					$heading = $column->is_primary() ? '"ID"' : '"'.$this->get_pretty($name).'"';;
					
					$get_function = "\$".$this->_model."->get_".$name."()";


					
					if($column->is_primary() || $name=="name") {
												
						$this->_id_get_column = $get_function;
						$this->_id_column = $name;
						$get_function = "\$name";

						$name_field = array("get_function"=>$get_function,"heading"=>$heading);

						continue;
						
					} elseif($name=="state") 
						$get_function = "\$".$this->_model."->get_state_name()";
						
					$get_functions[] 	= $get_function;
					$headings[] 		= $heading;
				}
			}

			if($name_field) {
				array_unshift($get_functions,get_value($name_field,"get_function"));
				array_unshift($headings,get_value($name_field,"heading"));
			}
			
			$this->_smarty->assign("lower_model_spaced",LANG_UTIL::get_plural_string(strtolower(str_replace("_"," ",$this->_model))));
			$this->_smarty->assign("list_table_classes",$this->_list_table_classes);
			$this->_smarty->assign("base_task",strtolower($this->_base_task));
			$this->_smarty->assign("base_tasks",LANG_UTIL::get_plural_string($this->_base_task));
			$this->_smarty->assign("get_functions",implode(",",$get_functions));
			$this->_smarty->assign("headings",implode(",",$headings));
		}
		
		function get_template_filename() 			{ return "list_ajax_component_view.inc"; }
		function get_template_template_filename()	{ return "list_ajax_component_template.inc"; }
		function get_view_filename()				{ return strtolower(str_replace("_","",$this->_lower_task))."_view.inc"; }
		function get_view_template_filename()		{ return strtolower(str_replace("_","",$this->_lower_task)).".php"; }
		
		function get_base_task()					{ return $this->_base_task; }
		
		function set_model($model) 					{ $this->_model = $model; }		
	}