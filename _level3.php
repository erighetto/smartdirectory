<?php
/**
 * level3
 */
$level3 = $app['controllers_factory'];
$level3->get('/{cat1}/{cat2}/{cat3}', function (Silex\Application $app, $cat1, $cat2, $cat3) {

    $cat1 = explode("-", $cat1);
    $id1 = $cat1[count($cat1) - 1];

    $cat2 = explode("-", $cat2);
    $id2 = $cat2[count($cat2) - 1];

    $cat3 = explode("-", $cat3);
    $id3 = $cat3[count($cat3) - 1];

    $sql = "select * from cncat_main where cat1 = ? order by moder_vote desc;";
    $links = $app['db']->fetchAll($sql, array((int)$id3));

    $sql = "select * from cncat_cat where cid = ? order by name asc;";
    $actual= $app['db']->fetchAssoc($sql, array((int)$id3));

    $sql = "select * from cncat_cat where cid = ? order by name asc;";
    $parent = $app['db']->fetchAssoc($sql, array((int)$id1));

    $sql = "select * from cncat_cat where cid = ? order by name asc;";
    $children = $app['db']->fetchAssoc($sql, array((int)$id2));

    return $app['twig']->render(
        'level3.html.twig',
        array(
            'title' => $parent['name'] ." - ". $children['name'] ." - ".$actual['name'],
            'links' => $links,
            'children' => $children,
            'parent' => $parent,
            'actual' => $actual
        )
    );
})->bind('level3');

return $level3;