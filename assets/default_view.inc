<?
	class {if $is_framework}BASE_{/if}VIEW_{$upper_controller}_{$upper_task} extends VIEW {

		public function __construct() {
			parent::__construct("{$lower_controller}","{$lower_task}")->set_permissions([{{$permissions}}]);
{if $has_form==true}			$this->set_form("/{$lower_controller}/do{$lower_task}/");
{/if}
{if $is_type_body}
{if $page_title}			$this->set_title("{$page_title}");
{/if}
{/if}
{if $is_type_blank}			$this->type_blank();
			$this->set_title("");
{/if}
{if $is_type_component}			$this->type_component();
{/if}
{if $is_type_popup}			$this->type_popup();
{/if}
		}

		public function init() {

		}
	}