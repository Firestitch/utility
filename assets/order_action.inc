<?
	class {if $is_framework}BASE_{/if}ACTION_{$upper_controller}_{$upper_task}ORDER extends ACTION_JSON {
	
		function __construct() {
			parent::__construct([{$security_roles}]);
		}
		
		function process() {
		
			${$lower_model}_ids	= (array)$this->post("{$id}s");
{if $relation_field}
			${$relation_field}	= $this->get("{$relation_field_abr}");
{/if}						
			DB::start_transaction();
			
			foreach(${$lower_model}_ids as $index=>${$lower_model}_id) {
				DBQ_{$upper_model}::create()
{if $relation_field}
					->where("{$relation_field}","=",${$relation_field})
{/if}
					->where("{$lower_model}_id","=",${$lower_model}_id)
					->update(array("priority"=>$index));
			}
			
			$this->success(DB::complete_transaction());
		}
	}
	

