<?php
declare(strict_types=1);

namespace SlimLittleTools\Tests;

use Psr\Container\ContainerInterface;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\App;


/**
 * testsの基底クラス: appとか作るの一箇所に集中させたい
 */

class TestBase extends \PHPUnit\Framework\TestCase
{
    //
    public static function getContainer($settings = []) : ContainerInterface
    {
        $containerBuilder = new ContainerBuilder();
        if ([] !== $settings) {
            $containerBuilder->addDefinitions($settings);
        }
        return $containerBuilder->build();
    }

    //
    public static function getApp($settings = []) : App
    {
        AppFactory::setContainer(static::getContainer($settings));
        static::appAddition();
        return AppFactory::create();
    }

    // XXX app作るのになんか追加処理入れたい時用
    public static function appAddition()
    {
    }

}
