<?php

use Framework\Util\HtmlUtil;
use Utility\View\Namespaces\NamespacesView;

?>
<h1>Db, Model & Handler</h1>

<form id="form-db">
  <?php
  $tablenameDd = HtmlUtil::dropdown("tablename", $tablenameList, $tablename, [
    "onKeyUp" => "update_class_name(this)",
    "onChange" => "update_class_name(this)",
    "size" => 30,
  ], 50);
  ?>

  <table>
    <tr>
      <td>
        <div class="lbl">Table</div>
        <?php echo $tablenameDd ?>
      </td>
      <td>
        <?php NamespacesView::create()->setClass("namespace")->show(); ?>
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
            "hmodel" => "Handler",
          ], ["dbo", "dbq", "trait"], ["class" => "objects"]) ?>
        </div>

        <div class="form-field">
          <div class="lbl">Options</div>
          <div>
            <?php echo HtmlUtil::checkbox("override", "1", $override, ["class" => "override"], "Override existing files") ?>
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
    return str.toLowerCase().replace(/(?:[^a-zA-Z0-9]+|^)(.)/g, function (match, chr) {
      return chr.toUpperCase();
    });
  }

  function update_class_name(obj) {
    value = $(obj).val();
    $("#pascalName").val(pascalize(get_singular(value)));
    $("#name").val(value);

    update_links(value, get_singular(value));
  }

  $(".objects").on("change", function () {
    $(".override").attr("checked", false);
  })

  $("#name").on("input", function () {
    update_links("", $(this).val());
  })

  $("#generate").click(function () {
    $.post("/dbmodel/api", $("#form-db").serializeArray(), function (response) {
      displayResponse(response, 'Successfully generated');
    });
  });
</script>