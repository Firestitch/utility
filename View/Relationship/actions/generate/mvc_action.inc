<?
	class ACTION_GENERATE_MVC extends ACTION {
		
		function __construct() {
			$this->disable_authorization();	
		}
		
		function process() {
			
			$task 			= strtolower($this->post("task"));
			$controller		= strtolower($this->post("controller"));
			$location		= strtolower($this->post("location"));
			$page_title		= strtolower($this->post("page_title"));
			$mva_components = is_array($this->post("mva_components")) ? $this->post("mva_components") : array();
			$has_form 		= $this->post("has_form");
			$override 		= $this->post("override");
			$view_type 		= $this->post("view_type");
			$security_roles = is_array($this->post("security_roles")) ? $this->post("security_roles") : array();			
			$app_dir		= DIR_INSTANCE.$location."/";
			
			if($this->post("cmd_generate") && $this->is_form_valid($task,$controller,$mva_components)) {				
				
				$has_success = false;
				
				if(in_array("V",$mva_components)) {
				
					$va_generator_cmodel = new CMODEL_VA_GENERATOR_VIEW($controller,$task,"",$security_roles,$app_dir);
					$va_generator_cmodel->set_has_form($has_form);
					$va_generator_cmodel->set_page_title($page_title);
					$va_generator_cmodel->set_view_type($view_type);
					
					if($override || !$va_generator_cmodel->has_view_file()) {
										
						$has_success = $va_generator_cmodel->generate();
						
						APPLICATION::inherit_messages($va_generator_cmodel);
					} else {
						APPLICATION::add_warning_message("The ".$va_generator_cmodel->get_view_file()." view already exists");					
						APPLICATION::add_warning_message("The ".$va_generator_cmodel->get_view_template_file()." template already exists");					
					}
				}
					
				if(in_array("A",$mva_components)) {
				
					$va_generator_cmodel = new CMODEL_VA_GENERATOR_ACTION($controller,$task,$security_roles,$app_dir);
					
					if($override || !$va_generator_cmodel->has_action_file()) {
					
						$has_success = $va_generator_cmodel->generate();
						
						APPLICATION::inherit_messages($va_generator_cmodel);
					} else
						APPLICATION::add_warning_message("The ".$va_generator_cmodel->get_action_file()." action already exists");								
				}			
			}
			
			$view = APPLICATION::get_view_instance("generate","mvc");			
			$view->set_mva_components($mva_components);
			$view->set_task($task);
			$view->set_controller($controller);
			$view->set_page_title($page_title);
			$view->set_has_form($has_form);		
			$view->set_selected_security_roles($security_roles);
			$view->set_override($override);
			$view->set_view_type($view_type);
			$this->set_view($view);			
		}	
		
		function is_form_valid($task,$controller,$mva_components) {
			
			if(is_empty($mva_components))
				APPLICATION::add_error_message("Please specify at least one MVA component");
			
			if(is_empty($task))
				APPLICATION::add_error_message("Invalid task");
				
			if(is_empty($controller))
				APPLICATION::add_error_message("Invalid controller");			
			return !APPLICATION::has_error_messages();
		}		
	}
	