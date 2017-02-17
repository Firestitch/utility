<?
	class {if $is_framework}BASE_{/if}ACTION_{$upper_controller}_{$upper_task}REMOVE extends ACTION_JSON {

		function __construct() {
			parent::__construct()->set_permissions([{{$permissions}}]);
		}

		function process() {

			${$lower_model}_cmodel = HMODEL_{$upper_model}::get($this->post("{$id}"));

			if(!${$lower_model}_cmodel)
				throw new Exception("Failed to load {$title}");

			${$lower_model}_cmodel->delete();

			$this->success(true);
		}
	}