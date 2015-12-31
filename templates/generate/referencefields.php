<?php		

	$reference_model_column_dd = HTML_UTIL::get_dropdown("reference_model_column",$reference_model_column_list,$reference_model_column,array(),count($reference_model_column_list));

	echo HTML_UTIL::get_heading3("Reference Field");

	if($reference_model_column_list) 
		echo $reference_model_column_dd;
	else
		echo "There are no reference fields";

