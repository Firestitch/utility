<?php

namespace Utility\Model;

use Framework\Arry\Arry;
use Framework\Core\Model;
use Framework\Core\WebApplication;
use Framework\Db\Db;
use Framework\Db\Dbo\Dbo;
use Framework\Db\Dbq\Dbq;
use Framework\Util\FileUtil;
use Framework\Util\LangUtil;
use Framework\Util\StringUtil;
use ReflectionClass;


class ModelGeneratorModel {

  protected $_appDir = null;
  protected $_lowerModel = null;
  protected $_upperModel = null;
  protected $_pascalName = null;
  protected $_tablename = null;
  protected ?SmartyModel $_smarty = null;
  protected $_namespace = null;
  protected $_primaryObjectId = null;

  public function __construct($namespace, $name, $pascalName, $appDir = null, $options = []) {
    $this->_lowerModel = LangUtil::singular($name);
    $this->_upperModel = LangUtil::singular(strtoupper($name));
    $this->_primaryObjectId = value($options, "primaryObjectId");
    $this->_pascalName = $pascalName;
    $this->_namespace = $namespace;
    $this->_appDir = $appDir;
    $this->_smarty = new SmartyModel();
    $this->_smarty->disableSecurity();
    $this->_smarty->registerModifierPlugin("pascalize", [StringUtil::class, "pascalize"]);
    $this->_smarty->registerModifierPlugin("camelize", [StringUtil::class, "camelize"]);
    $this->_smarty->registerModifierPlugin("plural", [LangUtil::class, "plural"]);
    $this->_smarty->assign("primaryObjectId", $this->_primaryObjectId);
    $this->_smarty->assign("upperModel", $this->_upperModel);
    $this->_smarty->assign("pascalName", $this->_pascalName);
    $this->_smarty->assign("namespace", $namespace);
    $this->_smarty->assign("pascalNames", LangUtil::getPlural($this->_pascalName));
    $this->_smarty->assign("lowerModel", $this->_lowerModel);
    $this->_smarty->assign("lowerModels", LangUtil::getPluralString($this->_lowerModel));
    $this->_smarty->assign("dboName", $pascalName);
  }

  public static function getHandlerClass($basename) {
    return StringUtil::pascalize($basename) . "Handler";
  }

  public static function getModels($dir) {
    $files = FileUtil::getDirectoryListing($dir . "/Model");

    $models = [];
    foreach ($files as $file) {
      if (preg_match("/(.*)Model\\.php/", $file, $matches)) {
        if ($matches[1]) {
          $models[$matches[1]] = $matches[1];
        }
      }
    }

    return $models;
  }

  public function generateComplexModel() {
    $this->init();
    $dbo = $this->getDbo();

    $columns = [];
    foreach ($dbo->getColumns() as $name => $column) {
      $columns[$name] = $column;
    }

    $refl = new ReflectionClass($this->getDbq());
    $objectConsts = array_keys($refl->getConstants());
    $refl = new ReflectionClass(dbq::class);
    $dbqConsts = array_keys($refl->getConstants());
    $diffConsts = array_diff($objectConsts, $dbqConsts);
    $dbo = $this->getDbo();
    $primaryKeys = array_keys($dbo->getPrimaryKeys());

    $consts = [];
    foreach ($diffConsts as $const) {
      $field = strtolower(get_value(explode("_", $const), 0));
      $consts[] = ["const" => $const, "field" => $field];
    }

    $uniqueIndex = Arry::create(Db::getInstance()->getUtility()->getIndexes($dbo->getTablename()))
      ->find(function ($index) use ($primaryKeys) {
        return value($index, "unique") && array_diff($primaryKeys, value($index, "columns"));
      });

    $this->_smarty
      ->assign("uniqueIndex", $uniqueIndex)
      ->assign("keys", $primaryKeys)
      ->assign("primaryKeys", $primaryKeys)
      ->assign("primaryKey", value($primaryKeys, 0))
      ->assign("hasGuid", array_key_exists("guid", $columns))
      ->assign("hasState", array_key_exists("state", $columns))
      ->assign("hasCreateDate", array_key_exists("create_date", $columns))
      ->assign("hasModifyDate", array_key_exists("modify_date", $columns))
      ->assign("hasName", array_key_exists("name", $columns))
      ->assign("hasModifyAccountId", array_key_exists("modify_account_id", $columns))
      ->assign("hasObjectId", array_key_exists("object_id", $columns))
      ->assign("consts", $consts)
      ->assign("id", self::getAbr($this->_lowerModel) . "id");

    $this->_smarty->allowPhpTag();

    // Generate state enum if needed
    $hasState = array_key_exists("state", $columns);
    if ($hasState) {
      $this->generateStateEnum();
    }

    return $this->generateModel("model");
  }

  public function init() {
    $dbo = $this->getDbo();
    $this->_smarty->assign("columns", $dbo->getColumns());
  }

  public function getDbo(): Dbo {
    return DbGeneratorModel::getDbo($this->_namespace, $this->_pascalName);
  }

  public function getDbq(): Dbq {
    return DbGeneratorModel::getDbq($this->_namespace, $this->_pascalName);
  }

  public static function getAbr($field) {
    $parts = [];
    foreach (explode("_", $field) as $part) {
      $parts[] = $part == "id" ? "id" : substr($part, 0, 1);
    }

    return implode("", $parts);
  }

