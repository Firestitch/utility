<?php

namespace Utility\View\DbModel;

use Exception;
use Framework\Api\ApiResponse;
use Framework\Core\View;
use Framework\Core\WebApplication;
use Framework\Model\PathModel;
use Utility\Model\DbGeneratorModel;
use Utility\Model\GeneratorModel;
use Utility\Model\ModelDescribeGeneratorModel;
use Utility\Model\ModelGeneratorModel;
use Utility\Model\ModelTraitGeneratorModel;


class DbModelApi extends View {

  public function __construct() {
    $this->disableAuthorization();
  }

  public function init() {
    if ($this->isPost()) {
      try {
        $response = new ApiResponse();
        $tablename = strtolower($this->post("tablename"));
        $namespace = trim($this->post("namespace"), "\\");
        $name = $this->post("name");
        $pascalName = $this->post("pascalName");

        $override = $this->post("override");
        $objects = (array) $this->post("objects");
        $dir = $this->_getDir(trim($namespace, "\\"));

        if ($this->isFormValid($tablename, $name)) {
          $dbGeneratorModel = new DbGeneratorModel($dir);
          $keyCount = $dbGeneratorModel->getKeyCount($tablename);
          $primaryObjectId = DbGeneratorModel::isPrimaryObjectId($tablename);

          if (!$keyCount) {
            WebApplication::addWarning("There are no key columns for this table. If this is the intended design, please disregard this warning");
          }

          if (in_array("dbq", $objects)) {
            $hasSuccess = $dbGeneratorModel->createDbq($tablename, $namespace, $name, $pascalName, $override);
            $classname = DbGeneratorModel::getDbqClass($namespace, $pascalName);
            if ($hasSuccess) {
              WebApplication::addNotify('Successfully created ' . $classname);
            }
          }

          if (in_array("dbo", $objects)) {
            $hasSuccess = $dbGeneratorModel->createDbo($tablename, $namespace, $name, $pascalName, $override);
            $classname = DbGeneratorModel::getDboClass($namespace, $pascalName);
            if ($hasSuccess) {
              WebApplication::addNotify('Successfully created ' . $classname);
            }
          }

          if (in_array("trait", $objects)) {
            $modelTraitGeneratorModel = new ModelTraitGeneratorModel($dir);
            $modelTraitGeneratorModel->createTrait($tablename, $namespace, $pascalName, $override);
            $classname = $modelTraitGeneratorModel::getTraitName($namespace, $pascalName);
            WebApplication::addNotify('Successfully created ' . $classname);
          }

          $modelGeneratorModel = new ModelGeneratorModel($namespace, $name, $pascalName, $dir, ["primaryObjectId" => $primaryObjectId]);
          if (in_array("cmodel", $objects)) {
            if (!is_file($modelGeneratorModel->getComplexModelFile()) || $override) {
              $modelGeneratorModel->generateComplexModel();
              WebApplication::addNotify('Successfully created Model ' . GeneratorModel::getModelClassname($pascalName));
            } else {
              WebApplication::addWarning("The model " . $modelGeneratorModel->getComplexModelFile() . " already exists");
            }
          }

          if (in_array("hmodel", $objects)) {
            if (!is_file($modelGeneratorModel->getHandlerModelFile()) || $override) {
              $modelGeneratorModel->generateHandlerModel();
              WebApplication::addNotify('Successfully created Handler ' . GeneratorModel::getHandlerClassname($pascalName));
            } else {
              WebApplication::addWarning("The handler " . $modelGeneratorModel->getHandlerModelFile() . " already exists");
            }
          }

          if (in_array("trait", $objects)) {
            $modelFile = GeneratorModel::getModelFile($pascalName, $dir);
            try {
              $modelDescribeGeneratorModel = new ModelDescribeGeneratorModel($modelFile);
              $modelDescribeGeneratorModel->update($tablename);
              WebApplication::addNotify('Successfully updated describe()');
            } catch (Exception $e) {
              WebApplication::addWarning($e->getMessage());
            }
          }
        }

        $response->success();
      } catch (Exception $e) {
        WebApplication::addError($e->getMessage());
        $response->data("errors", WebApplication::getErrorMessages());
        $response->exception($e);
      }

      $response
        ->data("warnings", WebApplication::getWarningMessages())
        ->data("messages", WebApplication::getNotifyMessages())
        ->render();
    }
  }

  public function isFormValid($tablename, $name) {
    if (!$tablename) {
      throw new Exception("Invalid tablename");
    }
    if (!$name) {
      throw new Exception("Invalid name");
    }

    return true;
  }

  private function _getDir($namespace) {
    $path = "";
    $dir = "";

    if (preg_match('/^Backend(?:$|\\\)(.*)/', $namespace, $matches)) {
      $path = value($matches, 1);
      $dir = PathModel::getBackendDir();
    }

    if (preg_match("/^Framework(?:$|\\\)(.*)/", $namespace, $matches)) {
      $path = value($matches, 1);
      $dir = PathModel::getFrameworkDir();
    }

    if (preg_match("/^Utility(?:$|\\\)(.*)/", $namespace, $matches)) {
      $path = value($matches, 1);
      $dir = PathModel::getInstanceDir();
    }

    if (!$dir) {
      throw new Exception("Invalid namespace");
    }

    $path = trim($path, "\\");
    if ($path) {
      $dir .= str_replace("\\", "/", "/" . $path);
    }

    return $dir . "/";
  }
}