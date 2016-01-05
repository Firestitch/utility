<?
	
	$view_types = array(VIEW::TYPE_BODY=>"Body",VIEW::TYPE_POPUP=>"Popup",VIEW::TYPE_BLANK=>"Blank",VIEW::TYPE_COMPONENT=>"Component");

	if($is_mva_component)
		$data[] = array("MVA Components",HTML_UTIL::get_checkboxes("mva_components",array("V"=>"View","A"=>"Action"),$mva_components));
		
	$task_name_div = HTML_UTIL::get_div("",array("id"=>"task_name"));
	
	$data[] = array("ACL Roles",HTML_UTIL::dropdown("security_roles",$security_roles,$selected_security_roles,array("class"=>"w100p"),count($security_roles),true));
	
	if($show_view_type)
		$data[] = array("Type",HTML_UTIL::dropdown("view_type",$view_types,$view_type,array("class"=>"w100p")));
		

	$data[] = array("Controller",HTML_UTIL::input("controller",$controller,array("class"=>"w100p")));
	
	$data[] = array($task_name_div,HTML_UTIL::input("task",$task,array("class"=>"w100p")));


	$data[] = array("Location",HTML_UTIL::dropdown("location",CMODEL_GENERATOR::get_locations(),"",array("class"=>"wa")));
	
	if($show_is_form)
		$data[] = array("",HTML_UTIL::get_checkbox("has_form","1",$has_form,[],"Has Form"));
		
			
	$data[] = array("",HTML_UTIL::get_checkbox("override","1",$override,[],"Override"));
	
	$data[] = array("",HTML_UTIL::get_button("cmd_generate","Generate",array("class"=>"btn-primary")));
	
	$html_table = new HTML_TABLE_UTIL();
	$html_table->set_data($data);
	$html_table->set_row_id_prefix("row-");
	$html_table->set_column_attribute(0,"width",120);
	$html_table->set_padding(2);
	$html_table->set_class("");
	$html_table->render();	
