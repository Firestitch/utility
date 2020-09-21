<?php

namespace Utility\Manager;

use Framework\Manager\RouteManagerBase;

class RouteManager extends RouteManagerBase {

  public function get_routes() {
    return [
      [
        "base" => \Utility\View\Application\Body\BodyView::class,
        "children" => [
          ["path" => "dbmodel", "class" => \Utility\View\DbModel\DbModelView::class],
          ["path" => "api", "class" => \Utility\View\Api\ApiView::class],
          [
            "path" => "**", "class" => \Utility\View\DbModel\DbModelView::class,
          ]
        ]
      ]
    ];
  }
}
