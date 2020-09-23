<?

namespace Utility\Model;

use Exception;
use Framework\Core\WebApplication;
use Framework\Db\DB;
use Framework\Util\FILE_UTIL;
use Framework\Util\STRING_UTIL;

class DbGeneratorModel {

  protected $_app_dir = null;

  function __construct($app_dir = null) {
    $this->_app_dir  = $app_dir ? $app_dir : WebApplication::get_main_application_directory();
    $this->_db_utility = DB::get_instance()->get_utility();
  }

  function create_dbo($tablename, $name, $override = false) {

    $classname = basename(self::get_dbo_class(strtolower($name)));
    $dbo_file = self::get_dbo_file($classname, $this->_app_dir);

    $has_success = false;

    if (!is_file($dbo_file) || $override) {

      $result = $this->_db_utility->get_table_fields($tablename);

      $columns = array();
      foreach ($result as $row) {
        $type     = str_replace("unsigned", "", strtolower($row["Type"]));

        $data_type   = preg_replace("/[^a-z]+/", "$1", $type);
        $size     = preg_replace("/[a-z]+\((\d+)\)/", "$1", $type);

        $size    = is_numeric($size) ? $size : "null";


        $not_null  = $row["Null"] == "YES" ? "true" : "false";
        $primary  = $row["Key"] == "PRI" ? "true" : "false";

        $name     = $row["Field"];

        $columns[] = "\$this->_columns[\"" . $name . "\"] = new Column(\"" . $data_type . "\"," . $size . "," . $not_null . "," . $primary . ");";
      }

      $str =   "<?php

namespace Backend\Dbo;

use Framework\Db\Dbo\Dbo;
use Framework\Db\Dbo\Column;

class " . $classname . " extends Dbo {\n\n";

      $str .= "  function __construct() {\n" .
        "    \$this->_tablename = \"" . $tablename . "\";\n";

      foreach ($columns as $column)
        $str .= "    " . $column . "\n";

      $str .=  "  }\n\n";

      foreach ($result as $row) {
        $str .= '  public function set_' . $row["Field"] . '($value) {
    return $this->set_column_value("' . $row["Field"] . '", $value);
  }' . "\n\n";
        $str .= '  public function get_' . $row["Field"] . '() {
    return $this->get_column_value("' . $row["Field"] . '");
  }' . "\n\n";
      }

      $str .= "}";

      $has_success = $this->write_file($dbo_file, $str);
    } else
      WebApplication::instance()->add_warning("The class `" . $classname . "` already exists");

    return $has_success;
  }

  function create_dbq($tablename, $name, $override = false) {

    $classname = basename(self::get_dbq_class(strtolower($name)));

    $dbq_file = self::get_dbq_file($classname, $this->_app_dir);

    $has_success = false;

    if (!is_file($dbq_file) || $override) {

      $str = "<?php

namespace Backend\Dbq;

use Framework\Db\Dbq\Dbq;

class " . $classname . " extends Dbq {\n\n";

      $fields = $this->_db_utility->get_table_fields($tablename);

      $primary_keys = array();

      foreach ($fields as $field) {

        $name = value($field, "Field");

        if ($name == "state")
          continue;

        if (get_value($field, "Key") == "PRI")
          $primary_keys[] = '"' . $name . '"';
      }

      $primary_key = count($primary_keys) == 1 ? implode(",", $primary_keys) :  "array(" . implode(",", $primary_keys) . ")";

      $str .= "  public function __construct() {\n" .
        "    parent::__construct(\"" . $tablename . "\", " . $primary_key . ");\n" .
        "  }\n" .
        "}";

      $has_success = $this->write_file($dbq_file, $str);
    } else
      WebApplication::instance()->add_warning("The DBQ class `" . $classname . "` already exists");

    return $has_success;
  }

  function get_key_count($tablename) {
    $fields = $this->_db_utility->get_table_fields($tablename);

    $count = 0;
    foreach ($fields as $field)
      if (get_value($field, "Key") == "PRI")
        $count++;
    return $count;
  }

  function write_file($file, $string) {

    $error_message = "";
    $has_success = FILE_UTIL::put_file_contents($file, $string, $error_message);

    if (!$has_success)
      throw new Exception($error_message);

    return $has_success;
  }

  static function get_dbo_file($classname, $app_dir) {
    return FILE_UTIL::sanitize_file($app_dir . "Dbo/" . $classname . ".php");
  }

  static function get_dbq_file($classname, $app_dir) {
    return FILE_UTIL::sanitize_file($app_dir . "Dbq/" . $classname . ".php");
  }

  static function get_dbq_tablename($basename, $app_dir) {
    $dbq = self::get_dbq($basename, $app_dir);
    return $dbq ? $dbq->get_tablename() : "";
  }

  static function get_dbq_class($basename) {
    return "Backend\Dbq\\" . STRING_UTIL::pascalize($basename) . "Dbq";
  }

  static function get_dbo_class($basename) {
    return "Backend\Dbo\\" . STRING_UTIL::pascalize($basename) . "Dbo";
  }

  static function get_dbo($basename) {
    $class = self::get_dbo_class($basename);
    return new $class();
  }

  static function get_dbq($basename) {
    $class = self::get_dbq_class($basename);
    return new $class();
  }
}
