<?php

use Framework\Util\HtmlUtil;
?>
<div class="lbl">Model Field</div>
<?php 
if ($referenceModelColumnList) {
    ?>
  <?php 
    echo HtmlUtil::getDropdown("reference_model_column", $referenceModelColumnList, $referenceModelColumn, array(), count($referenceModelColumnList));
} else {
    ?>
  There are no reference fields
<?php 
}