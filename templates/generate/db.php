<h1>DBOs &amp; DBQs</h1>
<form id="form-db">
<?


	$object_list = array("dbo"=>"DBO","dbq"=>"DBQ");

	$select_object_list = ["dbo","dbq"];

	$data[] = array("Generate:",HTML_UTIL::get_checkboxes("objects",$object_list,$select_object_list));
	$data[] = array("Class Name:",HTML_UTIL::get_input("classname",$classname,array("class"=>"w300")));
	$data[] = array("Location:",HTML_UTIL::dropdown("location",CMODEL_GENERATOR::get_locations(),"",array("class"=>"wa")));
	$data[] = array("",HTML_UTIL::get_checkbox("override","1",$override,[],"Override"));
	$data[] = array("",HTML_UTIL::link("javascript:;","Generate",array("id"=>"generate","class"=>"btn btn-primary")));


	$db_table = new HTML_TABLE_UTIL();
	$db_table->set_data($data);
	$db_table->set_column_attribute(0,"style","padding-top:2px");
	$db_table->set_row_id_prefix("row-");
	$db_table->set_class("");
	$db_table->set_padding(3);

	$tablename_dd = HTML_UTIL::dropdown("tablename",$tablename_list,$tablename,array("onKeyUp"=>"update_class_name(this)","onChange"=>"update_class_name(this)","size"=>30),50);

	$html_table = new HTML_TABLE_UTIL();
	$html_table->set_data(array(array("Table Name: ",$tablename_dd,$db_table->get_html())));
	$html_table->set_default_column_attribute("class","vat");
	$html_table->set_class("");
	$html_table->set_padding(3);
	$html_table->render();

?>
</form>

<script>

	var dbqs = [<?=implode(",",$existing_dbqs)?>];

	function update_class_name(obj) {
		value = $(obj).val();

		$("#classname").val(get_singular(value).toUpperCase());

		update_links(value,get_singular(value));
	}

	$("#classname").on("input",function() {
		update_links("",$(this).val());
	})

	$("#generate").click(function() {
		$.post("/generate/dodb",$("#form-db").serializeArray(),function(response) {

			if(response.data.messages)
				FF.msg.success(response.data.messages);

			if(response.data.warnings.length)
				FF.msg.warning(response.data.warnings,{ append: true });
		});
	});

</script>