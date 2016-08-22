<?
	class {if $is_framework}BASE_{/if}ACTION_{$upper_controller}_{$upper_task}REMOVE extends ACTION {
	
		function __construct() {
			parent::__construct([{$security_roles}]);
		}
		
		function process() {
			
			$has_success = false;
			
			if(is_numeric(${$lower_model}_id=$this->get("{$id}"))) {
				${$lower_model}_hmodel = new HMODEL_{$upper_model}();
				${$lower_model}_hmodel->set_{$lower_model}_id(${$lower_model}_id);
				
				if(${$lower_model}_cmodel=get_value(${$lower_model}_hmodel->get_{$lower_models}(),0))
					$has_success = ${$lower_model}_cmodel->delete();
			}

			if($has_success)
				APPLICATION::add_notify_message("Sucessfully removed the {$title}");
			else
				APPLICATION::add_error_message("There was a problem trying to remove the {$title}"); 

			$view = APPLICATION::get_view_instance("{$lower_controller}","{$lower_task_plural}");
				
			$this->set_view($view);			

		}	
	}