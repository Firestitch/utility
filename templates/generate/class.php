<?
	$table_data = array();
	
	$table_data[] = array(HTML_UTIL::get_radiobuttons("format",array("P"=>"PHP","J"=>"JavaScript"),$format));	
	$table_data[] = array(HTML_UTIL::get_textarea("fields",$fields,array("style"=>"width:500px;height:100px")));
	$table_data[] = array("A list of fields seperated by commas");
	$table_data[] = array(HTML_UTIL::get_button("cmd_generate","Generate"));
	
	if($class)
		echo "<pre>".$class."</pre>";
	
	$html_table = new HTML_TABLE_UTIL();
	$html_table->set_data($table_data);
	$html_table->render();