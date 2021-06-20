<?php

namespace Utility\View\MapModel;

use Exception;
use Framework\Api\ApiResponse;
use Framework\Arry\Arry;
use Framework\Core\View;
use Framework\Core\WebApplication;
use Framework\Db\DB;
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
use Utility\Model\ModelGeneratorModel;


class MapModelView extends View {

  protected $_referenceModel = "";
  protected $_joiner = "";
  protected $_model = null;
  protected $_sourceModelColumn = "";

  public function __construct() {
    $this->setTemplate("./MapModelTemplate.php");
    $this->setForm("javascript:;", false, "form-relation");
    $this->disableAuthorization();
  }

  public function init() {
    $this->processPost();
    $application = strtolower($this->request("application"));
    $appDir = $application ? DIR_INSTANCE . $application . "/" : WebApplication::getMainApplicationDirectory();
    $modelList = ModelGeneratorModel::getCmodels($appDir);
    $joinerList = Db::getInstance()
      ->getUtility()
      ->getTableNames();
    $this->setVar("joiner", $this->_joiner);
    $this->setVar("referenceModel", $this->_referenceModel);
    $this->setVar("modelList", $modelList);
    $this->setVar("joinerList", $joinerList);
    $this->setVar("sourceModelColumn", $this->_sourceModelColumn);
    $this->setVar("model", $this->_model);
  }

