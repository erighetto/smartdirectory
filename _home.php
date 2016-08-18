<?php
/**
 * Home
 */
$home = $app['controllers_factory'];
$home->get('/', function (Silex\Application $app) {

    $sql = "select * from cncat_cat where parent = 0 order by name asc;";
    $categ = $app['db']->fetchAll($sql);

    return $app['twig']->render(
        'home.html.twig',
        array(
            'title' => 'Home',
            'categ' => $categ
        )
    );
})->bind('home');

return $home;
