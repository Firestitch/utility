<?php

namespace Utility\View\Model\ModelFields;

use Framework\Arry\Arry;
use Framework\Core\View;
use Framework\Db\Dbo\Dbo;
use Utility\Model\ModelGeneratorModel;

class ModelFieldsView extends View {

  public function __construct() {
    $this->setTemplate("./ModelFieldsTemplate.php");
    $this->disableAuthorization();
  }

  public function init() {
    $model = $this->request("model");
    $selected = $this->request("selected");
    $name = $this->request("name");
    $namespace = $this->request("namespace");

    $model = ModelGeneratorModel::getModel($namespace, $model);
    $list = Arry::create($model->getDbos())
      ->reduce(function ($list, Dbo $dbo) {
        return array_merge($list, array_keys($dbo->getColumns()));
      }, [])
      ->get();

    $list = array_combine($list, $list);

    $this
      ->setVar("list", $list)
      ->setVar("name", $name)
      ->setVar("selected", $selected);
  }
}