<?php

namespace Utility\View\Model\ModelFields;

use Framework\Arry\Arry;
use Framework\Core\View;
use Framework\Db\Dbo\Dbo;
use Utility\Model\DbGeneratorModel;
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
        return Arry::create($dbo->getColumns())
          ->concat($list)
          ->get();
      }, [])
      ->map(function ($column, $name) {
        return $name;
      })
      ->get();

    $this
      ->setVar("list", $list)
      ->setVar("name", $name)
      ->setVar("selected", $selected);
  }
}