  public function generateModel($modelType) {
    $templateFile = \Utility\Model\PathModel::getAssetsDirectory() . $modelType . "_model.inc";
    if (!file_exists($templateFile)) {
      throw new \Exception("Template file does not exist: {$templateFile}");
    }
    $content = $this->_smarty->fetch($templateFile);
    return $this->writeFile($this->getModelFile($modelType), $content);
  }

  public function writeFile($file, $string) {
    FileUtil::mkdir(dirname($file));
    FileUtil::put($file, $string);
    WebApplication::addNotify('Successfully added the file ' . basename($file));

    return true;
  }

  public function getModelFile($modelType) {
    return FileUtil::getSanitizedFile($this->_appDir . StringUtil::pascalize($modelType) . "/" . $this->_pascalName . StringUtil::pascalize($modelType) . ".php");
  }

  public function generateHandlerModel() {
    $this->init();
    $cmodel = self::getModel($this->_namespace, $this->_pascalName);
    $dbos = array_values($cmodel::create()->getDbos());
    $extendPrimaryId = null;
    $fields = [];
    foreach ($dbos as $index => $dbo) {
      $tablenames[] = $dbo->getTablename();
      if ($this->_primaryObjectId && !$index) {
        $extendPrimaryId = value(array_keys($dbo->getPrimaryKeys()), 0);
      }

      foreach ($dbo->getColumns() as $name => $column) {
        if (preg_match("/(^state\$|_id\$|guid)/", $name)) {
          $fields[$name] = $dbo->getTablename() . "." . $name;
        }
      }
    }

    $this->_smarty->assign("selectFields", '"' . implode('.*","', $tablenames) . '.*"');
    $this->_smarty->assign("extendPrimaryId", $extendPrimaryId);
    $this->_smarty->assign("extendTablename", value($tablenames, 0));
    $this->_smarty->assign("tablename", value($tablenames, count($tablenames) - 1));
    $this->_smarty->assign("fields", $fields);
    $this->_smarty->assign("hasState", array_key_exists("state", $fields));

    return $this->generateModel("handler");
  }

  /**
   * @return Model
   */
  public static function getModel($namespace, $basename) {
    $class = $namespace . "\\Model\\" . self::getModelClass($basename);

    /** @var Model */
    $model = new $class();

    return $model;
  }

  public static function getModelClass($basename) {
    return StringUtil::pascalize($basename) . "Model";
  }

  public function getComplexModelFile() {
    return $this->getModelFile("Model");
  }

  public function getHandlerModelFile() {
    return $this->getModelFile("Handler");
  }

  public function getModelDirectory($modelType) {
    return $this->_appDir . "Model";
  }

  public static function getNamespaceDir($namespace) {
    $namespace = lcfirst($namespace);
    return FileUtil::sanitize(WebApplication::getInstanceDirectory() . $namespace . "/");
  }

  /**
   * Get the directory path for a namespace
   */
  private function _getNamespaceDir($namespace) {
    $path = "";
    $dir = "";

    if (preg_match('/^Backend(?:$|\\\)(.*)/', $namespace, $matches)) {
      $path = value($matches, 1);
      $dir = \Framework\Model\PathModel::getBackendDir();
    }

    if (preg_match("/^Framework(?:$|\\\)(.*)/", $namespace, $matches)) {
      $path = value($matches, 1);
      $dir = \Framework\Model\PathModel::getFrameworkDir();
    }

    if (preg_match("/^Utility(?:$|\\\)(.*)/", $namespace, $matches)) {
      $path = value($matches, 1);
      $dir = \Framework\Model\PathModel::getInstanceDir();
    }

    if (!$dir) {
      throw new \Exception("Invalid namespace: {$namespace}");
    }

    $path = trim($path, "\\");
    if ($path) {
      $dir .= str_replace("\\", "/", "/" . $path);
    }

    return $dir . "/";
  }

  /**
   * Generate the StateEnum class if it doesn't exist
   */
  public function generateStateEnum() {
    $enumClass = $this->_namespace . "\\Enum\\" . $this->_pascalName . "StateEnum";

    // Check if enum already exists
    if (class_exists($enumClass)) {
      return false;
    }

    $enumDir = $this->_getNamespaceDir($this->_namespace) . "Enum/";
    $enumFile = $enumDir . $this->_pascalName . "StateEnum.php";

    // Don't overwrite if file exists
    if (file_exists($enumFile)) {
      return false;
    }

    $enumContent = $this->_generateStateEnumContent();

    FileUtil::mkdir($enumDir);
    FileUtil::put($enumFile, $enumContent);
    WebApplication::addNotify('Successfully added the file ' . basename($enumFile));

    return true;
  }

  /**
   * Generate the content for the StateEnum class
   */
  private function _generateStateEnumContent() {
    $namespace = $this->_namespace . "\\Enum";
    $enumName = $this->_pascalName . "StateEnum";

    return <<<PHP
<?php

namespace {$namespace};

enum {$enumName}: string {

  case Active = "active";
  case Deleted = "deleted";

  public function label(): string {
    return match (\$this) {
      self::Active => 'Active',
      self::Deleted => 'Deleted',
    };
  }
}

PHP;
  }
}