  public function processPost() {

    if (!$this->isPost()) {
      return;
    }

    $sourceModel = strtolower($this->request("source_model"));
    $sourceModelColumn = $this->request("source_model_column");
    $referenceModelColumn = $this->request("reference_model_column");
    $referenceModel = strtolower($this->request("reference_model"));
    $application = $this->request("application");

    $dir = $application ? DIR_INSTANCE . $application . "/" : WebApplication::getMainApplicationDirectory();
    $modelFile = $dir . "Model/" . ModelGeneratorModel::getModelClass($sourceModel) . ".php";

    try {
      $response = new ApiResponse();
      $debug = false;
      $sourceModel = strtolower($this->request("source_model"));
      $sourceModelColumn = $this->request("source_model_column");
      $referenceModelColumn = $this->request("reference_model_column");
      $referenceModel = strtolower($this->request("reference_model"));
      $referenceModelPlual = LangUtil::plural($referenceModel);
      $joiners = (array)$this->request("joiners");
      $mapChild = $this->request("relationship") == "child";
      $application = $this->request("application");
      $sourceModelHandler = ModelGeneratorModel::getHandlerClass($sourceModel);
      $dir = $application ? DIR_INSTANCE . $application . "/" : WebApplication::getMainApplicationDirectory();
      $modelFile = $dir . "Model/" . ModelGeneratorModel::getModelClass($sourceModel) . ".php";
      $handlerFile = $dir . "Handler/" . $sourceModelHandler . ".php";
      $referenceName = $this->post("object_name_custom");

      if (!$sourceModelColumn) {
        throw new Exception("Invalid sourse column");
      }
      if ($this->post("object_name") == "source" || $this->post("object_name") == "reference") {
        $referenceName = preg_replace("/_id\$/", "", $this->post("object_name") == "source" ? $sourceModelColumn : $referenceModel);
      }

      if (!$referenceName) {
        throw new Exception("Invalid reference name");
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

      $referenceNameSet = "public function {$referenceNameSetFunction}({$setFunctionType}) { return \$this->data(\"{$referenceKey}\",\$value); }
";
      $whereColumn = $referenceModelPlual . "." . $referenceModelColumn;

      if ($joiner = value($joiners, 0)) {
        $whereColumn = $joiner["table"] . "." . $joiner["source_column"];
      }

      $getFunctionType = $mapChild ? ": ?" . GeneratorModel::getModelClassname($referenceModel) : "";
      $referenceNameGet = "
  public function {$referenceNameGetFunction}(\$handler = false){$getFunctionType} {
    if(\$handler && !\$this->hasData(\"{$camelizeReferenceKey}\") && \$this->get{$pascalSourceModelColumn}())
      \$this->data(
        \"{$camelizeReferenceKey}\",
        {$sourceModelHandler}::create{$pascalReferenceKey}Handler(\$handler)
          ->where(\"{$whereColumn}\",\"=\",\$this->get{$pascalSourceModelColumn}())
          ->" . ($mapChild ? "get" : "gets") . "()
        );

    return " . ($mapChild ? "" : "(array)") . "\$this->data(\"{$camelizeReferenceKey}\");
  }";
      $this->_saveModel($modelFile, $referenceNameSetFunction, $referenceNameGetFunction, $referenceNameSet, $referenceNameGet);

      $function = $mapChild ? "mapChild" : "mapChildren";
      $parentObjectFunction = "set" . StringUtil::pascalize($mapChild ? $referenceName : $referenceNamePlual);
      $childReferenceColumn = LangUtil::plural($referenceModel) . "." . $referenceModelColumn;
      foreach (array_reverse($joiners) as $joiner) {
        $childReferenceColumn = $joiner["table"] . "." . $joiner["source_column"];
      }

      $modelParser = new ModelParser($handlerFile);
      $arrayVariable = "models";
      foreach ($modelParser->getMethod("gets")->stmts as $stmt) {
        if ($stmt instanceof Expression && $stmt->expr instanceof Assign && $stmt->expr->expr instanceof Array_) {
          $arrayVariable = $stmt->expr->var->name;
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
      WebApplication::addError($e->getMessage());
      $response->data("errors", WebApplication::getErrorMessages());
    }

    $response
      ->data("warnings", WebApplication::getWarningMessages())
      ->data("messages", WebApplication::getNotifyMessages())
      ->render();
  }

  private function _saveModel($modelFile, $referenceNameSetFunction, $referenceNameGetFunction, $referenceNameSet, $referenceNameGet) {
    $modelParser = new ModelParser($modelFile);

    $index = Arry::create($modelParser->getClass()->stmts)
        ->indexOf(function ($item) {
          return $item instanceof ClassMethod && $item->name->name === "__construct";
        }) + 1;

    if (!$modelParser->getClass()
      ->getMethod($referenceNameSetFunction)) {
      $node = (new BuilderFactory())->property("referenceNameSetFunction")
        ->getNode();
      array_splice($modelParser->getClass()->stmts, $index, 0, [$node]);
    }

    if (!$modelParser->getClass()
      ->getMethod($referenceNameGetFunction)) {
      $node = (new BuilderFactory())->property("referenceNameGetFunction")
        ->getNode();
      array_splice($modelParser->getClass()->stmts, $index, 0, [$node]);
    }

    $code = $modelParser->getCode();

    $code = str_replace('public $referenceNameSetFunction;', $referenceNameSet, $code);
    $code = str_replace('public $referenceNameGetFunction;', $referenceNameGet, $code);

    FileUtil::put($modelFile, $code);
  }

  private function _saveHandler($handlerFile, $loadFunctionName, $createFunctionName, $getsCode, $loadFunctionCode, $createFunctionCode) {

    $modelParser = new ModelParser($handlerFile);

    if (!$modelParser->getClass()
      ->getMethod($loadFunctionName)) {
      $node = (new BuilderFactory())->property("loadFunctionName")
        ->getNode();
      $modelParser->getClass()->stmts[] = $node;
    }

    if (!$modelParser->getClass()
      ->getMethod($createFunctionName)) {
      $node = (new BuilderFactory())->property("createFunctionName")
        ->getNode();
      $modelParser->getClass()->stmts[] = $node;
    }

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

  public function hasCode($content, $code) {
    $content = preg_replace("/\\s/", "", $content);
    $code = preg_replace("/\\s/", "", $code);

    return strpos($content, $code);
  }

}
