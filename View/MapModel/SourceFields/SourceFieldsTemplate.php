<?

use Framework\Util\HTML_UTIL;

?>
<div class="lbl">Model Field</div>
<? if ($source_model_column_list) { ?>
  <?= HTML_UTIL::get_dropdown("source_model_column", $source_model_column_list, $source_model_column, array(), count($source_model_column_list)) ?>
<? } else { ?>
  There are no source fields
<? } ?>
