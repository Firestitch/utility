<?
	class VIEW_API_{$model_plural_upper_trim} extends VIEW_API_BASE {

		public function {$model_plural_trim}(${$model_id}=null,$action=null) {

			if($this->is_get()) {

				${$model}_hmodel = HMODEL_{$model_upper}::create(false);

				if(${$model_id})
					${$model}_hmodel->set_{$model_id}(${$model_id});
{foreach $dbos as $dbo}{foreach $dbo->get_columns() as $name=>$column}{if in_array($name,$accessible_fields)}{if $name=="state"}

				if(!is_null($value=$this->get("state")))
					${$model}_hmodel->set_states(explode(",",$value));
{elseif preg_match("/name/",$name)}

				if(!is_null($value=$this->get("{$name}")))
					${$model}_hmodel->group(DB_GROUP::create()
												->where("{$dbo->get_tablename()}.{$name}","LIKE","%".$value."%","OR"));
{elseif $column->is_data_type("date") || $column->is_data_type("datetime")}

				if(!is_null($value=$this->get("{$name}")))
					${$model}_hmodel->where("{$dbo->get_tablename()}.{$name}",">",CMODEL_TIME::create($value)->db());

				if(!is_null($value=$this->get("{$name}")))
					${$model}_hmodel->where("{$dbo->get_tablename()}.{$name}","<",CMODEL_TIME::create($value)->db());
{else}

				if(!is_null($value=$this->get("{$name}")))
					${$model}_hmodel->where("{$dbo->get_tablename()}.{$name}","=",$value);
{/if}{/if}{/foreach}{/foreach}

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

				${$model}_cmodel = HMODEL_{$model_upper}::create()
										->get(${$model_id});

				if(!${$model}_cmodel)
					throw new Exception("Failed to load the {$model}");

				${$model}_cmodel
					->fill($this->posts(),["{'", "'|implode:$accessible_fields}"])
					->validate()
					->save();

				$this->data("{$model}",${$model}_cmodel->arry());



			} elseif($this->is_post()) {

				${$model}_cmodel = CMODEL_{$model_upper}::create();

				${$model}_cmodel
					->fill($this->posts(),["{'", "'|implode:$accessible_fields}"])
					->validate()
					->save();

				$this->data("{$model}",${$model}_cmodel->arry());



			} elseif($this->is_delete()) {

				${$model}_cmodel = HMODEL_{$model_upper}::create()
										->get(${$model_id});

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
														"parms"=>[],
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