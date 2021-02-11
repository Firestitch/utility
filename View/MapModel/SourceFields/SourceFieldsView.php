<?php

namespace Utility\View\MapModel\SourceFields;

use Framework\Core\View;
use Utility\Model\DbGeneratorModel;

class SourceFieldsView extends View {

  protected $_source_columns     = array();

  function __construct() {
    $this->set_template("./SourceFieldsTemplate.php");
    $this->disable_authorization();
  }

  function init() {

    $source_model       = $this->request("source_model");
    $source_model_column   = $this->request("source_model_column");

    if ($source_model)
      $source_model = $source_model;

    if ($source_model && @class_exists(DbGeneratorModel::get_dbo_class($source_model)))
      $this->_source_columns = DbGeneratorModel::get_dbo($source_model)->get_columns();

    $source_model_column_list = array();
    foreach ($this->_source_columns as $name => $column)
      $source_model_column_list[$name] = $name;

    $this->set_var("source_model", $source_model);
    $this->set_var("source_columns", $this->_source_columns);
    $this->set_var("source_model_column_list", $source_model_column_list);
    $this->set_var("source_model_column", $source_model_column);
  }
}
