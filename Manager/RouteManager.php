<?php

namespace Utility\Manager;

use Utility\View\Wsdl\WsdlView;
use Framework\Manager\RouteManagerBase;
use Utility\View\Api\ApiView;
use Utility\View\Api\Apis\ApisView;
use Utility\View\Application\Body\BodyView;
use Utility\View\DbModel\DbModelView;
use Utility\View\MapModel\JoinerFields\JoinerFieldsView;
use Utility\View\MapModel\MapModelView;
use Utility\View\Model\ModelList\ModelListView;
use Utility\View\Model\ModelFields\ModelFieldsView;


class RouteManager extends RouteManagerBase {

  public function getRoutes() {
    return [
      [
        "bodyClass" => BodyView::class,
        "children" => [
          [
            "path" => "model", "bodyClass" => null,
            "children" => [
              ["path" => "list", "class" => ModelListView::class],
              ["path" => "fields", "class" => ModelFieldsView::class]
            ]
          ],
          ["path" => "dbmodel", "class" => DbModelView::class],
          [
            "path" => "api",
            "children" => [
              [
                "path" => "",
                "bodyClass" => null,
                "children" => [
                  ["path" => "apis", "class" => ApisView::class],
                ]
              ],
              ["path" => "", "class" => ApiView::class],
            ]
          ],
          ["path" => "wsdl", "class" => WsdlView::class],
          [
            "path" => "mapmodel",
            "children" => [
              ["path" => "", "class" => MapModelView::class],
              ["path" => "joinerfields", "class" => JoinerFieldsView::class, "bodyClass" => null]
            ]
          ],
          ["path" => "**", "class" => DbModelView::class]
        ]
      ]
    ];
  }
}
