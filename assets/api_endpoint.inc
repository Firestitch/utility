		public function {$method}($params,$data) {

			${$model_id} = value($params,"{$model_id}");

{if $parent_model}

			${$parent_model}_cmodel = HMODEL_{$parent_model|upper}::create()
											->require(${$parent_model}_id);
{/if}{if in_array("get",$methods)}
			if($this->is_get()) {

				${$model}_hmodel = HMODEL_{$model_upper}::create(false){if $parent_model}

										->set_{$parent_model}_id(${$parent_model}_id){/if};

				if(${$model_id})
					${$model}_hmodel->set_{$model_id}(${$model_id});
{if $keywords}

				if(!is_null($value=$this->get("keyword")))
					${$model}_hmodel->group(DB_GROUP::create()
{foreach from=$keywords item=keyword name=keywords}										->where("{$keyword.tablename}.{$keyword.name}","LIKE","%".$value."%","OR"){if $smarty.foreach.keywords.last});
{else}

{/if}{/foreach}
{/if}{foreach $dbos as $dbo}{foreach $dbo->get_columns() as $name=>$column}{if in_array($name,$accessible_fields)}{if $name=="state"}

				if(!is_null($value=$this->get("state")))
					${$model}_hmodel->set_states(explode(",",$value));
				elseif(!${$model_id})
					${$model}_hmodel->set_state(CMODEL_{$model_upper}::STATE_ACTIVE);
{elseif preg_match("/(name|description)/",$name)}{continue}{elseif $column->is_data_type("date") || $column->is_data_type("datetime")}

				if(!is_null($value=$this->get("{$name}")))
					${$model}_hmodel->where("{$dbo->get_tablename()}.{$name}",">",CMODEL_TIME::create($value)->db());

				if(!is_null($value=$this->get("{$name}")))
					${$model}_hmodel->where("{$dbo->get_tablename()}.{$name}","<",CMODEL_TIME::create($value)->db());
{else}

				if(!is_null($value=$this->get("{$name}")))
					${$model}_hmodel->where("{$dbo->get_tablename()}.{$name}","=",$value);
{/if}{/if}{/foreach}{/foreach}
{foreach $loads as $load}				if(!is_null($this->get("{$load}")))
					${$model}_hmodel->load_{$load}();

{/foreach}
				$this->acl()->apply(${$model}_hmodel);

				if(!${$model_id})
					$this->handler(${$model}_hmodel{if $order_by},"{$order_by}"{/if});

				${$model_plural} = [];
				foreach(${$model}_hmodel->gets() as ${$model}_cmodel)
					${$model_plural}[] = ${$model}_cmodel->arry();

				if(${$model_id})
					$this->require_data("{$model}",array_shift(${$model_plural}));
				else
					$this->data("{$model_plural}",array_values(${$model_plural}));



			}{/if}{if in_array("post",$methods) || in_array("put",$methods)} elseif($this->is_post() || $this->is_put()) {

				${$model}_cmodel = CMODEL_{$model_upper}::create();

				if($this->is_post()) {
{if $parent_model}
					${$model}_cmodel->set_{$parent_model}_id(${$parent_model}_id);
{/if}
				} elseif($this->is_put())
					${$model}_cmodel = $this->acl()->write(HMODEL_{$model_upper}::create(false){if $parent_model}

														->set_{$parent_model}_id(${$parent_model}_id){/if}

														->set_{$model_id}(${$model_id}));

				${$model}_cmodel
					//->start_audit()
					->fill($this->posts(),["{'", "'|implode:$accessible_fields}"])
					->validate()
					->save();
					//->end_audit($this->get_session());

				$this->data("{$model}",${$model}_cmodel->arry());

			}{/if}{if in_array("delete",$methods)} elseif($this->is_delete()) {

				${$model}_cmodel = $this->acl()->write(HMODEL_{$model_upper}::create(false){if $parent_model}

														->set_{$parent_model}_id(${$parent_model}_id){/if}

														->set_{$model_id}(${$model_id}));

				${$model}_cmodel->delete();
					//->audit_delete($this->get_session());

				$this->data("{$model}",${$model}_cmodel->arry());
			}
{/if}
		}
{if in_array("order",$options)}

		public function order() {

			if($this->is_put()) {

				$hmodel = $this->acl()->apply(HMODEL_{$model_upper}::create(false));
				$ids = is_array($this->post("{$model_id}s")) ? $this->post("{$model_id}s") : explode(",",$this->post("{$model_id}s"));
				
				CMODEL_ORDERING::create($hmodel,"{$model_id}")
					->reorder($ids,$this->post("page") * $this->post("limit"));
			}
		}
{/if}