<?php

use Framework\Util\HtmlUtil;
?>
<div class="lbl">Model Field</div>
<?php 
if ($sourceModelColumnList) {
    ?>
  <?php 
    echo HtmlUtil::getDropdown("source_model_column", $sourceModelColumnList, $sourceModelColumn, array(), count($sourceModelColumnList));
} else {
    ?>
  There are no source fields
<?php 
}