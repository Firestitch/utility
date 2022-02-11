<?php

use Utility\View\Application\Body\BodyView;

/**
 * @var BodyView $self
 */


?>

<!DOCTYPE html>
<html>

<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />

  <? $web_asset_manager->render() ?>
</head>

<body>
  <div class="container">
    <? $self->show_view("header"); ?>

    <? $self->show_view("messages"); ?>

    <? $self->show_view("body"); ?>

    <? $self->show_view("footer"); ?>
  </div>

</body>

</html>