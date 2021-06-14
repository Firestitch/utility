<?php

use Framework\Util\HtmlUtil;
if ($joinerColumns) {
    ?>
  <div>
    <div class="lbl pt10">Source Joiner Field</div>
    <?php 
    echo HtmlUtil::dropdown("joiners[" . $index . "][source_column]", $joinerColumns, $joinerSourceColumn, array(), 7);
    ?>

    <div class="lbl pt10">Reference Joiner Field</div>
    <?php 
    echo HtmlUtil::dropdown("joiners[" . $index . "][reference_column]", $joinerColumns, $joinerReferenceColumn, array(), 7);
    ?>
  </div>
<?php 
} else {
    ?>
  There are no joiner fields
<?php 
}