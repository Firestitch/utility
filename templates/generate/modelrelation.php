<h1>Model Relationships</h1>

<div class="mt15 mb20 cb">
	<div class="fl mr50">
		<div class="lbl">Relationship</div>
		<?=HTML_UTIL::radiobuttons("relationship",array("child"=>"Map Child","children"=>"Map Children"))?>
	</div>
	<div class="fl mr50">
		<div class="lbl">Joiner Tables</div>
		<?=HTML_UTIL::button("add-joiner","Add Joiner Table",array("class"=>"btn-primary"))?>
	</div>
</div>
<div>
	<div class="model">
		<div class="lbl">Source Model</div>
		<div class="dib">
			<?=HTML_UTIL::dropdown("source_model",$model_list,$model,array(""),15)?>
		</div>

		<div class="mt10">
			<div id="source_fields"></div>
		</div>
	</div>

	<div class="model" id="joiners"></div>

	<div class="model">
		<div class="lbl">Reference Model</div>
		<div class="">
			<?=HTML_UTIL::dropdown("reference_model",$model_list,$reference_model,array(),15)?>
		</div>

		<div class=" mt10">
			<div id="reference_fields"></div>
		</div>
	</div>
	<div class="model">
		<div class="lbl">Generate</div>
		<?=HTML_UTIL::button("generate","Generate",array("class"=>"btn-primary"))?>
	</div>
	<div class="cb"></div>
</div>

 <?=HTML_UTIL::hidden("joiner_tables",JSON_UTIL::encode($joiner_list))?>

<script>

	$(function() {

		$("select[name='source_model']").bind("click keyup",function() {
			$("#source_fields").load("/generate/sourcefields/",{ source_model: $(this).val(), source_model_column: "<?=$source_model_column?>" });
		});

		if($("select[name='source_model'] option:selected").length)
			$("select[name='source_model']").trigger("click");

		$("#add-joiner").on("click",function() {
			var fields = $("<div>",{ "class": "joiner-fields" });
			var index = $(".joiner").length;
			var tables = JSON.parse($("#joiner_tables").val());
			var select = $("<select>",{ name: 'joiners[' + index + '][table]', 'class': 'form-control' })
							.attr("size",15)
							.click(function() {
								fields.load("/generate/joinerfields/",{ index: index, table: $(this).val() });
							});

			$.each(tables,function(index,value) {
				select.append($("<option>",{ name: index }).text(value));
			});

			$("#joiners").append($("<div>",{ 'class': 'joiner' })
									.append('<div class="lbl">Joiner Table</div>')
									.append(select)
									.append(fields));
		});

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


		$("#generate").click(function() {
			$.post("/generate/domodelrelation",$("#form-relation").serializeArray(),function(response) {

				FF.msg.clear();

				if(response.has_success) {
					FF.msg.success('Successfully generated');
				} else
					FF.msg.error(response.errors);

				if(response.data.warnings.length)
					FF.msg.warning(response.data.warnings,{ append: true });
			});
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
.joiner {
	display: inline-block;
	margin-right: 50px;
	vertical-align: top;
}
.joiner:last-child {
	margin-right: 0px;
}
</style>