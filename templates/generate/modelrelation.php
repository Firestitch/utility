<h1>Model Relationships</h1>

<div class="mt15 mb20">
	<div class="fwb">Relationship</div>
	<?=HTML_UTIL::radiobuttons("relationship",array("child"=>"Map Child","children"=>"Map Children"))?>
	<div class="fwb mt20">Joiner</div>
	<?=HTML_UTIL::checkbox("add-joiner",1,false,["id"=>"add-join"],"Add joiner table")?>
</div>
<div>
	<div class="model">
		<div class="lbl">Source Model</div>
		<div class="dib">
			<?=HTML_UTIL::dropdown("source_model",$model_list,$model,array(""),15)?>
		</div>

		<div class="mt10">
			<div class="lbl">Model Field</div>
			<div id="source_fields"></div>
		</div>
	</div>

	<div class="model dn" id="joiner">
		<div class="lbl">Joiner Model</div>
		<div class="">
			<?=HTML_UTIL::dropdown("joiner",$joiner_list,$joiner,array(),15)?>
		</div>

		<div class="dib mt10">
			<div id="joiner_fields"></div>
		</div>
	</div>

	<div class="model">
		<div class="lbl">Reference Model</div>
		<div class="">
			<?=HTML_UTIL::dropdown("reference_model",$model_list,$reference_model,array(),15)?>
		</div>

		<div class=" mt10">
			<div class="lbl">Model Field</div>
			<div id="reference_fields"></div>
		</div>
	</div>
	<div class="model">
		<div class="lbl">Generate</div>
		<?=HTML_UTIL::button("generate","Generate",array("class"=>"btn-primary"))?>
	</div>
</div>

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

		$("#add-join").click(function() {

			if($(this).is(":checked")) {
				$("#joiner").show();
			} else
				$("#joiner").hide();
		});

	});

</script>

<style>
.lbl {
	font-weight: bold;
	margin-bottom: 5px;
}
.model {
	float:  left;
	margin-right: 50px;
}
</style>