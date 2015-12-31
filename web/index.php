<?php
	$has_bootstrap = @include_once(realpath(dirname(__FILE__)."/../..")."/framework/boot/bootstrap.inc");
	
	if(!$has_bootstrap) {
		header("location: /unavailable/");
		die;
	}
		
	BASE_APPLICATION::initialize_web_application();	

	$application = APPLICATION::get_instance();
	$application->initialize();	
	$application->process();
	
	
