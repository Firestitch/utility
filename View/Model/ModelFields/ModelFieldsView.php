<?php

namespace Utility\View\Model\ModelFields;

use Framework\Core\View;
use Utility\Model\DbGeneratorModel;


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

    $list = [];
    if (@class_exists(DbGeneratorModel::getDboClass($namespace, $model))) {
      $columns = DbGeneratorModel::getDbo($namespace, $model)->getColumns();

      foreach ($columns as $columnName => $column) {
        $list[$columnName] = $columnName;
      }
    }

    $this
      ->setVar("list", $list)
      ->setVar("name", $name)
      ->setVar("selected", $selected);
  }
}
