<?php

namespace Utility\Model;

use Framework\Core\WebApplication;
use Framework\Db\Dbq\Dbq;
use Framework\Model\SmartyModel;
use Framework\Util\FILE_UTIL;
use Framework\Util\LANG_UTIL;
use Framework\Util\STRING_UTIL;
use ReflectionClass;

class ModelGeneratorModel {

  protected $_app_dir     = null;
  protected $_lower_model   = null;
  protected $_upper_model   = null;
  protected $_pascal_model   = null;
  protected $_tablename     = null;
  protected $_framework     = null;
  protected $_smarty       = null;
  protected $_extends       = null;

  function __construct($model, $app_dir = null, $framework = false, $extends = "", $options = []) {
    $this->_extends     = strtoupper($extends);
    $this->_lower_model   = strtolower($model);
    $this->_upper_model   = strtoupper($model);
    $this->_pascal_model   = STRING_UTIL::pascalize($this->_lower_model);
    $this->_framework     = $framework;
    $this->_app_dir      = $app_dir ? $app_dir : WebApplication::get_main_application_directory();

    $this->_smarty = new SmartyModel();
    $this->_smarty->disableSecurity();
    $this->_smarty->assign("primary_object_id", value($options, "primary_object_id"));
    $this->_smarty->assign("upper_model", $this->_upper_model);
    $this->_smarty->assign("pascal_model", $this->_pascal_model);
    $this->_smarty->assign("lower_model", $this->_lower_model);
    $this->_smarty->assign("lower_models", LANG_UTIL::get_plural_string($this->_lower_model));
  }

  function init() {

    $dbo = self::get_dbo($this->_lower_model);

    $this->_smarty->assign("columns", $dbo->get_columns());
  }

  function generate_complex_model() {

    $this->init();

    $dbo = DbGeneratorModel::get_dbo($this->_lower_model);

    $columns = array();
    foreach ($dbo->get_columns() as $name => $column)
      $columns[$name] = $column;

    $extend_id = "";

    if ($this->_extends) {
      $extend_class = "DBO_" . strtoupper($this->_extends);
      $extend_dbo = $extend_class::create();
      $extend_id = current(array_keys($extend_dbo->get_primary_keys()));
    }

    $dbq = DbGeneratorModel::get_dbq($this->_lower_model);

    $refl = new ReflectionClass($dbq);
    $object_consts = array_keys($refl->getConstants());

    $refl = new ReflectionClass(Dbq::class);
    $dbq_consts = array_keys($refl->getConstants());

    $diff_consts = array_diff($object_consts, $dbq_consts);

    $consts = array();
    foreach ($diff_consts as $const) {
      $field = strtolower(get_value(explode("_", $const), 0));
      $consts[] = array("const" => $const, "field" => $field);
    }

    $dbo = self::get_dbo($this->_lower_model);

    $extends = "CMODEL";

    if ($this->_extends)
      $extends .= "_" . $this->_extends;

    $this->_smarty->assign("extends", $extends);
    $this->_smarty->assign("extended", !!$this->_extends);
    $this->_smarty->assign("extend_id", $extend_id);
    $this->_smarty->assign("has_multiple_keys", count($dbo->get_primary_keys()) > 1);
    $this->_smarty->assign("keys", array_keys($dbo->get_primary_keys()));
    $this->_smarty->assign("primary_key", value(array_keys($dbo->get_primary_keys()), 0));
    $this->_smarty->assign("framework", $this->_framework);
    $this->_smarty->assign("has_guid", array_key_exists("guid", $columns));
    $this->_smarty->assign("has_state", array_key_exists("state", $columns));
    $this->_smarty->assign("has_create_date", array_key_exists("create_date", $columns));
    $this->_smarty->assign("has_object_id", array_key_exists("object_id", $columns));
    $this->_smarty->assign("consts", $consts);
    $this->_smarty->assign("id", self::get_abr($this->_lower_model) . "id");
    $this->_smarty->allow_php_tag();

    return $this->generate_model("model");
  }

  static function get_abr($field) {
    $parts = array();
    foreach (explode("_", $field) as $part)
      $parts[] = $part == "id" ? "id" : substr($part, 0, 1);

    return implode("", $parts);
  }

  static function get_model_class($basename) {
    return STRING_UTIL::pascalize($basename) . "Model";
  }

  static function get_handler_class($basename) {
    return STRING_UTIL::pascalize($basename) . "Handler";
  }

  static function get_model($basename) {
    $class = "Backend\Model\\" . self::get_model_class($basename);
    return new $class();
  }

  function generate_handler_model() {

    $this->init();

    $cmodel = self::get_model($this->_lower_model);
    $dbos   = array_values($cmodel::create()->get_dbos());

    $extend_primary_id = null;
    $fields = array();
    foreach ($dbos as $index => $dbo) {

      $tablenames[] = $dbo->get_tablename();

      if (!$index) {
        $extend_primary_id = value(array_keys($dbo->get_primary_keys()), 0);
      }

      foreach ($dbo->get_columns() as $name => $column)
        if (!$column->is_primary() && preg_match("/(^state$|_id$|guid)/", $name))
          $fields[$name] = $dbo->get_tablename() . "." . $name;
    }

    $this->_smarty->assign("select_fields", '"' . implode('.*","', $tablenames) . '.*"');
    $this->_smarty->assign("extends", $this->_extends);
    $this->_smarty->assign("extend_primary_id", $extend_primary_id);
    $this->_smarty->assign("extend_tablename", value($tablenames, 0));
    $this->_smarty->assign("tablename", value($tablenames, count($tablenames) - 1));
    $this->_smarty->assign("fields", $fields);
    $this->_smarty->assign("has_state", array_key_exists("state", $fields));
    $this->_smarty->assign("framework", $this->_framework);

    return $this->generate_model("handler");
  }

  function get_dbo($model) {
    return DbGeneratorModel::get_dbo($model);
  }

  function generate_model($model_type) {

    $template_file = PathModel::get_assets_directory() . $model_type . "_model.inc";

    $content = $this->_smarty->fetch($template_file);

    return $this->write_file($this->get_model_file($model_type), $content);
  }

  function get_model_file($model_type) {
    return FILE_UTIL::sanitize_file($this->_app_dir . STRING_UTIL::pascalize($model_type) . "/" . STRING_UTIL::pascalize($this->_lower_model) . STRING_UTIL::pascalize($model_type) . ".php");
  }

  function get_complex_model_file() {
    return $this->get_model_file("Model");
  }

  function get_handler_model_file() {
    return $this->get_model_file("Handler");
  }

  function get_model_directory($model_type) {
    return $this->_app_dir . "Model";
  }

  function write_file($file, $string) {

    FILE_UTIL::put($file, $string);
    WebApplication::add_notify('Successfully added the file <a href="' . $file . '">' . $file . '</a>');

    return true;
  }

  static function get_cmodels() {
    $files = FILE_UTIL::get_directory_listing(WebApplication::get_main_application_directory() . "Model");

    $cmodels = ["" => ""];
    foreach ($files as $file) {
      if (preg_match("/(.*)Model\.php/", $file, $matches)) {
        $cmodels[$matches[1]] = $matches[1];
      }
    }

    return $cmodels;
  }
}
