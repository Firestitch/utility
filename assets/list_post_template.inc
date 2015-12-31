<h1 class="fl">{$pretty_model|capitalize}</h1>

{if $is_view_format_page}
<?=HTML_UTIL::get_link("/{$lower_controller}/{$lower_task}/","Add {$pretty_model|capitalize}",array("class"=>"{$small_button_class} btn-back fr"))?>
{else if $is_view_format_popup}
<?=HTML_UTIL::get_link("javascript:show_box('/{$lower_controller}/{$lower_task}/',500,400)","Add {$title|capitalize}",array("class"=>"{$small_button_class} btn-back fr"))?>
{/if}

<div class="cb"></div>

<?{if $is_search_form}
	
	$search_data[] = array("Search: ",HTML_UTIL::get_input("search[keyword]",""),"State: ",HTML_UTIL::get_dropdown("search[state]",DBQ_{$upper_model}::get_state_list(),get_value($search,array("state"))));
	$search_data[] = array("",HTML_UTIL::get_button("search[submit]","Search",array("type"=>{if $is_format_post}"submit"{else}"button"{/if})));
	
	$search_table = new HTML_TABLE_UTIL();
	$search_table->set_data($search_data);
	$search_table->disable_css();
	$search_table->render();
	
{/if}

	$table_data = array();

	foreach(${$lower_models} as ${$lower_model}) {
		
		$actions = array();
		$actions[] = HTML_UTIL::get_link(${$lower_model}->get_manage_url(),MODEL_IMAGE_ICON::get_edit());
		$actions[] = HTML_UTIL::get_link("/{$lower_controller}/do{$lower_task}remove/{$id}:".${$lower_model}->get_{$lower_model}_id()."/",MODEL_IMAGE_ICON::get_delete(),array("onclick"=>"return confirm('Are you sure you would like to delete this {$title}?');"));

		$name = HTML_UTIL::get_link(${$lower_model}->get_manage_url(),{$id_get_column});
		
		$table_data[] = array({$get_functions},array("data"=>implode(" ",$actions),"class"=>"wsnw tar"));
	}

	$html_table = new HTML_TABLE_UTIL();
	$html_table->set_data($table_data);
	$html_table->set_headings(array({$headings},""));
	$html_table->set_width("100%");
	$html_table->render();

	$this->show_view("paging");
