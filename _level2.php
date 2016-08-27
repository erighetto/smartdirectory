<?php
/**
 * level2
 */
$level2 = $app['controllers_factory'];
$level2->get('/{cat1}/{cat2}', function (Silex\Application $app, $cat1, $cat2) {

    $cat1 = explode("-", $cat1);
    $id1 = $cat2[count($cat1) - 1];

    $cat2 = explode("-", $cat2);
    $id2 = $cat2[count($cat2) - 1];

    $sql = "select * from cncat_main where cat1 = ? order by moder_vote desc;";
    $links = $app['db']->fetchAll($sql, array((int)$id2));

    $sql = "select * from cncat_cat where parent = ? order by name asc;";
    $children = $app['db']->fetchAll($sql, array((int)$id2));

    $sql = "select * from cncat_cat where cid = ? order by name asc;";
    $actual= $app['db']->fetchAssoc($sql, array((int)$id2));

    $sql = "select * from cncat_cat where cid = ? order by name asc;";
    $parent = $app['db']->fetchAssoc($sql, array((int)$actual['parent']));

    return $app['twig']->render(
        'level2.html.twig',
        array(
            'title' => $parent['name'] ." - ". $actual['name'],
            'links' => $links,
            'children' => $children,
            'parent' => $parent,
            'actual' => $actual
        )
    );
})->bind('level2');

return $level2;