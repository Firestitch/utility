<?php

use DBlackborough\Quill\Parser\Html;
use Framework\Util\FormUtil;
use Framework\Util\HtmlUtil;

?>

<h1>API Generation</h1>

<?php

// ->dropdown("model", "Model", $models, "", ["placeholder" => "ie. account", "class" => "w400"])
// ->input("model-plural", "Plural Model Name", "", false, null, null, 1, ["placeholder" => "ie. accounts", "class" => "w400"])
// ->input("method", "Namespace", "", false, null, null, 1, [
//   "placeholder" => "ie. users",
//   "class" => "w400 api-existing"
// ])
// ->checkboxes("options", "Options", ["order" => "Add ordering method", "override" => "Override existing files"])
// ->button("generate", "Generate", ["type" => "button", "id" => "generate", "class" => "btn-primary"])
// ->render();
?>


<div class="form-field">
  <div class="lbl">API</div>
  <?php echo HtmlUtil::dropdown("api", ["" => "Create new API", "Existing API" => $apis], "", ["class" => "w400 api-name"]) ?>
</div>

<div class="form-field">
  <div class="lbl">Model</div>
  <?php echo HtmlUtil::dropdown("model", $models, "", ["class" => "w400"]) ?>
</div>

<div class="form-field">
  <div class="lbl">Plural Model Name</div>
  <?php echo HtmlUtil::input("model-plural", "", ["placeholder" => "ie. accounts", "class" => "w400"]) ?>
</div>

<div class="form-field">
  <div class="lbl">Enpoint Name</div>
  <?php echo HtmlUtil::input("method", "", ["placeholder" => "ie. accounts", "class" => "w400"]) ?>
</div>

<div class="form-field">
  <div class="lbl">Methods</div>
  <?php echo HtmlUtil::checkboxes(
    "methods",
    ["get" => "GET", "post" => "POST", "put" => "PUT", "delete" => "DELETE"],
    [
      "get",
      "put",
      "post",
      "delete"
    ]
  ) ?>
</div>

<div class="form-field">
  <div class="lbl">Options</div>
  <?php echo HtmlUtil::checkboxes(
    "options",
    ["order" => "Add ordering method", "override" => "Override existing files"]
  ) ?>
</div>

<?php echo HtmlUtil::button("generate", "Generate", ["type" => "button", "id" => "generate", "class" => "btn-primary"]) ?>

<script>
  $(function() {

    $("select[name='api']").change(function() {

      updateNamespaceExample();

      var existing = $(".api-existing").parents("tr");
      if ($(this).val()) {
        existing.show();
      } else {
        existing.hide();
      }

    }).trigger("change");

    $("input[name='method']").keydown(updateNamespaceExample);

    $("select[name='model']").change(function() {

      if ($(this).val()) {
        $("input[name='model-plural']").val($(this).val().plural());

        var method = $("input[name='model-plural']").val()
          .replace(get_singular($(".api-name").val()) + '_', '');
        $("input[name='method']").val(method);
      }

      updateNamespaceExample();
    });

    $("#generate").click(function() {
      $.post("/api", $("#form-api").serializeArray(), function(response) {

        if (response.data.messages.length)
          FF.msg.success(response.data.messages);

        if (response.data.errors.length)
          FF.msg.error(response.data.errors);

      });
    });

    function updateNamespaceExample() {
      var namespace = "/" + $("select[name='api']").val();

      if ($("input[name='method']").val())
        namespace += "/id/" + $("input[name='method']").val();

      $("#namespace-example").text(namespace);
    }

  });
</script>