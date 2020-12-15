<?

namespace Utility\View\MapModel;

use Exception;
use Framework\Core\View;
use Framework\Core\WebApplication;
use Framework\Db\DB;
use Framework\Api\ApiResponse;
use Framework\Util\FILE_UTIL;
use Framework\Util\LANG_UTIL;
use Framework\Util\STRING_UTIL;
use Utility\Model\GeneratorModel;
use Utility\Model\ModelGeneratorModel;

class MapModelView extends View {

  protected $_reference_model   = "";
  protected $_joiner         = "";
  protected $_model        = null;
  protected $_source_model_column  = "";

  function __construct() {
    $this->set_template("./MapModelTemplate.php");
    $this->set_form("javascript:;", false, "form-relation");
    $this->disable_authorization();
  }

  function init() {

    $this->process_post();

    $task       = strtolower($this->request("task"));
    $application  = strtolower($this->request("application"));
    $app_dir    = $application ? DIR_INSTANCE . $application . "/" : WebApplication::get_main_application_directory();

    $model_list = ModelGeneratorModel::get_cmodels($app_dir);

    $joiner_list = DB::get_instance()->get_utility()->get_table_names();

    $this->set_var("joiner", $this->_joiner);
    $this->set_var("reference_model", $this->_reference_model);
    $this->set_var("model_list", $model_list);
    $this->set_var("joiner_list", $joiner_list);
    $this->set_var("source_model_column", $this->_source_model_column);
    $this->set_var("model", $this->_model);
  }

