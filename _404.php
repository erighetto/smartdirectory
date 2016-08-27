<?php
/**
 * error 404
 */
$error404 = $app['controllers_factory'];
$error404->get('/404.html', function (Silex\Application $app) {

    return $app->abort(404, "Cerchi qualcosa che non esiste piu");

})->bind('error404');

return $error404;