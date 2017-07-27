<h1>New Controller</h1>
<form id="form-controller">

	<?
		HTML_FORM_UTIL::create()
				->input("controller_input","Name","","",["placeholder"=>"ie. AccountProfile","class"=>"w400"])
				->text("Controller Class",HTML_UTIL::div("",["id"=>"controller-classname"]))
				->text("Controller Filename",HTML_UTIL::div("",["id"=>"controller-filename"]))
				->button("generate","Generate",["type"=>"button"])
				->hidden("controller","","controller")
				->render();
	?>

</form>

<script>

	$(function() {

		$("input[name='controller_input'").on("keyup input",function(e) {
			var name = "";

			if($(this).val())
				name = $(this).val();
			name = name.charAt(0).toUpperCase() + name.slice(1);

			$("#controller").val(name);
			$("#controller-classname").text(name + "Ctrl");
			$("#controller-filename").text("/scripts/controllers/" + $(this).val().toLowerCase() + ".js");
		});
	});


	$("#generate").click(function() {
		$.post("/generate/docontroller",$("#form-controller").serializeArray(),function(response) {

			if(response.has_success) {
				FF.msg.success('Successfully generated');
			} else
				FF.msg.error(response.errors);

			if(response.data.warnings && response.data.warnings.length)
				FF.msg.warning(response.data.warnings,{ append: true });
		});
	});
</script>