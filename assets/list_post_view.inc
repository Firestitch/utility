<?
	class {if $is_framework}BASE_{/if}VIEW_{$upper_controller}_{$upper_task_plural} extends VIEW {
	
		function __construct() {
			$this->set_route("{$lower_controller}","{$lower_task_plural}");
			$this->set_form("/{$lower_controller}/{$lower_task_plural}/");

			$this->set_roles(array({$security_roles}));
{if $page_title}			$this->set_title("{$page_title}");
{/if}
			{if !$is_format_post}$this->set_view("{$lower_models}",APPLICATION::get_view_instance("{$lower_controller}","{$lower_task}list"));
{/if}
		}
		
		function init() {
{if $is_format_post}
			$page_index	= is_numeric($this->request("page_index")) ? $this->request("page_index") : 0;
			$page_limit	= is_numeric($this->request("page_limit")) ? $this->request("page_limit") : 15;
{if $is_search_form}			$search		= is_array($this->request("search")) ? $this->request("search") : array();
{if $has_state}			$state 		= value($search,"state") ? get_value($search,"state") : DBQ_{$upper_model}::STATE_ACTIVE;
{/if}{/if}						
			${$lower_model}_hmodel = new HMODEL_{$upper_model}();
{if $has_state}{if $is_search_form}			${$lower_model}_hmodel->set_state($state);

			if($keyword=get_value($search,"keyword"))
				${$lower_model}_hmodel->add_filter("{$id_column}","LIKE","%".$keyword."%","OR","searchgroup");
{if $has_column_name}			${$lower_model}_hmodel->add_filter("name","LIKE","%".$keyword."%","OR","searchgroup");
{/if}{if $has_column_description}			${$lower_model}_hmodel->add_filter("description","LIKE","%".$keyword."%","OR","searchgroup");
{/if}
{else}			${$lower_model}_hmodel->set_state(DBQ_{$upper_model}::STATE_ACTIVE);
{/if}
{/if}

			$record_count = ${$lower_model}_hmodel->get_count();

			${$lower_model}_hmodel->set_limit($page_limit,$page_limit * $page_index,$record_count);

			${$lower_model}_cmodels = ${$lower_model}_hmodel->get_{$lower_models}();

			$this->set_view("paging",APPLICATION::get_base_view_instance("components","paging"));
			$this->get_view("paging")->populate($page_index,$page_limit,$record_count);

			$this->set_var("{$lower_models}",${$lower_model}_cmodels);
{if $is_search_form}			$this->set_var("search",$search);{/if}		
{/if}
{if !$is_format_post}			$this->set_var("search",array());	
{/if}
		}	
	}
	
	
				
				