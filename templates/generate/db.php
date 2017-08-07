
<h1>DBO, DBQ, CMODEL, HMODEL Generation</h1>

<form id="form-db">
	<?
		$data[] = array("Generate:",HTML_UTIL::checkboxes("objects",[	"dbo"=>"DBO",
																		"dbq"=>"DBQ",
																		"cmodel"=>"CMODEL",
																		"hmodel"=>"HMODEL"],["dbo","dbq"],["class"=>"objects"]));
		$data[] = array("Name:",HTML_UTIL::input("name","",array("class"=>"w300")));
		$data[] = array("Location:",HTML_UTIL::dropdown("location",CMODEL_GENERATOR::get_locations(),"",array("class"=>"wa")));
		$data[] = array("",HTML_UTIL::checkbox("override","1",$override,["class"=>"override"],"Override"));
		$data[] = array("",HTML_UTIL::link("javascript:;","Generate",array("id"=>"generate","class"=>"btn btn-primary")));

		$db_table = HTML_TABLE_UTIL::create()
						->set_data($data)
						->set_class("")
						->set_padding(3);

		$tablename_dd = HTML_UTIL::dropdown("tablename",$tablename_list,$tablename,array("onKeyUp"=>"update_class_name(this)","onChange"=>"update_class_name(this)","size"=>30),50);

		HTML_TABLE_UTIL::create()
			->set_data(array(array("Table Name: ",$tablename_dd,$db_table->get_html())))
			->set_default_column_attribute("class","vat")
			->set_class("")
			->set_padding(3)
			->render();
	?>
</form>

<script>

	function update_class_name(obj) {
		value = $(obj).val();

		$("#name").val(get_singular(value).toUpperCase());

		update_links(value,get_singular(value));
	}

	$(".objects").on("change",function() {
		$(".override").attr("checked", false);
	})

	$("#name").on("input",function() {
		update_links("",$(this).val());
	})

	$("#generate").click(function() {
		$.post("/generate/dodb",$("#form-db").serializeArray(),function(response) {

			FF.msg.clear();

			if(response.data.messages)
				FF.msg.success(response.data.messages);

			if(response.data.warnings.length)
				FF.msg.warning(response.data.warnings);
		});
	});

</script>