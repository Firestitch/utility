<?

class ACTION_GENERATE_LISTVIEW extends ACTION_JSON {

  function __construct() {
    parent::__construct(SECURITY_MANAGER::ROLE_PUBLIC_USER);
  }

  function process() {

    $task         = strtolower($this->post("task"));
    $controller      = strtolower($this->post("controller"));
    $context      = strtolower($this->post("context"));
    $model         = strtolower($this->post("model"));
    $model_plural     = strtolower($this->post("model_plural"));
    $location      = $this->post("location");
    $list_body       = $this->post("list", "body");
    $view_settings     = $this->post("view_settings");
    $relation_field    = strtolower($this->post("relation_field"));
    $list_table_classes  = is_array($this->post("list_table_classes")) ? $this->post("list_table_classes") : array();
    $format        = $this->post("format");
    $override       = $this->post("override");
    $list_options     = is_array($this->post("list_options")) ? $this->post("list_options") : array();
    $security_roles   = is_array($this->post("security_roles")) ? $this->post("security_roles") : array();
    $frameworks     = is_array($this->post("frameworks")) ? $this->post("frameworks") : array();
    $dir        = DIR_INSTANCE . $location . "/";

    COOKIE_UTIL::set("list-view", JSON_UTIL::encode($this->posts()));

    $view_method = get_value($view_settings, "method");

    $messages = array();
    if ($this->is_form_valid($task, $controller, $context, $model, $dir)) {

      $list_format = value($format, "list");
      $view_format = value($format, "view");

      if ($view_format != VIEW_GENERATE_LISTVIEW::FORMAT_SKIP) {

        $va_generator_cmodel = new CMODEL_VA_GENERATOR_VIEW_UPDATE($controller, $task, $model_plural, $view_format, $security_roles, $dir, $relation_field);
        $va_generator_cmodel->set_method($view_method);
        $va_generator_cmodel->set_frameworks($frameworks);

        if ($override || !$va_generator_cmodel->has_view_file()) {

          $va_generator_cmodel->set_model($model);
          try {

            $va_generator_cmodel->generate($messages);
          } catch (Exception $e) {
            $this->error($e->getMessage());
          }
        } else {
          $this->error("The " . $va_generator_cmodel->get_view_file() . " view already exists");
          $this->error("The " . $va_generator_cmodel->get_view_template_file() . " template already exists");
        }
      }

      if ($list_body != VIEW_GENERATE_LISTVIEW::FORMAT_SKIP) {

        $search_form = array_key_exists("search_form", $list_options);

        $va_generator_cmodel = new CMODEL_VA_GENERATOR_VIEW_LIST($controller, $task, $model_plural, $list_format, $security_roles, $dir, $search_form, $view_format, $relation_field);
        $va_generator_cmodel->set_view_format($view_format);
        $va_generator_cmodel->set_view_method($view_method);
        $va_generator_cmodel->set_method($view_method);
        $va_generator_cmodel->set_list_body($list_body);
        $va_generator_cmodel->set_frameworks($frameworks);

        if ($override || !$va_generator_cmodel->has_view_file()) {

          try {

            $va_generator_cmodel->set_model($model);
            $va_generator_cmodel->generate($messages);
          } catch (Exception $e) {
            $this->error($e->getMessage());
          }
        } else {
          $this->error("The " . $va_generator_cmodel->get_view_file() . " view already exists");
          $this->error("The " . $va_generator_cmodel->get_view_template_file() . " template already exists");
        }

        if ($list_format == "A") {

          $model_condensed = str_replace("_", "", $model);

          $va_generator_cmodel = new CMODEL_VA_GENERATOR_VIEW_LIST_COMPONENT($controller, $task, $model_plural, $list_format, $security_roles, $dir, $search_form, $view_format, $relation_field, $list_table_classes);

          if ($override || !$va_generator_cmodel->has_view_file()) {

            try {

              $va_generator_cmodel->set_model($model);
              $va_generator_cmodel->set_list_body($list_body);
              $va_generator_cmodel->generate($messages);
            } catch (Exception $e) {
              $this->error($e->getMessage());
            }
          } else {
            $this->error("The " . $va_generator_cmodel->get_view_file() . " view already exists");
            $this->error("The " . $va_generator_cmodel->get_view_template_file() . " template already exists");
          }

          if ($list_format != VIEW_GENERATE_LISTVIEW::FORMAT_SKIP) {

            $va_generator_cmodel = new CMODEL_VA_GENERATOR_ACTION_ORDER($controller, $task, $list_format, $security_roles, $dir, $relation_field);
            $va_generator_cmodel->set_model($model);

            if ($va_generator_cmodel->has_priority_column()) {

              if ($override || !$va_generator_cmodel->has_action_file()) {
                try {

                  $va_generator_cmodel->generate($messages);
                } catch (Exception $e) {
                  $this->error($e->getMessage());
                }
              } else
                $this->error("The " . $va_generator_cmodel->get_action_file() . " action already exists");
            }
          }
        }
      }

      if ($view_format != VIEW_GENERATE_LISTVIEW::FORMAT_SKIP) {

        $va_generator_cmodel = new CMODEL_VA_GENERATOR_ACTION_UPDATE($controller, $task, $view_format, $security_roles, $dir, $relation_field);
        $va_generator_cmodel->set_method($view_method);
        if ($override || !$va_generator_cmodel->has_action_file()) {

          try {

            $va_generator_cmodel->set_model($model);
            $va_generator_cmodel->generate($messages);
          } catch (Exception $e) {
            $this->error($e->getMessage());
          }
        } else
          $this->error("The " . $va_generator_cmodel->get_action_file() . " action already exists");
      }

      if ($list_format != VIEW_GENERATE_LISTVIEW::FORMAT_SKIP) {
        $va_generator_cmodel = new CMODEL_VA_GENERATOR_ACTION_REMOVE($controller, $task, $list_format, $security_roles, $dir);

        if ($override || !$va_generator_cmodel->has_action_file()) {
          $va_generator_cmodel->set_model($model);
          $va_generator_cmodel->generate($messages);
        } else
          $this->error("The " . $va_generator_cmodel->get_action_file() . " action already exists");
      }
    }

    $this
      ->data("messages", $messages)
      ->success(!$this->has_errors());
  }

  function is_form_valid($task, $controller, $context, $model, $dir) {

    if (!$model) {
      $this->error("Please enter a model basename");
    } else {

      if (!is_file(MODEL_DB::get_dbo_file($model, $dir)))
        $this->error("The DBO `" . $model . "` does not exist");

      if (!is_file(MODEL_DB::get_dbq_file($model, $dir)))
        $this->error("The DBQ `" . $model . "` does not exist");

      if (!is_file(MODEL_MODEL::get_complex_model_file($model, $dir)))
        $this->error("Thsse complex model `" . $model . "` does not exist");

      if (!is_file(MODEL_MODEL::get_handler_model_file($model, $dir)))
        $this->error("The hanlder model `" . $model . "` does not exist");
    }

    if (is_empty($task))
      $this->error("Invalid task");

    if (is_empty($controller))
      $this->error("Invalid controller");


    return !$this->has_errors();
  }
}
