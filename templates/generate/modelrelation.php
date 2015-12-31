<h1>Model Relationships</h1>

<?php
	$dd_count = count($model_list)>30 ? 30 : count($model_list);
	
	$tablename_dd = HTML_UTIL::get_dropdown("source_model",$model_list,$model,array(),$dd_count);
	
	$generate 	= HTML_UTIL::get_div(HTML_UTIL::get_button("generate","Generate"),array("class"=>"pt10"));
	$relationships 	= HTML_UTIL::get_radiobuttons("relationship",array("O"=>"one-to-one","M"=>"one-to-many","N"=>"many-to-many"));
	
	echo HTML_UTIL::get_div(HTML_UTIL::get_div("Relationship",array("class"=>"fwb")).$relationships.$generate,array("class"=>"fl pr10"));
	
	$source_fields 		= HTML_UTIL::get_div("",array("class"=>"","id"=>"source_fields"));
	$reference_fields 	= HTML_UTIL::get_div("",array("class"=>"","id"=>"reference_fields"));
	$joiner_fields 		= HTML_UTIL::get_div("",array("class"=>"","id"=>"joiner_fields"));
	
	echo HTML_UTIL::get_div(HTML_UTIL::get_div("Source Models",array("class"=>"fwb")).$tablename_dd.$source_fields,array("class"=>"fl pr10"));
	
	$dd_count 		= count($joiner_list)>30 ? 30 : count($joiner_list);
	
	$joiner_dd 		= HTML_UTIL::get_dropdown("joiner",$joiner_list,$joiner,array(),$dd_count);

	echo HTML_UTIL::get_div(HTML_UTIL::get_div("Joiner Tables",array("class"=>"fwb")).$joiner_dd.$joiner_fields,array("class"=>"fl dn","id"=>"joiner"));	
	
	$dd_count 		= count($model_list)>30 ? 30 : count($model_list);
	
	$reference_model_dd 	= HTML_UTIL::get_dropdown("reference_model",$model_list,$reference_model,array(),$dd_count);

	echo HTML_UTIL::get_div(HTML_UTIL::get_div("Reference Models",array("class"=>"fwb")).$reference_model_dd.$reference_fields,array("class"=>"fl"));	
	
	echo HTML_UTIL::get_div("",array("class"=>"fl","id"=>"model-options"));
	
	echo HTML_UTIL::get_div("",array("class"=>"cb"));

?>
<script>
		
	$(function() {
				
		$("select[name='source_model']").bind("click keyup",function() {
			$("#source_fields").load("/generate/sourcefields/",{ source_model: $(this).val(), source_model_column: "<?=$source_model_column?>" });
		});

		if($("select[name='source_model'] option:selected").length)
			$("select[name='source_model']").trigger("click");
			
		$("select[name='joiner']").bind("click keyup",function() {
			$("#joiner_fields").load("/generate/joinerfields/",{ joiner: $(this).val() });
		});

		if($("select[name='joiner'] option:selected").length)
			$("select[name='joiner']").trigger("click");
		
		$("select[name='reference_model']").bind("click keyup",function() {
			$("#reference_fields").load("/generate/referencefields/",{ reference_model: $(this).val() },function() {
				$("#reference_model_column").find("option[value='" + $("#source_model_column").val() + "']").attr("selected","selected");
			});
		});

		if($("select[name='reference_model'] option:selected").length)
			$("select[name='reference_model']").trigger("click");
		
		$("input[name='relationship']").click(function() {
		
			if($(this).val()=="N") {
				$("#joiner").show();
			} else
				$("#joiner").hide();
		});
		
	});
	
</script>

