<h1>Complex Model</h1>
<form id="form-cmodel">

	<div class="fl w300 pr20">
		<h3>C-Models</h3>
		<?=HTML_UTIL::dropdown("cmodel",$cmodels,$model,array("size"=>30),count($cmodels))?>
	</div>
	<div>
		<h3>Addons</h3>

		<? 
			$db_table = FORM_UTIL::create() 
							->checkboxes("types","Type",array("image"=>"Image"))
							->custom(HTML_UTIL::h4("Image"),array("class"=>"row-image"))
							->input("image[name]","Name","",array("placeholder"=>"ie. avatar","class"=>"row-image w400"))
							->input("image[sizes]","Sizes","tiny:s25,small:s100,medium:s300,large:600,actual",array("placeholder"=>"ie. tiny:s25,small:s100,medium:s300,large:600,actual","class"=>"row-image w400"))
							->input("image[path]","Path","",array("placeholder"=>"ie. us/av","class"=>"row-image w400"));
		?>
	
		<?=$db_table->button("generate","Generate",array("type"=>"button"))->render()?>
	</div>

</form>

<script>

	
	$(function() {
		$(".row-image").parents("tr").hide();

		$("input[name='types[]']").click(function() {
			if($(this).is(":checked"))
				$(".row-" + $(this).val()).parents("tr").show();
			else
				$(".row-" + $(this).val()).parents("tr").hide();
		});

		$("select[name='cmodel'],input[name='image[name]']").on("input",function() {
			var path = "/pub/" + $("select[name='cmodel']").val().substring(0,2) + "/" + $("input[name='image[name]']").val().substring(0,2) + "/";
			$("input[name='image[path]']").val(path);
		});
	});


	$("#generate").click(function() {
		$.post("/generate/docmodel",$("#form-cmodel").serializeArray(),function(response) {

			if(response.has_success) {
				if(response.data.messages.length)
					FF.msg.success(response.data.messages);
			} else
				FF.msg.error(response.errors);

			if(response.data.warnings.length)
				FF.msg.warning(response.data.warnings,{ append: true });
		});
	});		
</script>