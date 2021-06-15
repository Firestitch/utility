<?php

namespace Utility\Model;

use Exception;
use Framework\Util\FileUtil;
use Framework\Util\HtmlUtil;
use Framework\Util\LangUtil;
use Framework\Util\StringUtil;

class ApiGeneratorModel extends GeneratorModel {
  protected $_dir = "";
  protected $_model = "";
  protected $_modelPlural = "";
  protected $_options = [];
  public function __construct($dir, $api, $model, $modelPlural, $methods = [], $parentModel = null, $options = []) {
    parent::__construct($dir);
    $this->_snakeModel = StringUtil::snakeize($model);
    $this->_model = $model;
    $this->_options = $options;
    $this->_api = $api;
    $this->_parentModel = StringUtil::snakeize($parentModel);
    $this->_modelPlural = $modelPlural;
    $this->_pluralSnakeModel = StringUtil::snakeize($modelPlural);
    $this->_methods = $methods;
    $this->_method = value($this->_options, "method", str_replace("_", "", $this->_modelPlural));
    $this->_dir = $this->getInstanceDir() . "View/Api/";
  }

  public function getFile() {
    return $this->_dir . str_replace("_", "", $this->_api) . "View.php";
  }

  public function append(&$messages = []) {
    $file = $this->getFile();
    if (!is_file($file)) {
      throw new Exception("API not found");
    }
    $code = FileUtil::get($this->getFile());
    $this->assign("parent_method", $this->_api . "/" . $this->_parentModel . "_id/");
    $endpoint = ltrim($this->getEndpoint());
    $regex = "/(public\\s+function\\s+wsdl\\(\\).*)/is";
    if (preg_match($regex, $code, $matches)) {
      $code = str_replace($matches[0], $endpoint . $matches[0], $code);
    } else {
      $pos = strrpos($code, "}");
      if ($pos === false) {
        throw new Exception("There was a problem trying to located the end of the class");
      }
      $code = substr_replace($code, $endpoint, $pos, 0);
    }
    FileUtil::put($file, $code);
    $messages = ["Successfully updated the file " . HtmlUtil::getLink("file:" . FileUtil::sanitizeFile($file), FileUtil::sanitizeFile($file))];
  }
  public function generate($override, &$messages = []) {
    $file = $this->getFile();
    if (!$override && is_file($file)) {
      throw new Exception("The file " . $file . " already exists");
    }

    $this->assign("parent_method", $this->_parentModel ? $this->_api . "/" : "")->assign("endpoint", $this->getEndpoint());
    if (!$this->writeTemplate(PathModel::getAssetsDirectory() . "api.inc", $file)) {
      throw new Exception("Failed to generate " . $file);
    }

    $messages = ["Successfully added the file " . HtmlUtil::getLink("file:" . FileUtil::sanitizeFile($file), FileUtil::sanitizeFile($file))];

    return true;
  }

  public function getEndpoint() {
    return $this->get(PathModel::getAssetsDirectory() . "api_endpoint.inc");
  }

  public function get($template) {
    $cmodel = ModelGeneratorModel::getModel($this->_model);
    $orderBy = "";
    $keywords = $accessibleFields = $fields = [];
    foreach ($cmodel->getDbos() as $dbo) {
      $fields += $dbo->getColumns();
      foreach ($dbo->getColumns() as $name => $column) {
        if (preg_match("/(name|description)/", $name)) {
          $keywords[] = ["name" => $name, "tablename" => $dbo->getTablename()];
        }
        if (preg_match("/(name|order)/", $name)) {
          $orderBy = $name;
        }
      }
    }
    $accessibleFields = array_values(array_filter(array_keys($fields), function ($v) {
      return !preg_match("/(" . $this->_snakeModel . "_id\$|guid|create_date|configs|_time|order|meta\$)/", $v);
    }));
    $pascalModel = StringUtil::pascalize($this->_model);
    $pascalParentModel = StringUtil::pascalize($this->_parentModel);

    return $this->assign("options", $this->_options)->assign("order_by", $orderBy)->assign("snake_model", $this->_snakeModel)->assign("plural_snake_model", $this->_pluralSnakeModel)->assign("model_upper", strtoupper($this->_model))->assign("pascal_model", $pascalModel)->assign("method", strtolower($this->_method))->assign("loads", (array) value($this->_options, "loads"))->assign("model_plural_upper", strtoupper($this->_modelPlural))->assign("model_plural_upper_trim", strtoupper(str_replace("_", "", $this->_modelPlural)))->assign("model_plural_proper", ucwords($this->_modelPlural))->assign("model_proper", ucwords($this->_model))->assign("model_name", ucwords(str_replace("_", " ", $this->_model)))->assign("model", $this->_model)->assign("methods", $this->_methods)->assign("keywords", $keywords)->assign("plural_pascal_model", LangUtil::getPlural($pascalModel))->assign("api", $this->_api)->assign("api_singular", rtrim($this->_api, "s"))->assign("dbos", $cmodel->getDbos())->assign("accessible_fields", $accessibleFields)->assign("has_state", in_array("state", array_keys($fields)))->assign("has_guid", in_array("guid", array_keys($fields)))->assign("create_date", in_array("create_date", array_keys($fields)))->assign("fields", array_keys($fields))->assign("model_id", $this->_snakeModel . "_id")->assign("model_plural", $this->_modelPlural)->assign("parent_model", $this->_parentModel)->assign("pascal_parent_model", $pascalParentModel)->fetch($template);
  }
}
