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
    public static function setUpBeforeClass(): void
    {
        //
        parent::setUpBeforeClass();

        // Noticeであろうとも、エラーが出たら速やかに例外をぶん投げる
        set_error_handler(
          function ($errno, $errstr, $errfile, $errline) {
            if (0 !== $errno & error_reporting()) {
                throw new ErrorException( $errstr, 0, $errno, $errfile, $errline);
            }
          }
        );
    }

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
