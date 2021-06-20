<?php

use Framework\Util\ServerUtil;


?>
<div id="header">
    <div class="navbar navbar-default">

        <div class="navbar-header">
            <a class="navbar-brand" href="/">
              <?php
              echo ServerUtil::getServerHost();
              ?> </a>
        </div>

        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav">

                <li class="dropdown">
                <li><a href="/dbmodel">Db, Model & Handler</a></li>
                <li><a href="/mapmodel">Map Model</a></li>
                <li><a href="/api">API</a></li>
                <li><a href="/wsdl">WSDL</a></li>
            </ul>
        </div>

    </div>
</div>
