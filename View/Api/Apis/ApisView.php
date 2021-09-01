<?php

namespace Utility\View\Api\Apis;

use Framework\Core\View;
use Framework\Util\FileUtil;
use Utility\Model\ModelGeneratorModel;


class ApisView extends View {

  public function __construct() {
    $this
      ->setTemplate("./ApisTemplate.php")
      ->disableAuthorization();
  }

  public function init() {
    $dir = ModelGeneratorModel::getNamespaceDir($this->request("namespace"));
    $views = FileUtil::getDirectoryListing($dir . "View/api/");
    $apis = [];

    foreach ($views as $view) {
      $name = preg_replace("/View\\.php/", "", $view);
      $apis[$name] = $name;
    }

    $this->setVar("apis", $apis);
  }
}
