<?php

/**
 * @var \Framework\Core\View $self
 * @var \Framework\Core\WebAssetManager $webAssetManager
 */
?>
<!DOCTYPE html>
<html>

<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />

  <?php $webAssetManager->render() ?>
  <script>
    function displayResponse(response, successMessage) {
      FF.msg.clear();

      if (response.data) {
        if (response.code === 200 && successMessage) {
          FF.msg.success(successMessage);
        }

        if (response.data.messages && response.data.messages.length)
          FF.msg.success(response.data.messages);

        if (response.data.warnings && response.data.warnings.length)
          FF.msg.warning(response.data.warnings, {
            append: true
          });

        if (response.data.errors && response.data.errors.length) {
          FF.msg.error(response.data.errors, {
            append: true
          });
        }
      }
    }
  </script>
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