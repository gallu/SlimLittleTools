<?php
//
$container = $app->getContainer();

//
$container[] = function ($c) {
    return \SlimLittleTools\Libs\Http\Request::createFromEnvironment($c->get('environment'));
};
