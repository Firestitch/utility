<h1>New Service</h1>
<form id="form-service">

	<?
		HTML_FORM_UTIL::create()
				->input("service","Name","","",["placeholder"=>"ie. accountUser","class"=>"w400"])
				->input("name","Object Name","","",["placeholder"=>"ie. user_account","class"=>"w400"])
				->input("plural_name","Plural Object Name","","",["placeholder"=>"ie. user_accounts","class"=>"w400"])
				->checkboxes("options","Options",[ "order"=>"Add ordering method" ])
				->text("Service Class",HTML_UTIL::div("",["id"=>"service-classname"]))
				->text("Service Filename",HTML_UTIL::div("",["id"=>"service-filename"]))
				->button("generate","Generate",["type"=>"button"])
				->render();
	?>

</form>

<script>

	$(function() {

		$("input[name='service'").on("keyup input",function(e) {

			$("#service-classname").text($(this).val() + "Service");
			$("#service-filename").text("/scripts/services/" + $(this).val().replace('_','').toLowerCase() + ".js");

			var name = $(this).val().match(/[A-Z]*[^A-Z]+/g).join('_').toLowerCase();
			$("input[name='name']").val(name);
			$("input[name='plural_name']").val(name.plural());
		});
	});


	$("#generate").click(function() {
		$.post("/generate/doservice",$("#form-service").serializeArray(),function(response) {

			if(response.has_success) {
				FF.msg.success('Successfully generated');
			} else
				FF.msg.error(response.errors);

			if(response.data.warnings && response.data.warnings.length)
				FF.msg.warning(response.data.warnings,{ append: true });
		});
	});
</script>