<h1>DBOs &amp; DBQs</h1>
<form id="form-db">
<?

	
	$object_list = array("dbo"=>"DBO","dbq"=>"DBQ");
	
	$select_object_list = array();
	
	if($create_dbo)
		$select_object_list[] = "dbo";
		
	if($create_dbq)
		$select_object_list[] = "dbq";
	
	$data[] = array("Override:",HTML_UTIL::get_checkbox("override","1",$override));
	$data[] = array("Generate Classes:",HTML_UTIL::get_checkboxes("objects",$object_list,$select_object_list));
	$data[] = array("Class Name:",HTML_UTIL::get_input("classname",$classname,array("class"=>"w300")));
	$data[] = array("",HTML_UTIL::link("javascript:;","Generate",array("id"=>"generate","class"=>"btn btn-primary")));
	
	
	$db_table = new HTML_TABLE_UTIL();
	$db_table->set_data($data);
	$db_table->set_default_column_attribute("class","vat");	
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
		
		if($.inArray(value,dbqs)==-1) 
			$("input[name='objects[]'][value='dbq']").attr("checked","checked");
		else
			$("input[name='objects[]'][value='dbq']").removeAttr("checked");
		
		$("#classname").val(get_singular(value).toUpperCase());

		update_links(value,get_singular(value));
	}

	$("#classname").on("input",function() {
		update_links("",$(this).val());
	})

	$("#generate").click(function() {
		$.post("/generate/dodb",$("#form-db").serializeArray(),function(response) {

			if(response.has_success) {
				if(response.data.messages)
					FF.msg.success(response.data.messages);
			} else
				FF.msg.error(response.errors);

			if(response.data.warnings.length)
				FF.msg.warning(response.data.warnings,{ append: true });
		});
	});	
	
</script>