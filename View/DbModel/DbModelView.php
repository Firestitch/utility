<?php

namespace Utility\View\DbModel;

use Framework\Core\View;
use Framework\Db\Db;


class DbModelView extends View {

  private $_classname = "";
  private $_tablename = "";
  private $_states = "";
  private $_createDbq = false;
  private $_createDbo = true;
  private $_override = true;

  public function __construct() {
    $this
      ->disableAuthorization()
      ->setTemplate("./DbModelTemplate.php")
      ->setStyle("./DbModel.scss");
  }

  public function init() {
    $this->_classname = $this->get("model");
    $this->_tablename = $this->get("table");

    $dbUtility = Db::getInstance()->getUtility();

    $tablenameList = $dbUtility->getTableNames();

    $this->setVar("tablenameList", $tablenameList);
    $this->setVar("classname", $this->_classname);
    $this->setVar("tablename", $this->_tablename);
    $this->setVar("states", $this->_states);
    $this->setVar("createDbq", $this->_createDbq);
    $this->setVar("createDbo", $this->_createDbo);
    $this->setVar("override", $this->_override);
  }
}