  public function process_post() {

    if (!$this->is_post())
      return;

    try {

      $response = new ApiResponse();

      $debug = false;
      $source_model = strtolower($this->request("source_model"));
      $source_model_column = $this->request("source_model_column");
      $reference_model_column = $this->request("reference_model_column");
      $reference_model = strtolower($this->request("reference_model"));
      $reference_model_plual = LANG_UTIL::plural($reference_model);
      $joiners = (array)$this->request("joiners");
      $map_child = $this->request("relationship") == "child";
      $application = $this->request("application");
      $dir = $application ? DIR_INSTANCE . $application . "/" : WebApplication::get_main_application_directory();
      $cmodel_model_file = $dir . "Model/" . ModelGeneratorModel::get_model_class($source_model) . ".php";
      $hmodel_model_file = $dir . "Handler/" . ModelGeneratorModel::get_handler_class($source_model) . ".php";
      $warnings = [];
      $reference_name = $this->post("object_name_custom");
      $reference_name_plual = LANG_UTIL::plural($reference_name);

      if ($this->post("object_name") == "source" || $this->post("object_name") == "reference") {
        $reference_name = preg_replace("/_id$/", "", $this->post("object_name") == "source" ? $source_model_column : $reference_model);
      }

      $plural_reference_name      = LANG_UTIL::get_plural_string($reference_name);
      $reference_name_set_function  = "set_" . ($map_child ? $reference_name : $plural_reference_name);
      $reference_name_get_function   = "get_" . ($map_child ? $reference_name : $plural_reference_name);

      $reference_key           = $map_child ? $reference_name : $plural_reference_name;

      $reference_name_set    = "\n\n\t\tpublic function " . $reference_name_set_function . "(?" . GeneratorModel::get_model_classname($reference_model) . " \$value) { return \$this->data(\"" . $reference_key . "\",\$value); }\n";

      $where_column       = $reference_model_plual . "." . $reference_model_column;
      $last_table       = $reference_model_plual;
      $last_column       = $reference_model_column;

      if ($joiner = value($joiners, 0))
        $where_column = $joiner["table"] . "." . $joiner["source_column"];

      $reference_name_get =   "\n\t\tpublic function " . $reference_name_get_function . "(\$handler=false): ?" . GeneratorModel::get_model_classname($reference_model) . " {\n" .
        "\t\t\tif(\$handler && !\$this->has_data(\"" . $reference_key . "\") && \$this->get_" . $source_model_column . "()) {\n" .
        "\t\t\t\t\$handler = \$handler instanceof Handler ? \$handler : " . basename(GeneratorModel::get_handler_class($reference_model)) . "::create(" . ($map_child ? "false" : "true") . ");\n" .
        "\t\t\t\t\$this->data(\"" . $reference_key . "\",\$handler";
      foreach (array_reverse($joiners) as $joiner) {
        $reference_name_get .= "->join(\"" . $last_table . "\",\"" . $joiner["table"] . "\",\"" . $last_column . "\",\"" . $joiner["reference_column"] . "\")\n\t\t\t\t\t\t\t\t\t\t\t\t";

        $last_table = $joiner["table"];
        $last_column = $joiner["source_column"];
      }

      $reference_name_get .= "->where(\"" . $where_column . "\",\"=\",\$this->get_" . $source_model_column . "())";

      $reference_name_get .=   "\n\t\t\t\t\t\t\t\t\t\t\t\t->" . ($map_child ? "get" : "gets") . "());\n" .
        "\t\t\t}\n" .
        "\t\t\treturn " . ($map_child ? "" : "(array)") . "\$this->data(\"" . $reference_key . "\");\n" .
        "\t\t}";

      if (!$reference_name)
        throw new Exception("Invalid reference name");

      $cmodel_content = FILE_UTIL::get($cmodel_model_file);

      if (stripos($cmodel_content, "n " . $reference_name_set_function . "(") === false) {
        if (preg_match("/^(.*?[^}]+})(.*?function\s+save\(.*)$/ism", $cmodel_content, $matches)) {
          $cmodel_content = value($matches, 1) . $reference_name_set . $reference_name_get . value($matches, 2);

          if ($debug) {
            p("CMODEL SET, GET", $reference_name_set . $reference_name_get);
          }
        }
      } else
        $warnings[] = "The CMODEL_" . strtoupper($source_model) . "->" . $reference_name_set_function . "() function is already generated";

      if (!$debug) {
        try {
          FILE_UTIL::put($cmodel_model_file, $cmodel_content);
        } catch (Exception $e) {
          throw new Exception("There was a problem trying to update the complex model");
        }
      }

      $hmodel_content = FILE_UTIL::get($hmodel_model_file);

      if (stripos($hmodel_content, "load_" . $plural_reference_name) === false) {

        if (preg_match("/^(.*?)(return\s+\\\$(:?" . $source_model . "_)?cmodels;.*)$/ism", $hmodel_content, $matches)) {

          $function         = $map_child ? "map_child" : "map_children";
          $parent_object_function = "set_" . ($map_child ? $reference_name : $reference_name_plual);
          $child_reference_column = $reference_model . "s." . $reference_model_column;
          $cmodels         = stripos($hmodel_content, 'return $cmodel') === false ? '$' . $source_model . '_cmodels' : '$cmodels';
          $joins           = [];

          $last_table = $reference_model_plual;
          $last_column = $reference_model_column;
          foreach (array_reverse($joiners) as $joiner) {
            $joins[] = '["from"=>"' . $last_table . '","to"=>"' . $joiner["table"] . '","from_column"=>"' . $last_column . '","to_column"=>"' . $joiner["reference_column"] . '"]';
            $last_table = $joiner["table"];
            $last_column = $joiner["source_column"];
            $child_reference_column = $joiner["table"] . "." . $joiner["source_column"];
          }

          $joins = "[" . implode(",", $joins) . "]";

          $code = "\n\t\t\t" . '$this->' . $function . '(' . $cmodels . ', $this->handler("' . $reference_name . '_handler"), "get_' . $source_model_column . '", "' . $parent_object_function . '", "' . $child_reference_column . '",' . $joins . ');' . "\n\n\t\t\t";

          if (!$this->has_code($hmodel_content, $code)) {

            if ($debug)
              p("Handler MAP", $code);

            $hmodel_content = value($matches, 1) . $code . value($matches, 2);
          }
        }

        if (preg_match("/(.*)(}[\s\n]*)$/ism", $hmodel_content, $matches)) {

          $code = "\n\t\tpublic function load_" . $plural_reference_name . "(\$handler=null) {\n" .
            "\t\t\treturn \$this->handler(\"" . $reference_name . "_handler\",\$handler ? \$handler : " . STRING_UTIL::pascalize($reference_model) . "Handler::create());\n" .
            "\t\t}\n\t}";

          if ($code && !$this->has_code($hmodel_content, $code)) {
            $hmodel_content = value($matches, 1) . $code;

            if ($debug)
              p("HMODEL LOAD", $code);
          }
        }

        if (!$debug) {
          try {
            FILE_UTIL::put($hmodel_model_file, $hmodel_content);
          } catch (Exception $e) {
            throw new Exception("There was a problem trying to update the handler model");
          }
        }
      } else
        $warnings[] = "The Handler" . strtoupper($source_model) . " load_" . $plural_reference_name . "() function is already generated";

      $response->success();
    } catch (Exception $e) {
      WebApplication::add_error($e->getMessage());
      $response->data("errors", WebApplication::get_error_messages());
    }

    $response
      ->data("warnings", WebApplication::get_warning_messages())
      ->data("messages", WebApplication::get_notify_messages())
      ->render();
  }

  function has_code($content, $code) {

    $content   = preg_replace("/\s/", "", $content);
    $code     = preg_replace("/\s/", "", $code);

    return strpos($content, $code);
  }
}
