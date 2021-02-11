<?php

namespace Utility\View\MapModel\JoinerFields;

use Framework\Core\View;
use Framework\Db\DB;
use Framework\Util\DEBUG_UTIL;

class JoinerFieldsView extends View {

  protected $_joiner       = "";
  protected $_joiner_source_column  = "";
  protected $_joiner_reference_column   = "";
  protected $_joiner_columns     = array();

  function __construct() {
    $this->disable_authorization();
    $this->set_template("./JoinerFieldsTemplate.php");
  }

  function init() {
    DEBUG_UTIL::enable_format_html();

    $joiner_columns = DB::get_instance()->get_utility()->get_table_field_names($this->post("table"));

    $this->_joiner_columns = array_combine($joiner_columns, $joiner_columns);

    $this->set_var("joiner_list", DB::get_instance()->get_utility()->get_table_names());
    $this->set_var("index", $this->post("index"));
    $this->set_var("joiner_columns", $this->_joiner_columns);
    $this->set_var("joiner_source_column", $this->_joiner_source_column);
    $this->set_var("joiner_reference_column", $this->_joiner_reference_column);
  }
}
