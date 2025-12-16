<?php

namespace Utility\Model;

use Exception;
use Framework\Arry\Arry;
use Framework\Core\WebApplication;
use Framework\PhpParser\PhpParser;
use Framework\Util\FileUtil;
use Framework\Util\StringUtil;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Utility\Model\DbGeneratorModel;

class ModelDescribeGeneratorModel {

  /**
   * @var PhpParser
   */
  private $_phpParser;

  /**
   * @var bool|null
   */
  private $_usesAttributePattern = null;

  /**
   * @var string
   */
  private $_modelFile;

  public function __construct($modelFile) {
    $this->_modelFile = $modelFile;
    $this->_phpParser = new PhpParser($modelFile);
  }

  /**
   * Detects which pattern is being used: describe() method or #[Describe] attribute
   * @return bool true if using attribute pattern, false if using method pattern
   */
  private function usesAttributePattern(): bool {
    if ($this->_usesAttributePattern !== null) {
      return $this->_usesAttributePattern;
    }

    // First check for describe() method
    $describeMethod = Arry::create($this->_phpParser->getClass()->stmts)
      ->find(function ($item) {
        return $item instanceof ClassMethod && $item->name->name === "describe";
      });

    if ($describeMethod) {
      $this->_usesAttributePattern = false;
      return false;
    }

    // Check for Describe attribute
    $class = $this->_phpParser->getClass();
    foreach ($class->attrGroups as $attrGroup) {
      foreach ($attrGroup->attrs as $attr) {
        if ($attr->name instanceof Name && $attr->name->getLast() === "Describe") {
          $this->_usesAttributePattern = true;
          return true;
        }
        if ($attr->name instanceof FullyQualified && $attr->name->getLast() === "Describe") {
          $this->_usesAttributePattern = true;
          return true;
        }
      }
    }

    throw new Exception("Failed to locate describe() method or #[Describe] attribute");
  }

  public function getDescribeMethod(): ?ClassMethod {
    $describe = Arry::create($this->_phpParser->getClass()->stmts)
      ->find(function ($item) {
        return $item instanceof ClassMethod && $item->name->name === "describe";
      });

    if (!$describe) {
      throw new Exception("Failed to locate describe");
    }

    return $describe;
  }

  public function getDescribeAttribute(): ?Attribute {
    $class = $this->_phpParser->getClass();

    foreach ($class->attrGroups as $attrGroup) {
      foreach ($attrGroup->attrs as $attr) {
        if ($attr->name instanceof Name && $attr->name->getLast() === "Describe") {
          return $attr;
        }
        if ($attr->name instanceof FullyQualified && $attr->name->getLast() === "Describe") {
          return $attr;
        }
      }
    }

    throw new Exception("Failed to locate #[Describe] attribute");
  }

  /**
   * @suppress PHP0416
   */
  public function update($tablenames) {
    $usesAttribute = $this->usesAttributePattern();

    // Get namespace and basename to access Dbo
    $namespace = $this->getModelNamespace();
    $className = $this->getModelClassName();
    if (!$namespace || !$className) {
      throw new Exception("Failed to determine namespace or class name");
    }

    // Extract basename (e.g., "SigninCredential" from "SigninCredentialModel")
    $basename = preg_replace("/Model$/", "", $className);
    if (!$basename) {
      throw new Exception("Failed to extract basename from class name: {$className}");
    }

    // Remove \Model from namespace to get the base namespace for Dbo
    // e.g., "Framework\Signin\Model" -> "Framework\Signin"
    $dboNamespace = preg_replace("/\\\\Model$/", "", $namespace);
    if (!$dboNamespace) {
      $dboNamespace = $namespace;
    }

    // Get Dbo to access Column objects
    $dbo = DbGeneratorModel::getDbo($dboNamespace, $basename);
    $columns = $dbo->getColumns();
    $primaryKeys = array_keys($dbo->getPrimaryKeys());

    foreach ((array)$tablenames as $tablename) {
      foreach ($columns as $columnName => $column) {
        $name = StringUtil::camelize(strtolower($columnName));
        $exists = false;

        if ($usesAttribute) {
          $propertiesArray = $this->getDescribePropertiesArray();
          $exists = Arry::create($propertiesArray->items)
            ->exists(function ($item) use ($name) {
              if ($item instanceof New_) {
                // Check if this is a Property class (either short name or fully qualified)
                $isProperty = false;
                if ($item->class instanceof Name) {
                  $isProperty = $item->class->getLast() === "Property";
                } elseif ($item->class instanceof FullyQualified) {
                  $isProperty = $item->class->getLast() === "Property" ||
                    $item->class->toString() === "Framework\\Model\\Attribute\\Property";
                }

                if ($isProperty) {
                  // Check if this Property has name: "name" as an argument
                  $isFirstArg = true;
                  foreach ($item->args as $arg) {
                    if ($arg instanceof Arg) {
                      // Check named argument
                      if ($arg->name && $arg->name->name === "name") {
                        if ($arg->value instanceof String_ && $arg->value->value === $name) {
                          return true;
                        }
                      }
                      // Check first positional argument (name is first parameter)
                      if (!$arg->name && $isFirstArg && $arg->value instanceof String_ && $arg->value->value === $name) {
                        return true;
                      }
                      $isFirstArg = false;
                    }
                  }
                }
              }
              return false;
            });
        } else {
          $array_ = $this->getDescribeArray();
          $exists = Arry::create($array_->items)
            ->exists(function (ArrayItem $item) use ($name) {
              return $item->key->value === $name;
            });
        }

        if (!$exists) {
          $this->appendDescribe($name, $column, $columnName, $primaryKeys, $basename);
        }
      }
    }

    $this->saveCode();

    return $this;
  }

