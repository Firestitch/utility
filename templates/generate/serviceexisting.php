<h1>Existing Service</h1>
<form id="form-service">

	<?
		FORM_UTIL::create()
				->dropdown("form[service]","Name",$services,"",["placeholder"=>"ie. account","class"=>"w400"])
				->input("form[model]","Model","",["placeholder"=>"ie. account_user","class"=>"w400"],["info"=>"The full name of the related child object ie: account-><b>account_user</b>"])
				->text("Plural Model",HTML_UTIL::input("form[model-plural]","",array("placeholder"=>"ie. account_users","class"=>"w400")))
				->input("form[namespace]","Namespace","",["placeholder"=>"ie. user","class"=>"w400"],["info"=>"The simplified name used for the service functions and APIs.<br>ie: accountService.usersPost() that points to POST /accounts/account_id/users/user_id"])
				->checkboxes("form[methods]","Methods",[	"get"=>"GET",
															"post"=>"POST",
															"put"=>"PUT",
															"delete"=>"DELETE"],[])
				->button("generate","Generate",["type"=>"button","class"=>"btn-primary"])
				->render();
	?>

</form>

<script>

	$("input[name='form[model]']").keyup(function() {
		if($(this).val()) {
			$("input[name='form[model-plural]']").val($(this).val().replace(/y$/i,'ie') + 's');
			$("input[name='form[namespace]']").val($("input[name='form[model-plural]']").val().replace($("select[name='form[service]']").val() + '_',''));
		}
	}).trigger("keyup");

	$("#generate").click(function() {
		$.post("/generate/doserviceexisting",$("#form-service").serializeArray(),function(response) {

			if(response.has_success) {
				FF.msg.success('Successfully generated');
			} else
				FF.msg.error(response.errors);

			if(response.data.warnings.length)
				FF.msg.warning(response.data.warnings,{ append: true });
		});
	});
</script>