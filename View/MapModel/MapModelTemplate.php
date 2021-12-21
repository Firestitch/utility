<?php

use Framework\Util\HtmlUtil;
use Framework\Util\JsonUtil;


?>
<h1>Map Model</h1>

<div class="row row-container">
  <div class="col">
    <div class="form-field">
      <div class="lbl">Source Namespace</div>
      <?php echo HtmlUtil::input("source-namespace", "Backend", ["class" => "source-namespace"]) ?>
    </div>

    <div class="form-field">
      <div class="lbl">Source Model</div>
      <div id="source_models"></div>
    </div>

    <div>
      <div class="form-field">
        <div class="lbl">Source Field</div>
        <div id="source_fields">Source Model Not Selected</div>
      </div>
    </div>
  </div>

  <div>
    <div class="row">
      <div id="joiners"></div>
      <div class="add-joiner">
        <?php echo HtmlUtil::button("add-joiner", "+", ["class" => "btn"]) ?>
      </div>
    </div>
  </div>


  <div class="col">
    <div class="form-field">
      <div class="lbl">Reference Namespace</div>
      <?php echo HtmlUtil::input("reference-namespace", "Backend", ["class" => "reference-namespace"]) ?>
    </div>

    <div class="form-field">
      <div class="lbl">Reference Model</div>
      <div id="reference_models"></div>
    </div>

    <div class=" mt10">
      <div class="form-field">
        <div class="lbl">Reference Field</div>
        <div id="reference_fields">Reference Model Not Selected</div>
      </div>
    </div>
  </div>
  <div class="cb"></div>
</div>

<div class="form-field relationship">
  <div class="lbl">Relationship</div>
  <?php echo HtmlUtil::radiobuttons("relationship", ["child" => "Map Child", "children" => "Map Children"], "", [], true, "") ?>
</div>
<div class="form-field">
  <div class="lbl">Object Name</div>
  <?php echo HtmlUtil::input("object_name", "", ["class" => "object-name"]);
  ?>
</div>

<div class="generate">
  <?php echo HtmlUtil::button("generate", "Generate", ["class" => "btn-primary"]) ?>
  <?php echo HtmlUtil::hidden("joiner_tables", JsonUtil::encode($joinerList)) ?>
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

  $(function() {
    $(".source-namespace").on("keyup", function() {
      $("#source_fields").html("Source Model Not Selected");
      $("#source_models").load("/generate/model/list", {
        namespace: $('.source-namespace').val(),
        name: 'source_model',
        limit: 12
      }, function() {
        $("select[name='source_model']").bind("click keyup", function() {
          $("#source_fields").load("/generate/model/fields", {
            model: $(this).val(),
            namespace: $('.source-namespace').val(),
            name: "source_model_column"
          }, function() {
            $("select[name='source_model_column']").on("click keyup", function() {
              $(".object-name").val(camelize($(this).val().replace(/_id$/, '')));
            });
          });
        });

        if ($("select[name='source_model'] option:selected").length) {
          $("select[name='source_model']").trigger("click");
        }
      });
    }).trigger('keyup');

    $(".reference-namespace").on("keyup", function() {
      $("#reference_fields").html("Reference Model Not Selected");
      $("#reference_models").load("/generate/model/list", {
        namespace: $('.reference-namespace').val(),
        name: 'reference_model',
        limit: 12
      }, function() {
        $("select[name='reference_model']").bind("click keyup", function() {
          $("#reference_fields").load("/generate/model/fields", {
            model: $(this).val(),
            namespace: $('.reference-namespace').val(),
            name: "reference_model_column"
          }, function() {
            $("#reference_model_column").find("option[value='" + $("#source_model_column").val() + "']").attr("selected", "selected");
          });
        });

        if ($("select[name='reference_model'] option:selected").length) {
          $("select[name='reference_model']").trigger("click");
        }
      });
    }).trigger('keyup');

    $(".reference-namespace,.source-namespace").on("blur", function() {
      $(this).val($(this).val().sanitizeNamespace());
    });

    $(".source-namespace").on("blur", function() {
      $(".reference-namespace").val($(this).val().sanitizeNamespace());
      $(".reference-namespace").trigger("keyup");
    });

    $("#add-joiner").on("click", function() {
      var fields = $("<div>", {
        "class": "joiner-fields"
      });
      var index = $(".joiner").length;
      var tables = JSON.parse($("#joiner_tables").val());
      var select = $("<select>", {
          name: 'joiners[' + index + '][table]',
          'class': 'form-control'
        })
        .attr("size", 15)
        .click(function() {
          fields.load("/generate/mapmodel/joinerfields/", {
            index: index,
            table: $(this).val()
          });
        });

      $.each(tables, function(index, value) {
        select.append($("<option>", {
          name: index
        }).text(value));
      });

      $("#joiners").append($("<div>", {
          'class': 'joiner col'
        })
        .append('<div class="lbl">Joiner Table</div>')
        .append(select)
        .append(fields));
    });


    $("#add-join").click(function() {
      if ($(this).is(":checked")) {
        $("#joiner").show();
      } else
        $("#joiner").hide();
    });


    $("#generate").click(function() {
      $.post("/generate/mapmodel/api", $("#form-relation").serializeArray(), function(response) {
        FF.msg.clear();
        if (response.success) {
          FF.msg.success('Successfully generated');
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