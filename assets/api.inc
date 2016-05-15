<?
	class VIEW_API_{$model_plural_upper_trim} extends VIEW_API_BASE {

		//public function require_api_token() { return false; }
		//public function skip_group() { return false; }

		public function {$model_plural_trim}(${$model_id}=null,$action=null) {

			if($this->is_get()) {

				${$model}_hmodel = HMODEL_{$model_upper}::create(false);

				if(${$model_id})
					${$model}_hmodel->set_{$model_id}(${$model_id});
{if $has_state}

				if(!is_null($value=$this->get("state")))
					${$model}_hmodel->set_states(explode(",",$value));
{/if}
				
				if(!${$model_id})
					$this->handler_paging(${$model}_hmodel,25);

				${$model}_cmodels = ${$model}_hmodel->gets();

				${$model_plural} = array();
				foreach(${$model}_cmodels as ${$model}_cmodel)
					${$model_plural}[] = ${$model}_cmodel->arry();

				if(${$model_id})
					$this->data("{$model}",value(${$model_plural},0));
				else
					$this->data("{$model_plural}",${$model_plural});



			} elseif($this->is_put()) {

				${$model}_cmodel = HMODEL_{$model_upper}::create()
										->set_{$model_id}(${$model_id})
										->get();

				if(!${$model}_cmodel)
					throw new Exception("Failed to load the {$model}");

				foreach(["{'", "'|implode:$accessible_fields}"] as $field)
					if(!is_null($value=$this->post($field))) 
						${$model}_cmodel->{ldelim}"set_$field"{rdelim}($value);
					
				${$model}_cmodel->save();
				
				$this->data("{$model}",${$model}_cmodel->arry());
				

				
			} elseif($this->is_post()) {
				
				${$model}_cmodel = CMODEL_{$model_upper}::create();
				
				foreach(["{'", "'|implode:$accessible_fields}"] as $field)
					if(!is_null($value=$this->post($field))) 
						${$model}_cmodel->{ldelim}"set_$field"{rdelim}($value);

				${$model}_cmodel->save();
				
				$this->data("{$model}",${$model}_cmodel->arry());



			} elseif($this->is_delete()) {

				${$model}_cmodel = HMODEL_{$model_upper}::create()
										->set_{$model_id}(${$model_id})
										->get();

				if(!${$model}_cmodel)
					throw new Exception("Failed to load the {$model}");

				${$model}_cmodel->delete();
				
				$this->data("{$model}",${$model}_cmodel->arry());
			}
		}

		public function wsdl() {
			return array(	"model"=>"{$model}",
							"name"=>"{$model_name}",
							"endpoints"=>array(array(	"path"=>array("{$model_plural_trim}","{$model_plural_trim}/{$model_id}"),
														"description"=>"Retrieve {$model_plural_proper} or a {$model_proper}",
														"method"=>"get",
														"parms"=>array(),
														"response"=>array("{$model_plural}","{$model}")),
												array(	"path"=>"{$model_plural_trim}/{$model_id}",
														"description"=>"Update a {$model_proper}",
														"method"=>"put",
														"parms"=>array({foreach $accessible_fields as $index=>$name}{if $name=="state"}"state"=>CMODEL_{$model_upper}::get_states(){else}"{$name}"{/if}{if $index<(count($accessible_fields)-1)},{/if}{/foreach}),
														"response"=>array("{$model}")),
												array(	"path"=>"{$model_plural_trim}",
														"description"=>"Create a {$model_proper}",
														"method"=>"post",
														"parms"=>array({foreach $accessible_fields as $index=>$name}{if $name=="state"}"state"=>CMODEL_{$model_upper}::get_states(){else}"{$name}"{/if}{if $index<(count($accessible_fields)-1)},{/if}{/foreach}),
														"response"=>array("{$model}")),
												array(	"path"=>"{$model_plural_trim}/{$model_id}",
														"description"=>"Delete a {$model_proper}",
														"method"=>"delete",
														"response"=>array("{$model}")),

			));
		}
	}