<?php

namespace Utility\Model;

use Exception;
use Framework\Arry\Arry;
use Framework\Core\WebApplication;
use Framework\Db\DB;
use Framework\Util\FileUtil;
use Framework\Util\StringUtil;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Utility\View\MapModel\ModelParser;

class ModelDescribeGeneratorModel {

  public static $baseNamespace = "Backend";
  protected $_appDir = null;
  protected $_dbUtility;

  public function __construct($appDir = null) {
    $this->_appDir = $appDir ? $appDir : WebApplication::getMainApplicationDirectory();
    $this->_dbUtility = Db::getInstance()
      ->getUtility();
  }

  public function update($tablenames, $name) {

    $modelClassname = basename(self::getModelClassname($name));
    $modelFile = self::getModelFile($modelClassname, $this->_appDir);

    $modelParser = new ModelParser($modelFile);

    /**
     * @var ClassMethod
     */
    $describe = Arry::create($modelParser->getClass()->stmts)
      ->find(function ($item) {
        return $item instanceof ClassMethod && $item->name->name === "describe";
      });

    if (!$describe)
      throw new Exception("Failed to locate describe");

    $return = value($describe->stmts, 0);

    if ($return instanceof Return_) {
      if ($return->expr instanceof Array_) {
        foreach ((array)$tablenames as $tablename) {
          foreach ($this->_dbUtility->getTableFields($tablename) as $field) {
            $name = StringUtil::camelize(strtolower(value($field, "Field")));
            $exists = Arry::create($return->expr->items)
              ->exists(function (ArrayItem $item) use ($name) {
                return $item->key->value === $name;
              });

            if (!$exists) {

              $key = new String_($name, ["kind" => String_::KIND_DOUBLE_QUOTED]);

              /**
               * @var ArrayItem[]
               */
              $items = [];

              $type = value($field, "Type");

              if ($type === "date" || $type === "datetime") {
                $typeKey = new String_("type", ["kind" => String_::KIND_DOUBLE_QUOTED]);
                $typeValue = new String_($type, ["kind" => String_::KIND_DOUBLE_QUOTED]);
                $items[] = new ArrayItem($typeValue, $typeKey);
              }

              $value = new Array_($items);
              $describeArrayItem = new ArrayItem($value, $key);

              $return->expr->items[] = $describeArrayItem;
            }
          }
        }
      }
    }

    $code = $modelParser->getCode();

    FileUtil::put($modelFile, $code);
  }

  public function writeFile($file, $string) {
    $errorMessage = "";
    $hasSuccess = FileUtil::putFileContents($file, $string, $errorMessage);
    if (!$hasSuccess) {
      throw new Exception($errorMessage);
    }

    return $hasSuccess;
  }

  public static function getModelClassname($basename) {
    return self::$baseNamespace . "\\Model\\" . StringUtil::pascalize(strtolower($basename)) . "Model";
  }

  public static function getModelFile($classname, $appDir) {
    return FileUtil::sanitizeFile($appDir . "/Model/" . $classname . ".php");
  }
}
