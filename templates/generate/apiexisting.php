<h1>API Generation</h1>

<?
	$data[] = array("API: ",HTML_UTIL::dropdown("api",$apis,$model,array("placeholder"=>"ie. accounts","class"=>"w200 api-name")));
	$data[] = array("Model Name: ",HTML_UTIL::input("model","",array("placeholder"=>"ie. credit_card","class"=>"w200")));
	$data[] = array("Plural Model Name: ",HTML_UTIL::input("model-plural","",array("placeholder"=>"ie. credit_cards","class"=>"w200")));
	$data[] = array("Method Name: ",HTML_UTIL::input("method","",array("placeholder"=>"ie. credit_card","class"=>"w200 method")));
	$data[] = array("",HTML_UTIL::checkbox("override","1","",array(),"Override"));
	$data[] = array("",HTML_UTIL::button("generate","Generate",array("id"=>"generate","class"=>"btn-primary")));

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
		if($(this).val()) {
			$("input[name='model-plural']").val($(this).val().replace(/y$/i,'ie') + 's');

			var method = $("input[name='model-plural']").val().replace(get_singular($(".api-name").val()) + '_','');
			$(".method").val(method);
		}
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