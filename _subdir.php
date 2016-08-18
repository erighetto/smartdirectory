<?php
/**
 * subdir
 */
$subdir = $app['controllers_factory'];
$subdir->get('/{cat}', function (Silex\Application $app) {

  return $app['twig']->render(
      'subdir.html.twig',
      array(
          'title' => 'subdir'
      )
  );
})->value ( 'cat', false )->bind('subdir');

return $subdir;
