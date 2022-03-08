<?php

namespace Utility\Model;

use Exception;
use Framework\Arry\Arry;
use Framework\Util\FileUtil;
use Framework\Util\HtmlUtil;
use Framework\Util\LangUtil;
use Framework\Util\StringUtil;
use Backend\Lib\Provider\RouteManager;
use Framework\PhpParser\PhpParser;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Identifier;
use ReflectionClass;


class ApiGeneratorModel extends GeneratorModel {

  protected $_dir = "";
  protected $_model = "";
  protected $_modelPlural = "";
  protected $_options = [];
  protected $_modelId;
  protected $_namespace;

  public function __construct($namespace, $api, $model, $modelPlural, $methods = [], $parentModel = null, $options = []) {
    $namespace = trim($namespace, "\\");
    $dir = ModelGeneratorModel::getNamespaceDir($namespace);
    parent::__construct($dir);
    $this->_snakeModel = StringUtil::snakeize($model);
    $this->_modelId = $this->_snakeModel . "_id";
    $this->_model = $model;
    $this->_namespace = $namespace;
    $this->_options = $options;
    $this->_api = $api;
    $this->_parentModel = StringUtil::snakeize($parentModel);
    $this->_modelPlural = $modelPlural;
    $this->_pluralSnakeModel = StringUtil::snakeize($modelPlural);
    $this->_methods = $methods;
    $this->_method = value($this->_options, "method", $this->_modelPlural);
    $this->_dir = $this->getInstanceDir() . "View/Api/";
  }

  public function append(&$messages = []) {
    $file = $this->getFile();

    if (!is_file($file)) {
      throw new Exception("API not found");
    }

    $code = FileUtil::get($this->getFile());
    $this->assign("parent_method", $this->_api . "/" . $this->_parentModel . "_id/");
    $endpoint = ltrim($this->getEndpoint());
    $regex = "/(public\\s+function\\s+wsdl\\(\\).*)/is";

    if (preg_match($regex, $code, $matches)) {
      $code = str_replace($matches[0], $endpoint . $matches[0], $code);
    } else {
      $pos = strrpos($code, "}");
      if ($pos === false) {
        throw new Exception("There was a problem trying to located the end of the class");
      }

      $code = substr_replace($code, $endpoint, $pos, 0);
    }

    FileUtil::put($file, $code);
    $messages = ["Successfully updated the file " . HtmlUtil::getLink("file:" . FileUtil::sanitizeFile($file), FileUtil::sanitizeFile($file))];
  }

  public function getFile() {
    return $this->_dir . str_replace("_", "", $this->_api) . "View.php";
  }

  public function getEndpoint() {
    return $this->get(PathModel::getAssetsDirectory() . "api_endpoint.inc");
  }

  public function get($template) {
    $cmodel = ModelGeneratorModel::getModel($this->_namespace, $this->_model);
    $orderBy = "";
    $keywords = $accessibleFields = $fields = [];
    foreach ($cmodel->getDbos() as $dbo) {
      $fields += $dbo->getColumns();
      foreach ($dbo->getColumns() as $name => $column) {
        if (preg_match("/(name|description)/", $name)) {
          $keywords[] = ["name" => $name, "tablename" => $dbo->getTablename()];
        }
        if (preg_match("/(name|order)/", $name)) {
          $orderBy = $name;
        }
      }
    }
    $accessibleFields = array_values(array_filter(array_keys($fields), function ($v) {
      return !preg_match("/(" . $this->_snakeModel . "_id\$|guid|create_date|configs|_time|order|meta\$)/", $v);
    }));

    $fillFields = Arry::create($accessibleFields)
      ->filter(function ($field) {
        return !preg_match("/_id$/", $field);
      })
      ->map(function ($field) {
        return StringUtil::camelize($field);
      })
      ->get();

    $pascalModel = StringUtil::pascalize($this->_model);
    $pascalParentModel = StringUtil::pascalize($this->_parentModel);

    return $this
      ->assign("options", $this->_options)
      ->assign("orderBy", $orderBy)
      ->assign("snakeModel", $this->_snakeModel)
      ->assign("pluralSnakeModel", $this->_pluralSnakeModel)
      ->assign("modelUpper", strtoupper($this->_model))
      ->assign("pascalModel", $pascalModel)
      ->assign("method", $this->_method)
      ->assign("loads", (array)value($this->_options, "loads"))
      ->assign("modelPluralUpper", strtoupper($this->_modelPlural))
      ->assign("modelPluralUpperTrim", strtoupper(str_replace("_", "", $this->_modelPlural)))
      ->assign("modelPluralProper", ucwords($this->_modelPlural))
      ->assign("modelProper", ucwords($this->_model))
      ->assign("modelName", ucwords(str_replace("_", " ", $this->_model)))
      ->assign("model", $this->_model)
      ->assign("methods", $this->_methods)
      ->assign("keywords", $keywords)
      ->assign("pluralPascalModel", LangUtil::getPlural($pascalModel))
      ->assign("api", $this->_api)
      ->assign("apiSingular", rtrim($this->_api, "s"))
      ->assign("dbos", $cmodel->getDbos())
      ->assign("accessibleFields", $accessibleFields)
      ->assign("fillFields", $fillFields)
      ->assign("hasState", in_array("state", array_keys($fields)))
      ->assign("hasGuid", in_array("guid", array_keys($fields)))
      ->assign("createDate", in_array("create_date", array_keys($fields)))
      ->assign("fields", array_keys($fields))
      ->assign("modelId", $this->_modelId)
      ->assign("modelPlural", $this->_modelPlural)
      ->assign("namespace", $this->_namespace)
      ->assign("parentModel", $this->_parentModel)
      ->assign("pascalParentModel", $pascalParentModel)
      ->fetch($template);
  }

