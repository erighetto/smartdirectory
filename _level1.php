<?php
/**
 * level1
 */
$level1 = $app['controllers_factory'];
$level1->get('/{cat1}', function (Silex\Application $app, $cat1) {

    $cat1 = explode("-", $cat1);
    $id1 = $cat1[count($cat1) - 1];

    $sql = "select * from cncat_main where cat1 = ? order by moder_vote desc;";
    $links = $app['db']->fetchAll($sql, array((int)$id1));

    $sql = "select * from cncat_cat where parent = ? order by name asc;";
    $children = $app['db']->fetchAll($sql, array((int)$id1));

    $sql = "select * from cncat_cat where cid = ? order by name asc;";
    $actual = $app['db']->fetchAssoc($sql, array((int)$id1));

    return $app['twig']->render(
        'level1.html.twig',
        array(
            'title' => $actual['name'],
            'links' => $links,
            'children' => $children,
            'actual' => $actual
        )
    );
})->value('cat1', false)->bind('level1');

return $level1;
