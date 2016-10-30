<h1>Model Relationships</h1>

<div class="mt15">
	<div class="fwb">Relationship</div>

	<?=HTML_UTIL::radiobuttons("relationship",array("O"=>"one-to-one","M"=>"one-to-many","N"=>"many-to-many"))?>
</div>
<div class="w200">
	<div class="model">
		<h3>Source Model</h3>
		<div class="dib">
			<div class="lbl">Model</div>
			<?=HTML_UTIL::dropdown("source_model",$model_list,$model,array(""),15)?>
		</div>

		<div class="mt10">
			<div class="lbl">Model Field</div>
			<div id="source_fields"></div>
		</div>
	</div>

	<div class="model dn" id="joiner">
		<h3>Joiner Model</h3>
		<div class="">
			<div class="lbl">Model</div>
			<?=HTML_UTIL::dropdown("joiner",$joiner_list,$joiner,array(),15)?>
		</div>

		<div class="dib mt10">
			<div id="joiner_fields"></div>
		</div>
	</div>

	<div class="model">
		<h3>Reference Model</h3>
		<div class="">
			<div class="lbl">Model</div>
			<?=HTML_UTIL::dropdown("reference_model",$model_list,$reference_model,array(),15)?>
		</div>

		<div class=" mt10">
			<div class="lbl">Model Field</div>
			<div id="reference_fields"></div>
		</div>
	</div>
</div>
<div class="cb pt30"><?=HTML_UTIL::get_button("generate","Generate",array("class"=>"btn-primary"))?></div>
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

<style>
.lbl {
	font-weight: bold;
	margin-bottom: 5px;
}
.model {

	margin-bottom: 50px;
}
</style>