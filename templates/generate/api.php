<h1>New API Generation</h1>

	<?
		FORM_UTIL::create()
				->dropdown("model","Model",$models,"",["placeholder"=>"ie. account","class"=>"w400"])
				->input("model-plural","Plural Model Name","",["placeholder"=>"ie. accounts","class"=>"w400"])
				->checkboxes("options","Options",[ "order"=>"Add ordering method","override"=>"Override existing files" ])
				->button("generate","Generate",["type"=>"button","id"=>"generate","class"=>"btn-primary"])
				->render();
	?>

<script>

$(function() {

	$("select[name='model']").change(function() {

		if($(this).val())
			$("input[name='model-plural']").val($(this).val().plural());

	});

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