<?php

namespace Utility\Lib\Provider;

use Framework\Manager\RouteManagerBase;
use Utility\View\Api\ApiApi;
use Utility\View\Api\Apis\ApisView;
use Utility\View\Api\ApiView;
use Utility\View\Application\Body\BodyView;
use Utility\View\DbModel\DbModelApi;
use Utility\View\DbModel\DbModelView;
use Utility\View\MapModel\JoinerFields\JoinerFieldsView;
use Utility\View\MapModel\MapModelApi;
use Utility\View\MapModel\MapModelView;
use Utility\View\Model\ModelFields\ModelFieldsView;
use Utility\View\Model\ModelList\ModelListView;
use Utility\View\ModelInterface\ModelInterfaceApi;
use Utility\View\ModelInterface\ModelInterfaceView;
use Utility\View\Refactor\RefactorApiView;
use Utility\View\Refactor\RefactorView;


class RouteManager extends RouteManagerBase {

  public function getRoutes() {
    return [
      [
        "path" => "model",
        "bodyClass" => null,
        "children" => fn() => [
          ["path" => "list", "class" => ModelListView::class],
          ["path" => "fields", "class" => ModelFieldsView::class],
        ],
      ],
      [
        "bodyClass" => BodyView::class,
        "children" => fn() => [
          [
            "path" => "model/interface",
            "children" => fn() => [
              ["path" => "api/update", "class" => ModelInterfaceApi::class, "bodyClass" => null, "data" => ["action" => "update"]],
              ["path" => "api/preview", "class" => ModelInterfaceApi::class, "bodyClass" => null, "data" => ["action" => "preview"]],
              ["path" => "", "class" => ModelInterfaceView::class],
            ],
          ],
          [
            "path" => "dbmodel",
            "children" => fn() => [
              ["path" => "api/generate", "class" => DbModelApi::class, "bodyClass" => null, "data" => ["action" => "generate"]],
              ["path" => "api/exists", "class" => DbModelApi::class, "bodyClass" => null, "data" => ["action" => "exists"]],
              ["path" => "", "class" => DbModelView::class],
            ],
          ],
          [
            "path" => "api",
            "children" => fn() => [
              ["path" => "apis", "class" => ApisView::class, "bodyClass" => null],
              ["path" => "api", "class" => ApiApi::class, "bodyClass" => null],
              ["path" => "", "class" => ApiView::class],
            ],
          ],
          [
            "path" => "mapmodel",
            "children" => fn() => [
              ["path" => "api", "class" => MapModelApi::class, "bodyClass" => null],
              ["path" => "joinerfields", "class" => JoinerFieldsView::class, "bodyClass" => null],
              ["path" => "", "class" => MapModelView::class],
            ],
          ],
          [
            "path" => "refactor",
            "children" => fn() => [
              ["path" => "api", "class" => RefactorApiView::class, "bodyClass" => null],
              ["path" => "", "class" => RefactorView::class],
            ],
          ],
        ],
      ],
      ["path" => "**", "redirect" => "/dbmodel"],
    ];
  }
}
