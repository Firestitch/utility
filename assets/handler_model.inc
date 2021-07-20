<?php
namespace Backend\Handler;

use Framework\Core\Handler;
use Backend\Model\{$pascalModel}Model;
use Backend\Dbq\{$pascalModel}Dbq;

/**
 * @method {$pascalModel}Model get($id = null)
 * @method {$pascalModel}Model require($id = null)
 * @method {$pascalModel}Model[] page($limit = 25)
 */
class {$pascalModel}Handler extends Handler {

  protected $_selectFields = [{$selectFields}];

  public function __construct() {
{if $extends}    parent::__construct({$pascalModel}Dbq::create()->inner("{$extendTablename}","{$extendPrimaryId}"));
{else}    parent::__construct({$pascalModel}Dbq::create());
{/if}
  }

  /**
   * @return {$pascalModel}Model[]
   */
  public function gets() {
    ${$lowerModel|camelize|plural} = $this->getDbq()->select($this->_selectFields);

    $models = [];
    foreach(${$lowerModel|camelize|plural} as ${$lowerModel|camelize}) {
      $model = {$pascalModel}Model::create()->populate(${$lowerModel|camelize},true,false);
      $this->applyProperties($model,${$lowerModel|camelize});
      $models[] = $model;
    }

    if(!$models)
      return [];

    return $models;
  }

{if $hasState}  public function whereStateActive() { return $this->setState({$pascalModel}Model::STATE_ACTIVE); }

{/if}
  public function set{$pascalModel}Ids($values)	{ return $this->where("{$tablename}.{$lowerModel}_id","IN",$values); }

  public function set{$pascalModel}Id($value)	{ return $this->where("{$tablename}.{$lowerModel}_id","=",$value); }

{foreach $fields as $name=>$field}  public function set{$name|pascalize}($value) { return $this->where("{$field}","=",$value); }

  public function set{$name|pascalize}s($values) { return $this->where("{$field}","IN",$values); }

{/foreach}
  public static function create($defaults=false) {
    $hmodel = parent::create();
{if $hasState}    if($defaults)
      $hmodel->where("{$tablename}.state","<>",{$pascalModel}Model::STATE_DELETED);
{/if}
    return $hmodel;
  }
}