  public function saveCode() {
    $code = $this->_phpParser->getCode();

    // Format properties array to be on multiple lines
    // Find the position of #[Describe(properties: [ and then find the matching ])]
    $pattern = '/#\[Describe\s*\(\s*properties:\s*\[/';
    if (preg_match($pattern, $code, $matches, PREG_OFFSET_CAPTURE)) {
      $startPos = $matches[0][1] + strlen($matches[0][0]);

      // Find the matching closing bracket by counting brackets
      $bracketCount = 1;
      $inString = false;
      $stringChar = '';
      $endPos = $startPos;

      for ($i = $startPos; $i < strlen($code); $i++) {
        $char = $code[$i];

        if (!$inString && ($char === '"' || $char === "'")) {
          $inString = true;
          $stringChar = $char;
        } elseif ($inString && $char === $stringChar && ($i === 0 || $code[$i - 1] !== '\\')) {
          $inString = false;
        } elseif (!$inString) {
          if ($char === '[') {
            $bracketCount++;
          } elseif ($char === ']') {
            $bracketCount--;
            if ($bracketCount === 0) {
              $endPos = $i;
              break;
            }
          }
        }
      }

      // Extract properties content
      $properties = substr($code, $startPos, $endPos - $startPos);

      if (!empty(trim($properties))) {
        // Split by '), new Property(' or '),new Property('
        $formatted = preg_replace('/\),\s*new\s+Property\(/', "),\n\tnew Property(", $properties);
        // Ensure first Property is on new line
        $formatted = preg_replace('/^new\s+Property\(/', "\n\tnew Property(", $formatted);
        // Add newline after opening bracket and before closing bracket
        $formatted = "\n\t" . trim($formatted) . "\n";

        // Replace in code
        $code = substr_replace($code, $formatted, $startPos, $endPos - $startPos);
      } else {
        // Empty array
        $code = substr_replace($code, "\n", $startPos, $endPos - $startPos);
      }
    }

    FileUtil::put($this->_modelFile, $code);
  }

  public function getDescribeArray(): ?Array_ {
    $describe = $this->getDescribeMethod();
    $return = value($describe->stmts, 0);

    if ($return instanceof Return_) {
      if ($return->expr instanceof Array_) {
        return $return->expr;
      }

      if ($return->expr instanceof FuncCall) {
        /**
         * @var FuncCall
         */
        $expr = $return->expr;
        if ($expr->name instanceof Name) {
          $nameParts = $expr->name->getParts();
          if ($nameParts === ["array_merge"]) {
            foreach ($expr->args as $arg) {
              if ($arg->value instanceof Array_) {
                return $arg->value;
              }
            }
          }
        }
      }
    }

    throw new Exception("Failed to locate describe() Array");
  }

  public function appendDescribe($name, $column = null, $columnName = null, $primaryKeys = [], $basename = null) {
    if ($this->usesAttributePattern()) {
      $this->appendDescribeAttribute($name, $column, $columnName, $primaryKeys, $basename);
    } else {
      $type = "";
      if ($column && preg_match("/^date|json/", $column->getDataType())) {
        $type = $column->getDataType();
      }
      $this->appendDescribeMethod($name, $type);
    }
    return $this;
  }

  private function appendDescribeMethod($name, $type) {
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
    //$describeArrayItem = new ArrayItem($value, $key, false, ['comments' => [new Comment("")]]);
    $describeArrayItem = new ArrayItem($value, $key);

    $array_ = $this->getDescribeArray();
    $array_->items[] = $describeArrayItem;
  }