  public function generate($override, &$messages = []) {
    $file = $this->getFile();
    if (!$override && is_file($file)) {
      throw new Exception("The file " . $file . " already exists");
    }

    $this
      ->assign("parent_method", $this->_parentModel ? $this->_api . "/" : "")
      ->assign("endpoint", $this->getEndpoint());

    if (!$this->writeTemplate(PathModel::getAssetsDirectory() . "api.inc", $file)) {
      throw new Exception("Failed to generate " . $file);
    }

    $this->_updateRouteManager();

    $messages = ["Successfully added the file " . basename($file)];

    return true;
  }

  private function _updateRouteManager() {
    $reflector = new ReflectionClass(RouteManager::class);

    $phpParser = new PhpParser($reflector->getFileName());
    /**
     * @var Return_
     */
    $return = value($phpParser->getMethod("getRoutes")->stmts, 0);

    if ($return instanceof Return_) {
      /**
       * @var Array_
       */
      $route = $this->_findApiRoute($return->expr, null);

      if ($route) {
        $class = "{$this->_namespace}\View\Api\\" . StringUtil::pascalize($this->_modelPlural) . "View";
        $method = StringUtil::camelize($this->_method);

        /**
         * @var ArrayItem
         */
        $apiChildren = Arry::create($route->items)
          ->find(function (ArrayItem $item) {
            return $item->key->value === "children";
          });

        if ($apiChildren) {
          $exists = Arry::create($apiChildren->value->items)
            ->exists(function (ArrayItem $item) use ($method) {
              return Arry::create($item->value->items)
                ->exists(function ($item) use ($method) {
                  return $item->key->value === "path" && $item->value instanceof String_ && $item->value->value === $method;
                });
            });

          if (!$exists) {
            $id = StringUtil::camelize($this->_modelId);
            $arrayItem = [
              new ArrayItem(PhpParser::createString(strtolower($method)), PhpParser::createString("path")),
              new ArrayItem(new ClassConstFetch(new FullyQualified($class), new Identifier("class")), PhpParser::createString("class")),
              new ArrayItem(
                new Array_([
                  new ArrayItem(
                    new Array_([
                      new ArrayItem(PhpParser::createString(":{$id}?"), PhpParser::createString("path")),
                      new ArrayItem(PhpParser::createString($method), PhpParser::createString("function")),
                    ])
                  )
                ]),
                PhpParser::createString("children")
              )
            ];

            array_unshift($apiChildren->value->items, new Array_($arrayItem));

            $phpParser->save();
          }
        }
      }
    }
  }

  private function _findApiRoute($stmt, $parent) {
    if ($stmt instanceof Array_) {
      foreach ($stmt->items as $item) {
        $route = $this->_findApiRoute($item, $stmt);
        if ($route) {
          return $route;
        }
      }
    } elseif ($stmt instanceof ArrayItem) {

      if ($stmt->value instanceof Array_) {
        $route = $this->_findApiRoute($stmt->value, $stmt);
        if ($route) {
          return $route;
        }
      } elseif ($stmt->value instanceof String_) {
        if ($stmt->key && $stmt->key->value === "path" && $stmt->value->value === "api") {
          return $parent;
        }
      }
    }

    return null;
  }
}
