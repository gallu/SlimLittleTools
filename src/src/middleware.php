<?php

use SlimLittleTools\Middleware\AddHeader;
use SlimLittleTools\Middleware\Cookie;
use SlimLittleTools\Middleware\CsrfGuard;
use SlimLittleTools\Middleware\SlimLittleToolsUse;

// CSRFの例外リストを引数で渡す
$CSRF_not_covered_list = [
];
$app->add((new CsrfGuard())->setNotCoveredList($CSRF_not_covered_list));

// 定番の諸々
// XXX SlimLittleToolsUseは「一番外側(最後)」にaddすること
$app->add(new AddHeader($app->getContainer()));
$app->add(new Cookie($app->getContainer()));
$app->add(new SlimLittleToolsUse($app->getContainer()));