  private function appendDescribeAttribute($name, $column, $columnName, $primaryKeys, $basename) {
    if (!$column || !$columnName) {
      return;
    }

    $namespace = $this->getModelNamespace();
    $hasState = array_key_exists("state", $this->getAllColumns());
    $primaryObjectId = $this->isPrimaryObjectId();

    // Check if this field should use an enum (e.g., "state" -> StateEnum)
    $enumClass = null;
    $useEnum = false;
    if ($columnName === "state" && $hasState) {
      $enumClass = $this->getEnumClassForField($name);
      if ($enumClass) {
        // Check if enum exists, if not generate it
        if (!class_exists($enumClass) && !enum_exists($enumClass)) {
          $this->generateEnumForField($name, $enumClass);
        }
        $useEnum = true;
      }
    }

    // Build Property arguments array
    $args = [];

    // 1. name argument (required, first positional)
    $args[] = new Arg(
      new String_($name, ["kind" => String_::KIND_DOUBLE_QUOTED])
    );

    // 2. type argument (required, second positional or named)
    $typeArg = null;
    if ($useEnum) {
      // Use enum class (e.g., SigninCredentialStateEnum::class)
      $enumNameParts = explode("\\", $enumClass);
      $enumShortName = end($enumNameParts);
      $enumNameNode = new Name($enumShortName);
      $enumClassConst = new ClassConstFetch($enumNameNode, new Identifier("class"));
      $typeArg = new Arg(
        $enumClassConst,
        false,
        false,
        [],
        new Identifier("type")
      );
    } else {
      // Map database types to TypeEnum case names (from template logic)
      $typeEnumCase = "String";
      if ($column->isPrimary() && count($primaryKeys) === 1) {
        $typeEnumCase = "BigInteger";
      } elseif ($column->isDataTypeDatetime()) {
        $typeEnumCase = "Datetime";
      } elseif ($column->isDataTypeDate()) {
        $typeEnumCase = "Date";
      } elseif ($column->isDataTypeJson()) {
        $typeEnumCase = "Object";
      } elseif ($column->isDataTypeBool()) {
        $typeEnumCase = "Boolean";
      } elseif ($column->getDataType() === "bigint") {
        $typeEnumCase = "BigInteger";
      } elseif ($column->isDataTypeInt()) {
        $typeEnumCase = "Integer";
      } elseif ($column->isDataTypeDecimal()) {
        $typeEnumCase = "Float";
      }

      $typeEnumClass = new Name("TypeEnum");
      $typeEnumConst = new ClassConstFetch($typeEnumClass, new Identifier($typeEnumCase));
      $typeArg = new Arg(
        $typeEnumConst,
        false,
        false,
        [],
        new Identifier("type")
      );
    }
    $args[] = $typeArg;

    // 3. toArray argument (if primary key with single primary key and primaryObjectId)
    if ($column->isPrimary() && count($primaryKeys) === 1 && $primaryObjectId) {
      $toArrayAliasArg = new Arg(
        new String_("id", ["kind" => String_::KIND_DOUBLE_QUOTED]),
        false,
        false,
        [],
        new Identifier("alias")
      );
      $toArrayExpr = new New_(
        new Name("ToArray"),
        [$toArrayAliasArg]
      );
      $args[] = new Arg(
        $toArrayExpr,
        false,
        false,
        [],
        new Identifier("toArray")
      );
    }

    // 4. diff argument (if primary key, guid, order, modify_date, or create_date)
    if ($column->isPrimary() || $columnName === "guid" || $columnName === "order" ||
      $columnName === "modify_date" || $columnName === "create_date") {
      $args[] = new Arg(
        new ConstFetch(new Name("false")),
        false,
        false,
        [],
        new Identifier("diff")
      );
    }

    // 5. default and validations for state field
    if ($columnName === "state" && $hasState && $useEnum) {
      $enumNameParts = explode("\\", $enumClass);
      $enumShortName = end($enumNameParts);
      $enumNameNode = new Name($enumShortName);
      $enumActiveConst = new ClassConstFetch($enumNameNode, new Identifier("Active"));

      $defaultValueArg = new Arg(
        $enumActiveConst,
        false,
        false,
        [],
        new Identifier("value")
      );
      $defaultValueExpr = new New_(
        new Name("DefaultValue"),
        [$defaultValueArg]
      );
      $args[] = new Arg(
        $defaultValueExpr,
        false,
        false,
        [],
        new Identifier("default")
      );

      $validationRequiredExpr = new New_(new Name("ValidationRequired"));
      $validationsArray = new Array_([new ArrayItem($validationRequiredExpr)]);
      $args[] = new Arg(
        $validationsArray,
        false,
        false,
        [],
        new Identifier("validations")
      );
    } elseif ((preg_match("/_id$/", $columnName) && !$column->isPrimary() && !$column->isNotNull()) ||
      $columnName === "name") {
      // validations for nullable foreign keys and name field
      $validationRequiredExpr = new New_(new Name("ValidationRequired"));
      $validationsArray = new Array_([new ArrayItem($validationRequiredExpr)]);
      $args[] = new Arg(
        $validationsArray,
        false,
        false,
        [],
        new Identifier("validations")
      );
    }

    // Create new Property(...) expression using short name (Property is imported)
    $propertyExpr = new New_(
      new Name("Property"),
      $args
    );

    $propertiesArray = $this->getDescribePropertiesArray();
    $propertiesArray->items[] = $propertyExpr;
  }

