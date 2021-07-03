<?php

namespace Utility\Model;

use Exception;
use Framework\Arry\Arry;
use Framework\Db\Db;
use Framework\Util\StringUtil;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Utility\View\MapModel\ModelParser;

class ModelDescribeGeneratorModel {

  /**
   * @var ModelParser
   */
  private $_modelParser;

  public function __construct($modelFile) {
    $this->_modelParser = new ModelParser($modelFile);
  }

  public function getDescribeMethod(): ?ClassMethod {
    $describe = Arry::create($this->_modelParser->getClass()->stmts)
      ->find(function ($item) {
        return $item instanceof ClassMethod && $item->name->name === "describe";
      });

    if (!$describe) {
      throw new Exception("Failed to locate describe");
    }

    return $describe;
  }

  public function update($tablenames) {

    $dbUtility = Db::getInstance()
      ->getUtility();

    $array_ = $this->getDescribeArray();

    foreach ((array)$tablenames as $tablename) {
      foreach ($dbUtility->getTableFields($tablename) as $field) {
        $name = StringUtil::camelize(strtolower(value($field, "Field")));
        $exists = Arry::create($array_->items)
          ->exists(function (ArrayItem $item) use ($name) {
            return $item->key->value === $name;
          });

        if (!$exists) {
          $type = "";
          if (preg_match("/^date/", value($field, "Type")))
            $type = value($field, "Type");

          $this->appendDescribe($name, $type);
        }
      }
    }

    $this->saveCode();

    return $this;
  }

  public function saveCode() {
    $this->_modelParser->saveCode();
  }

  public function getDescribeArray(): Array_ {
    $describe = $this->getDescribeMethod();
    $return = value($describe->stmts, 0);
    if ($return instanceof Return_) {
      if ($return->expr instanceof Array_) {
        return $return->expr;
      }
    }

    throw new Exception("Failed to locate describe() Array");
  }

  public function appendDescribe($name, $type) {
    $key = new String_($name, ["kind" => String_::KIND_DOUBLE_QUOTED]);

    /**
     * @var ArrayItem[]
     */
    $items = [];

    if ($type) {
      $typeKey = new String_("type", ["kind" => String_::KIND_DOUBLE_QUOTED]);
      $typeValue = new String_($type, ["kind" => String_::KIND_DOUBLE_QUOTED]);
      $items[] = new ArrayItem($typeValue, $typeKey);
    }

    $value = new Array_($items);
    $describeArrayItem = new ArrayItem($value, $key);

    $array_ = $this->getDescribeArray();
    $array_->items[] = $describeArrayItem;
    return $this;
  }
}
