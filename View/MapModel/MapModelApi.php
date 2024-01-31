<?php

namespace Utility\View\MapModel;

use Exception;
use Framework\Api\ApiResponse;
use Framework\Arry\Arry;
use Framework\Core\View;
use Framework\Core\WebApplication;
use Framework\PhpParser\PhpParser;
use Framework\Util\FileUtil;
use Framework\Util\LangUtil;
use Framework\Util\StringUtil;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Utility\Model\GeneratorModel;
use Utility\Model\ModelDescribeGeneratorModel;
use Utility\Model\ModelGeneratorModel;


class MapModelApi extends View {

  public function __construct() {
    $this->disableAuthorization();
  }

  /**
   * @suppresswarnings
   */
  public function init() {
    try {
      $response = new ApiResponse();
      $sourceModel = $this->request("sourceModel");
      $sourceModelColumn = $this->request("sourceModelColumn");
      $referenceModelColumn = $this->request("referenceModelColumn");
      $referenceModel = $this->request("referenceModel");
      $referenceModelPlual = LangUtil::plural($referenceModel);
      $joiners = (array)$this->request("joiners");
      $sourceNamespace = $this->request("sourceNamespace");
      $mapChild = $this->request("relationship") === "child";
      $sourceModelHandler = ModelGeneratorModel::getHandlerClass($sourceModel);
      $dir = ModelGeneratorModel::getNamespaceDir($sourceNamespace);
      $modelFile = $dir . "Model/" . ModelGeneratorModel::getModelClass($sourceModel) . ".php";
      $handlerFile = $dir . "Handler/" . $sourceModelHandler . ".php";
      $referenceName = $this->post("object_name");

      if (!$referenceName) {
        throw new Exception("Invalid object name");
      }

      $pascalSourceModelColumn = StringUtil::pascalize($sourceModelColumn);
      $referenceNamePlual = LangUtil::plural($referenceName);
      $pluralReferenceName = LangUtil::getPluralString($referenceName);
      $pascalPluralReferenceName = StringUtil::pascalize($pluralReferenceName);
      $referenceNameSetFunction = "set" . StringUtil::pascalize($mapChild ? $referenceName : $pluralReferenceName);
      $referenceNameGetFunction = "get" . StringUtil::pascalize($mapChild ? $referenceName : $pluralReferenceName);
      $referenceKey = $mapChild ? $referenceName : $pluralReferenceName;
      $pascalReferenceKey = StringUtil::pascalize($referenceKey);
      $camelizeReferenceKey = StringUtil::camelize($referenceKey);
      $setFunctionType = $mapChild ? "?" . GeneratorModel::getModelClassname($referenceModel) . " \$value" : "\$value";

      $referenceNameSet = "  /**
   * @return static
   */
  public function {$referenceNameSetFunction}({$setFunctionType}) { 
    return \$this->data(\"{$camelizeReferenceKey}\",\$value);
  }";
      $whereColumn = StringUtil::snakeize($referenceModelPlual) . "." . $referenceModelColumn;

      if ($joiner = value($joiners, 0)) {
        $whereColumn = $joiner["table"] . "." . $joiner["source_column"];
      }

      $getFunctionType = $mapChild ? ": ?" . GeneratorModel::getModelClassname($referenceModel) : "";
      $referenceNameGet = "";

      if (!$mapChild)
        $referenceNameGet = "
  /**
   * @return " . GeneratorModel::getModelClassname($referenceModel) . "[]
   */";

      $referenceNameGet .= "
  public function {$referenceNameGetFunction}(\$handler = false){$getFunctionType} {
    if(\$handler && \$this->get{$pascalSourceModelColumn}() && (!\$this->hasData(\"{$camelizeReferenceKey}\") || \$handler instanceof Handler))
      \$this->data(
        \"{$camelizeReferenceKey}\",
        {$sourceModelHandler}::create{$pascalReferenceKey}Handler(\$handler)
          ->where(\"{$whereColumn}\",\"=\",\$this->get{$pascalSourceModelColumn}())
          ->" . ($mapChild ? "get" : "gets") . "()
        );

    return " . ($mapChild ? "" : "(array)") . "\$this->data(\"{$camelizeReferenceKey}\");
  }
  ";
      $this->_saveModel($camelizeReferenceKey, $modelFile, $referenceNameSetFunction, $referenceNameGetFunction, $referenceNameSet, $referenceNameGet, $sourceNamespace, $sourceModelHandler);


      $function = $mapChild ? "mapChild" : "mapChildren";
      $parentObjectFunction = "set" . StringUtil::pascalize($mapChild ? $referenceName : $referenceNamePlual);
      $childReferenceColumn = LangUtil::plural(StringUtil::snakeize($referenceModel)) . "." . $referenceModelColumn;
      foreach (array_reverse($joiners) as $joiner) {
        $childReferenceColumn = $joiner["table"] . "." . $joiner["source_column"];
      }

      $phpParser = new PhpParser($handlerFile);
      $methodGets = $phpParser->getMethod("gets");
      $arrayVariable = "models";

      if ($methodGets) {
        foreach ($methodGets->stmts as $stmt) {
          if ($stmt instanceof Expression) {
            if ($stmt->expr instanceof Assign) {
              /**
               * @var Assign
               */
              $expr = $stmt->expr;
              if ($expr->expr instanceof Array_) {
                if ($expr->var instanceof Variable) {
                  /**
                   * @var Variable 
                   */
                  $var = $expr->var;
                  $arrayVariable = $var->name;
                }
              }
            }
          }
        }
      }

      $getsCode = "\$this->{$function}(\${$arrayVariable}, \$this->handler(\"{$camelizeReferenceKey}\"), \"get{$pascalSourceModelColumn}\", \"{$parentObjectFunction}\", \"{$childReferenceColumn}\");";

      $referenceModelPasalize = StringUtil::pascalize($referenceModel);
      $joins = "";
      $lastTable = $referenceModelPlual;
      $lastColumn = $referenceModelColumn;
      foreach (array_reverse($joiners) as $joiner) {
        $joins .= "->join(\"{$lastTable}\", \"{$joiner["table"]}\", \"{$lastColumn}\", \"{$joiner["reference_column"]}\")";
        $lastTable = $joiner["table"];
        $lastColumn = $joiner["source_column"];
      }
      $defaultBoolean = $mapChild ? "false" : "true";

      $loadFunctionCode = "
  /**
   * @return static
   */
  public function load{$pascalPluralReferenceName}(\$handler = null) {
    \$this->handler(\"{$camelizeReferenceKey}\", \$this->create{$pascalReferenceKey}Handler(\$handler));
    return \$this;
  }";

      $createFunctionCode = "
      
  /**
   * @return {$referenceModelPasalize}Handler
   */
  public static function create{$pascalReferenceKey}Handler(\$handler = null): {$referenceModelPasalize}Handler {
    \$handler = \$handler instanceof {$referenceModelPasalize}Handler ? \$handler : {$referenceModelPasalize}Handler::create({$defaultBoolean});
    return \$handler{$joins};
  }";

      $this->_saveHandler($handlerFile, "load$pascalPluralReferenceName", "create{$pascalReferenceKey}Handler", $getsCode, $loadFunctionCode, $createFunctionCode);

      $response->success();
    } catch (Exception $e) {
      $response->success(false);
      WebApplication::addError($e->getMessage());
      $response->data("errors", WebApplication::getErrorMessages());
    }

    $response
      ->data("warnings", WebApplication::getWarningMessages())
      ->data("messages", WebApplication::getNotifyMessages())
      ->render();
  }

  private function _saveModel($name, $modelFile, $referenceNameSetFunction, $referenceNameGetFunction, $referenceNameSet, $referenceNameGet, $sourceNamespace, $sourceModelHandler) {
    $phpParser = new phpParser($modelFile);

    $namespace = $phpParser->getNamespace();
    if ($namespace) {
      $exists = $this->_useExists($namespace, [$sourceNamespace, "Handler", $sourceModelHandler]);
      if (!$exists) {
        $this->_useInsert($namespace, [$sourceNamespace, "Handler", $sourceModelHandler]);
      }

      $exists = $this->_useExists($namespace, ["Framework", "Core", "Handler"]);
      if (!$exists) {
        $this->_useInsert($namespace, ["Framework", "Core", "Handler"]);
      }
    }

    $setExists = $phpParser->getClass()->getMethod($referenceNameSetFunction);
    $getExists = $phpParser->getClass()->getMethod($referenceNameGetFunction);

    if (!$getExists && !$setExists) {
      $index = Arry::create($phpParser->getClass()->stmts)
        ->indexOf(function ($item) {
          return $item instanceof ClassMethod && $item->name->name === "__construct";
        }) + 1;

      $node = (new BuilderFactory())->property("referenceNameSetFunction")
        ->getNode();
      array_splice($phpParser->getClass()->stmts, $index, 0, [$node]);

      $node = (new BuilderFactory())->property("referenceNameGetFunction")
        ->getNode();
      array_splice($phpParser->getClass()->stmts, $index, 0, [$node]);

      $code = $phpParser->getCode();

      $code = str_replace('public $referenceNameSetFunction;', $referenceNameSet, $code);
      $code = str_replace('public $referenceNameGetFunction;', $referenceNameGet, $code);

      FileUtil::put($modelFile, $code);

      try {
        (new ModelDescribeGeneratorModel($modelFile))
          ->appendDescribe($name, "data")
          ->saveCode();
      } catch (Exception $e) {
        WebApplication::addWarning($e->getMessage());
      }
    }
  }

  private function _saveHandler($handlerFile, $loadFunctionName, $createFunctionName, $getsCode, $loadFunctionCode, $createFunctionCode) {

    $phpParser = new PhpParser($handlerFile);

    $loadExists = $phpParser->getClass()->getMethod($loadFunctionName);
    $createExists = $phpParser->getClass()->getMethod($createFunctionName);

    if (!$loadExists && !$createExists) {
      $node = (new BuilderFactory())->property("loadFunctionName")
        ->getNode();
      $phpParser->getClass()->stmts[] = $node;

      $node = (new BuilderFactory())->property("createFunctionName")
        ->getNode();
      $phpParser->getClass()->stmts[] = $node;

      $stmt = $this->getMethod($phpParser, "mapModels");

      if (!$stmt)
        $stmt = $this->getMethod($phpParser, "gets");

      if (!$stmt) {
        throw new Exception("Failed to locate gets() or mapModels() methods");
      }

      $index = Arry::create($stmt->stmts)
        ->indexOf(function ($item) {
          return $item instanceof Return_;
        });

      $node = (new BuilderFactory())->var("getsCode");

      array_splice($stmt->stmts, $index, 0, [$node]);

      $code = $phpParser->getCode();
      $code = str_replace('$getsCode', $getsCode, $code);
      $code = str_replace('public $loadFunctionName;', $loadFunctionCode, $code);
      $code = str_replace('public $createFunctionName;', $createFunctionCode, $code);

      FileUtil::put($handlerFile, $code);
    }
  }

  public function getMethod($phpParser, $name) {
    foreach ($phpParser->getClass()->stmts as $stmt) {
      if ($stmt instanceof ClassMethod) {

        if ($stmt->name->name === $name) {
          return $stmt;
        }
      }
    }

    return null;
  }

  public function hasCode($content, $code) {
    $content = preg_replace("/\\s/", "", $content);
    $code = preg_replace("/\\s/", "", $code);

    return strpos($content, $code);
  }

  private function _useExists(Namespace_ $namespace, $classParts) {
    $class = implode("\\", $classParts);
    return Arry::create($namespace->stmts)
      ->filter(function ($stmt) {
        return $stmt instanceof Use_;
      })
      ->exists(function ($stmt) use ($class) {
        return Arry::create($stmt->uses)
          ->exists(function ($use) use ($class) {
            return strpos((string)$use->name, $class) !== false;
          });
      });
  }

  private function _useInsert(Namespace_ &$namespace, $classParts) {
    $index = Arry::create($namespace->stmts)
      ->findIndex(function ($stmt) {
        return $stmt instanceof Use_;
      });

    array_splice($namespace->stmts, $index + 1, 0, [new Use_([new UseUse(new Name($classParts))])]);
  }
}