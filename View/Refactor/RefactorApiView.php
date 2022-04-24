<?php

namespace Utility\View\Refactor;

use Exception;
use Framework\Api\ApiResponse;
use Framework\Core\View;
use Framework\Core\WebApplication;
use Framework\File\File;
use Utility\Model\ModelGeneratorModel;


class RefactorApiView extends View {

  public function __construct() {
    $this
      ->disableAuthorization();
  }

  public function init() {
    $response = new ApiResponse();

    try {
      $sourceModel = $this->request("source_model");
      $sourceNamespace = $this->request("sourceNamespace");

      $dir = ModelGeneratorModel::getNamespaceDir($sourceNamespace);

      $file = (new File($dir));

      $file
        ->clone()
        ->appendDir("Model")
        ->setFile("{$sourceModel}Model.php")
        ->delete();

      $file
        ->clone()
        ->appendDir("Model/Traits")
        ->setFile("{$sourceModel}Trait.php")
        ->delete();

      $file
        ->clone()
        ->appendDir("Handler")
        ->setFile("{$sourceModel}Handler.php")
        ->delete();

      $file
        ->clone()
        ->appendDir("Dbo")
        ->setFile("{$sourceModel}Dbo.php")
        ->delete();

      $file
        ->clone()
        ->appendDir("Dbq")
        ->setFile("{$sourceModel}Dbq.php")
        ->delete();
    } catch (Exception $e) {
      WebApplication::addError($e->getMessage());
    }

    $response
      ->data("warnings", WebApplication::getWarningMessages())
      ->data("messages", WebApplication::getNotifyMessages())
      ->render();
  }
}
