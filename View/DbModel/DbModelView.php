<?

namespace Utility\View\DbModel;

use Exception;
use Framework\Core\View;
use Framework\Core\WebApplication;
use Framework\Db\DB;
use Framework\Model\PathModel;
use Framework\Model\ResponseModel;
use Framework\Util\ARRAY_UTIL;
use Utility\Model\DbGeneratorModel;
use Utility\Model\ModelGeneratorModel;

class DbModelView extends View {

  private $_classname   = "";
  private $_tablename   = "";
  private $_states   = "";
  private $_create_dbq   = false;
  private $_create_dbo  = true;
  private $_override  = true;

  function __construct() {
    $this->set_template("./DbModelTemplate.php");
    $this->disable_authorization();

    $this->_classname   = $this->get("model");
    $this->_tablename   = $this->get("table");
  }

  function init() {

    $this->process_post();

    $db_utility = DB::get_instance()->get_utility();
    $tablename_list = $db_utility->get_table_names();

    $sql = "SELECT `table_name` FROM `information_schema`.`columns` WHERE `table_schema` = '" . DB::get_instance()->get_db_name() . "' AND `column_name` = 'state'";
    $state_column_tables = ARRAY_UTIL::get_list_from_array(DB::get_instance()->select($sql), "table_name");

    $this->set_var("tablename_list", $tablename_list);
    $this->set_var("classname", $this->_classname);
    $this->set_var("tablename", $this->_tablename);
    $this->set_var("states", $this->_states);
    $this->set_var("create_dbq", $this->_create_dbq);
    $this->set_var("create_dbo", $this->_create_dbo);
    $this->set_var("override", $this->_override);
    $this->set_var("state_column_tables", $state_column_tables);
  }

  public function process_post() {

    if ($this->is_post()) {

      try {

        $response = new ResponseModel();

        $tablename       = strtolower($this->post("tablename"));
        $name         = strtoupper($this->post("name"));
        $primary_object_id  = $this->post("primary_object_id");
        $override       = $this->post("override");
        $objects       = (array)$this->post("objects");
        $dir        = PathModel::get_backend_dir();
        $app_dir      = WebApplication::get_main_application_directory();

        $messages = $warnings = [];
        if ($this->is_form_valid($tablename, $name)) {

          $db_generator_cmodel = new DbGeneratorModel($dir);
          $key_count = $db_generator_cmodel->get_key_count($tablename);

          if (!$key_count)
            $warnings[] = "There are no key columns for this table. If this is the intended design, please disregard this warning.";

          if (in_array("dbq", $objects)) {

            $has_success = $db_generator_cmodel->create_dbq($tablename, $name, $override, false);

            if ($has_success)
              WebApplication::add_notify('Successfully created DBQ_' . strtoupper($name));
          }

          if (in_array("dbo", $objects)) {

            $has_success = $db_generator_cmodel->create_dbo($tablename, $name, $override, false);

            if ($has_success)
              WebApplication::add_notify('Successfully created DBO_' . strtoupper($name));
          }

          $model_generator_complex_cmoddel = new ModelGeneratorModel($name, $app_dir, false, false, ["primary_object_id" => $primary_object_id]);

          if (in_array("cmodel", $objects)) {

            if (!is_file($model_generator_complex_cmoddel->get_complex_model_file()) || $override) {
              $model_generator_complex_cmoddel->generate_complex_model();

              $messages[] = 'Successfully created Model' . strtoupper($name);
            } else
              $warnings[] = "The complex model " . $model_generator_complex_cmoddel->get_complex_model_file() . " already exists";
          }

          if (in_array("hmodel", $objects)) {

            if (!is_file($model_generator_complex_cmoddel->get_handler_model_file()) || $override) {
              $model_generator_complex_cmoddel->generate_handler_model();

              $messages[] = 'Successfully created Handler' . strtoupper($name);
            } else
              $warnings[] = "The handler model " . $model_generator_complex_cmoddel->get_handler_model_file() . " already exists";
          }
        }

        $response->success();
      } catch (Exception $e) {
        WebApplication::add_error($e->getMessage());
        $response->data("errors", WebApplication::get_error_messages());
      }

      $response
        ->data("warnings", WebApplication::get_warning_messages())
        ->data("messages", WebApplication::get_notify_messages())
        ->render(true);
    }
  }

  function is_form_valid($tablename, $name) {

    if (!$tablename)
      throw new Exception("Invalid tablename");

    if (!$name)
      throw new Exception("Invalid name");

    return true;
  }
}
