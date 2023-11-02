<?php

use Framework\Util\HtmlUtil;


?>
<h1>Model Interface</h1>

<div class="row row-container">
  <div class="col">
    <div class="form-field">
      <div class="lbl">Namespace</div>
      <?php echo HtmlUtil::input("namespace", "Backend", ["class" => "source-namespace"]) ?>
    </div>

    <div class="form-field">
      <div class="lbl">Model</div>
      <div id="sourceModels"></div>
    </div>
  </div>

  <div class="col">
    <div class="lbl">Output</div>
    <code id="output"></code>
  </div>
  <div class="cb"></div>
</div>


<script>
  function camelize(s) {
    return s.replace(/([-_][a-z])/ig, ($1) => {
      return $1.toUpperCase()
        .replace('-', '')
        .replace('_', '');
    });
  }

  $(function () {
    $(".source-namespace").on("keyup", function () {
      $("#sourceModels").load("/model/list", {
        namespace: $('.source-namespace').val(),
        name: 'sourceModel',
        limit: 30
      }, function () {
        $("select[name='sourceModel']").bind("click keyup", function () {
          $.post("/model/interface/api", $("#form-relation").serializeArray(), function (response) {
            $('#output').html(response);
          });
        });
      });
    }).trigger('keyup');

    $(".source-namespace").on("blur", function () {
      $(".reference-namespace").val($(this).val().sanitizeNamespace());
      $(".reference-namespace").trigger("keyup");
    });

    $("#generate").click(function () {
      $.post("/model/interface/api", $("#form-relation").serializeArray(), function (response) {
        debugger;
        displayResponse(response, 'Successfully generated');
      });
    });

  });
</script>