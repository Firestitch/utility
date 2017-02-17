<?
	class {if $is_framework}BASE_{/if}ACTION_{$upper_controller}_{$upper_task} extends ACTION_JSON {

		function __construct() {
			parent::__construct()->set_permissions([{{$permissions}}]);
		}

		function process() {

			${$lower_model}_id	= $this->post("{$id}");
			$form			= $this->post("form");
{if $relation_field}			${$relation_field}		= $this->post("{$relation_field_abr}");
{/if}

			${$lower_model}_cmodel = (${$lower_model}_cmodel=HMODEL_{$upper_model}::get(${$lower_model}_id)) ? ${$lower_model}_cmodel : CMODEL_{$upper_model}::create();
			${$lower_model}_cmodel->populate($form);

{if $relation_field}			if(${$relation_field})
				${$lower_model}_cmodel->set_{$relation_field}(${$relation_field});

{/if}
			try {

				${$lower_model}_cmodel->save();

				if(!${$lower_model}_id) {
					APPLICATION::add_persistent_notify("Successfully added the {$pretty_model}");
					$this->redirect(${$lower_model}_cmodel->get_manage_url());
				}

				$this
					->data("{$id}",${$lower_model}_cmodel->get_{$lower_model}_id())
					->success();

			} catch(Exception $e) {
				$this->error($e);
			}

			if(!$this->has_success() && !$this->has_error())
				$this->error("There was a problem trying to save the {$pretty_model}");
		}
	}


