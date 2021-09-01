<?php

use Framework\Util\HtmlUtil;

?>
<?php
if ($list) {
?>
<?php
  echo HtmlUtil::dropdown($name, $list, null, [], count($list) > 10 ? 10 : count($list));
} else {
?>
  No fields available
<?php
}
