<?php

use SlimLittleTools\Middleware\AddHeader;
use SlimLittleTools\Middleware\Cookie;
use SlimLittleTools\Middleware\CsrfGuard;
use SlimLittleTools\Middleware\SlimLittleToolsUse;


//
$CSRF_not_covered_list = [
];
$app->add((new CsrfGuard())->setNotCoveredList($CSRF_not_covered_list));


// 以下、特にSlimLittleToolsUseは「一番外側(最後)」にaddすること
$app->add(new AddHeader($app->getContainer()));
$app->add(new Cookie($app->getContainer()));
$app->add(new SlimLittleToolsUse($app->getContainer()));
