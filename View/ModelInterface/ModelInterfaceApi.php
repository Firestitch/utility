<?php

namespace Utility\View\ModelInterface;

use Exception;
use Framework\Api\ApiResponse;
use Framework\Arry\Arry;
use Framework\Core\Model;
use Framework\Core\View;
use Framework\Core\WebApplication;
use Framework\Util\StringUtil;
use ReflectionClass;
use Utility\Model\ModelGeneratorModel;


class ModelInterfaceApi extends View {

  public function __construct() {
    $this->disableAuthorization();
  }

  /**
   * @suppresswarnings
   */
  public function init() {
    try {
      $response = new ApiResponse();
      $sourceModel = strtolower($this->request("namespace"));
      $sourceModel = $this->request("sourceModel");
      $sourceNamespace = $this->request("namespace");
      $class = $sourceNamespace . "\\Model\\" . ModelGeneratorModel::getModelClass($sourceModel);
      /**
       * @var Model
       */
      $model = new $class;
      $items = Arry::create($model->describe())
        ->unique(function ($value1, $key1, $value2, $key2) {
          $name1 = ($arryName1 = value($value1, ["arry", "name"])) ? $arryName1 : $key1;
          $name2 = ($arryName2 = value($value2, ["arry", "name"])) ? $arryName2 : $key2;
          return $name1 === $name2;
        })
        ->reduce(function ($accum, $value, $key) use ($model, $class) {
          $dataType = null;

          if ($column = $model->getColumn(StringUtil::snakeize($key))) {
            if ($column->isDataTypeDate() || $column->isDataTypeDatetime()) {
              $dataType = "Date";
            } elseif ($column->isDataTypeBool()) {
              $dataType = "boolean";
            } elseif ($column->isDataTypeInt() || $column->isDataTypeDecimal()) {
              $dataType = "number";
            } elseif ($column->isDataTypeString()) {
              $dataType = "string";
            }
          }

          if ($type = value($value, ["type"])) {
            if (strpos($type, "date") !== false)
              $dataType = "Date";
          }

          if ($dataType === null) {
            $method = "get" . StringUtil::pascalize($key);
            $reflection = new ReflectionClass($class);

            try {
              $reflectionMethod = $reflection->getMethod($method);

              if ($reflectionMethod) {
                if ($reflectionMethod->getReturnType()) {
                  $parts = explode("\\", $reflectionMethod->getReturnType()->getName());
                  $dataType = $this->_getMethodDataType(array_pop($parts));

                } else {
                  if (preg_match('/@return\s+(.*)/', $reflectionMethod->getDocComment(), $matches)) {
                    $dataType = $this->_getMethodDataType(trim(value($matches, 1)));
                  }
                }
              }
            } catch (Exception $e) {
            }
          }

          if ($dataType === null) {
            if (value($value, "type") === "array") {
              $dataType = "any[]";
            }
          }

          $dataType = $dataType ? $dataType : 'any';
          $name = ($arryName = value($value, ["arry", "name"])) ? $arryName : $key;
          $code = "  {$name}?: {$dataType}";

          return array_merge($accum, [$code]);
        }, [])
        ->join(";\n");

      echo "export interface {$sourceModel} {\n" . $items . ";\n}";

      die;
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

  private function _getMethodDataType($value) {
    if ($value === "string") {
      return "string";
    }

    if (in_array($value, ["float", "int"])) {
      return "number";
    }

    if (preg_match("/(.+)Model(\[\])?/", $value, $matches)) {
      return value($matches, 1) . value($matches, 2);
    }

    return null;
  }





}

