<?

use Framework\Util\HTML_UTIL;

?>
<? if ($joiner_columns) { ?>
  <div>
    <div class="lbl pt10">Source Joiner Field</div>
    <?= HTML_UTIL::dropdown("joiners[" . $index . "][source_column]", $joiner_columns, $joiner_source_column, array(), 7) ?>

    <div class="lbl pt10">Reference Joiner Field</div>
    <?= HTML_UTIL::dropdown("joiners[" . $index . "][reference_column]", $joiner_columns, $joiner_reference_column, array(), 7) ?>
  </div>
<? } else { ?>
  There are no joiner fields
<? } ?>
