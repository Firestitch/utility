<?php

use Framework\Util\HtmlUtil;
use Utility\View\Namespaces\NamespacesView;

?>
<h1>Db, Model & Handler</h1>

<form id="form-db">
  <?php
  $tablenameDd = HtmlUtil::dropdown("tablename", $tablenameList, $tablename, [
    "onKeyUp" => "tablenameChange(this)",
    "onChange" => "tablenameChange(this)",
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
        <?php NamespacesView::create()->show(); ?>
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

  function tablenameChange(obj) {
    value = $(obj).val();
    $("#pascalName").val(pascalize(getSingular(value)));
    $("#name").val(value);
    exists();
  }

  function exists() {
    $.post("/dbmodel/api/exists", $("#form-db").serializeArray(), function (response) {
      $('input[value="cmodel"]').prop("checked", response.data.modelExists ? false : true);
      $('input[value="hmodel"]').prop("checked", response.data.handlerExists ? false : true);

      if (!response.data.dbqExists) {
        $('input[value="dbq"]').prop("checked", true);
      }

      if (!response.data.dboExists) {
        $('input[value="dbo"]').prop("checked", true);
      }

      if (!response.data.traitExists) {
        $('input[value="trait"]').prop("checked", true);
      }

      $('#override').prop("checked", response.data.dbqExists || response.data.dboExists ? true : false);
    });
  }

  $(".objects").on("change", function () {
    $(".override").prop("checked", false);
  });

  $("#namespace").on("change", function () {
    exists();
  });

  $("#generate").click(function () {
    $.post("/dbmodel/api/generate", $("#form-db").serializeArray(), function (response) {
      displayResponse(response, 'Successfully generated');
    });
  });
</script>