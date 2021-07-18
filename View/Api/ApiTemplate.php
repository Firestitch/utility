<?php

use Framework\Util\FormUtil;


?>

<h1>API Generation</h1>

<?php
FormUtil::create()
  ->dropdown("api", "API", ["" => "Create new API", "Existing API" => $apis], "", false, ["class" => "w400 api-name"])
  ->dropdown("model", "Model", $models, "", ["placeholder" => "ie. account", "class" => "w400"])
  ->input("model-plural", "Plural Model Name", "", false, null, null, 1, ["placeholder" => "ie. accounts", "class" => "w400"])
  ->input("method", "Namespace", "", false, null, null, 1, [
    "placeholder" => "ie. users",
    "class" => "w400 api-existing"
  ])
  ->checkboxes("methods", "Methods", ["get" => "GET", "post" => "POST", "put" => "PUT", "delete" => "DELETE"], [
    "get",
    "put",
    "post",
    "delete"
  ])
  ->checkboxes("options", "Options", ["order" => "Add ordering method", "override" => "Override existing files"])
  ->button("generate", "Generate", ["type" => "button", "id" => "generate", "class" => "btn-primary"])
  ->render();
?>
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
