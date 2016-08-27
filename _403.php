<?php
/**
 * error 403
 */
$error403 = $app['controllers_factory'];
$error403->get('/403.html', function (Silex\Application $app) {

    return $app->abort(403, "Non hai accesso a questa sezione!");

})->bind('error403');

return $error403;