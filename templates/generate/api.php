<h1>API Generation</h1>

<?
	FORM_UTIL::create()
		->dropdown("api","API",[""=>"Create new API","Existing API"=>$apis],"",["class"=>"w400 api-name"])
		->dropdown("model","Model",$models,"",["placeholder"=>"ie. account","class"=>"w400"])
		->input("model-plural","Plural Model Name","",["placeholder"=>"ie. accounts","class"=>"w400"])
		->input("method","Namespace","",["placeholder"=>"ie. users","class"=>"w400 api-existing"],["info"=>"ie: <span id='namespace-example'></span>"])
		->checkboxes("methods","Methods",[	"get"=>"GET",
													"post"=>"POST",
													"put"=>"PUT",
													"delete"=>"DELETE"],["get","put","post","delete"])
		->checkboxes("loads","Load",[],"",[],["row"=>["class"=>"api-loads"]])
		->checkboxes("options","Options",[ "order"=>"Add ordering method","override"=>"Override existing files" ])
		->button("generate","Generate",["type"=>"button","id"=>"generate","class"=>"btn-primary"])
		->render();
?>
<script>

$(function() {

	var apiLoads = $(".api-loads").hide();

	$("select[name='api']").change(function() {

		updateNamespaceExample();

		var existing = $(".api-existing").parents("tr");
		if($(this).val()) {
			existing.show();
		} else {
			existing.hide();
		}

	}).trigger("change");

	$("input[name='method']").keydown(updateNamespaceExample);

	$("select[name='model']").change(function() {

		if($(this).val()) {
			$("input[name='model-plural']").val($(this).val().plural());
			var method = $("input[name='model-plural']").val()
					.replace(get_singular($(".api-name").val()) + '_','')
					.replace('_','');
			$("input[name='method']").val(method);
		}

		$.post("/generate/dohmodelfunctions",$("#form-api").serializeArray(),function(response) {

			var content = apiLoads.find(".table-form-content").empty();

			if(response.data.functions.length) {
				apiLoads.show();

				$.each(response.data.functions,function(key,value) {
					var id = FF.util.guid();
					content
						.append($("<div>")
									.append($("<input>",{ type: 'checkbox', value: value, name: 'options[loads][]', class: 'checkbox', id: id }))
									.append($("<label>",{ for: id }).html('&nbsp;' + value))
								);
				});
				content
			} else {
				apiLoads.hide();
			}
		});


		updateNamespaceExample();
	});

	$("#generate").click(function() {
		$.post("/generate/doapi",$("#form-api").serializeArray(),function(response) {

			if(response.has_success)
				FF.msg.success(response.data.messages);
			else
				FF.msg.error(response.errors);
		});
	});

	function updateNamespaceExample() {
		var namespace = "/" + $("select[name='api']").val();

		if($("input[name='method']").val())
			namespace += "/id/" + $("input[name='method']").val();

		$("#namespace-example").text(namespace);
	}

});

</script>

