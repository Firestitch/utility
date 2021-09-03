<?php

namespace Utility\View\MapModel;

use Exception;
use Framework\Api\ApiResponse;
use Framework\Arry\Arry;
use Framework\Core\View;
use Framework\Core\WebApplication;
use Framework\Db\Db;
use Framework\Util\FileUtil;
use Framework\Util\LangUtil;
use Framework\Util\StringUtil;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Utility\Model\GeneratorModel;
use Utility\Model\ModelDescribeGeneratorModel;
use Utility\Model\ModelGeneratorModel;


class MapModelView extends View {

  protected $_referenceModel = "";
  protected $_joiner = "";
  protected $_model = null;
  protected $_sourceModelColumn = "";

  public function __construct() {
    $this
      ->setTemplate("./MapModelTemplate.php")
      ->setStyle("./MapModel.scss")
      ->setForm("javascript:;", false, "form-relation")
      ->disableAuthorization();
  }

  public function init() {
    $this->processPost();

    $joinerList = Db::getInstance()
      ->getUtility()
      ->getTableNames();

    $this->setVar("joiner", $this->_joiner);
    $this->setVar("referenceModel", $this->_referenceModel);
    $this->setVar("joinerList", $joinerList);
    $this->setVar("sourceModelColumn", $this->_sourceModelColumn);
    $this->setVar("model", $this->_model);
  }

  public function processPost() {

    if (!$this->isPost()) {
      return;
    }

    try {
      $response = new ApiResponse();
      $sourceModel = strtolower($this->request("source_model"));
      $sourceModelColumn = $this->request("source_model_column");
      $referenceModelColumn = $this->request("reference_model_column");
      $referenceModel = $this->request("reference_model");
      $referenceModelPlual = LangUtil::plural($referenceModel);
      $joiners = (array)$this->request("joiners");
      $sourceNamespace = $this->request("source-namespace");
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

      $referenceNameSet = "public function {$referenceNameSetFunction}({$setFunctionType}) { return \$this->data(\"{$camelizeReferenceKey}\",\$value); }";
      $whereColumn = StringUtil::snakeize($referenceModelPlual) . "." . $referenceModelColumn;

      if ($joiner = value($joiners, 0)) {
        $whereColumn = $joiner["table"] . "." . $joiner["source_column"];
      }

      $getFunctionType = $mapChild ? ": ?" . GeneratorModel::getModelClassname($referenceModel) : "";
      $referenceNameGet = "";

      if (!$mapChild)
        $referenceNameGet = "  /**
   * @return " . GeneratorModel::getModelClassname($referenceModel) . "[]
   */";

      $referenceNameGet .= "
  public function {$referenceNameGetFunction}(\$handler = false){$getFunctionType} {
    if(\$handler && !\$this->hasData(\"{$camelizeReferenceKey}\") && \$this->get{$pascalSourceModelColumn}())
      \$this->data(
        \"{$camelizeReferenceKey}\",
        {$sourceModelHandler}::create{$pascalReferenceKey}Handler(\$handler)
          ->where(\"{$whereColumn}\",\"=\",\$this->get{$pascalSourceModelColumn}())
          ->" . ($mapChild ? "get" : "gets") . "()
        );

    return " . ($mapChild ? "" : "(array)") . "\$this->data(\"{$camelizeReferenceKey}\");
  }
  ";
      $this->_saveModel($camelizeReferenceKey, $modelFile, $referenceNameSetFunction, $referenceNameGetFunction, $referenceNameSet, $referenceNameGet);

      $function = $mapChild ? "mapChild" : "mapChildren";
      $parentObjectFunction = "set" . StringUtil::pascalize($mapChild ? $referenceName : $referenceNamePlual);
      $childReferenceColumn = LangUtil::plural(StringUtil::snakeize($referenceModel)) . "." . $referenceModelColumn;
      foreach (array_reverse($joiners) as $joiner) {
        $childReferenceColumn = $joiner["table"] . "." . $joiner["source_column"];
      }

      $modelParser = new ModelParser($handlerFile);
      $methodGets = $modelParser->getMethod("gets");
      $arrayVariable = "models";

      if ($methodGets) {
        foreach ($methodGets->stmts as $stmt) {
          if ($stmt instanceof Expression && $stmt->expr instanceof Assign && $stmt->expr->expr instanceof Array_) {
            $arrayVariable = $stmt->expr->var->name;
          }
        }
      }

      $getsCode = "
    \$this->{$function}(\${$arrayVariable}, \$this->handler(\"{$camelizeReferenceKey}\"), \"get{$pascalSourceModelColumn}\", \"{$parentObjectFunction}\", \"{$childReferenceColumn}\");";

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

  private function _saveModel($name, $modelFile, $referenceNameSetFunction, $referenceNameGetFunction, $referenceNameSet, $referenceNameGet) {
    $modelParser = new ModelParser($modelFile);

    $setExists = $modelParser->getClass()->getMethod($referenceNameSetFunction);
    $getExists = $modelParser->getClass()->getMethod($referenceNameGetFunction);

    if (!$getExists && !$setExists) {
      $index = Arry::create($modelParser->getClass()->stmts)
        ->indexOf(function ($item) {
          return $item instanceof ClassMethod && $item->name->name === "__construct";
        }) + 1;

      $node = (new BuilderFactory())->property("referenceNameSetFunction")
        ->getNode();
      array_splice($modelParser->getClass()->stmts, $index, 0, [$node]);

      $node = (new BuilderFactory())->property("referenceNameGetFunction")
        ->getNode();
      array_splice($modelParser->getClass()->stmts, $index, 0, [$node]);

      $code = $modelParser->getCode();

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

    $modelParser = new ModelParser($handlerFile);

    $loadExists = $modelParser->getClass()->getMethod($loadFunctionName);
    $createExists = $modelParser->getClass()->getMethod($createFunctionName);

    if (!$loadExists && !$createExists) {
      $node = (new BuilderFactory())->property("loadFunctionName")
        ->getNode();
      $modelParser->getClass()->stmts[] = $node;

      $node = (new BuilderFactory())->property("createFunctionName")
        ->getNode();
      $modelParser->getClass()->stmts[] = $node;

      foreach ($modelParser->getClass()->stmts as $stmt) {
        if ($stmt instanceof ClassMethod && $stmt->name->name === "gets") {
          $index = Arry::create($stmt->stmts)
            ->indexOf(function ($item) {
              return $item instanceof Return_;
            });

          $node = (new BuilderFactory())->var("getsCode");

          array_splice($stmt->stmts, $index, 0, [$node]);
        }
      }

      $code = $modelParser->getCode();
      $code = str_replace('$getsCode', $getsCode, $code);
      $code = str_replace('public $loadFunctionName;', $loadFunctionCode, $code);
      $code = str_replace('public $createFunctionName;', $createFunctionCode, $code);

      FileUtil::put($handlerFile, $code);
    }
  }

  public function hasCode($content, $code) {
    $content = preg_replace("/\\s/", "", $content);
    $code = preg_replace("/\\s/", "", $code);

    return strpos($content, $code);
  }
}
