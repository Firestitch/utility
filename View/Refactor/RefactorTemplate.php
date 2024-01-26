<?php

use Framework\Util\HtmlUtil;

?>
<h1>Refactor Model</h1>

<div class="row row-container">
  <div class="col">
    <div class="form-field">
      <div class="lbl">Namespace</div>
      <?php echo HtmlUtil::input("namespace", "Backend", ["class" => "namespace"]) ?>
    </div>

    <div class="form-field">
      <div class="lbl">Model</div>
      <div id="models"></div>
    </div>
  </div>

  <div class="generate">
    <?php echo HtmlUtil::button("generate", "Delete", ["class" => "btn-primary"]) ?>
  </div>

  <script>
    function lowerlize(s) {
      return s && s[0].toLowerCase() + s.slice(1);
    }

    function camelize(s) {
      return s.replace(/([-_][a-z])/ig, ($1) => {
        return $1.toUpperCase()
          .replace('-', '')
          .replace('_', '');
      });
    }

    function loadModels() {
      $("#models").load("/model/list", {
        namespace: $('.namespace').val(),
        name: 'models',
        multiple: true,
        limit: 30
      }, function () { });
    }

    $(function () {
      $(".namespace").on("keyup", function () {
        loadModels();
      }).trigger('keyup');

      $(".reference-namespace,.namespace").on("blur", function () {
        $(this).val($(this).val().sanitizeNamespace());
      });

      $("#generate").click(function () {
        $.post("/refactor/api", $("#form-relation").serializeArray(), function (response) {
          displayResponse(response, 'Successfully refactored');
          if (response.code === 200) {
            loadModels();
          }
        });
      });

    });
  </script>