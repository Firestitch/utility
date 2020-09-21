<?

use Framework\Util\HTML_UTIL;

?>
<div class="lbl">Model Field</div>
<? if ($reference_model_column_list) { ?>
  <?= HTML_UTIL::get_dropdown("reference_model_column", $reference_model_column_list, $reference_model_column, array(), count($reference_model_column_list)) ?>
<? } else { ?>
  There are no reference fields
<? } ?>
