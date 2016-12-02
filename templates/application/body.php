<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>Firestitch Utility</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

		<? $web_asset_manager->render() ?>

	</head>
	<body>

		<? $this->show_view("header"); ?>

		<div class="alert alert-warning dn" id="utility-alert"></div>

		<? $this->show_view("messages"); ?>

		<? $this->show_view("body"); ?>

		<? $this->show_view("footer"); ?>


	</body>
</html>