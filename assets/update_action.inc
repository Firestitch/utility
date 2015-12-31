<?
	class {if $is_framework}BASE_{/if}ACTION_{$upper_controller}_{$upper_task} extends ACTION_JSON {
	
		function __construct() {
			parent::__construct(array({$security_roles}));
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
			
			if($this->is_form_valid(${$lower_model}_cmodel)) {
				$has_success = ${$lower_model}_cmodel->save();
			
				if($has_success) {
					
					$this->data("{$id}",${$lower_model}_cmodel->get_{$lower_model}_id());

					if(!${$lower_model}_id) {
						APPLICATION::add_persistent_notify("Successfully added the {$pretty_model}"); 
						$this->redirect(${$lower_model}_cmodel->get_manage_url());
					}
				}
					
				$this->success($has_success);
			}
			
			if(!$this->has_success() && !$this->has_error())
				$this->error("There was a problem trying to save the {$pretty_model}");
		}
		
		function is_form_valid(${$lower_model}_cmodel) {
			return !$this->has_error();
		}		
	}
	

