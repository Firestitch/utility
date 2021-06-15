<?php

namespace Utility\View\MapModel;

use Exception;
use Framework\Core\View;
use Framework\Core\WebApplication;
use Framework\Db\DB;
use Framework\Api\ApiResponse;
use Framework\Util\FileUtil;
use Framework\Util\LangUtil;
use Framework\Util\StringUtil;
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
    $joinerList = Db::getInstance()->getUtility()->getTableNames();
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

    try {
      $response = new ApiResponse();
      $debug = false;
      $sourceModel = strtolower($this->request("source_model"));
      $sourceModelColumn = $this->request("source_model_column");
      $referenceModelColumn = $this->request("reference_model_column");
      $referenceModel = strtolower($this->request("reference_model"));
      $referenceModelPlual = LangUtil::plural($referenceModel);
      $joiners = (array) $this->request("joiners");
      $mapChild = $this->request("relationship") == "child";
      $application = $this->request("application");
      $sourceModelHandler = ModelGeneratorModel::getHandlerClass($sourceModel);
      $dir = $application ? DIR_INSTANCE . $application . "/" : WebApplication::getMainApplicationDirectory();
      $cmodelModelFile = $dir . "Model/" . ModelGeneratorModel::getModelClass($sourceModel) . ".php";
      $hmodelModelFile = $dir . "Handler/" . $sourceModelHandler . ".php";
      $warnings = [];
      $referenceName = $this->post("object_name_custom");
      if (!$sourceModelColumn) {
        throw new Exception("Invalid sourse column");
      }
      if ($this->post("object_name") == "source" || $this->post("object_name") == "reference") {
        $referenceName = preg_replace("/_id\$/", "", $this->post("object_name") == "source" ? $sourceModelColumn : $referenceModel);
      }
      $referenceNamePlual = LangUtil::plural($referenceName);
      $pluralReferenceName = LangUtil::getPluralString($referenceName);
      $referenceNameSetFunction = "set_" . ($mapChild ? $referenceName : $pluralReferenceName);
      $referenceNameGetFunction = "get_" . ($mapChild ? $referenceName : $pluralReferenceName);
      $referenceKey = $mapChild ? $referenceName : $pluralReferenceName;
      $setFunctionType = $mapChild ? "?" . GeneratorModel::getModelClassname($referenceModel) . " " : "";
      $referenceNameSet = "\n  public function {$referenceNameSetFunction}({$setFunctionType}\$value) { return \$this->data(\"{$referenceKey}\",\$value); }\n  ";
      $whereColumn = $referenceModelPlual . "." . $referenceModelColumn;
      $lastTable = $referenceModelPlual;
      $lastColumn = $referenceModelColumn;
      if ($joiner = value($joiners, 0)) {
        $whereColumn = $joiner["table"] . "." . $joiner["source_column"];
      }
      $getFunctionType = $mapChild ? ": ?" . GeneratorModel::getModelClassname($referenceModel) : "";
      $referenceNameGet = "\n  public function {$referenceNameGetFunction}(\$handler = false){$getFunctionType} {\n    if(\$handler && !\$this->has_data(\"{$referenceKey}\") && \$this->get_{$sourceModelColumn}())\n      \$this->data(\n        \"{$referenceKey}\",\n        {$sourceModelHandler}::create_{$referenceKey}_handler(\$handler)\n          ->where(\"{$whereColumn}\",\"=\",\$this->get_{$sourceModelColumn}())\n          ->" . ($mapChild ? "get" : "gets") . "()\n        );\n    return " . ($mapChild ? "" : "(array)") . "\$this->data(\"{$referenceKey}\");\n  }\n";
      if (!$referenceName) {
        throw new Exception("Invalid reference name");
      }
      $cmodelContent = FileUtil::get($cmodelModelFile);
      if (stripos($cmodelContent, "n " . $referenceNameSetFunction . "(") === false) {
        if (preg_match("/^(.*?[^}]+})(.*?function\\s+save\\(.*)\$/ism", $cmodelContent, $matches)) {
          $cmodelContent = value($matches, 1) . $referenceNameSet . $referenceNameGet . value($matches, 2);
          if ($debug) {
            p("CMODEL SET, GET", $referenceNameSet . $referenceNameGet);
          }
        }
      } else {
        $warnings[] = "The CMODEL_" . strtoupper($sourceModel) . "->" . $referenceNameSetFunction . "() function is already generated";
      }
      if (!$debug) {
        try {
          FileUtil::put($cmodelModelFile, $cmodelContent);
        } catch (Exception $e) {
          throw new Exception("There was a problem trying to update the complex model");
        }
      }
      $hmodelContent = FileUtil::get($hmodelModelFile);
      if (stripos($hmodelContent, "load_" . $pluralReferenceName) === false) {
        if (preg_match("/^(.*?)(return\\s+\\\$(:?" . $sourceModel . "_)?cmodels;.*)\$/ism", $hmodelContent, $matches)) {
          $function = $mapChild ? "map_child" : "map_children";
          $parentObjectFunction = "set_" . ($mapChild ? $referenceName : $referenceNamePlual);
          $childReferenceColumn = LangUtil::plural($referenceModel) . "." . $referenceModelColumn;
          $cmodels = stripos($hmodelContent, 'return $cmodel') === false ? '$' . $sourceModel . '_cmodels' : '$cmodels';
          foreach (array_reverse($joiners) as $joiner) {
            $childReferenceColumn = $joiner["table"] . "." . $joiner["source_column"];
          }
          $code = "\n    \$this->{$function}({$cmodels}, \$this->handler(\"{$referenceKey}_handler\"), \"get_{$sourceModelColumn}\", \"{$parentObjectFunction}\", \"{$childReferenceColumn}\");\n\n    ";
          if (!$this->hasCode($hmodelContent, $code)) {
            if ($debug) {
              p("Handler MAP", $code);
            }
            $hmodelContent = value($matches, 1) . $code . value($matches, 2);
          }
        }
        $referenceModelPasalize = StringUtil::pascalize($referenceModel);
        if (preg_match("/(.*)(}[\\s\n]*)\$/ism", $hmodelContent, $matches)) {
          $joins = "";
          $lastTable = $referenceModelPlual;
          $lastColumn = $referenceModelColumn;
          foreach (array_reverse($joiners) as $joiner) {
            $joins .= "->join(\"{$lastTable}\", \"{$joiner["table"]}\", \"{$lastColumn}\", \"{$joiner["reference_column"]}\")";
            $lastTable = $joiner["table"];
            $lastColumn = $joiner["source_column"];
          }
          $defaultBoolean = $mapChild ? "false" : "true";
          $code = "\n  public function load_{$pluralReferenceName}(\$handler = null) {\n    return \$this->handler(\"{$referenceKey}_handler\", \$this->create_{$referenceKey}_handler(\$handler));\n  }\n\n  public static function create_{$referenceKey}_handler(\$handler = null): {$referenceModelPasalize}Handler {\n    \$handler = \$handler instanceof {$referenceModelPasalize}Handler ? \$handler : {$referenceModelPasalize}Handler::create({$defaultBoolean});\n    return \$handler{$joins};\n  }\n}\n";
          // foreach (array_reverse($joiners) as $joiner) {
          //   $reference_name_get .= "->join(\"" . $last_table . "\",\"" . $joiner["table"] . "\",\"" . $last_column . "\",\"" . $joiner["reference_column"] . "\")\n\t\t\t\t\t\t\t\t\t\t\t\t";
          //   $last_table = $joiner["table"];
          //   $last_column = $joiner["source_column"];
          // }
          // $code = "\n\t\tpublic function load_" . $plural_reference_name . "(\$handler=null) {\n" .
          //   "\t\t\treturn \$this->handler(\"" . $reference_name . "_handler\",\$handler ? \$handler : " . STRING_UTIL::pascalize($reference_model) . "Handler::create());\n" .
          //   "\t\t}\n\t}";
          if ($code && !$this->hasCode($hmodelContent, $code)) {
            $hmodelContent = value($matches, 1) . $code;
            if ($debug) {
              p("Handler Load", $code);
            }
          }
        }
        if (!$debug) {
          try {
            FileUtil::put($hmodelModelFile, $hmodelContent);
          } catch (Exception $e) {
            throw new Exception("There was a problem trying to update the handler model");
          }
        }
      } else {
        $warnings[] = "The Handler" . strtoupper($sourceModel) . " load_" . $pluralReferenceName . "() function is already generated";
      }
      $response->success();
    } catch (Exception $e) {
      WebApplication::addError($e->getMessage());
      $response->data("errors", WebApplication::getErrorMessages());
    }
    $response->data("warnings", WebApplication::getWarningMessages())->data("messages", WebApplication::getNotifyMessages())->render();
  }
  public function hasCode($content, $code) {
    $content = preg_replace("/\\s/", "", $content);
    $code = preg_replace("/\\s/", "", $code);

    return strpos($content, $code);
  }
}
