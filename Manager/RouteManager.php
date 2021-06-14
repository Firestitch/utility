<?php

namespace Utility\Manager;

use Backend\View\Api\WsdlView;
use Framework\Manager\RouteManagerBase;
use Utility\View\Api\ApiView;
use Utility\View\Application\Body\BodyView;
use Utility\View\DbModel\DbModelView;
use Utility\View\MapModel\JoinerFields\JoinerFieldsView;
use Utility\View\MapModel\MapModelView;
use Utility\View\MapModel\ReferenceFields\ReferenceFieldsView;
use Utility\View\MapModel\SourceFields\SourceFieldsView;


class RouteManager extends RouteManagerBase {
  public function getRoutes() {
    return [
      [
        "bodyClass" => BodyView::class,
        "children" => [
          ["path" => "dbmodel", "class" => DbModelView::class],
          ["path" => "api", "class" => ApiView::class],
          ["path" => "wsdl", "class" => WsdlView::class],
          [
            "path" => "mapmodel",
            "children" => [
              ["path" => "", "class" => MapModelView::class],
              ["path" => "referencefields", "class" => ReferenceFieldsView::class, "bodyClass" => null],
              ["path" => "sourcefields", "class" => SourceFieldsView::class, "bodyClass" => null],
              ["path" => "joinerfields", "class" => JoinerFieldsView::class, "bodyClass" => null]
            ]
          ],
          ["path" => "**", "class" => DbModelView::class]
        ]
      ]
    ];
  }
}
