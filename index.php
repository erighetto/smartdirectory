<?php
/**
 * Author: emanuel
 * Date: 09/08/16
 * Time: 16.24
 */

require_once __DIR__ . '/vendor/autoload.php';

// init application
$app = new Silex\Application();

// set debug mode
$app['debug'] = true;

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/templates',
));

$app->register(new Silex\Provider\AssetServiceProvider(), array(
    'assets.version' => 'v1',
    'assets.version_format' => '%s?version=%s',
    'assets.named_packages' => array(
        'css' => array('version' => '1.0', 'base_path' => '/css'),
        'js' => array('version' => '1.0', 'base_path' => '/js'),
        'images' => array('base_path' => 'img'),
    ),
));

$app->register(new DF\Silex\Provider\YamlConfigServiceProvider(__DIR__ . '/settings.yml'));

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => $app['config']['database']
));

$app->mount('/', include __DIR__ .'/_home.php');
$app->mount('/', include __DIR__ .'/_level1.php');
$app->mount('/', include __DIR__ .'/_level2.php');
$app->mount('/', include __DIR__ .'/_level3.php');

$app->run();
