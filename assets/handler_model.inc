<?php
namespace Backend\Handler;

use Framework\Core\Handler;
use Backend\Model\{$pascal_model}Model;
use Backend\Dbq\{$pascal_model}Dbq;

	class {$pascal_model}Handler extends Handler {

		protected $_select_fields = [{$select_fields}];

		public function __construct() {
{if $extends}			parent::__construct({$pascal_model}Dbq::create()->inner("{$extend_tablename}","{$extend_primary_id}"));
{else}			parent::__construct({$pascal_model}Dbq::create());
{/if}
		}

		public function gets() {
			${$lower_models} = $this->get_dbq()->select($this->_select_fields);

			$cmodels = [];
			foreach(${$lower_models} as ${$lower_model}) {
				$cmodel = {$pascal_model}Model::create()->populate(${$lower_model},true,false);
				$this->apply_properties($cmodel,${$lower_model});
				$cmodels[] = $cmodel;
			}

			if(!$cmodels)
				return [];

			return $cmodels;
		}

{if $has_state}		public function where_state_active()			{ return $this->set_state({$pascal_model}Model::STATE_ACTIVE); }
{/if}
		public function set_{$lower_model}_ids($values)	{ return $this->where("{$tablename}.{$lower_model}_id","IN",$values); }
		public function set_{$lower_model}_id($value)	{ return $this->where("{$tablename}.{$lower_model}_id","=",$value); }
{foreach $fields as $name=>$field}		public function set_{$name}($value)		{ return $this->where("{$field}","=",$value); }
		public function set_{$name}s($values)			{ return $this->where("{$field}","IN",$values); }
{/foreach}

		public static function create($defaults=false) {
			$hmodel = parent::create();
{if $has_state}			if($defaults)
				$hmodel->where("{$tablename}.state","<>",{$pascal_model}Model::STATE_DELETED);
{/if}
			return $hmodel;
		}

		public function acl() {
			return [];
		}
	}
