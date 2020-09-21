<?php

use Framework\Util\SERVER_UTIL;
?>
<div id="header">
  <div class="navbar navbar-default">

    <div class="navbar-header">
      <a class="navbar-brand" href="/"> <?= SERVER_UTIL::get_server_host() ?> </a>
    </div>

    <div class="collapse navbar-collapse">
      <ul class="nav navbar-nav">


        <li class="dropdown">
          <a href="/dbmodel" class="dropdown-toggle" data-toggle="dropdown">DB &amp; Models<b class="caret"></b></a>
          <ul class="dropdown-menu">
            <li><a href="/dbmodel">Generate</a></li>
            <li><a href="/relationship">Relationship</a></li>
            <!-- <li><a href="javascript:;" class="update-link" data-url="/generate/cmodel">C-Model Addons</a></li> -->
          </ul>
        </li>

        <li><a href="/api">API</a></li>
      </ul>
    </div>

  </div>
</div>
