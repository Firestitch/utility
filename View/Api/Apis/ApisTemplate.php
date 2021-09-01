<?php

use Framework\Util\HtmlUtil;
?>

<?php echo HtmlUtil::dropdown("api", ["" => "Create new API", "Existing API" => $apis], "", ["class" => "api-name"]) ?>
