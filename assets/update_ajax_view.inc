<?
	class {if $is_framework}BASE_{/if}VIEW_{$upper_controller}_{$upper_task} extends VIEW {
	
		protected $_{$lower_model}_cmodel = null;
		
		function __construct() {
			parent::__construct("{$lower_controller}","{$lower_task}",[{$security_roles}]);
{if $page_title}			$this->set_title("{$page_title}");
{/if}
{if $is_interface_popup}			$this->type_popup();{/if}
{if $is_interface_blank}			$this->type_blank();{/if}
			
			$this->_{$lower_model}_cmodel = CMODEL_{$upper_model}::create();
		}
		
		function init() {
			
			if(${$lower_model}_id=$this->get("{$id}"))
				$this->load(${$lower_model}_id);

{if $relation_field}			if(is_numeric(${$relation_field}=$this->get("{$relation_field_abr}")))
				$this->_{$lower_model}_cmodel->set_{$relation_field}(${$relation_field});
{/if}
{if $dependent_objects}/*		
{foreach $dependent_objects as $object=>$field}			${$object}_list = ARRAY_UTIL::listing(HMODEL_{$object|upper}::create()
								->order("{$field}")
								->gets(),"get_{$field}","get_{$object}_id");
			
{/foreach}
			
{foreach $dependent_objects as $object=>$field}			$this->set_var("{$object}_list",${$object}_list);
{/foreach}	
*/{/if}
		$this->set_var("{$lower_model}",$this->_{$lower_model}_cmodel);
		}	

		function load(${$lower_model}_id) {

			${$lower_model}_cmodel = HMODEL_{$upper_model}::create(false)->get(${$lower_model}_id);

			if(${$lower_model}_cmodel)
				$this->_{$lower_model}_cmodel = ${$lower_model}_cmodel;
		}		
	}