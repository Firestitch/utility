<h1>New Filter</h1>
<form id="form-filter">

	<?
		HTML_FORM_UTIL::create()
				->input("filter","Name","","",["placeholder"=>"ie. fsSomething","class"=>"w400"])
				->text("Filter Filename",HTML_UTIL::div("",["id"=>"filter-filename"]))

				->button("generate","Generate",["type"=>"button"])
				->render();
	?>

</form>

<script>

	$(function() {

		$("input[name='filter'").on("keyup input",function(e) {
			$("#filter-filename").text("/scripts/filters/" + $(this).val().toLowerCase() + ".js");
		});
	});


	$("#generate").click(function() {
		$.post("/generate/dofilter",$("#form-filter").serializeArray(),function(response) {

			if(response.has_success) {
				FF.msg.success('Successfully generated');
			} else
				FF.msg.error(response.errors);

			if(response.data.warnings && response.data.warnings.length)
				FF.msg.warning(response.data.warnings,{ append: true });
		});
	});
</script>