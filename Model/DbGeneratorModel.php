<?php

namespace Utility\Model;

use Exception;
use Framework\Core\WebApplication;
use Framework\Db\DB;
use Framework\Util\FileUtil;
use Framework\Util\StringUtil;

class DbGeneratorModel {
  protected $_appDir = null;
  public function __construct($appDir = null) {
    $this->_appDir = $appDir ? $appDir : WebApplication::getMainApplicationDirectory();
    $this->_dbUtility = Db::getInstance()->getUtility();
  }
  public function createDbo($tablename, $name, $override = false) {
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
        $notNull = $row["Null"] == "YES" ? "true" : "false";
        $primary = $row["Key"] == "PRI" ? "true" : "false";
        $name = $row["Field"];
        $columns[] = "\$this->_columns[\"" . $name . "\"] = new Column(\"" . $dataType . "\"," . $size . "," . $notNull . "," . $primary . ");";
      }
      $str = "<?php\n\nnamespace Backend\\Dbo;\n\nuse Framework\\Db\\Dbo\\Dbo;\nuse Framework\\Db\\Dbo\\Column;\n\nclass " . $classname . " extends Dbo {\n\n";
      $str .= "  function __construct() {\n" . "    \$this->_tablename = \"" . $tablename . "\";\n";
      foreach ($columns as $column) {
        $str .= "    " . $column . "\n";
      }
      $str .= "  }\n\n";
      foreach ($result as $row) {
        $str .= '  public function set_' . $row["Field"] . '($value) {
    return $this->set_column_value("' . $row["Field"] . '", $value);
  }' . "\n\n";
        $str .= '  public function get_' . $row["Field"] . '() {
    return $this->get_column_value("' . $row["Field"] . '");
  }' . "\n\n";
      }
      $str .= "}";
      $hasSuccess = $this->writeFile($dboFile, $str);
    } else {
      WebApplication::instance()->addWarning("The class `" . $classname . "` already exists");
    }

    return $hasSuccess;
  }
  public function createDbq($tablename, $name, $override = false) {
    $classname = self::getDbqClassname(strtolower($name));
    $dbqFile = self::getDbqFile($classname, $this->_appDir);
    $hasSuccess = false;
    if (!is_file($dbqFile) || $override) {
      $str = "<?php\n\nnamespace Backend\\Dbq;\n\nuse Framework\\Db\\Dbq\\Dbq;\n\nclass " . $classname . " extends Dbq {\n\n";
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
      WebApplication::instance()->addWarning("The DBQ class `" . $classname . "` already exists");
    }

    return $hasSuccess;
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
  public function writeFile($file, $string) {
    $errorMessage = "";
    $hasSuccess = FileUtil::putFileContents($file, $string, $errorMessage);
    if (!$hasSuccess) {
      throw new Exception($errorMessage);
    }

    return $hasSuccess;
  }
  public static function getDboFile($classname, $appDir) {
    return FileUtil::sanitizeFile($appDir . "Dbo/" . $classname . ".php");
  }
  public static function getDbqFile($classname, $appDir) {
    return FileUtil::sanitizeFile($appDir . "Dbq/" . $classname . ".php");
  }
  public static function getDbqTablename($basename, $appDir) {
    $dbq = self::getDbq($basename, $appDir);

    return $dbq ? $dbq->getTablename() : "";
  }
  public static function getDbqClass($basename) {
    return "Backend\\Dbq\\" . self::getDbqClassname($basename);
  }
  public static function getDboClass($basename) {
    return "Backend\\Dbo\\" . self::getDboClassname($basename);
  }
  public static function getDbqClassname($basename) {
    return StringUtil::pascalize($basename) . "Dbq";
  }
  public static function getDboClassname($basename) {
    return StringUtil::pascalize($basename) . "Dbo";
  }
  public static function getDbo($basename) {
    $class = self::getDboClass($basename);

    return new $class();
  }
  public static function getDbq($basename) {
    $class = self::getDbqClass($basename);

    return new $class();
  }
}
