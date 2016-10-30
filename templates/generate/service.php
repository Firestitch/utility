<h1>New Service</h1>
<form id="form-service">

	<?
		HTML_FORM_UTIL::create()
				->input("service","Name","","",["placeholder"=>"ie. accountUser","class"=>"w400"])
				->input("json-name","JSON Object Name","","",["placeholder"=>"ie. user_account","class"=>"w400"])
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

		$("input[name='service'").on("keyup input",function(e) {

			$("#service-classname").text($(this).val() + "Service");
			$("#service-filename").text("/scripts/services/" + $(this).val().toLowerCase() + ".js");

			$("input[name='json-name']").val($(this).val().match(/[A-Z]*[^A-Z]+/g).join('_').toLowerCase());
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