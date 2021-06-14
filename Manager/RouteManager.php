<?php

namespace Utility\Manager;

use Framework\Manager\RouteManagerBase;
class RouteManager extends RouteManagerBase
{
    public function getRoutes()
    {
        return [["bodyClass" => \utility\view\application\body\bodyView::class, "children" => [["path" => "dbmodel", "class" => \utility\view\dbModel\dbModelView::class], ["path" => "api", "class" => \utility\view\api\apiView::class], ["path" => "wsdl", "class" => \utility\view\wsdl\wsdlView::class], ["path" => "mapmodel", "children" => [["path" => "", "class" => \utility\view\mapModel\mapModelView::class], ["path" => "referencefields", "class" => \utility\view\mapModel\referenceFields\referenceFieldsView::class, "bodyClass" => null], ["path" => "sourcefields", "class" => \utility\view\mapModel\sourceFields\sourceFieldsView::class, "bodyClass" => null], ["path" => "joinerfields", "class" => \utility\view\mapModel\joinerFields\joinerFieldsView::class, "bodyClass" => null]]], ["path" => "**", "class" => \utility\view\dbModel\dbModelView::class]]]];
    }
}