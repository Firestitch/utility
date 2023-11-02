<?php

namespace Utility\View\ModelInterface;

use Framework\Core\View;
use Framework\Db\Db;


class ModelInterfaceView extends View {

  protected $_referenceModel = "";
  protected $_joiner = "";
  protected $_model = null;
  protected $_sourceModelColumn = "";

  public function __construct() {
    $this
      ->setTemplate("./ModelInterfaceTemplate.php")
      ->setStyle("./ModelInterface.scss")
      ->setForm("javascript:;", false, "form-relation")
      ->disableAuthorization();
  }

  public function init() {
    $joinerList = Db::getInstance()
      ->getUtility()
      ->getTableNames();

    $this->setVar("joiner", $this->_joiner);
    $this->setVar("referenceModel", $this->_referenceModel);
    $this->setVar("joinerList", $joinerList);
    $this->setVar("sourceModelColumn", $this->_sourceModelColumn);
    $this->setVar("model", $this->_model);
  }
}
