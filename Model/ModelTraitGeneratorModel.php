<?php

namespace Utility\Model;

use Exception;
use Framework\Core\WebApplication;
use Framework\Db\DB;
use Framework\Util\FileUtil;
use Framework\Util\StringUtil;


class ModelTraitGeneratorModel {

  public static $baseNamespace = "Backend";
  protected $_appDir = null;
  protected $_dbUtility;

  public function __construct($appDir = null) {
    $this->_appDir = $appDir ? $appDir : WebApplication::getMainApplicationDirectory();
    $this->_dbUtility = Db::getInstance()
      ->getUtility();
  }

  public function createTrait($tablenames, $name, $override = false) {
    $tablenames = is_array($tablenames) ? $tablenames : [$tablenames];
    $classname = basename(self::getTraitName(strtolower($name)));
    $traitFile = self::getTraitFile($classname, $this->_appDir);

    if (!is_dir($this->_appDir . "/Model/Traits/")) {
      FileUtil::mkdir($this->_appDir . "/Model/Traits/");
    }

    if (!is_file($traitFile) || $override) {
      $str = "<?php\n\nnamespace " . self::$baseNamespace . "\\Model\\Traits;\n\ntrait {$classname} {\n\n";
      $fieldNames = [];
      foreach ($tablenames as $tablename) {
        try {
          foreach ($this->_dbUtility->getTableFields($tablename) as $field) {
            $fieldNames[] = $field["Field"];
          }
        } catch (Exception $e) {
          WebApplication::instance()
            ->addWarning("The tablename `" . $tablename . "` doest not exists");

          continue;
        }
      }
      $fieldNames = array_unique($fieldNames);
      foreach ($fieldNames as $fieldName) {
        $pascalFieldName = StringUtil::pascalize($fieldName);
        $str .= '  /**
   * @return static
   */
  public function set' . $pascalFieldName . '($value) {
    return $this->setDboValue("' . $fieldName . '", $value);
  }' . "\n\n";
        $str .= '  public function get' . $pascalFieldName . '() {
    return $this->getDboValue("' . $fieldName . '");
  }' . "\n\n";
      }
      $str .= "}";
      $this->writeFile($traitFile, $str);
    } else {
      WebApplication::instance()
        ->addWarning("The Trait `" . $classname . "` already exists");
    }

    return $this;
  }

  public static function getTraitName($basename) {
    return self::$baseNamespace . "\\Model\\Traits\\" . StringUtil::pascalize(strtolower($basename)) . "Trait";
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

  public function appendModelTrait($modelName, $traitName = null) {
    $modelClassname = basename(self::getModelClassname($modelName));
    $modelFile = self::getModelFile($modelClassname, $this->_appDir);
    $traitName = $traitName ? $traitName : basename(self::getTraitName(strtolower($modelName)));
    $code = FileUtil::get($modelFile);
    if (strpos($code, $traitName) === false) {
      $code = preg_replace_callback("/extends.+{/", function ($matches) use ($traitName) {
        return $matches[0] . "\n\n" . "  use Traits\\{$traitName};";
      }, $code);
    }
    FileUtil::put($modelFile, $code);

    return $this;
  }

  public static function getModelClassname($basename) {
    return self::$baseNamespace . "\\Model\\" . StringUtil::pascalize(strtolower($basename)) . "Model";
  }

  public static function getModelFile($classname, $appDir) {
    return FileUtil::sanitizeFile($appDir . "/Model/" . $classname . ".php");
  }
}
