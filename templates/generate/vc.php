<h1>Frontend View / Controller</h1>
<form id="form-vc">

	<?
		HTML_FORM_UTIL::create()
				->input("form[url]","URL","","",["placeholder"=>"ie. user/profile","class"=>"w400"])
				->input("form[state]","State Name","","",["placeholder"=>"ie. page.userprofile","class"=>"w400"])
				->input("form[controller]","Controller Name","","",["placeholder"=>"ie. UserProfile","class"=>"w400"])
				->input("form[view]","View Name","","",["placeholder"=>"ie. userprofile","class"=>"w400"])
				->input("form[object]","Object Name","","",["placeholder"=>"ie. user","class"=>"w400"])
				->dropdown("form[view_format]","View Format",["page"=>"Full Page","modal"=>"Modal","drawer"=>"Drawer"],"")
				->radiobuttons("form[lister]","Lister",[	""=>"Do not include Lister",
															"include"=>"Include Lister",
															"page"=>"Include Lister with edit/delete interface on a full page",
															"modal"=>"Include Lister with edit/delete interface in a modal",
															//"drawer"=>"Include Lister with edit/delete interface in a drawer"
															])
				->custom("<h3>Will Be Generated</h3>")
				->text("Controller Class",HTML_UTIL::div("",["id"=>"controller-classname"]))
				->text("Controller Filename",HTML_UTIL::div("",["id"=>"controller-filename"]))
				->text("View Filename",HTML_UTIL::div("",["id"=>"view-filename"]))
				->button("generate","Generate",["type"=>"button"])
				->render();
	?>

</form>

<script>

	String.prototype.capitalize = function(){
       return this.replace( /(^|\s)([a-z])/g , function(m,p1,p2){ return p1+p2.toUpperCase(); } );
    };

	$(function() {

		$("input[name='form[url]'").on("keyup input",function(e) {

			var parts = $(this).val().replace(/(^\/|\/$)/,'').split("/");

			if(!$("input[name='form[controller]'").data("touched")) {

				var names = $.merge([], parts);

				$.each(names,function(key,value) {
					names[key] = value.capitalize();
				});

				$("input[name='form[controller]'").val(names.join(''));
			}

			$("input[name='form[controller]']").trigger("keyup");

			if(!$("input[name='form[view]'").data("touched")) {
				$("input[name='form[view]'").val(parts.join(''));
			}

			if(!$("input[name='form[state]'").data("touched")) {
				$("input[name='form[state]'").val(parts.join(''));
			}

			$("input[name='form[view]']").trigger("keyup");
		});

		$("input[name='form[controller]'").on("keyup",function(e) {

			if(e.keyCode)
				$(this).data("touched",true);

			if(!$(this).val())
				$(this).data("touched",false);

			$("#controller-classname").text($(this).val() + "Ctrl");
			$("#controller-filename").text("/scripts/controllers/" + $(this).val().toLowerCase() + ".js");

			if(!$("input[name='form[view]'").data("touched"))
				$("input[name='form[view]'").val($(this).val());

			$("input[name='form[view]']").trigger("keyup");
		});

		$("input[name='form[view]'").on("keyup",function(e) {

			if(e.keyCode)
				$(this).data("touched",true);

			if(!$(this).val())
				$(this).data("touched",false);

			$("#view-filename").text("/views/" + $(this).val() + ".html");
		});


		$("input[name='form[state]'").on("keyup",function(e) {

			if(e.keyCode)
				$(this).data("touched",true);

			if(!$(this).val())
				$(this).data("touched",false);
		});
	});


	$("#generate").click(function() {
		$.post("/generate/dovc",$("#form-vc").serializeArray(),function(response) {

			if(response.has_success) {
				FF.msg.success('Successfully generated');
			} else
				FF.msg.error(response.errors);

			if(response.data.warnings.length)
				FF.msg.warning(response.data.warnings,{ append: true });
		});
	});
</script>