  /**
   * Get all columns from the Dbo
   */
  private function getAllColumns() {
    $namespace = $this->getModelNamespace();
    $className = $this->getModelClassName();
    if (!$namespace || !$className) {
      return [];
    }
    $basename = preg_replace("/Model$/", "", $className);
    if (!$basename) {
      return [];
    }
    // Remove \Model from namespace to get the base namespace for Dbo
    $dboNamespace = preg_replace("/\\\\Model$/", "", $namespace);
    if (!$dboNamespace) {
      $dboNamespace = $namespace;
    }
    try {
      $dbo = DbGeneratorModel::getDbo($dboNamespace, $basename);
      return $dbo->getColumns();
    } catch (\Exception $e) {
      return [];
    }
  }

  /**
   * Check if model extends BaseObjectModel (primaryObjectId)
   */
  private function isPrimaryObjectId() {
    $class = $this->_phpParser->getClass();
    if ($class->extends) {
      $extendsName = $class->extends->toString();
      return strpos($extendsName, "BaseObjectModel") !== false;
    }
    return false;
  }

  /**
   * Get the enum class name for a field (e.g., "state" -> "Framework\Signin\Enum\SigninCredentialStateEnum")
   */
  private function getEnumClassForField($fieldName) {
    // Only generate enums for "state" fields for now
    if ($fieldName !== "state") {
      return null;
    }

    $namespace = $this->getModelNamespace();
    $className = $this->getModelClassName();

    if (!$namespace || !$className) {
      return null;
    }

    // Extract model name (e.g., "SigninCredentialModel" -> "SigninCredential")
    $modelName = preg_replace("/Model$/", "", $className);
    if (!$modelName) {
      return null;
    }

    return $namespace . "\\Enum\\" . $modelName . "StateEnum";
  }

  /**
   * Get the namespace of the model class
   */
  private function getModelNamespace(): ?string {
    $namespace = $this->_phpParser->getNamespace();
    if ($namespace && $namespace->name) {
      return implode("\\", $namespace->name->getParts());
    }
    return null;
  }

  /**
   * Get the class name of the model
   */
  private function getModelClassName(): ?string {
    $class = $this->_phpParser->getClass();
    if ($class && $class->name) {
      return $class->name->name;
    }
    return null;
  }

  /**
   * Generate enum file for a field
   */
  private function generateEnumForField($fieldName, $enumClass) {
    if ($fieldName !== "state") {
      return false; // Only generate StateEnum for now
    }

    $namespace = $this->getModelNamespace();
    $className = $this->getModelClassName();

    if (!$namespace || !$className) {
      return false;
    }

    // Extract model name (e.g., "SigninCredentialModel" -> "SigninCredential")
    $modelName = preg_replace("/Model$/", "", $className);
    if (!$modelName) {
      return false;
    }

    $enumName = $modelName . "StateEnum";
    $enumNamespace = $namespace . "\\Enum";

    // Get directory path for enum
    $enumDir = $this->_getNamespaceDir($namespace) . "Enum/";
    $enumFile = $enumDir . $enumName . ".php";

    // Don't overwrite if file exists
    if (file_exists($enumFile)) {
      return false;
    }

    $enumContent = $this->_generateStateEnumContent($enumNamespace, $enumName);

    FileUtil::mkdir($enumDir);
    FileUtil::put($enumFile, $enumContent);
    WebApplication::addNotify('Successfully added the file ' . basename($enumFile));

    return true;
  }

  /**
   * Generate the content for the StateEnum class
   */
  private function _generateStateEnumContent($namespace, $enumName) {
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

  public function getDescribePropertiesArray(): Array_ {
    $describeAttr = $this->getDescribeAttribute();

    // Find the "properties" argument
    foreach ($describeAttr->args as $arg) {
      if ($arg instanceof Arg) {
        // Check if this is the "properties" named argument
        if ($arg->name && $arg->name->name === "properties") {
          if ($arg->value instanceof Array_) {
            return $arg->value;
          }
        }
        // Or if it's the first positional argument (properties is first)
        if (!$arg->name && $arg->value instanceof Array_) {
          return $arg->value;
        }
      }
    }

    throw new Exception("Failed to locate properties array in #[Describe] attribute");
  }
}