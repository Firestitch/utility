<?php

use Framework\Util\HtmlTableUtil;
use Framework\Util\HtmlUtil;
?>
<h1>Db, Model & Handler</h1>

<form id="form-db">
  <?php
  $data[] = array("Generate:", HtmlUtil::checkboxes("objects", ["dbo" => "Ddo", "dbq" => "Dbq", "trait" => "Trait", "cmodel" => "Model", "hmodel" => "Handler"], ["dbo", "dbq", "trait"], ["class" => "objects"]));
  $data[] = array("Name:", HtmlUtil::input("name", "", array("class" => "w300")));
  $data[] = array("", HtmlUtil::checkbox("primary_object_id", "1", false, ["class" => ""], "CMODEL has primary key object_id"));
  $data[] = array("", HtmlUtil::checkbox("override", "1", $override, ["class" => "override"], "Override existing files"));
  $data[] = array("", HtmlUtil::link("javascript:;", "Generate", array("id" => "generate", "class" => "btn btn-primary")));
  $dbTable = HtmlTableUtil::create()->setData($data)->setClass("")->setPadding(3);
  $tablenameDd = HtmlUtil::dropdown("tablename", $tablenameList, $tablename, array("onKeyUp" => "update_class_name(this)", "onChange" => "update_class_name(this)", "size" => 30), 50);
  HtmlTableUtil::create()->setData(array(array("Table Name: ", $tablenameDd, $dbTable->getHtml())))->setDefaultColumnAttribute("class", "vat")->setClass("")->setPadding(3)->render();
  ?>
</form>

<script>
  function update_class_name(obj) {
    value = $(obj).val();

    $("#name").val(get_singular(value).toUpperCase());

    update_links(value, get_singular(value));
  }

  $(".objects").on("change", function() {
    $(".override").attr("checked", false);
  })

  $("#name").on("input", function() {
    update_links("", $(this).val());
  })

  $("#generate").click(function() {
    $.post("/dbmodel", $("#form-db").serializeArray(), function(response) {

      FF.msg.clear();

      if (response.data.messages.length)
        FF.msg.success(response.data.messages);

      if (response.data.warnings.length)
        FF.msg.warning(response.data.warnings);

      if (response.data.errors.length)
        FF.msg.error(response.data.errors);
    });
  });
</script>
