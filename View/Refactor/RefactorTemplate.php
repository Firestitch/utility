<?php

use Framework\Util\HtmlUtil;

?>
<h1>Refactor Model</h1>

<div class="row row-container">
  <div class="col">
    <div class="form-field">
      <div class="lbl">Namespace</div>
      <?php echo HtmlUtil::input("sourceNamespace", "Backend", ["class" => "sourceNamespace"]) ?>
    </div>

    <div class="form-field">
      <div class="lbl">Model</div>
      <div id="source_models"></div>
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
      $("#source_models").load("/model/list", {
        namespace: $('.sourceNamespace').val(),
        name: 'source_model',
        limit: 30
      }, function() {});
    }

    $(function() {
      $(".sourceNamespace").on("keyup", function() {
        $("#source_fields").html("Source Model Not Selected");
        loadModels();
      }).trigger('keyup');

      $(".reference-namespace,.sourceNamespace").on("blur", function() {
        $(this).val($(this).val().sanitizeNamespace());
      });

      $("#generate").click(function() {
        $.post("/refactor/api", $("#form-relation").serializeArray(), function(response) {
          FF.msg.clear();
          if (response.success) {
            loadModels();
            FF.msg.success('Successfully deleted');
          } else
            FF.msg.error(response.data.errors);

          if (response.data.warnings.length)
            FF.msg.warning(response.data.warnings, {
              append: true
            });
        });
      });

    });
  </script>