<?php

namespace Utility\Model;

use Exception;
use Framework\Arry\Arry;
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

  public function getFile() {
    return $this->_dir . str_replace("_", "", $this->_api) . "View.php";
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

    $fillFields = Arry::create($accessibleFields)
      ->filter(function ($field) {
        return !preg_match("/_id$/", $field);
      })
      ->get();

    $pascalModel = StringUtil::pascalize($this->_model);
    $pascalParentModel = StringUtil::pascalize($this->_parentModel);

    return $this->assign("options", $this->_options)
      ->assign("orderBy", $orderBy)
      ->assign("snakeModel", $this->_snakeModel)
      ->assign("pluralSnakeModel", $this->_pluralSnakeModel)
      ->assign("modelUpper", strtoupper($this->_model))
      ->assign("pascalModel", $pascalModel)
      ->assign("method", strtolower($this->_method))
      ->assign("loads", (array)value($this->_options, "loads"))
      ->assign("modelPluralUpper", strtoupper($this->_modelPlural))
      ->assign("modelPluralUpperTrim", strtoupper(str_replace("_", "", $this->_modelPlural)))
      ->assign("modelPluralProper", ucwords($this->_modelPlural))
      ->assign("modelProper", ucwords($this->_model))
      ->assign("modelName", ucwords(str_replace("_", " ", $this->_model)))
      ->assign("model", $this->_model)
      ->assign("methods", $this->_methods)
      ->assign("keywords", $keywords)
      ->assign("pluralPascalModel", LangUtil::getPlural($pascalModel))
      ->assign("api", $this->_api)
      ->assign("apiSingular", rtrim($this->_api, "s"))
      ->assign("dbos", $cmodel->getDbos())
      ->assign("accessibleFields", $accessibleFields)
      ->assign("fillFields", $fillFields)
      ->assign("hasState", in_array("state", array_keys($fields)))
      ->assign("hasGuid", in_array("guid", array_keys($fields)))
      ->assign("createDate", in_array("create_date", array_keys($fields)))
      ->assign("fields", array_keys($fields))
      ->assign("modelId", $this->_snakeModel . "_id")
      ->assign("modelPlural", $this->_modelPlural)
      ->assign("parentModel", $this->_parentModel)
      ->assign("pascalParentModel", $pascalParentModel)
      ->fetch($template);
  }

  public function generate($override, &$messages = []) {
    $file = $this->getFile();
    if (!$override && is_file($file)) {
      throw new Exception("The file " . $file . " already exists");
    }

    $this->assign("parent_method", $this->_parentModel ? $this->_api . "/" : "")
      ->assign("endpoint", $this->getEndpoint());
    if (!$this->writeTemplate(PathModel::getAssetsDirectory() . "api.inc", $file)) {
      throw new Exception("Failed to generate " . $file);
    }

    $messages = ["Successfully added the file " . HtmlUtil::getLink("file:" . FileUtil::sanitizeFile($file), FileUtil::sanitizeFile($file))];

    return true;
  }

}
