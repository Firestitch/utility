<?php

use Framework\Util\HtmlUtil;
use Framework\Util\JsonUtil;


?>
<h1>Map Model</h1>

<div class="mt15 mb20 cb">
  <div class="fl mr50">
    <div class="lbl">Relationship</div>
    <?php
    echo HtmlUtil::radiobuttons("relationship", ["child" => "Map Child", "children" => "Map Children"]);
    ?>
  </div>
  <div class="fl mr50 dns" id="object_names">
    <div class="lbl">Object Name</div>
    <?php
    echo HtmlUtil::radiobuttons("object_name", [
      "source" => HtmlUtil::span("N/A", ["id" => "source_object_name"]),
      "reference" => HtmlUtil::span("N/A", ["id" => "reference_object_name"]),
      "custom" => HtmlUtil::span(HtmlUtil::input("object_name_custom", "", [
        "class" => "object-name-custom",
        "placeholder" => "Custom"
      ]))
    ]);
    ?>
  </div>
  <div class="fl mr50">
    <div class="lbl">Joiner Tables</div>
    <?php
    echo HtmlUtil::button("add-joiner", "Add Joiner Table", ["class" => "btn"]);
    ?>
  </div>

  <div class="fl mr50">
    <?php
    echo HtmlUtil::button("generate", "Generate", ["class" => "btn-primary"]);
    ?>
  </div>
</div>
<div>
  <div class="model">
    <div class="lbl">Source Model</div>
    <div class="dib">
      <?php
      echo HtmlUtil::dropdown("source_model", $modelList, $model, [""], 15);
      ?>
    </div>

    <div class="mt10">
      <div id="source_fields"></div>
    </div>
  </div>

  <div class="model" id="joiners"></div>

  <div class="model">
    <div class="lbl">Reference Model</div>
    <div class="">
      <?php
      echo HtmlUtil::dropdown("reference_model", $modelList, $referenceModel, [], 15);
      ?>
    </div>

    <div class=" mt10">
      <div id="reference_fields"></div>
    </div>
  </div>
  <div class="cb"></div>
</div>

<?php
echo HtmlUtil::hidden("joiner_tables", JsonUtil::encode($joinerList));
?>

<script>
  $(function() {

    $("select[name='source_model']").bind("click keyup", function() {
      $("#source_fields").load("/mapmodel/sourcefields/", {
        source_model: $(this).val(),
        source_model_column: "<?php echo $sourceModelColumn ?>"
      });
    });

    if ($("select[name='source_model'] option:selected").length)
      $("select[name='source_model']").trigger("click");

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
          fields.load("/mapmodel/joinerfields/", {
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
          'class': 'joiner'
        })
        .append('<div class="lbl">Joiner Table</div>')
        .append(select)
        .append(fields));
    });

    $("#source_fields").on("click keyup", "select[name='source_model_column']", function() {
      $("#source_object_name").text($(this).val().replace(/_id$/, ''));
    });

    $("select[name='reference_model']").bind("click keyup", function() {
      $("#reference_object_name").text($(this).val());
      $("#reference_fields").load("/mapmodel/referencefields/", {
        reference_model: $(this).val()
      }, function() {
        $("#reference_model_column").find("option[value='" + $("#source_model_column").val() + "']").attr("selected", "selected");
      });
    });

    if ($("select[name='reference_model'] option:selected").length)
      $("select[name='reference_model']").trigger("click");

    $("#add-join").click(function() {

      if ($(this).is(":checked")) {
        $("#joiner").show();
      } else
        $("#joiner").hide();
    });


    $("#generate").click(function() {
      $.post("/mapmodel", $("#form-relation").serializeArray(), function(response) {

        FF.msg.clear();

        if (response.success) {
          FF.msg.success('Successfully generated');
        } else
          FF.msg.error(response.errors);

        if (response.data.warnings.length)
          FF.msg.warning(response.data.warnings, {
            append: true
          });
      });
    });

  });
</script>

<style>
  .model {
    float: left;
    margin-right: 50px;
  }

  .joiner {
    display: inline-block;
    margin-right: 50px;
    vertical-align: top;
  }

  .joiner:last-child {
    margin-right: 0px;
  }

  .object-name-custom {
    display: inline-block;
    width: 200px;
  }
</style>