<?php

namespace Utility\View\ModelInterface;

use Framework\Arry\Arry;
use Framework\Core\View;
use Framework\Core\WebApplication;
use Framework\Db\Db;
use Framework\Util\FileUtil;
use Framework\Util\JsonUtil;


class ModelInterfaceView extends View {

  protected $_referenceModel = "";
  protected $_joiner = "";
  protected $_model = null;
  protected $_sourceModelColumn = "";

  public function __construct() {
    $this
      ->setTemplate("./ModelInterfaceTemplate.php")
      ->setStyle("./ModelInterface.scss")
      ->setForm("javascript:;", false, "form")
      ->disableAuthorization();
  }

  public function init() {
    $joinerList = Db::getInstance()
      ->getUtility()
      ->getTableNames();

    $interfaceDirs = Arry::create(JsonUtil::decode(FileUtil::get(WebApplication::getMainFrontendDirectory() . "angular.json")))
      ->select("projects")
      ->getReduce(function ($accum, $project) {
        $dir = $project["sourceRoot"] . "/app/common";

        return array_merge($accum, [
          $dir => $dir,
        ]);
      }, []);

    $this
      ->setVar("interfaceDirs", $interfaceDirs)
      ->setVar("joiner", $this->_joiner)
      ->setVar("referenceModel", $this->_referenceModel)
      ->setVar("joinerList", $joinerList)
      ->setVar("sourceModelColumn", $this->_sourceModelColumn)
      ->setVar("model", $this->_model);
  }
}
