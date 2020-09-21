<?

use Framework\Util\HTML_TABLE_UTIL;
use Framework\Util\HTML_UTIL;
use Utility\Model\GeneratorModel;

?>
<h1>Dbo, Ddq, Model & Handler Generation</h1>

<form id="form-db">
  <?
  $data[] = array("Generate:", HTML_UTIL::checkboxes("objects", [
    "dbo" => "Ddo",
    "dbq" => "Dbq",
    "cmodel" => "Model",
    "hmodel" => "Handler"
  ], ["dbo", "dbq"], ["class" => "objects"]));
  $data[] = array("Name:", HTML_UTIL::input("name", "", array("class" => "w300")));
  $data[] = array("", HTML_UTIL::checkbox("primary_object_id", "1", false, ["class" => ""], "CMODEL has primary key object_id"));
  $data[] = array("", HTML_UTIL::checkbox("override", "1", $override, ["class" => "override"], "Override existing files"));
  $data[] = array("", HTML_UTIL::link("javascript:;", "Generate", array("id" => "generate", "class" => "btn btn-primary")));

  $db_table = HTML_TABLE_UTIL::create()
    ->set_data($data)
    ->set_class("")
    ->set_padding(3);

  $tablename_dd = HTML_UTIL::dropdown("tablename", $tablename_list, $tablename, array("onKeyUp" => "update_class_name(this)", "onChange" => "update_class_name(this)", "size" => 30), 50);

  HTML_TABLE_UTIL::create()
    ->set_data(array(array("Table Name: ", $tablename_dd, $db_table->get_html())))
    ->set_default_column_attribute("class", "vat")
    ->set_class("")
    ->set_padding(3)
    ->render();
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
