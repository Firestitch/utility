<?php

use Framework\Util\HtmlUtil;

$size = count($list) > $limit ? $limit : count($list);

?>
<?php if ($list) { ?>
<?php
  echo HtmlUtil::dropdown($name, $list, "", [],  $size === 1 ? 2 : $size);
} else {
?>
  No Models available
<?php } ?>