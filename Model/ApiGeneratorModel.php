<?

namespace Utility\Model;

use Exception;
use Framework\Util\FILE_UTIL;
use Framework\Util\HTML_UTIL;
use Framework\Util\STRING_UTIL;

class ApiGeneratorModel extends GeneratorModel {

  protected $_dir          = "";
  protected $_model        = "";
  protected $_model_plural    = "";
  protected $_options        = [];

  function __construct($dir, $api, $model, $model_plural, $methods = [], $parent_model = null, $options = []) {
    parent::__construct($dir);

    $this->_model = $model;
    $this->_options = $options;
    $this->_api = $api;
    $this->_parent_model = $parent_model;
    $this->_model_plural = $model_plural;
    $this->_methods = $methods;
    $this->_method = value($this->_options, "method", str_replace("_", "", $this->_model_plural));
    $this->_dir = $this->get_instance_dir() . "View/Api/";
  }

  function get_file() {
    return $this->_dir . str_replace("_", "", $this->_api) . "View.php";
  }

  function append(&$messages = array()) {

    $file = $this->get_file();

    if (!is_file($file))
      throw new Exception("API not found");

    $code = FILE_UTIL::get($this->get_file());
    $this->assign("parent_method", $this->_api . "/" . $this->_parent_model . "_id/");
    $endpoint = ltrim($this->get_endpoint());

    $regex = "/(public\s+function\s+wsdl\(\).*)/is";
    if (preg_match($regex, $code, $matches)) {
      $code = str_replace($matches[0], $endpoint . $matches[0], $code);
    } else {

      $pos = strrpos($code, "}");

      if ($pos === false)
        throw new Exception("There was a problem trying to located the end of the class");

      $code = substr_replace($code, $endpoint, $pos, 0);
    }

    FILE_UTIL::put($file, $code);

    $messages = array("Successfully updated the file " . HTML_UTIL::get_link("file:" . FILE_UTIL::sanitize_file($file), FILE_UTIL::sanitize_file($file)));
  }

  function generate($override, &$messages = array()) {

    $file = $this->get_file();

    if (!$override && is_file($file))
      throw new Exception("The file " . $file . " already exists");

    $this
      ->assign("parent_method", $this->_parent_model ? $this->_api . "/" : "")
      ->assign("endpoint", $this->get_endpoint());

    if (!$this->write_template(PathModel::get_assets_directory() . "api.inc", $file))
      throw new Exception("Failed to generate " . $file);

    $messages = array("Successfully added the file " . HTML_UTIL::get_link("file:" . FILE_UTIL::sanitize_file($file), FILE_UTIL::sanitize_file($file)));

    return true;
  }

  function get_endpoint() {
    return $this->get(PathModel::get_assets_directory() . "api_endpoint.inc");
  }

  function get($template) {

    $cmodel = ModelGeneratorModel::get_model($this->_model);
    $order_by = "";
    $keywords = $accessible_fields = $fields = [];
    foreach ($cmodel->get_dbos() as $dbo) {
      $fields += $dbo->get_columns();

      foreach ($dbo->get_columns() as $name => $column) {
        if (preg_match("/(name|description)/", $name))
          $keywords[] = ["name" => $name, "tablename" => $dbo->get_tablename()];

        if (preg_match("/(name|order)/", $name))
          $order_by = $name;
      }
    }

    $accessible_fields = array_values(array_filter(array_keys($fields), function ($v) {
      return !preg_match("/(" . $this->_model . "_id$|guid|create_date|configs|_time|meta$)/", $v);
    }));

    return $this
      ->assign("options", $this->_options)
      ->assign("order_by", $order_by)
      ->assign("model_upper", strtoupper($this->_model))
      ->assign("pascal_model", STRING_UTIL::pascalize($this->_model))
      ->assign("method", $this->_method)
      ->assign("loads", (array)value($this->_options, "loads"))
      ->assign("model_plural_upper", strtoupper($this->_model_plural))
      ->assign("model_plural_upper_trim", strtoupper(str_replace("_", "", $this->_model_plural)))
      ->assign("model_plural_proper", ucwords($this->_model_plural))
      ->assign("model_proper", ucwords($this->_model))
      ->assign("model_name", ucwords(str_replace("_", " ", $this->_model)))
      ->assign("model", $this->_model)
      ->assign("methods", $this->_methods)
      ->assign("keywords", $keywords)
      ->assign("api", $this->_api)
      ->assign("api_singular", rtrim($this->_api, "s"))
      ->assign("dbos", $cmodel->get_dbos())
      ->assign("accessible_fields", $accessible_fields)
      ->assign("has_state", in_array("state", array_keys($fields)))
      ->assign("fields", array_keys($fields))
      ->assign("model_id", $this->_model . "_id")
      ->assign("model_plural", $this->_model_plural)
      ->assign("parent_model", $this->_parent_model)
      ->fetch($template);
  }
}
