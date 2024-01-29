<?php

use Framework\Util\HtmlUtil;
use Utility\View\Namespaces\NamespacesView;

?>

<h1>API Generation</h1>

<?php NamespacesView::create()->show(); ?>

<div class="form-field">
  <div class="lbl">API</div>
  <div id="apis"></div>
</div>

<div class="form-field">
  <div class="lbl">Model</div>
  <div id="models"></div>
</div>

<div class="form-field">
  <div class="lbl">Enpoint Name</div>
  <?php echo HtmlUtil::input("method", "", ["placeholder" => "ie. accounts"]) ?>
</div>

<div class="form-field">
  <div class="lbl">Model Plural Name</div>
  <?php echo HtmlUtil::input("model-plural", "", ["placeholder" => "ie. accounts"]) ?>
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
      "delete",
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
  $(function () {

    $(".namespace").on("change", function () {
      $("#apis").load("/api/apis", {
        namespace: $('.namespace').val(),
      }, function () {
        $("select[name='api']").change(function () {
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
      }, function () {

        $("select[name='model']").on("change click", function () {

          if ($(this).val()) {
            $("input[name='model-plural']").val($(this).val().plural());

            var method = $("input[name='model-plural']").val()
              .replace(getSingular($(".api-name").val()) + '_', '');
            $("input[name='method']").val(method);
          }

          updateNamespaceExample();
        });

      });
    }).trigger('change');

    $("input[name='method']").keydown(updateNamespaceExample);

    $("#generate").click(function () {
      $.post("/api/api", $("#form-api").serializeArray(), function (response) {
        displayResponse(response);
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