<?php
namespace {$namespace}\Handler;

use Framework\Core\Handler;
use {$namespace}\Model\{$pascalName}Model;
use {$namespace}\Dbq\{$pascalName}Dbq;


/**
 * @method ?{$pascalName}Model get($id = null)
 * @method {$pascalName}Model require($id = null)
 * @method {$pascalName}Model[] gets()
 * @method {$pascalName}Model[] page($limit = 25)
 * @method {$pascalName}Model[] chunk($primaryColumn, $primaryFunc = null, $limit = 25)
 */
class {$pascalName}Handler extends Handler {

  protected $_selectFields = [{$selectFields}];

  public function __construct() {
    parent::__construct({$pascalName}Dbq::create());
  }

  public function gets() {
    ${$lowerModel|camelize|plural} = $this->getDbq()->select($this->_selectFields);

    $models = [];
    foreach(${$lowerModel|camelize|plural} as ${$lowerModel|camelize}) {
      $model = {$pascalName}Model::create()->populate(${$lowerModel|camelize},true);
      $this->applyProperties($model,${$lowerModel|camelize});
      $models[] = $model;
    }

    if(!$models)
      return [];

    $this->mapModels($models);

    return $models;
  }
  
  public function mapModels($models) {
    return $models;
  }
  
{foreach $fields as $name=>$field}  public function set{$name|pascalize}($value) { return $this->where("{$field}","=",$value); }

  public function set{$name|pascalize}s($values) { return $this->where("{$field}","IN",$values); }

{/foreach}
  /**
   * @return static
   */
  public static function create($defaults=false) {
    $hmodel = parent::create();
{if $hasState}    if($defaults)
      $hmodel->where("{$tablename}.state","<>",{$pascalName}Model::STATE_DELETED);
{/if}
    return $hmodel;
  }

  /*
  public function acl($options = []) {
    return [
      AclModel::LEVEL_PROJECT => [
        "wheres" => [
          ["orders.project_id", "IN", value($options, "projectIds")]
        ]
      ],
      AclModel::LEVEL_ENVIRONMENT => [
        "joins" => [
          ["orders", "projects", "project_id", "project_id"]
        ],
        "wheres" => [
          ["projects.environment_id", "IN", value($options, "environmentIds")]
        ]
      ]
    ];
  }
  */
}
