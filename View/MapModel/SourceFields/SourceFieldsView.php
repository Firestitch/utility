<?php

namespace Utility\View\MapModel\SourceFields;

use Framework\Core\View;
use Utility\Model\DbGeneratorModel;


class SourceFieldsView extends View {

  protected $_sourceColumns = [];

  public function __construct() {
    $this->setTemplate("./SourceFieldsTemplate.php");
    $this->disableAuthorization();
  }

  public function init() {
    $sourceModel = $this->request("source_model");
    $sourceModelColumn = $this->request("source_model_column");
    if ($sourceModel && @class_exists(DbGeneratorModel::getDboClass($sourceModel))) {
      $this->_sourceColumns = DbGeneratorModel::getDbo($sourceModel)
        ->getColumns();
    }
    $sourceModelColumnList = [];
    foreach ($this->_sourceColumns as $name => $column) {
      $sourceModelColumnList[$name] = $name;
    }
    $this->setVar("sourceModel", $sourceModel);
    $this->setVar("sourceColumns", $this->_sourceColumns);
    $this->setVar("sourceModelColumnList", $sourceModelColumnList);
    $this->setVar("sourceModelColumn", $sourceModelColumn);
  }

}
