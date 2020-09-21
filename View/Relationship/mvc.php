<h1>Views &amp; Actions</h1>

<?
	APPLICATION::include_template("generate","component_mvc",$this->get_vars());
	
?>
<script>
	$(function() {
		
		$("#row-2,#row-3,#row-5").hide();
		
		$("input[type='checkbox']").change(function() {
												vc = $("input[name='mva_components[]'][value='V']").is(":checked");
												ac = $("input[name='mva_components[]'][value='A']").is(":checked");

												tn = [];

												if(vc) {
													tn.push("View");

													$("#row-2").show();
													$("#row-3").show();
													$("#row-5").show();
												}  else {
													$("#row-2").hide();
													$("#row-3").hide();		
													$("#row-5").hide();	
												}

												if(ac) 
													tn.push("Action");


												$("#task_name").html(tn.join(" &amp; ") + " Name");
											
											}).trigger("change");

	});
	
	

</script>