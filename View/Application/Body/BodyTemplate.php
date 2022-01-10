<?php

use Framework\Core\View;
use Framework\Core\WebAssetManager;

/**
 * @var View $self
 * @var WebAssetManager $webAssetManager
 */
?>
<!DOCTYPE html>
<html>

<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />

  <?php
  $webAssetManager->render();
  ?>
</head>

<body>
  <div class="container">
    <?php
    $self->showView("header");
    ?>

    <?php
    $self->showView("messages");
    ?>

    <?php
    $self->showView("body");
    ?>

    <?php
    $self->showView("footer");
    ?>
  </div>

</body>

</html>