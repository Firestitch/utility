<?php

use Framework\Util\HtmlUtil;

?>

<h1>API Generation</h1>

<div class="form-field">
  <div class="lbl">Namespace</div>
  <?php echo HtmlUtil::input("namespace", "Backend", ["class" => "namespace"]) ?>
</div>

<div class="form-field">
  <div class="lbl">API</div>
  <div id="apis"></div>
</div>

<div class="form-field">
  <div class="lbl">Model</div>
  <div id="models"></div>
</div>

<div class="form-field">
  <div class="lbl">Plural Model Name</div>
  <?php echo HtmlUtil::input("model-plural", "", ["placeholder" => "ie. accounts"]) ?>
</div>

<div class="form-field">
  <div class="lbl">Enpoint Name</div>
  <?php echo HtmlUtil::input("method", "", ["placeholder" => "ie. accounts"]) ?>
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

    $(".namespace").on("keyup", function() {
      $("#apis").load("/api/apis", {
        namespace: $('.namespace').val(),
      }, function() {
        $("select[name='api']").change(function() {
          updateNamespaceExample();

          var existing = $(".api-existing").parents("tr");
          if ($(this).val()) {
            existing.show();
          } else {
            existing.hide();
          }
        }).trigger("change");
      });

      $("#models").load("/model/list", {
        namespace: $('.namespace').val(),
        name: 'model'
      }, function() {

        $("select[name='model']").on("change click", function() {

          if ($(this).val()) {
            $("input[name='model-plural']").val($(this).val().plural());

            var method = $("input[name='model-plural']").val()
              .replace(get_singular($(".api-name").val()) + '_', '');
            $("input[name='method']").val(method);
          }

          updateNamespaceExample();
        });

      });
    }).trigger('keyup');

    $("input[name='method']").keydown(updateNamespaceExample);

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