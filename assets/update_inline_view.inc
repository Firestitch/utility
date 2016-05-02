<?
	class {if $is_framework}BASE_{/if}VIEW_{$upper_controller}_{$upper_task} extends VIEW {
	
		protected $_{$lower_model}_cmodel = null;
		
		function __construct() {
			$this->set_route("{$lower_controller}","{$lower_task}");
			$this->set_roles([{$security_roles}]);
			$this->set_form("javascript:;",false,"{$hyphen_model}-form");			
			$this->type_blank();
			
			$this->_{$lower_model}_cmodel = new CMODEL_{$upper_model}();
		}
		
		function init() {
			$registry = REGISTRY::get_instance();

			if($this->is_autoload() && is_numeric(${$lower_model}_id=$registry->get_post_parm("{$id}")))
				$this->load(${$lower_model}_id);

{if $relation_field}			if(is_numeric(${$relation_field}=$registry->get_post_parm("{$relation_field_abr}")))
				$this->_{$lower_model}_cmodel->set_{$relation_field}(${$relation_field});
{/if}			
{foreach $dependent_objects as $object=>$field}			${$object}_dbq = new DBQ_{$object|upper}();
			${$object}_dbq->add_order_by("{$field}");
			${$object}_list = ${$object}_dbq->select_column("{$field}","{$object}_id");
			
{/foreach}
			$this->set_var("{$lower_model}", $this->_{$lower_model}_cmodel);
{foreach $dependent_objects as $object=>$field}			$this->set_var("{$object}_list",${$object}_list);
{/foreach}		}	

		function load(${$lower_model}_id) {

			${$lower_model}_hmodel = new HMODEL_{$upper_model}();
			${$lower_model}_hmodel->set_{$lower_model}_id(${$lower_model}_id);
			${$lower_model}_cmodel = get_value(${$lower_model}_hmodel->get_{$lower_models}(),0);

			if(${$lower_model}_cmodel)
				$this->_{$lower_model}_cmodel = ${$lower_model}_cmodel;
		}

		function set_{$lower_model}(${$lower_model}_cmodel) { $this->_{$lower_model}_cmodel = ${$lower_model}_cmodel; }			
	}