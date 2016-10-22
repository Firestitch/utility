<h1>Frotnend Service</h1>
<form id="form-service">

	<?
		HTML_FORM_UTIL::create()
				->input("form[service]","Name","","",["placeholder"=>"ie. user","class"=>"w400"])
				->text("Service Class",HTML_UTIL::div("",["id"=>"service-classname"]))
				->text("Service Filename",HTML_UTIL::div("",["id"=>"service-filename"]))
				->button("generate","Generate",["type"=>"button"])
				->render();
	?>

</form>

<script>

	String.prototype.capitalize = function(){
       return this.replace( /(^|\s)([a-z])/g , function(m,p1,p2){ return p1+p2.toUpperCase(); } );
    };

	$(function() {

		$("input[name='form[service]'").on("keyup input",function(e) {

			$("#service-classname").text($(this).val() + "Service");
			$("#service-filename").text("/scripts/services/" + $(this).val().toLowerCase() + ".js");
		});
	});


	$("#generate").click(function() {
		$.post("/generate/doservice",$("#form-service").serializeArray(),function(response) {

			if(response.has_success) {
				FF.msg.success('Successfully generated');
			} else
				FF.msg.error(response.errors);

			if(response.data.warnings.length)
				FF.msg.warning(response.data.warnings,{ append: true });
		});
	});
</script>