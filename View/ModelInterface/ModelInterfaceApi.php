<?php

namespace Utility\View\ModelInterface;

use Exception;
use Framework\Api\ApiResponse;
use Framework\Arry\Arry;
use Framework\Core\Model;
use Framework\Core\View;
use Framework\Core\WebApplication;
use Framework\Util\StringUtil;
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
        ->reduce(function ($accum, $value, $name) use ($model) {
          $dataType = "any";
          if ($column = $model->getColumn(StringUtil::snakeize($name))) {
            if ($column->isDataTypeDate() || $column->isDataTypeDatetime()) {
              $dataType = "Date";
            } elseif ($column->isDataTypeInt() || $column->isDataTypeDecimal()) {
              $dataType = "number";
            } elseif ($column->isDataTypeString()) {
              $dataType = "string";
            }
          }

          if ($arryName = value($value, ["arry", "name"])) {
            $name = $arryName;
          }

          $code = "  {$name}?: {$dataType}";

          return array_merge($accum, [$code]);
        }, [])
        ->join(",\n");

      echo "export interface {$sourceModel} {\n" . $items . "\n}";

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
}