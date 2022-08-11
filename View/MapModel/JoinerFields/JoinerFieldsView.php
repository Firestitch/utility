<?php

namespace Utility\View\MapModel\JoinerFields;

use Framework\Core\View;
use Framework\Db\Db;
use Framework\Util\DebugUtil;


class JoinerFieldsView extends View {

  protected $_joiner = "";
  protected $_joinerSourceColumn = "";
  protected $_joinerReferenceColumn = "";
  protected $_joinerColumns = [];

  public function __construct() {
    $this->disableAuthorization();
    $this->setTemplate("./JoinerFieldsTemplate.php");
  }

  public function init() {
    DebugUtil::enableFormatHtml();
    $joinerColumns = Db::getInstance()
      ->getUtility()
      ->getTableColumnNames($this->post("table"));

    $this->_joinerColumns = array_combine($joinerColumns, $joinerColumns);
    $this->setVar("joinerList", Db::getInstance()
      ->getUtility()
      ->getTableNames());

    $this->setVar("index", $this->post("index"));
    $this->setVar("joinerColumns", $this->_joinerColumns);
    $this->setVar("joinerSourceColumn", $this->_joinerSourceColumn);
    $this->setVar("joinerReferenceColumn", $this->_joinerReferenceColumn);
  }
}
