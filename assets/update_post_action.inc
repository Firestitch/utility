<?
	class {if $is_framework}BASE_{/if}ACTION_{$upper_controller}_{$upper_task} extends ACTION {
	
		function __construct() {
			parent::__construct([{$security_roles}]);
		}
		
		function process() {
			
			${$lower_model}_id	= $this->post("{$id}");
			$form			= $this->post("form");
{if $relation_field}			${$relation_field}		= $this->post("{$relation_field_abr}");
{/if}
				
			${$lower_model}_cmodel = null;
			
			if(${$lower_model}_id)
				${$lower_model}_cmodel = HMODEL_{$upper_model}::get(${$lower_model}_id);
				
			${$lower_model}_cmodel = ${$lower_model}_cmodel ? ${$lower_model}_cmodel : new CMODEL_{$upper_model}();
			${$lower_model}_cmodel->populate($form);

{if $relation_field}			if(${$relation_field})
				${$lower_model}_cmodel->set_{$relation_field}(${$relation_field});

{/if}
			$has_success 	= false;
			$view 		= null;

			if($this->is_form_valid(${$lower_model}_cmodel)) {
				$has_success = ${$lower_model}_cmodel->save();
			
				if($has_success)
					APPLICATION::add_notify("Successfully saved the {$pretty_model}"); 
				else
					APPLICATION::add_error("There was a problem trying to save the {$pretty_model}"); 
			}
{if $is_view_format_popup}
			if($has_success)
				die("<script>parent.FF.popup.hide()</script>");
{/if}			
			if(!$view) {
				$view = APPLICATION::get_view_instance("{$lower_controller}","{$lower_task}");
				$view->set_{$lower_model}(${$lower_model}_cmodel);
			}

			$this->set_view($view);
		}
		
		function is_form_valid(${$lower_model}_cmodel) {
			return !APPLICATION::has_error_messages();
		}		
	}
	

