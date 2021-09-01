<?php

use Framework\Util\HtmlUtil;

?>
<?php if ($list) { ?>
<?php
  echo HtmlUtil::dropdown($name, $list, "", [],  count($list) > $limit ? $limit : count($list));
} else {
?>
  No Models available
<?php } ?>