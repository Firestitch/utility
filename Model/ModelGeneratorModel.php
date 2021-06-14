<?php

namespace Utility\Model;

use Framework\Core\WebApplication;
use Framework\Db\Dbq\Dbq;
use Framework\Model\SmartyModel;
use Framework\Util\FileUtil;
use Framework\Util\LangUtil;
use Framework\Util\StringUtil;
use ReflectionClass;

class ModelGeneratorModel {
  protected $_appDir = null;
  protected $_lowerModel = null;
  protected $_upperModel = null;
  protected $_pascalModel = null;
  protected $_tablename = null;
  protected $_framework = null;
  protected $_smarty = null;
  protected $_extends = null;
  function __construct($model, $appDir = null, $framework = false, $extends = "", $options = []) {
    $this->_extends = strtoupper($extends);
    $this->_lowerModel = strtolower($model);
    $this->_upperModel = strtoupper($model);
    $this->_pascalModel = StringUtil::pascalize($this->_lowerModel);
    $this->_framework = $framework;
    $this->_appDir = $appDir ? $appDir : WebApplication::getMainApplicationDirectory();
    $this->_smarty = new SmartyModel();
    $this->_smarty->disableSecurity();
    $this->_smarty->assign("primary_object_id", value($options, "primary_object_id"));
    $this->_smarty->assign("upper_model", $this->_upperModel);
    $this->_smarty->assign("pascal_model", $this->_pascalModel);
    $this->_smarty->assign("lower_model", $this->_lowerModel);
    $this->_smarty->assign("lower_models", LangUtil::getPluralString($this->_lowerModel));
  }
  function init() {
    $dbo = $this->getDbo($this->_lowerModel);
    $this->_smarty->assign("columns", $dbo->getColumns());
  }
  function generateComplexModel() {
    $this->init();
    $dbo = DbGeneratorModel::getDbo($this->_lowerModel);
    $columns = array();
    foreach ($dbo->getColumns() as $name => $column) {
      $columns[$name] = $column;
    }
    $extendId = "";
    if ($this->_extends) {
      $extendClass = "DBO_" . strtoupper($this->_extends);
      $extendDbo = $extendClass::create();
      $extendId = current(array_keys($extendDbo->getPrimaryKeys()));
    }
    $dbq = DbGeneratorModel::getDbq($this->_lowerModel);
    $refl = new ReflectionClass($dbq);
    $objectConsts = array_keys($refl->getConstants());
    $refl = new ReflectionClass(dbq::class);
    $dbqConsts = array_keys($refl->getConstants());
    $diffConsts = array_diff($objectConsts, $dbqConsts);
    $consts = array();
    foreach ($diffConsts as $const) {
      $field = strtolower(get_value(explode("_", $const), 0));
      $consts[] = array("const" => $const, "field" => $field);
    }
    $dbo = $this->getDbo($this->_lowerModel);
    $extends = "CMODEL";
    if ($this->_extends) {
      $extends .= "_" . $this->_extends;
    }
    $this->_smarty->assign("extends", $extends);
    $this->_smarty->assign("extended", !!$this->_extends);
    $this->_smarty->assign("extend_id", $extendId);
    $this->_smarty->assign("has_multiple_keys", count($dbo->getPrimaryKeys()) > 1);
    $this->_smarty->assign("keys", array_keys($dbo->getPrimaryKeys()));
    $this->_smarty->assign("primary_key", value(array_keys($dbo->getPrimaryKeys()), 0));
    $this->_smarty->assign("framework", $this->_framework);
    $this->_smarty->assign("has_guid", array_key_exists("guid", $columns));
    $this->_smarty->assign("has_state", array_key_exists("state", $columns));
    $this->_smarty->assign("has_create_date", array_key_exists("create_date", $columns));
    $this->_smarty->assign("has_object_id", array_key_exists("object_id", $columns));
    $this->_smarty->assign("consts", $consts);
    $this->_smarty->assign("id", self::getAbr($this->_lowerModel) . "id");
    $this->_smarty->allowPhpTag();
    return $this->generateModel("model");
  }
  static function getAbr($field) {
    $parts = array();
    foreach (explode("_", $field) as $part) {
      $parts[] = $part == "id" ? "id" : substr($part, 0, 1);
    }
    return implode("", $parts);
  }
  static function getModelClass($basename) {
    return StringUtil::pascalize($basename) . "Model";
  }
  static function getHandlerClass($basename) {
    return StringUtil::pascalize($basename) . "Handler";
  }
  static function getModel($basename) {
    $class = "Backend\\Model\\" . self::getModelClass($basename);
    return new $class();
  }
  function generateHandlerModel() {
    $this->init();
    $cmodel = self::getModel($this->_lowerModel);
    $dbos = array_values($cmodel::create()->getDbos());
    $extendPrimaryId = null;
    $fields = array();
    foreach ($dbos as $index => $dbo) {
      $tablenames[] = $dbo->getTablename();
      if (!$index) {
        $extendPrimaryId = value(array_keys($dbo->getPrimaryKeys()), 0);
      }
      foreach ($dbo->getColumns() as $name => $column) {
        if (!$column->isPrimary() && preg_match("/(^state\$|_id\$|guid)/", $name)) {
          $fields[$name] = $dbo->getTablename() . "." . $name;
        }
      }
    }
    $this->_smarty->assign("select_fields", '"' . implode('.*","', $tablenames) . '.*"');
    $this->_smarty->assign("extends", $this->_extends);
    $this->_smarty->assign("extend_primary_id", $extendPrimaryId);
    $this->_smarty->assign("extend_tablename", value($tablenames, 0));
    $this->_smarty->assign("tablename", value($tablenames, count($tablenames) - 1));
    $this->_smarty->assign("fields", $fields);
    $this->_smarty->assign("has_state", array_key_exists("state", $fields));
    $this->_smarty->assign("framework", $this->_framework);
    return $this->generateModel("handler");
  }
  function getDbo($model) {
    return DbGeneratorModel::getDbo($model);
  }
  function generateModel($modelType) {
    $templateFile = PathModel::getAssetsDirectory() . $modelType . "_model.inc";
    $content = $this->_smarty->fetch($templateFile);
    return $this->writeFile($this->getModelFile($modelType), $content);
  }
  function getModelFile($modelType) {
    return FileUtil::sanitizeFile($this->_appDir . StringUtil::pascalize($modelType) . "/" . StringUtil::pascalize($this->_lowerModel) . StringUtil::pascalize($modelType) . ".php");
  }
  function getComplexModelFile() {
    return $this->getModelFile("Model");
  }
  function getHandlerModelFile() {
    return $this->getModelFile("Handler");
  }
  function getModelDirectory($modelType) {
    return $this->_appDir . "Model";
  }
  function writeFile($file, $string) {
    FileUtil::put($file, $string);
    WebApplication::addNotify('Successfully added the file <a href="' . $file . '">' . $file . '</a>');
    return true;
  }
  static function getCmodels() {
    $files = FileUtil::getDirectoryListing(WebApplication::getMainApplicationDirectory() . "Model");
    $cmodels = ["" => ""];
    foreach ($files as $file) {
      if (preg_match("/(.*)Model\\.php/", $file, $matches)) {
        $cmodels[$matches[1]] = $matches[1];
      }
    }
    return $cmodels;
  }
}
