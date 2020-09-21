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
        <li><a href="/dbmodel">Db, Model & Handler</a></li>
        <li><a href="/mapmodel">Map Model</a></li>
        <li><a href="/api">API</a></li>
      </ul>
    </div>

  </div>
</div>
