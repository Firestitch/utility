<?
	if(${$lower_models}) {

		$table_data = [];

		foreach(${$lower_models} as ${$lower_model}) {

			$actions = [];
{if $is_view_format_popup}			$actions[] = HTML_UTIL::link("javascript:;",MODEL_IMAGE_ICON::get_edit(),["class"=>"{$hyphen_model}-update","data-{$id}"=>${$lower_model}->get_{$lower_model}_id()]);
{else}			$actions[] = HTML_UTIL::link(${$lower_model}->get_manage_url(),MODEL_IMAGE_ICON::get_edit());
{/if}			$actions[] = HTML_UTIL::link("javascript:;",MODEL_IMAGE_ICON::get_delete(),["class"=>"{$hyphen_model}-remove","data-{$id}"=>${$lower_model}->get_{$lower_model}_id()]);
{if $has_priority}			$actions[] = HTML_UTIL::link("javascript:;",MODEL_IMAGE_ICON::get_drag(),["class"=>"order"]);
{/if}
{if $is_view_format_popup}			$name = HTML_UTIL::link("javascript:;",{$id_get_column},["class"=>"{$hyphen_model}-update","data-{$id}"=>${$lower_model}->get_{$lower_model}_id()]);
{else}			$name = HTML_UTIL::link(${$lower_model}->get_manage_url(),{$id_get_column});
{/if}
			$table_data[{if $has_priority}${$lower_model}->get_{$lower_model}_id(){/if}] = [{$get_functions},["data"=>implode(" ",$actions),"class"=>"wsnw w1"]];
		}

		HTML_TABLE_UTIL::create()
			->set_data($table_data)
			->set_headings([{$headings},""])
			->set_id("{$hyphen_model}-table")
{if $has_priority}
			->set_row_id_prefix("{$id}s_")
{/if}
			->add_class("w100p")
			->render();

		$this->show_view("paging");
	} else
		echo "There are currently no {$lower_model_spaced}";
