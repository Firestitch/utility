		public function {$method}($params,$data) {

		${$modelId|camelize} = value($params,"{$modelId}");{if $parentModel}

		${$parentModel|camelize}Id = value($params,"{$parentModel}Id");

		${$parentModel|pascalize}Model = {$pascalParentModel}Handler::create()
		->require(${$parentModel|camelize}Id);
		{/if}{if in_array("get",$methods)}


		if($this->isGet()) {
		${$snakeModel|camelize}Handler = {$pascalModel}Handler::create(false){if $parentModel}

		->set{$parentModel|pascalize}Id(${$parentModel|camelize}Id){/if};

		if(${$modelId|camelize})
		${$snakeModel|camelize}Handler->set{$modelId|pascalize}(${$modelId|camelize});
		{if $keywords}

		{if $keywords}
		if(!is_null($value=$this->get("keyword")))
		${$snakeModel|camelize}Handler->likeKeyword(["{$keywords[0].tablename}.{$keywords[0].name}"],$value);
		{/if}
		{/if}{foreach $dbos as $dbo}{foreach $dbo->getColumns() as $name=>$column}{if in_array($name,$accessibleFields)}{if $name=="state"}

		if(!is_null($value=$this->get("state")))
		${$snakeModel|camelize}Handler->setStates(explode(",",$value));
		elseif(!${$modelId|camelize})
		${$snakeModel|camelize}Handler->setState({$pascalModel}Model::STATE_ACTIVE);
		{elseif preg_match("/(name|description)/",$name)}{continue}{elseif $column->isDataType("date") || $column->isDataType("datetime")}

		if(!is_null($value=$this->get("{$name}")))
		${$snakeModel|camelize}Handler->where("{$dbo->getTablename()}.{$name}",">",TimeModel::create($value)->db());

		if(!is_null($value=$this->get("{$name}")))
		${$snakeModel|camelize}Handler->where("{$dbo->getTablename()}.{$name}","<",TimeModel::create($value)->db());
		  {else}

		  if(!is_null($value=$this->get("{$name}")))
		  ${$snakeModel|camelize}Handler->where("{$dbo->getTablename()}.{$name}","=",$value);
		  {/if}{/if}{/foreach}{/foreach}
		  {foreach $loads as $load} if(!is_null($this->get("{$load}")))
		  ${$snakeModel|camelize}Handler->load{$load|pascalize}();

		  {/foreach}

		  $this->acl()->apply(${$snakeModel|camelize}Handler);

		  if(!${$modelId|camelize})
		  $this->handler(${$snakeModel|camelize}Handler{if $orderBy},"{$orderBy}"{/if});

		  ${$snakeModel|camelize}Arry = Arry::create(${$snakeModel|camelize}Handler->gets())
		  ->map("arry");

		  if (${$snakeModel|camelize}Id)
		  $this->requireData("{$snakeModel}", ${$snakeModel|camelize}Arry->first());
		  else
		  $this->data("{$pluralSnakeModel|camelize}", ${$snakeModel|camelize}Arry->values());

		  }{/if}{if in_array("post",$methods) || in_array("put",$methods) || in_array("delete",$methods)} else {

		  ${$snakeModel|camelize}Model = {$pascalModel}Model::create();

		  if($this->isPut() || $this->isDelete()) {
		  /**
		  * @var {$pascalModel}Model
		  */
		  ${$snakeModel|camelize}Model = $this->acl()->write({$pascalModel}Handler::create(false){if $parentModel}

		  ->set{$parentModel|pascalize}Id(${$parentModel|camelize}id){/if}

		  ->set{$modelId|pascalize}(${$modelId|camelize}));
		  }

		  {if $parentModel}
		  if($this->isPost()) {
		  ${$snakeModel|camelize}Model->set{$parentModel|pascalize}Id(${$parentModel|pascalize}Model->get{$parentModel|pascalize}Id());
		  }
		  {/if}
		  //${$snakeModel|camelize}Model->startAudit($this->getSession()->getAccountId());

		  if($this->isPost() || $this->isPut()) {

		  ${$snakeModel|camelize}Model
		  ->fill($this->posts(),["{'", "'|implode:$fillFields}"])
		  ->validate()
		  ->save();

		  $this->data("{$snakeModel}",${$snakeModel|camelize}Model->arry());

		  }{/if}{if in_array("delete",$methods)} elseif($this->isDelete()) {

		  ${$snakeModel|camelize}Model->delete();

		  $this->data("{$snakeModel}",${$snakeModel|camelize}Model->arry());
		  }
		  {/if}
		  //${$snakeModel|camelize}Model->endAudit();
		  }
		  }
		  {if in_array("order",$options)}

		  public function order() {

		  if($this->isPut()) {

		  $handler = $this->acl()->apply({$pascalModel}Handler::create(false));
		  $ids = is_array($this->post("{$modelId}s")) ? $this->post("{$modelId}s") : explode(",",$this->post("{$modelId}s"));

		  OrderingModel::create($handler,"{$modelId}")
		  ->reorder($ids,$this->post("page") * $this->post("limit"));
		  }
		  }
		  {/if}
