<h1>New Directive</h1>
<form id="form-directive">

	<?
		HTML_FORM_UTIL::create()
				->input("directive","Name","","",["placeholder"=>"ie. fsSomething","class"=>"w400"])
				->text("Directive Filename",HTML_UTIL::div("",["id"=>"directive-filename"]))

				->button("generate","Generate",["type"=>"button"])
				->render();
	?>

</form>

<script>

	String.prototype.capitalize = function(){
       return this.replace( /(^|\s)([a-z])/g , function(m,p1,p2){ return p1+p2.toUpperCase(); } );
    };

	$(function() {

		$("input[name='directive'").on("keyup input",function(e) {

			$("#directive-filename").text("/scripts/directives/" + $(this).val().toLowerCase() + ".js");
		});
	});


	$("#generate").click(function() {
		$.post("/generate/dodirective",$("#form-directive").serializeArray(),function(response) {

			if(response.has_success) {
				FF.msg.success('Successfully generated');
			} else
				FF.msg.error(response.errors);

			if(response.data.warnings.length)
				FF.msg.warning(response.data.warnings,{ append: true });
		});
	});
</script>