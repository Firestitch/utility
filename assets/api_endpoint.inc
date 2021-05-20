		public function {$method}($params,$data) {

		${$model_id} = value($params,"{$model_id}");{if $parent_model}

		${$parent_model}_id = value($params,"{$parent_model}_id");

		${$parent_model}_model = {$pascal_parent_model}Handler::create()
		->require(${$parent_model}_id);
		{/if}{if in_array("get",$methods)}
		if($this->is_get()) {

		${$snake_model}_handler = {$pascal_model}Handler::create(false){if $parent_model}

		->set_{$parent_model}_id(${$parent_model}_id){/if};

		if(${$model_id})
		${$snake_model}_handler->set_{$model_id}(${$model_id});
		{if $keywords}

		{if $keywords}
      if(!is_null($value=$this->get("keyword")))
		  ${$snake_model}_handler->like_keyword(["{$keywords[0].tablename}.{$keywords[0].name}"],$value);
    {/if}
		
		{/if}{foreach $dbos as $dbo}{foreach $dbo->get_columns() as $name=>$column}{if in_array($name,$accessible_fields)}{if $name=="state"}

		if(!is_null($value=$this->get("state")))
		${$snake_model}_handler->set_states(explode(",",$value));
		elseif(!${$model_id})
		${$snake_model}_handler->set_state({$pascal_model}Model::STATE_ACTIVE);
		{elseif preg_match("/(name|description)/",$name)}{continue}{elseif $column->is_data_type("date") || $column->is_data_type("datetime")}

		if(!is_null($value=$this->get("{$name}")))
		${$snake_model}_handler->where("{$dbo->get_tablename()}.{$name}",">",TimeModel::create($value)->db());

		if(!is_null($value=$this->get("{$name}")))
		${$snake_model}_handler->where("{$dbo->get_tablename()}.{$name}","<",TimeModel::create($value)->db());
		  {else}

		  if(!is_null($value=$this->get("{$name}")))
		  ${$snake_model}_handler->where("{$dbo->get_tablename()}.{$name}","=",$value);
		  {/if}{/if}{/foreach}{/foreach}
		  {foreach $loads as $load} if(!is_null($this->get("{$load}")))
		  ${$snake_model}_handler->load_{$load}();

		  {/foreach}
		  $this->acl()->apply(${$snake_model}_handler);

		  if(!${$model_id})
		  $this->handler(${$snake_model}_handler{if $order_by},"{$order_by}"{/if});

		  ${$plural_snake_model} = [];
		  foreach(${$snake_model}_handler->gets() as ${$snake_model}_model)
		  ${$plural_snake_model}[] = ${$snake_model}_model->arry();

		  if(${$model_id})
		  $this->require_data("{$snake_model}",array_shift(${$plural_snake_model}));
		  else
		  $this->data("{$plural_snake_model}",array_values(${$plural_snake_model}));

		  }{/if}{if in_array("post",$methods) || in_array("put",$methods) || in_array("delete",$methods)} else {

		  ${$snake_model}_model = {$pascal_model}Model::create();

		  if($this->is_put() || $this->is_delete()) {
		  ${$snake_model}_model = $this->acl()->write({$pascal_model}Handler::create(false){if $parent_model}

		  ->set_{$parent_model}_id(${$parent_model}_id){/if}

		  ->set_{$model_id}(${$model_id}));
		  }

		  {if $parent_model}
      if($this->is_post()) {
		  ${$snake_model}_model->set_{$parent_model}_id(${$parent_model}_model->get_{$parent_model}_id());		  
		  }
      {/if}
		  //${$snake_model}_model->start_audit($this->get_session()->get_account_id());

		  if($this->is_post() || $this->is_put()) {

		  ${$snake_model}_model
		  ->fill($this->posts(),["{'", "'|implode:$accessible_fields}"])
		  ->validate()
		  ->save();

		  $this->data("{$snake_model}",${$snake_model}_model->arry());

		  }{/if}{if in_array("delete",$methods)} elseif($this->is_delete()) {

		  ${$snake_model}_model->delete();

		  $this->data("{$snake_model}",${$snake_model}_model->arry());
		  }
		  {/if}
		  //${$snake_model}_model->end_audit();
		  }
		  }
		  {if in_array("order",$options)}

		  public function order() {

		  if($this->is_put()) {

		  $handler = $this->acl()->apply({$pascal_model}Handler::create(false));
		  $ids = is_array($this->post("{$model_id}s")) ? $this->post("{$model_id}s") : explode(",",$this->post("{$model_id}s"));

		  OrderingModel::create($handler,"{$model_id}")
		  ->reorder($ids,$this->post("page") * $this->post("limit"));
		  }
		  }
		  {/if}
