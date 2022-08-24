<?php

use Framework\Util\FormUtil;


?>

<h1>WSDL Generation</h1>

<?php
FormUtil::create()
  ->dropdown("api", "API", $apis, "", ["class" => "w400 api-name"])
  ->button("generate", "Generate", ["type" => "button", "id" => "generate", "class" => "btn-primary"])
  ->render();
?>
<script>
  $(function() {

    var apiLoads = $(".api-loads").hide();

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
          .replace(get_singular($(".api-name").val()) + '_', '')
          .replace('_', '');
        $("input[name='method']").val(method);
      }

      updateNamespaceExample();
    });

    $("#generate").click(function() {
      $.post("/wsdl", $("#form-api").serializeArray(), function(response) {
        displayResponse(response, 'Successfully generated');
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