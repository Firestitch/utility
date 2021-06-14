<?php

namespace Utility\View\Api;

use Exception;
use Framework\Core\View;
use Framework\Core\WebApplication;
use Framework\Api\ApiResponse;
use Framework\Util\FileUtil;
use Utility\Model\ApiGeneratorModel;
use Utility\Model\ModelGeneratorModel;
class ApiView extends View
{
    public function __construct()
    {
        $this->disableAuthorization();
        $this->setTemplate("./ApiTemplate.php");
        $this->setForm("javascript:;", false, "form-api");
    }
    public function init()
    {
        $this->processPost();
        $views = FileUtil::getDirectoryListing(WebApplication::getMainApplicationDirectory() . "View/api/");
        $apis = [];
        foreach ($views as $view) {
            $name = preg_replace("/View\\.php/", "", $view);
            $apis[$name] = $name;
        }
        $this->setVar("model", $this->get("model"));
        $this->setVar("models", ModelGeneratorModel::getCmodels());
        $this->setVar("apis", $apis);
    }
    public function processPost()
    {
        if ($this->isPost()) {
            try {
                $response = new ApiResponse();
                $dir = WebApplication::getMainApplicationDirectory();
                $model = $this->post("model");
                $api = $this->post("api");
                $modelPlural = $this->post("model-plural");
                $options = (array) $this->post("options");
                $methods = (array) $this->post("methods");
                $method = $this->post("method");
                if (!$model) {
                    throw new Exception("Invalid model");
                }
                if (!$modelPlural) {
                    throw new Exception("Plural model");
                }
                $messages = [];
                if ($api) {
                    $options["method"] = $method;
                    (new ApiGeneratorModel($dir, $api, $model, $modelPlural, $methods, rtrim($api, "s"), $options))->append($messages);
                } else {
                    (new ApiGeneratorModel($dir, $modelPlural, $model, $modelPlural, $methods, "", $options))->generate(in_array("override", $options), $messages);
                }
                $response->success();
                WebApplication::addNotify("Successfully generated API");
            } catch (Exception $e) {
                WebApplication::addError($e->getMessage());
                $response->data("errors", WebApplication::getErrorMessages());
            }
            $response->data("warnings", WebApplication::getWarningMessages())->data("messages", WebApplication::getNotifyMessages())->render();
        }
    }
}