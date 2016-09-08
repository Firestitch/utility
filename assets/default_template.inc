<form id="form-{$controller}-{$task}">
	<?
		FORM_UTIL::create()
			->text("Name","")
			->dropdown("form[state]","State",["A"=>"A","B"=>"B"],"")
			->text("",HTML_UTIL::link("javascript:;","Submit",array("class"=>"btn btn-primary","id"=>"submit-{$controller}-{$task}")))
			->render();
	?>
</form>

<script>

	$("#submit-{$controller}-{$task}")
	.go("/{$controller}/do{$task}",
		{ 	data: $("#form-{$controller}-{$task}"),
			success: function(response) {
				FF.msg.success("Successfully");
			}
		});

</script>