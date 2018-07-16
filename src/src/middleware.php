<?php

use SlimLittleTools\Middleware\AddHeader;
use SlimLittleTools\Middleware\Cookie;
use SlimLittleTools\Middleware\CsrfGuard;
use SlimLittleTools\Middleware\SlimLittleToolsUse;

//
$app->add(new AddHeader($app->getContainer()));
$app->add(new Cookie($app->getContainer()));
$app->add(new SlimLittleToolsUse($app->getContainer()));

//
$CSRF_not_covered_list = [
];
$app->add((new CsrfGuard())->setNotCoveredList($CSRF_not_covered_list));
