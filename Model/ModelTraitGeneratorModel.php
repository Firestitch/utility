<?

namespace Utility\Model;

use Exception;
use Framework\Core\WebApplication;
use Framework\Db\DB;
use Framework\Util\FILE_UTIL;
use Framework\Util\STRING_UTIL;

class ModelTraitGeneratorModel {

  protected $_app_dir = null;
  protected $_db_utility;

  public static $base_namespace = "Backend";

  function __construct($app_dir = null) {
    $this->_app_dir  = $app_dir ? $app_dir : WebApplication::get_main_application_directory();

    $this->_db_utility = DB::get_instance()->get_utility();
  }

  public function create_trait($tablenames, $name, $override = false) {

    $tablenames = is_array($tablenames) ? $tablenames : [$tablenames];
    $classname = basename(self::get_trait_name(strtolower($name)));
    $trait_file = self::get_trait_file($classname, $this->_app_dir);
    $has_success = false;
    $model_name = self::get_model_classname($name);
    $model_classname = basename(self::get_model_classname($name));

    if (!is_dir($this->_app_dir . "/Model/Traits/"))
      FILE_UTIL::mkdir($this->_app_dir . "/Model/Traits/");

    if (!is_file($trait_file) || $override) {

      $str = "<?php\n\nnamespace " . self::$base_namespace . "\Model\Traits;\n\ntrait {$classname} {\n\n";

      $field_names = [];
      foreach ($tablenames as $tablename) {
        try {
          foreach ($this->_db_utility->get_table_fields($tablename) as $field) {
            $field_names[] = $field["Field"];
          }
        } catch (\Exception $e) {
          WebApplication::instance()->add_warning("The tablename `" . $tablename . "` doest not exists");
          continue;
        }
      }

      $field_names = array_unique($field_names);
      foreach ($field_names as $field_name) {
        $str .= '  /**
   * @return static
   */
  public function set_' . $field_name . '($value) {
    return $this->set_dbo_value("' . $field_name . '", $value);
  }' . "\n\n";
        $str .= '  public function get_' . $field_name . '() {
    return $this->get_dbo_value("' . $field_name . '");
  }' . "\n\n";
      }

      $str .= "}";

      $has_success = $this->write_file($trait_file, $str);
    } else
      WebApplication::instance()->add_warning("The Trait `" . $classname . "` already exists");

    return $has_success;
  }

  function append_model_trait($model_name, $trait_name = null) {

    $model_classname = basename(self::get_model_classname($model_name));
    $model_file = self::get_model_file($model_classname, $this->_app_dir);

    $trait_name = $trait_name ? $trait_name : basename(self::get_trait_name(strtolower($model_name)));

    $code = FILE_UTIL::get($model_file);

    if (strpos($code, $trait_name) === false) {

      $code = preg_replace_callback("/extends.+{/", function ($matches) use ($trait_name) {

        return $matches[0] . "\n\n" . "  use Traits\\$trait_name;";
      }, $code);
    }

    FILE_UTIL::put($model_file, $code);

    return $this;
  }

  function write_file($file, $string) {

    $error_message = "";
    $has_success = FILE_UTIL::put_file_contents($file, $string, $error_message);

    if (!$has_success)
      throw new Exception($error_message);

    return $has_success;
  }

  public static function get_trait_name($basename) {
    return self::$base_namespace . "\Model\Traits\\" . STRING_UTIL::pascalize(strtolower($basename)) . "Trait";
  }

  public static function get_trait_file($classname, $app_dir) {
    return FILE_UTIL::sanitize_file($app_dir . "/Model/Traits/" . $classname . ".php");
  }

  public static function get_model_classname($basename) {
    return self::$base_namespace . "\Model\\" . STRING_UTIL::pascalize(strtolower($basename)) . "Model";
  }

  public static function get_model_file($classname, $app_dir) {
    return FILE_UTIL::sanitize_file($app_dir . "/Model/" . $classname . ".php");
  }
}
