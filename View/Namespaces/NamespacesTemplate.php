<?php

use Framework\Util\HtmlUtil;


?>
<div class="form-field">
  <div class="lbl">
    <?php echo $label ?>
  </div>
  <?php echo HtmlUtil::dropdown("namespace", $namespaces, "Backend", ["class" => $class]) ?>
</div>