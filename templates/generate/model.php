<h1>Model Creation</h1>
<form id="form-model">
<?
	
	$model_type_list = HTML_UTIL::get_checkboxes("model_types",array("c"=>"Complex","h"=>"Handler"),$model_types,array());

	$data[] = array("Model Type",$model_type_list);		
	$data[] = array("Model",HTML_UTIL::get_input("model",$model,array("style"=>"width:300px")));
	$data[] = array("Extends Model",HTML_UTIL::get_input("extends",$extends,array("style"=>"width:300px")));
	$data[] = array("Location:",HTML_UTIL::dropdown("location",CMODEL_GENERATOR::get_locations(),"",array("class"=>"wa")));
	$data[] = array("",HTML_UTIL::get_checkbox("override","1",$override,[],"Override"));
	$data[] = array("",HTML_UTIL::link("javascript:;","Generate",array("class"=>"btn btn-primary","id"=>"generate")));
	
	$db_table = HTML_TABLE_UTIL::create()
					->set_data($data)
					->set_padding(3)
					->set_class("");
	
	$tablename_dd_count = count($tablename_list);
	
	$tablename_dd = HTML_UTIL::dropdown("tablename",$tablename_list,$tablename,array("onKeyUp"=>"update_class_name(this)","onChange"=>"update_class_name(this)","size"=>30),$tablename_dd_count);
	
	HTML_TABLE_UTIL::create()
		->set_data(array(array("Table Name: ",$tablename_dd,$db_table->get_html())))
		->set_default_column_attribute("class","vat")
		->set_padding(3)
		->set_class("")
		->render();		
?>
</form>

<script>
	function update_class_name(obj) {
		$("#model").val(get_singular($(obj).val()));
	
		update_links($(obj).val(),get_singular($(obj).val()));
	}

	$("input[name='model']").on("input",function() {
		update_links("",$(this).val());
	});

	$("#generate").click(function() {
		$.post("/generate/domodel",$("#form-model").serializeArray(),function(response) {

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