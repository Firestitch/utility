<?php

namespace Utility\Model;

use Exception;
use Framework\Core\WebApplication;
use Framework\Db\Db;
use Framework\Util\FileUtil;
use Framework\Util\StringUtil;


class ModelTraitGeneratorModel {

  protected $_appDir = null;
  protected $_dbUtility;

  public function __construct($appDir) {
    $this->_appDir = $appDir;
    $this->_dbUtility = Db::getInstance()->getUtility();
  }

  public function createTrait($tablenames, $namespace, $name, $override = false) {
    $tablenames = is_array($tablenames) ? $tablenames : [$tablenames];
    $classname = self::getBaseName(self::getTraitName($namespace, strtolower($name)));

    $traitFile = self::getTraitFile($classname, $this->_appDir);

    if (!is_dir($this->_appDir . "/Model/Traits/")) {
      FileUtil::mkdir($this->_appDir . "/Model/Traits/");
    }

    if (!is_file($traitFile) || $override) {
      $str = "<?php\n\nnamespace " . $namespace . "\\Model\\Traits;\n\ntrait {$classname} {\n\n";
      $fields = [];
      foreach ($tablenames as $tablename) {
        try {
          foreach ($this->_dbUtility->getTableFields($tablename) as $field) {
            $fields[$field["Field"]] = $field;
          }
        } catch (Exception $e) {
          WebApplication::instance()
            ->addWarning("The tablename `" . $tablename . "` doest not exists");

          continue;
        }
      }

      foreach ($fields as $field) {
        $fieldName = $field["Field"];
        $type = DbGeneratorModel::getPhpType($field["Type"]);
        $null = $field["Null"] === "YES";
        $pascalFieldName = StringUtil::pascalize($fieldName);
        $str .= "  /**
   * @return static
   * @param {$type} \$value
   */
  public function set{$pascalFieldName}(\$value) {
    return \$this->setDboValue(\"{$fieldName}\", \$value);
  }

  /**
   * @return {$type}
   */
  public function get{$pascalFieldName}() {
    return \$this->getDboValue(\"{$fieldName}\");
  }
  
";
      }
      $str .= "}";
      $this->writeFile($traitFile, $str);
    } else {
      WebApplication::instance()
        ->addWarning("The " . $classname . " Trait already exists");
    }

    return $this;
  }

  public static function getTraitName($namespace, $basename) {
    return $namespace . "\\Model\\Traits\\" . StringUtil::pascalize(strtolower($basename)) . "Trait";
  }

  public static function getTraitFile($classname, $appDir) {
    return FileUtil::sanitizeFile($appDir . "/Model/Traits/" . $classname . ".php");
  }

  public function writeFile($file, $string) {

    $errorMessage = "";
    $hasSuccess = FileUtil::putFileContents($file, $string, $errorMessage);

    if (!$hasSuccess) {
      throw new Exception($errorMessage);
    }

    return $hasSuccess;
  }

  public function appendModelTrait($namespace, $modelName, $traitName = null) {
    $modelClassname = self::getBaseName($modelName);
    $modelFile = self::getModelFile($modelClassname, $this->_appDir);
    $traitName = $traitName ? $traitName : basename(self::getTraitName($namespace, strtolower($modelName)));
    $code = FileUtil::get($modelFile);
    if (strpos($code, $traitName) === false) {
      $code = preg_replace_callback("/extends.+{/", function ($matches) use ($traitName) {
        return $matches[0] . "\n\n" . "  use Traits\\{$traitName};";
      }, $code);
    }
    FileUtil::put($modelFile, $code);

    return $this;
  }

  public static function getModelClassname($namespace, $basename) {
    return $namespace . "\\Model\\" . StringUtil::pascalize(strtolower($basename)) . "Model";
  }

  public static function getBaseName($modelName) {
    return basename(str_replace("\\", "/", $modelName));
  }

  public static function getModelFile($classname, $appDir) {
    return FileUtil::sanitizeFile($appDir . "/Model/" . $classname . ".php");
  }
}
