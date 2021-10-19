<?php

namespace Utility\Model;

use Exception;
use Framework\Core\WebApplication;
use Framework\Db\Db;
use Framework\Util\FileUtil;
use Framework\Util\StringUtil;


class DbGeneratorModel {

  protected $_appDir = null;

  public function __construct($appDir = null) {
    $this->_appDir = $appDir ? $appDir : WebApplication::getMainApplicationDirectory();
    $this->_dbUtility = Db::getInstance()
      ->getUtility();
  }

  public static function getDbqTablename($basename, $appDir) {
    $dbq = self::getDbq($basename, $appDir);

    return $dbq ? $dbq->getTablename() : "";
  }

  public static function getDbq($namespace, $basename) {
    $class = self::getDbqClass($namespace, $basename);

    return new $class();
  }

  public static function getDbqClass($namespace, $basename) {
    return "{$namespace}\\Dbq\\" . self::getDbqClassname($basename);
  }

  public static function getDbo($namespace, $basename) {
    $class = self::getDboClass($namespace, $basename);

    return new $class();
  }

  public static function getDboClass($namespace, $basename) {
    return "{$namespace}\\Dbo\\" . self::getDboClassname($basename);
  }

  public function createDbo(string $tablename, string $namespace, string $name, $override = false) {
    $classname = self::getDboClassname(strtolower($name));
    $dboFile = self::getDboFile($classname, $this->_appDir);
    $hasSuccess = false;

    if (!is_file($dboFile) || $override) {
      $result = $this->_dbUtility->getTableFields($tablename);

      $columns = [];
      foreach ($result as $row) {
        $type = str_replace("unsigned", "", strtolower($row["Type"]));
        $dataType = preg_replace("/[^a-z]+/", "\$1", $type);
        $size = preg_replace("/[a-z]+\\((\\d+)\\)/", "\$1", $type);
        $size = is_numeric($size) ? $size : "null";
        $null = $row["Null"] === "YES";
        $notNull = $null ? "true" : "false";
        $primary = $row["Key"] === "PRI" ? "true" : "false";
        $name = $row["Field"];
        $columns[] = "\$this->_columns[\"" . $name . "\"] = new Column(\"" . $dataType . "\"," . $size . "," . $notNull . "," . $primary . ");";
      }

      $str = "<?php\n\nnamespace {$namespace}\\Dbo;\n\nuse Framework\\Db\\Dbo\\Dbo;\nuse Framework\\Db\\Dbo\\Column;\n\nclass " . $classname . " extends Dbo {\n\n";
      $str .= "  function __construct() {\n" . "    \$this->_tablename = \"" . $tablename . "\";\n";
      foreach ($columns as $column) {
        $str .= "    " . $column . "\n";
      }
      $str .= "  }\n\n";
      foreach ($result as $row) {
        $type = self::getPhpType($row["Type"]);
        $null = $row["Null"] === "YES";
        $str .= "
  /**
   * @param {$type} \$value
   * @return static
   */
  public function set" . StringUtil::pascalize($row["Field"]) . "(\$value) {
    return \$this->setColumnValue(\"" . $row["Field"] . "\", \$value);
  }

  /**
   * @return {$type}
   */
  public function get" . StringUtil::pascalize($row["Field"]) . "() {
    return \$this->getColumnValue(\"" . $row["Field"] . "\");
  }
  
  ";
      }

      $str .= "}";
      $hasSuccess = $this->writeFile($dboFile, $str);
    } else {
      WebApplication::instance()
        ->addWarning("The class `" . $classname . "` already exists");
    }

    return $hasSuccess;
  }

  public static function getPhpType($type) {
    if (preg_match("/int/i", $type)) {
      return "int";
    }

    if (preg_match("/(char|blob|text)/i", $type)) {
      return "string";
    }

    if (preg_match("/(float|double|decimal|real)/i", $type)) {
      return "float";
    }

    if (preg_match("/(date|time)/i", $type)) {
      return "string|\\Framework\\Model\\TimeModel";
    }

    if (preg_match("/bool/i", $type)) {
      return "bool";
    }

    if (preg_match("/json/i", $type)) {
      return "mixed";
    }

    return "string";
  }

  public static function getDboClassname($basename) {
    return StringUtil::pascalize($basename) . "Dbo";
  }

  public static function getDboFile($classname, $appDir) {
    return FileUtil::sanitizeFile($appDir . "Dbo/" . $classname . ".php");
  }

  public function writeFile($file, $string) {
    FileUtil::mkdir(dirname($file));
    $errorMessage = "";
    $hasSuccess = FileUtil::putFileContents($file, $string, $errorMessage);
    if (!$hasSuccess) {
      throw new Exception($errorMessage);
    }

    return $hasSuccess;
  }

  public function createDbq(string $tablename, string $namespace, string $name, $override = false) {
    $classname = self::getDbqClassname(strtolower($name));
    $dbqFile = self::getDbqFile($classname, $this->_appDir);
    $hasSuccess = false;
    if (!is_file($dbqFile) || $override) {
      $str = "<?php\n\nnamespace {$namespace}\\Dbq;\n\nuse Framework\\Db\\Dbq\\Dbq;\n\nclass " . $classname . " extends Dbq {\n\n";
      $fields = $this->_dbUtility->getTableFields($tablename);
      $primaryKeys = [];
      foreach ($fields as $field) {
        $name = value($field, "Field");
        if ($name == "state") {
          continue;
        }
        if (get_value($field, "Key") == "PRI") {
          $primaryKeys[] = '"' . $name . '"';
        }
      }

      $primaryKey = count($primaryKeys) == 1 ? implode(",", $primaryKeys) : "array(" . implode(",", $primaryKeys) . ")";
      $str .= "  public function __construct() {\n" . "    parent::__construct(\"" . $tablename . "\", " . $primaryKey . ");\n" . "  }\n" . "}";
      $hasSuccess = $this->writeFile($dbqFile, $str);
    } else {
      WebApplication::instance()
        ->addWarning("The DBQ class `" . $classname . "` already exists");
    }

    return $hasSuccess;
  }

  public static function getDbqClassname($basename) {
    return StringUtil::pascalize($basename) . "Dbq";
  }

  public static function getDbqFile($classname, $appDir) {
    return FileUtil::sanitizeFile($appDir . "Dbq/" . $classname . ".php");
  }

  public function getKeyCount($tablename) {
    $fields = $this->_dbUtility->getTableFields($tablename);
    $count = 0;
    foreach ($fields as $field) {
      if (get_value($field, "Key") == "PRI") {
        $count++;
      }
    }

    return $count;
  }
}
