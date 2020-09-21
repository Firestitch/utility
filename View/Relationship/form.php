<?php

	$data[] = array("Page Number:",HTML_UTIL::get_input("page_number",$page_number,array("style"=>"width:50px")));
	$data[] = array("Excel File:",HTML_UTIL::get_filefield("excel_file"));
	$data[] = array("",HTML_UTIL::get_button("cmd_generate","Generate"));

	$html_table = new HTML_TABLE_UTIL();
	$html_table->set_data($data);	
	$html_table->render();

