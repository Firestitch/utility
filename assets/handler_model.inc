<?php
namespace Backend\Handler;

use Framework\Core\Handler;
use Backend\Model\{$pascal_model}Model;
use Backend\Dbq\{$pascal_model}Dbq;

/**
 * @method {$pascal_model}Model get()
 * @method {$pascal_model}Model require()
 * @method {$pascal_model}Model[] page()
 */
class {$pascal_model}Handler extends Handler {

  protected $_selectFields = [{$select_fields}];

  public function __construct() {
{if $extends}    parent::__construct({$pascal_model}Dbq::create()->inner("{$extend_tablename}","{$extend_primary_id}"));
{else}    parent::__construct({$pascal_model}Dbq::create());
{/if}
  }

  /**
   * @return {$pascal_model}Model[]
   */
  public function gets() {
    ${$pascalModels} = $this->getDbq()->select($this->_select_fields);

    $models = [];
    foreach(${$pascalModels} as ${$pascalModel}) {
      $model = {$pascal_model}Model::create()->populate(${$pascalModel},true,false);
      $this->applyProperties($model,${$pascalModel});
      $models[] = $model;
    }

    if(!$models)
      return [];

    return $models;
  }

{if $has_state}  public function whereStateActive() { return $this->setState({$pascal_model}Model::STATE_ACTIVE); }
{/if}
  public function set{$pascalModel}_ids($values)	{ return $this->where("{$tablename}.{$pascalModel}_id","IN",$values); }
  public function set{$pascalModel}_id($value)	{ return $this->where("{$tablename}.{$pascalModel}_id","=",$value); }
{foreach $fields as $name=>$field}  public function set{$name}($value) { return $this->where("{$field}","=",$value); }
  public function set{$name}s($values) { return $this->where("{$field}","IN",$values); }
{/foreach}

  public static function create($defaults=false) {
    $hmodel = parent::create();
{if $has_state}    if($defaults)
      $hmodel->where("{$tablename}.state","<>",{$pascal_model}Model::STATE_DELETED);
{/if}
    return $hmodel;
  }

  public function acl() {
    return [];
  }
}
