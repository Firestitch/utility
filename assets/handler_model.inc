<?
	class {if $framework}B{/if}HMODEL_{$upper_model} extends HMODEL {

		protected $_select_fields = array({$select_fields});

		public function __construct() {
{if $extends}			return $this->register_dbq(DBQ_{$upper_model}::create()->inner("{$extend_tablename}","{$extend_primary_id}"));
{else}			return $this->register_dbq(DBQ_{$upper_model}::create());
{/if}
		}

		public function gets() {
			${$lower_models} = $this->get_dbq()->select($this->_select_fields);

			${$lower_model}_cmodels = array();
			foreach(${$lower_models} as ${$lower_model}) {
				${$lower_model}_cmodel = CMODEL_{$upper_model}::create()->populate(${$lower_model},true,false);
				$this->apply_properties(${$lower_model}_cmodel,${$lower_model});
				${$lower_model}_cmodels[] = ${$lower_model}_cmodel;
			}

			if(!${$lower_model}_cmodels)
				return array();

			return ${$lower_model}_cmodels;
		}

{if $has_state}		public function where_state_active()			{ return $this->set_state(CMODEL_{$upper_model}::STATE_ACTIVE); }
{/if}
		public function set_{$lower_model}_ids($values)	{ return $this->where("{$tablename}.{$lower_model}_id","IN",$values); }
		public function set_{$lower_model}_id($value)	{ return $this->where("{$tablename}.{$lower_model}_id","=",$value); }
{foreach $fields as $name=>$field}		public function set_{$name}($value)		{ return $this->where("{$field}","=",$value); }
		public function set_{$name}s($values)			{ return $this->where("{$field}","IN",$values); }
{/foreach}

		public static function create($defaults=true) {
			return $defaults ? parent::create(){if $has_state}->where("{$tablename}.state","<>",CMODEL_{$upper_model}::STATE_DELETED){/if} : parent::create();
		}
	}
