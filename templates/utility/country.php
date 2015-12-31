<?

	FORM_UTIL::create()
		->checkbox("country[only_name]","Only include country names")
		->checkbox("country[name]","Include country name")
		->checkbox("country[code]","Include country codes")
		->checkbox("country[key_code]","Use the country code as the key in the countries array")
		->checkbox("country[regions]","Include regions")
		//->checkbox("form[capitals]","Include region capitals")
		->checkbox("regions[lat]","Include region latitude")
		->checkbox("regions[lng]","Include region longitude")		
		->checkbox("regions[value_keys]","Use the key names in the region")
		->button("generate","Generate Countries/Regions",array("class"=>"generate"))
		->render();
?>

<p id="generated" class="p20"></p>


<script>

	$(function() {

		$(".generate").click(function() {

			var form = $("#form-country").serializeArray();
			form.push({ name: "action", value: $(this).attr("name") });

			$.post("/generate/docountry",form,function(response) {
				if(response.has_success) {
					$("#generated").text(response.data.generated);
					FF.msg.success("Successfully generated");				
				} else
					FF.msg.error(response.errors);

			});
		});	

	});
</script>
