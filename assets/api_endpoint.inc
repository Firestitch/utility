		public function {$model_plural_trim}({if $parent_model}${$parent_model}_id=null,{/if}${$model_id}=null,$action=null) {

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
				else
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

				$this->acl()->apply(${$model}_hmodel);

				if(!${$model_id})
					$this->handler(${$model}_hmodel);

				${$model_plural} = [];
				foreach(${$model}_hmodel->gets() as ${$model}_cmodel)
					${$model_plural}[] = ${$model}_cmodel->arry();

				if(${$model_id})
					$this->data("{$model}",value(${$model_plural},0));
				else
					$this->data("{$model_plural}",${$model_plural});



			} elseif($this->is_put()) {

				${$model}_cmodel = $this->acl()->write(HMODEL_{$model_upper}::create(){if $parent_model}

														->set_{$parent_model}_id(${$parent_model}_id){/if}

														->set_{$model_id}(${$model_id}));

				${$model}_cmodel
					->fill($this->posts(),["{'", "'|implode:$accessible_fields}"])
					->validate()
					->save();

				$this->data("{$model}",${$model}_cmodel->arry());



			} elseif($this->is_post()) {

				$this->acl()->writable();

				${$model}_cmodel = CMODEL_{$model_upper}::create()
{if $parent_model}
					->set_{$parent_model}_id(${$parent_model}_id)
{/if}
					->fill($this->posts(),["{'", "'|implode:$accessible_fields}"])
					->validate()
					->save();

				$this->data("{$model}",${$model}_cmodel->arry());



			} elseif($this->is_delete()) {

				${$model}_cmodel = $this->acl()->write(HMODEL_{$model_upper}::create(false){if $parent_model}

														->set_{$parent_model}_id(${$parent_model}_id){/if}

														->set_{$model_id}(${$model_id}));

				${$model}_cmodel->delete();

				$this->data("{$model}",${$model}_cmodel->arry());
			}
		}