<h1>New API Generation</h1>

	<?
		HTML_FORM_UTIL::create()
				->input("model","Model Name","","",["placeholder"=>"ie. account","class"=>"w400"])
				->input("model-plural","Plural Model Name","","",["placeholder"=>"accounts","class"=>"w400"])
				->checkboxes("options","Options",[ "order"=>"Add ordering method","override"=>"Override existing files" ])
				->button("generate","Generate",["type"=>"button","id"=>"generate","class"=>"btn-primary"])
				->render();
	?>

<script>

$(function() {

	$("input[name='model']").keyup(function() {

		if($(this).val())
			$("input[name='model-plural']").val($(this).val().plural());

	}).trigger("keyup");

	$("#generate").click(function() {
		$.post("/generate/doapi",$("#form-api").serializeArray(),function(response) {

			if(response.has_success)
				FF.msg.success(response.data.messages);
			else
				FF.msg.error(response.errors);
		});
	});

});

</script>