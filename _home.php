<?php
require_once __DIR__ . '/app.php';

/**
 * Home
 */
$home = $app['controllers_factory'];
$home->get('/', function (Silex\Application $app) {

  return $app['twig']->render(
      'home.html.twig',
      array(
          'title' => 'Home'
      )
  );
})->bind('home');

return $home;
