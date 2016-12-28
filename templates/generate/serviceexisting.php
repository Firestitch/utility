<h1>Existing Service</h1>
<form id="form-service">

	<?
		HTML_FORM_UTIL::create()
				->input("form[service]","Name","","",["placeholder"=>"ie. user","class"=>"w400"])
				->input("form[model]","Model","","",["placeholder"=>"","class"=>"w400"])
				->text("Plural Model",HTML_UTIL::input("form[model-plural]","",array("placeholder"=>"","class"=>"w200")))
				->text("Service Class",HTML_UTIL::div("",["id"=>"service-classname"]))
				->text("Service Filename",HTML_UTIL::div("",["id"=>"service-filename"]))
				->button("generate","Generate",["type"=>"button"])
				->render();
	?>

</form>

<script>


	$(function() {

		$("input[name='form[service]'").on("keyup input",function(e) {

			$("#service-classname").text($(this).val() + "Service");
			$("#service-filename").text("/scripts/services/" + $(this).val().toLowerCase() + ".js");
		});
	});

	$("input[name='form[model]']").keyup(function() {

		if($(this).val())
			$("input[name='form[model-plural]']").val($(this).val().replace(/y$/i,'ie') + 's');
	}).trigger("keyup");

	$("#generate").click(function() {
		$.post("/generate/doserviceexisting",$("#form-service").serializeArray(),function(response) {

			if(response.has_success) {
				FF.msg.success('Successfully generated');
			} else
				FF.msg.error(response.errors);

			if(response.data.warnings.length)
				FF.msg.warning(response.data.warnings,{ append: true });
		});
	});
</script>