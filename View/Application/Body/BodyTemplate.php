<!DOCTYPE html>
<html>

<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />

  <? $web_asset_manager->render() ?>
</head>

<body>
  <div class="container">
    <? $this->show_view("header"); ?>

    <? $this->show_view("messages"); ?>

    <? $this->show_view("body"); ?>

    <? $this->show_view("footer"); ?>
  </div>

</body>

</html>
