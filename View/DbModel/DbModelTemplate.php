<?php

use Framework\Util\HtmlTableUtil;
use Framework\Util\HtmlUtil;


?>
<h1>Db, Model & Handler</h1>

<form id="form-db">
  <?php
  $data[] = [
    "",

  ];

  $data[] = ["Name:",];
  $data[] = [
    "",
    HtmlUtil::checkbox("primary_object_id", "1", false, ["class" => ""], "Model extends Object")
  ];

  $data[] = ["", HtmlUtil::checkbox("override", "1", $override, ["class" => "override"], "Override existing files")];
  $data[] = ["", HtmlUtil::link("javascript:;", "Generate", ["id" => "generate", "class" => "btn btn-primary"])];
  $dbTable = HtmlTableUtil::create()
    ->setData($data)
    ->setClass("")
    ->setPadding(3);

  $tablenameDd = HtmlUtil::dropdown("tablename", $tablenameList, $tablename, [
    "onKeyUp" => "update_class_name(this)",
    "onChange" => "update_class_name(this)",
    "size" => 30
  ], 50);
  ?>

  <table>
    <tr>
      <td>
        <div class="lbl">Table</div>
        <?php echo $tablenameDd ?>
      </td>
      <td>
        <div class="form-field">
          <div class="lbl">Namespace</div>
          <?php echo HtmlUtil::input("namespace", "Backend", ["class" => "namespace"]) ?>
        </div>
        <div class="form-field">
          <div class="lbl">Name</div>
          <?php echo HtmlUtil::input("pascalName", "") ?>
        </div>
        <div class="form-field">
          <div class="lbl">Generate Classes</div>
          <?php echo HtmlUtil::checkboxes("objects", [
            "dbo" => "Ddo",
            "dbq" => "Dbq",
            "trait" => "Trait",
            "cmodel" => "Model",
            "hmodel" => "Handler"
          ], ["dbo", "dbq", "trait"], ["class" => "objects"]) ?>
        </div>

        <div class="form-field">
          <div class="lbl">Options</div>
          <div>
            <?php echo HtmlUtil::checkbox("override", "1", $override, ["class" => "override"], "Override existing files") ?>
          </div>
          <div>
            <?php echo HtmlUtil::checkbox("primary_object_id", "1", false, ["class" => ""], "Model extends Object") ?>
          </div>
        </div>
        <?php echo HtmlUtil::link("javascript:;", "Generate", ["id" => "generate", "class" => "generate btn btn-primary"]) ?>
      </td>
    </tr>
  </table>
  <?php echo HtmlUtil::hidden("name", "", "name") ?>
</form>

<script>
  function pascalize(str) {
    return str.toLowerCase().replace(/(?:[^a-zA-Z0-9]+|^)(.)/g, function(match, chr) {
      return chr.toUpperCase();
    });
  }

  function update_class_name(obj) {
    value = $(obj).val();
    $("#pascalName").val(pascalize(get_singular(value)));
    $("#name").val(value);

    update_links(value, get_singular(value));
  }

  $(".objects").on("change", function() {
    $(".override").attr("checked", false);
  })

  $(".namespace").on("blur", function() {
    $(this).val($(this).val().sanitizeNamespace());
  })

  $("#name").on("input", function() {
    update_links("", $(this).val());
  })

  $("#generate").click(function() {
    $.post("/dbmodel/api", $("#form-db").serializeArray(), function(response) {

      FF.msg.clear();

      if (response.data.messages.length)
        FF.msg.success(response.data.messages);

      if (response.data.warnings.length)
        FF.msg.warning(response.data.warnings, {
          append: true
        });

      if (response.data.errors.length)
        FF.msg.error(response.data.errors, {
          append: true
        });
    });
  });
</script>
