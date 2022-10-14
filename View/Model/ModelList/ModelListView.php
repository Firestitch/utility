<?php

namespace Utility\View\Model\ModelList;

use Framework\Core\View;
use Utility\Model\ModelGeneratorModel;


class ModelListView extends View {

  public function __construct() {
    $this->setTemplate("./ModelListTemplate.php");
    $this->disableAuthorization();
  }

  public function init() {
    ;
    $name = $this->request("name");
    $limit = ($limit = $this->request("limit")) ? $limit : 15;
    $dir = ModelGeneratorModel::getNamespaceDir($this->request("namespace"));
    $list = ModelGeneratorModel::getModels($dir);

    $this
      ->setVar("multiple", $this->post("multiple"))
      ->setVar("list", $list)
      ->setVar("limit", $limit)
      ->setVar("name", $name);
  }
}