<?
	class {if $is_framework}BASE_{/if}VIEW_{$upper_controller}_{$upper_task_plural} extends VIEW {

		function __construct() {
			parent::__construct("{$lower_controller}","{$lower_task_plural}");
			$this->set_permissions([{{$permissions}}]);
{if $is_list_body_blank}			$this->type_blank();{/if}
{if $is_list_body_popup}			$this->type_popup();{/if}
		}

		function init() {
{if $relation_field}
			${$relation_field} = $this->get("{$relation_field_abr}");
{/if}
{if $relation_field}
			$this->set_var("{$relation_field}",${$relation_field});
{/if}
		}
	}