<?
	class {if $is_framework}BASE_{/if}VIEW_{$upper_controller}_{$upper_task} extends VIEW {
		
		protected $_{$lower_model}_cmodels = array();

		function __construct() {
			parent::__construct("{$lower_controller}","{$lower_task}",[{$security_roles}]);

			{if !$is_format_post}$this->type_blank();
{/if}
			$this->set_view("paging",APPLICATION::get_base_view_instance("components","paging"));
		}
		
		function init() {

{if $relation_field}
			if(SERVER_UTIL::is_post() && (${$relation_field}=$this->post("{$relation_field_abr}")))
				$this->load(${$relation_field});
{else}		if(SERVER_UTIL::is_post())
				$this->load();
{/if}

			$this->set_var("{$lower_models}",$this->_{$lower_model}_cmodels);	
		}

{if $relation_field}		function load(${$relation_field}) {
{else}
		function load() {
{/if}
			$page_index	= $this->request("page_index") ? $this->request("page_index") : 0;
			$page_limit	= $this->request("page_limit") ? $this->request("page_limit") : 25;

{if $is_search_form}			$search		= (array)$this->request("search");
{if $has_state}			$state 		= value($search,"state")!==null ? value($search,"state") : CMODEL_{$upper_model}::STATE_ACTIVE;
{/if}{/if}						
			${$lower_model}_hmodel = HMODEL_{$upper_model}::create(false);
{if $has_state}{if $is_search_form}

			if($state)
				${$lower_model}_hmodel->set_state($state);
			
			if($keyword=value($search,"keyword")) {
				${$lower_model}_hmodel->where("{$id_column}","=",$keyword,"OR","searchgroup");
{if $has_column_name}				${$lower_model}_hmodel->where("name","LIKE","%".$keyword."%","OR","searchgroup");
{/if}{if $has_column_description}				${$lower_model}_hmodel->where("description","LIKE","%".$keyword."%","OR","searchgroup");
{/if}			}
			
{else}			${$lower_model}_hmodel->set_state(CMODEL_{$upper_model}::STATE_ACTIVE);
{/if}
{/if}
{if $has_priority}			${$lower_model}_hmodel->order("priority");
{/if}
{if $relation_field}			${$lower_model}_hmodel->where("{$relation_field}","=",${$relation_field});
{/if}
			$record_count = ${$lower_model}_hmodel->count();

			${$lower_model}_hmodel->set_limit($page_limit,$page_limit * $page_index,$record_count);

			$this->_{$lower_model}_cmodels = ${$lower_model}_hmodel->gets();

			$this->get_view("paging")->populate($page_index,$page_limit,$record_count,'$("#{$hyphen_model}-list").trigger("load")');
		}	
	}
	
	
				
				