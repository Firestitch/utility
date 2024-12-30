<?php

use Framework\Util\HtmlUtil;


?>
<div class="form-field">
  <div class="lbl">
    <?php echo $label ?>
  </div>
  <?php echo HtmlUtil::dropdown($name, $namespaces, "Backend", ["class" => $class]) ?>
</div>

<script>
  (function () {
    var el = document.querySelector('select[name="<?php echo $name ?>"]');
    el.addEventListener('change', function () {
      window.localStorage.setItem('namespaceSelect-<?php echo $name ?>', el.value);
    });

    el.value = window.localStorage.getItem('namespaceSelect-<?php echo $name ?>');
  })();
</script>