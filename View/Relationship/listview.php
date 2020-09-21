<h1>List View Generation</h1>

<?
	$data[] = array('<h3>Model Settings</h3>',"");

	$data[] = array("Model: ",HTML_UTIL::dropdown("model",$models,$model,array("placeholder"=>"user")));
	//$data[] = array(" ","<i>Model Basename is the corresponding database table name and cannot be plural</i>");

	$data[] = array("Relation Field Name: ",HTML_UTIL::input("relation_field",$relation_field,array("placeholder"=>"user_id")));

	$data[] = array('<h3>Location</h3>',"");

	$data[] = array("Roles: ",HTML_UTIL::dropdown("security_roles",$permissions,[],array(),count($permissions),true));

	$data[] = array("URL: ","/ ".HTML_UTIL::input("controller",$controller,array("placeholder"=>"manage","class"=>"dib w200"))." / ".HTML_UTIL::input("task",$task,array("placeholder"=>"user","class"=>"dib w200")));

	$data[] = array("Plural URL: ",HTML_UTIL::div("/ ".HTML_UTIL::span("",array("id"=>"plural-controller"))." / ".HTML_UTIL::input("model_plural","",array("class"=>"dib w200")),array("class"=>"wsnw")));

	$data[] = array('<h3>List Settings</h3>',"");

	$data[] = array("Body Type: ",HTML_UTIL::dropdown("list[body]",array("B"=>"Full Page","U"=>"Popup","L"=>"Blank",VIEW_GENERATE_LISTVIEW::FORMAT_SKIP=>"None")));

	$data[] = array("Method: ",HTML_UTIL::radiobuttons("format[list]",array("A"=>"Ajax","P"=>"Post"),get_value($format,"list")));

	$data[] = array("Options",HTML_UTIL::checkbox("list_options[search_form]","1",get_value($list_options,"search_form"),array(),"Search Form"));

	$data[] = array('<h3>View Settings</h3>',"");

	$data[] = array("Body Type: ",HTML_UTIL::dropdown("format[view]",array("P"=>"Full Page","U"=>"Popup","L"=>"Blank",VIEW_GENERATE_LISTVIEW::FORMAT_SKIP=>"None"),get_value($format,"view")));

	$data[] = array("Method: ",HTML_UTIL::radiobuttons("view_settings[method]",array("A"=>"Ajax","P"=>"Post"),get_value($view_settings,"method")));

	$data[] = array('<h3>Options</h3>',"");

	$data[] = array("Frontend: ",HTML_UTIL::dropdown("frameworks",$frameworks,$selected_frameworks,array(),count($frameworks),true));

	$data[] = array("Location:",HTML_UTIL::dropdown("location",CMODEL_GENERATOR::get_locations(),"",array("class"=>"wa")));

	$data[] = array("",HTML_UTIL::get_checkbox("override","1",$override,array(),"Override"));

	$data[] = array("",HTML_UTIL::button("generate","Generate",array("id"=>"generate","class"=>"btn-primary")));

	$data[] = array("",HTML_UTIL::div("",array("class"=>"pt20")));

	$html_table = new HTML_TABLE_UTIL();
	$html_table->set_data($data);
	$html_table->set_row_id_prefix("row-");
	$html_table->set_column_attribute(0,"class","vat");
	$html_table->set_padding(2);
	$html_table->set_class("");
	$html_table->render();

?>
<script>

$(function() {

	$("input[name='controller']").keyup(function() {
		$("#plural-controller").text($(this).val());
	});

	$("select[name='model']").change(function() {
		$("input[name='task']").val($(this).val().toLowerCase().replace(/[_\s]/g,""));

		if($(this).val())
			$("input[name='model_plural']").val($("input[name='task']").val() + "s");
	});

	$("#generate").click(function() {
		$.post("/generate/dolistview",$("#form-list-view").serializeArray(),function(response) {

			if(response.has_success) {
				FF.msg.success(response.data.messages);
			}
			else
				FF.msg.error(response.errors);
		});
	});

});

</script>