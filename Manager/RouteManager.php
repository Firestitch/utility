<?php

namespace Utility\Manager;

use Backend\View\Api\WsdlView;
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


class RouteManager extends RouteManagerBase {

  public function getRoutes() {
    return [
      [
        "bodyClass" => BodyView::class,
        "path" => "generate",
        "children" => [
          [
            "path" => "model", "bodyClass" => null,
            "children" => [
              ["path" => "list", "class" => ModelListView::class],
              ["path" => "fields", "class" => ModelFieldsView::class]
            ]
          ],
          [
            "path" => "dbmodel",
            "children" => [
              ["path" => "api", "class" => DbModelApi::class, "bodyClass" => null,],
              ["path" => "", "class" => DbModelView::class],
            ]
          ],
          [
            "path" => "api",
            "children" => [
              ["path" => "apis", "class" => ApisView::class, "bodyClass" => null],
              ["path" => "api", "class" => ApiApi::class, "bodyClass" => null],
              ["path" => "", "class" => ApiView::class],
            ]
          ],
          [
            "path" => "mapmodel",
            "children" => [
              ["path" => "api", "class" => MapModelApi::class, "bodyClass" => null],
              ["path" => "joinerfields", "class" => JoinerFieldsView::class, "bodyClass" => null],
              ["path" => "", "class" => MapModelView::class],
            ]
          ],
          ["path" => "**", "redirect" => "dbmodel"]
        ]
      ],
      ["path" => "wsdl", "class" => WsdlView::class],
      ["path" => "**", "redirect" => "/generate"]
    ];
  }
}
