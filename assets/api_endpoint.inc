		public function {$method}($params,$data) {

		${$model_id|camelize} = value($params,"{$model_id}");{if $parent_model}

		${$parent_model|camelize}Id = value($params,"{$parent_model}_id");

		${$parent_model|pascalize}Model = {$pascal_parent_model}Handler::create()
		->require(${$parent_model|camelize}Id);
		{/if}{if in_array("get",$methods)}
		if($this->is_get()) {

		${$snake_model|camelize}Handler = {$pascal_model}Handler::create(false){if $parent_model}

		->set{$parent_model|pascalize}Id(${$parent_model|camelize}Id){/if};

		if(${$model_id|camelize})
		${$snake_model|camelize}Handler->set{$model_id|pascalize}(${$model_id|camelize});
		{if $keywords}

		{if $keywords}
		if(!is_null($value=$this->get("keyword")))
		${$snake_model|camelize}Handler->likeKeyword(["{$keywords[0].tablename}.{$keywords[0].name}"],$value);
		{/if}

		{/if}{foreach $dbos as $dbo}{foreach $dbo->getColumns() as $name=>$column}{if in_array($name,$accessible_fields)}{if $name=="state"}

		if(!is_null($value=$this->get("state")))
		${$snake_model|camelize}Handler->setStates(explode(",",$value));
		elseif(!${$model_id|camelize})
		${$snake_model|camelize}Handler->set_state({$pascal_model}Model::STATE_ACTIVE);
		{elseif preg_match("/(name|description)/",$name)}{continue}{elseif $column->is_data_type("date") || $column->is_data_type("datetime")}

		if(!is_null($value=$this->get("{$name}")))
		${$snake_model|camelize}Handler->where("{$dbo->get_tablename()}.{$name}",">",TimeModel::create($value)->db());

		if(!is_null($value=$this->get("{$name}")))
		${$snake_model|camelize}Handler->where("{$dbo->get_tablename()}.{$name}","<",TimeModel::create($value)->db());
		  {else}

		  if(!is_null($value=$this->get("{$name}")))
		  ${$snake_model|camelize}Handler->where("{$dbo->get_tablename()}.{$name}","=",$value);
		  {/if}{/if}{/foreach}{/foreach}
		  {foreach $loads as $load} if(!is_null($this->get("{$load}")))
		  ${$snake_model|camelize}Handler->load{$load|pascalize}();

		  {/foreach}
		  $this->acl()->apply(${$snake_model|camelize}Handler);

		  if(!${$model_id|camelize})
		  $this->handler(${$snake_model|camelize}Handler{if $order_by},"{$order_by}"{/if});

		  ${$plural_snake_model|camelize} = [];
		  foreach(${$snake_model|camelize}Handler->gets() as ${$snake_model|camelize}_model)
		  ${$plural_snake_model|camelize}[] = ${$snake_model|camelize}_model->arry();

		  if(${$model_id|camelize})
		  $this->requireData("{$snake_model}",array_shift(${$plural_snake_model|camelize}));
		  else
		  $this->data("{$plural_snake_model|camelize}",array_values(${$plural_snake_model|camelize}));

		  }{/if}{if in_array("post",$methods) || in_array("put",$methods) || in_array("delete",$methods)} else {

		  ${$snake_model|camelize}_model = {$pascal_model}Model::create();

		  if($this->isPut() || $this->isDelete()) {
		  ${$snake_model|camelize}_model = $this->acl()->write({$pascal_model}Handler::create(false){if $parent_model}

		  ->set{$parent_model|pascalize}_id(${$parent_model|camelize}id){/if}

		  ->set{$model_id|pascalize}(${$model_id|camelize}));
		  }

		  {if $parent_model}
		  if($this->isPost()) {
		  ${$snake_model|camelize}_model->set{$parent_model|pascalize}Id(${$parent_model|pascalize}Model->get{$parent_model|pascalize}Id());
		  }
		  {/if}
		  //${$snake_model|camelize}_model->startAudit($this->getSession()->getAccountId());

		  if($this->isPost() || $this->isPut()) {

		  ${$snake_model|camelize}_model
		  ->fill($this->posts(),["{'", "'|implode:$accessible_fields}"])
		  ->validate()
		  ->save();

		  $this->data("{$snake_model}",${$snake_model|camelize}_model->arry());

		  }{/if}{if in_array("delete",$methods)} elseif($this->isDelete()) {

		  ${$snake_model|camelize}_model->delete();

		  $this->data("{$snake_model}",${$snake_model|camelize}_model->arry());
		  }
		  {/if}
		  //${$snake_model|camelize}_model->end_audit();
		  }
		  }
		  {if in_array("order",$options)}

		  public function order() {

		  if($this->isPut()) {

		  $handler = $this->acl()->apply({$pascal_model}Handler::create(false));
		  $ids = is_array($this->post("{$model_id}s")) ? $this->post("{$model_id}s") : explode(",",$this->post("{$model_id}s"));

		  OrderingModel::create($handler,"{$model_id}")
		  ->reorder($ids,$this->post("page") * $this->post("limit"));
		  }
		  }
		  {/if}
