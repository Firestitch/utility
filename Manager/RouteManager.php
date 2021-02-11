<?php

namespace Utility\Manager;

use Framework\Manager\RouteManagerBase;

class RouteManager extends RouteManagerBase {

  public function get_routes() {
    return [
      [
        "bodyClass" => \Utility\View\Application\Body\BodyView::class,
        "children" => [
          ["path" => "dbmodel", "class" => \Utility\View\DbModel\DbModelView::class],
          ["path" => "api", "class" => \Utility\View\Api\ApiView::class],
          ["path" => "wsdl", "class" => \Utility\View\Wsdl\WsdlView::class],
          [
            "path" => "mapmodel",
            "children" => [
              ["path" => "", "class" => \Utility\View\MapModel\MapModelView::class],
              ["path" => "referencefields", "class" => \Utility\View\MapModel\ReferenceFields\ReferenceFieldsView::class, "bodyClass" => null],
              ["path" => "sourcefields", "class" => \Utility\View\MapModel\SourceFields\SourceFieldsView::class, "bodyClass" => null],
              ["path" => "joinerfields", "class" => \Utility\View\MapModel\JoinerFields\JoinerFieldsView::class, "bodyClass" => null],
            ]
          ],
          [
            "path" => "**", "class" => \Utility\View\DbModel\DbModelView::class,
          ]
        ]
      ]
    ];
  }
}
