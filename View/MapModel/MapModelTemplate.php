<?php

use Framework\Util\HtmlUtil;
use Framework\Util\JsonUtil;
use Utility\View\Namespaces\NamespacesView;


?>
<h1>Map Model</h1>

<div class="row row-container">
  <div class="col">
    <?php NamespacesView::create()
      ->setName("sourceNamespace")
      ->setLabel("Source Namespace")
      ->show();
    ?>

    <div class="form-field">
      <div class="lbl">Source Model</div>
      <div id="sourceModels"></div>
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
    <?php NamespacesView::create()
      ->setName("referenceNamespace")
      ->setLabel("Reference Namespace")
      ->show();
    ?>

    <div class="form-field">
      <div class="lbl">Reference Model</div>
      <div id="referenceModels"></div>
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

  $(function () {
    $(".source-namespace").on("change", function () {
      $("#source_fields").html("Source Model Not Selected");
      $("#sourceModels").load("/model/list", {
        namespace: $('.source-namespace').val(),
        name: 'sourceModel',
        limit: 12
      }, function () {
        $("select[name='sourceModel']").bind("click keyup", function () {
          $("#source_fields").load("/model/fields", {
            model: $(this).val(),
            namespace: $('.source-namespace').val(),
            name: "sourceModelColumn"
          }, function () {
            $("select[name='sourceModelColumn']").on("click keyup", function () {
              $(".object-name").val(camelize($(this).val().replace(/_id$/, '')));
            });
          });
        });

        if ($("select[name='sourceModel'] option:selected").length) {
          $("select[name='sourceModel']").trigger("click");
        }
      });
    }).trigger('change');

    $(".reference-namespace").on("change", function () {
      $("#reference_fields").html("Reference Model Not Selected");
      $("#referenceModels").load("/model/list", {
        namespace: $('.reference-namespace').val(),
        name: 'referenceModel',
        limit: 12
      }, function () {
        $("select[name='referenceModel']").bind("click keyup", function () {
          $("#reference_fields").load("/model/fields", {
            model: $(this).val(),
            namespace: $('.reference-namespace').val(),
            name: "referenceModelColumn"
          }, function () {
            $("#referenceModelColumn").find("option[value='" + $("#sourceModelColumn").val() + "']").attr("selected", "selected");
          });
        });

        if ($("select[name='referenceModel'] option:selected").length) {
          $("select[name='referenceModel']").trigger("click");
        }
      });
    }).trigger('change');

    $("#add-joiner").on("click", function () {
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
        .click(function () {
          fields.load("/mapmodel/joinerfields/", {
            index: index,
            table: $(this).val()
          });
        });

      $.each(tables, function (index, value) {
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


    $("#add-join").click(function () {
      if ($(this).is(":checked")) {
        $("#joiner").show();
      } else
        $("#joiner").hide();
    });


    $("#generate").click(function () {
      $.post("/mapmodel/api", $("#form-relation").serializeArray(), function (response) {
        displayResponse(response, 'Successfully generated');
      });
    });

  });
</script>