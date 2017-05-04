<h1>Existing API Generation</h1>

<?

	FORM_UTIL::create()
		->dropdown("api","API",$apis,"",["class"=>"w400 api-name"])
		->input("model","Model","",["placeholder"=>"ie. account_user","class"=>"w400"],["info"=>"The full name of the related child object ie: account-><b>account_user</b>"])
		->input("model-plural","Plural Model","")
		->input("method","Namespace","",["placeholder"=>"ie. users","class"=>"w400"],["info"=>"Namespace used for the API path ie: /accounts/account_id/<b>users</b>"])
		->checkboxes("methods","Methods",[	"get"=>"GET",
													"post"=>"POST",
													"put"=>"PUT",
													"delete"=>"DELETE"],[])
		->button("generate","Generate",["type"=>"button","class"=>"btn-primary"])
		->render();

?>
<script>

$(function() {

	$("input[name='model']").keyup(function() {
		if($(this).val()) {
			$("input[name='model-plural']").val($(this).val().replace(/y$/i,'ie') + 's');
			$("input[name='method']").val($("input[name='model-plural']").val().replace(get_singular($(".api-name").val()) + '_',''));
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