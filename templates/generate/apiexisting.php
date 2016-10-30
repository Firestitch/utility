<h1>API Generation</h1>

<?
	$data[] = array("API: ",HTML_UTIL::input("api",$model,array("placeholder"=>"","class"=>"w200")));
	$data[] = array("Model Name: ",HTML_UTIL::input("model","",array("placeholder"=>"","class"=>"w200")));
	$data[] = array("Plural Model Name: ",HTML_UTIL::input("model-plural","",array("placeholder"=>"","class"=>"w200")));
	$data[] = array("",HTML_UTIL::button("generate","Generate",array("id"=>"generate"))." ".HTML_UTIL::checkbox("override","1","",array(),"Override"));
	$data[] = array("",HTML_UTIL::get_div("",array("class"=>"pt20")));

	HTML_TABLE_UTIL::create()
		->set_data($data)
		->set_column_attribute(0,"class","vat")
		->set_padding(2)
		->set_class("")
		->render();

?>
<script>

$(function() {

	$("input[name='model']").keyup(function() {
		if($(this).val())
			$("input[name='model-plural']").val($(this).val().replace(/y$/i,'ie') + 's');
	}).trigger("keyup");

	$("#generate").click(function() {
		$.post("/generate/doapiexisting",$("#form-api").serializeArray(),function(response) {

			if(response.has_success)
				FF.msg.success(response.data.messages);
			else
				FF.msg.error(response.errors);
		});
	});

});

</script>