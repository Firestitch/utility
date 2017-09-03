<?
	class {if $framework}B{/if}HMODEL_{$upper_model} extends HMODEL {

		protected $_select_fields = [{$select_fields}];

		public function __construct() {
{if $extends}			parent::__construct(DBQ_{$upper_model}::create()->inner("{$extend_tablename}","{$extend_primary_id}"));
{else}			parent::__construct(DBQ_{$upper_model}::create());
{/if}
		}

		public function gets() {
			${$lower_models} = $this->get_dbq()->select($this->_select_fields);

			$cmodels = [];
			foreach(${$lower_models} as ${$lower_model}) {
				$cmodel = CMODEL_{$upper_model}::create()->populate(${$lower_model},true,false);
				$this->apply_properties($cmodel,${$lower_model});
				$cmodels[] = $cmodel;
			}

			if(!$cmodels)
				return [];

			return $cmodels;
		}

{if $has_state}		public function where_state_active()			{ return $this->set_state(CMODEL_{$upper_model}::STATE_ACTIVE); }
{/if}
		public function set_{$lower_model}_ids($values)	{ return $this->where("{$tablename}.{$lower_model}_id","IN",$values); }
		public function set_{$lower_model}_id($value)	{ return $this->where("{$tablename}.{$lower_model}_id","=",$value); }
{foreach $fields as $name=>$field}		public function set_{$name}($value)		{ return $this->where("{$field}","=",$value); }
		public function set_{$name}s($values)			{ return $this->where("{$field}","IN",$values); }
{/foreach}

		public static function create($defaults=false) {
			$hmodel = parent::create();
{if $has_state}			if($defaults)
				$hmodel->where("{$tablename}.state","<>",CMODEL_{$upper_model}::STATE_DELETED);
{/if}
			return $hmodel;
		}

		public function acl() {
			return [];
		}
	}
