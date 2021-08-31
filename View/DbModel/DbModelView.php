<?php

namespace Utility\View\DbModel;

use Exception;
use Framework\Api\ApiResponse;
use Framework\Core\View;
use Framework\Core\WebApplication;
use Framework\Db\Db;
use Framework\Model\PathModel;
use Framework\Util\ArrayUtil;
use Utility\Model\DbGeneratorModel;
use Utility\Model\GeneratorModel;
use Utility\Model\ModelDescribeGeneratorModel;
use Utility\Model\ModelGeneratorModel;
use Utility\Model\ModelTraitGeneratorModel;


class DbModelView extends View {

  private $_classname = "";
  private $_tablename = "";
  private $_states = "";
  private $_createDbq = false;
  private $_createDbo = true;
  private $_override = true;

  public function __construct() {
    $this
      ->setTemplate("./DbModelTemplate.php")
      ->setStyle("./DbModel.scss")
      ->disableAuthorization();
  }

  public function init() {
    $this->_classname = $this->get("model");
    $this->_tablename = $this->get("table");

    $this->processPost();
    $dbUtility = Db::getInstance()->getUtility();

    $tablenameList = $dbUtility->getTableNames();
    $sql = "SELECT `table_name` FROM `information_schema`.`columns` WHERE `table_schema` = '" . Db::getInstance()
      ->getDbName() . "' AND `column_name` = 'state'";

    $stateColumnTables = ArrayUtil::getListFromArray(Db::getInstance()
      ->select($sql), "table_name");

    $this->setVar("tablenameList", $tablenameList);
    $this->setVar("classname", $this->_classname);
    $this->setVar("tablename", $this->_tablename);
    $this->setVar("states", $this->_states);
    $this->setVar("createDbq", $this->_createDbq);
    $this->setVar("createDbo", $this->_createDbo);
    $this->setVar("override", $this->_override);
    $this->setVar("stateColumnTables", $stateColumnTables);
  }

  public function processPost() {
    if ($this->isPost()) {
      try {
        $response = new ApiResponse();
        $tablename = strtolower($this->post("tablename"));
        $namespace = trim($this->post("namespace"), "\\");
        $name = strtolower($this->post("name"));
        $primaryObjectId = $this->post("primary_object_id");
        $override = $this->post("override");
        $objects = (array)$this->post("objects");
        $dir = $this->_getDir(trim($namespace, "\\"));

        if ($this->isFormValid($tablename, $name)) {
          $dbGeneratorModel = new DbGeneratorModel($dir);
          $keyCount = $dbGeneratorModel->getKeyCount($tablename);

          if (!$keyCount) {
            WebApplication::addWarning("There are no key columns for this table. If this is the intended design, please disregard this warning");
          }

          if (in_array("dbq", $objects)) {
            $hasSuccess = $dbGeneratorModel->createDbq($tablename, $namespace, $name, $override);
            $classname = DbGeneratorModel::getDbqClass($tablename);
            if ($hasSuccess) {
              WebApplication::addNotify('Successfully created ' . $classname);
            }
          }

          if (in_array("dbo", $objects)) {
            $hasSuccess = $dbGeneratorModel->createDbo($tablename, $namespace, $name, $override);
            $classname = DbGeneratorModel::getDboClass($tablename);
            if ($hasSuccess) {
              WebApplication::addNotify('Successfully created ' . $classname);
            }
          }

          if (in_array("trait", $objects)) {
            $modelTraitGeneratorModel = new ModelTraitGeneratorModel($dir);
            $modelTraitGeneratorModel->createTrait($tablename, $namespace, $name, $override);
            $classname = $modelTraitGeneratorModel::getTraitName($namespace, $name);
            WebApplication::addNotify('Successfully created ' . $classname);
          }

          $modelGeneratorComplexCmoddel = new ModelGeneratorModel($namespace, $name, $dir, ["primary_object_id" => $primaryObjectId]);
          if (in_array("cmodel", $objects)) {
            if (!is_file($modelGeneratorComplexCmoddel->getComplexModelFile()) || $override) {
              $modelGeneratorComplexCmoddel->generateComplexModel();
              WebApplication::addNotify('Successfully created Model ' . GeneratorModel::getModelClassname($name));
            } else {
              WebApplication::addWarning("The model " . $modelGeneratorComplexCmoddel->getComplexModelFile() . " already exists");
            }
          }

          if (in_array("hmodel", $objects)) {
            if (!is_file($modelGeneratorComplexCmoddel->getHandlerModelFile()) || $override) {
              $modelGeneratorComplexCmoddel->generateHandlerModel();
              WebApplication::addNotify('Successfully created Handler ' . GeneratorModel::getHandlerClassname($name));
            } else {
              WebApplication::addWarning("The handler " . $modelGeneratorComplexCmoddel->getHandlerModelFile() . " already exists");
            }
          }

          if (in_array("trait", $objects)) {
            $modelFile = GeneratorModel::getModelFile(strtolower($name));
            $modelDescribeGeneratorModel = new ModelDescribeGeneratorModel($modelFile);
            $modelDescribeGeneratorModel->update($tablename);
            WebApplication::addNotify('Successfully updated describe()');
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
