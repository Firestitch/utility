<?php

namespace Utility\View\MapModel\ReferenceFields;

use Framework\Core\View;
use Framework\Util\DEBUG_UTIL;
use Utility\Model\DbGeneratorModel;

class ReferenceFieldsView extends View {

  protected $_reference_model     = "";
  protected $_reference_model_column  = "";
  protected $_reference_columns     = array();

  function __construct() {
    $this->set_template("./ReferenceFieldsTemplate.php");
    $this->disable_authorization();
  }

  function init() {
    DEBUG_UTIL::enable_format_html();

    $reference_model   = $this->post("reference_model");

    if ($reference_model)
      $this->_reference_model = $reference_model;

    if ($this->_reference_model)
      $this->_reference_columns = DbGeneratorModel::get_dbo($this->_reference_model)->get_columns();

    $reference_model_column_list = array();
    foreach ($this->_reference_columns as $name => $column)
      $reference_model_column_list[$name] = $name;

    $this->set_var("reference_model", $this->_reference_model);
    $this->set_var("reference_columns", $this->_reference_columns);
    $this->set_var("reference_model_column_list", $reference_model_column_list);
    $this->set_var("reference_model_column", $this->_reference_model_column);
  }
}
