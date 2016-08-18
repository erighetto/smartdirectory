<?php
/**
 * child
 */
$child = $app['controllers_factory'];
$child->get('/{cat1}/{cat2}', function (Silex\Application $app, $cat1, $cat2) {

    $cat = explode("-", $cat2);
    $id = $cat[count($cat) - 1];

    $sql = "select * from cncat_main where cat1 = ? order by moder_vote desc;";
    $links = $app['db']->fetchAll($sql, array((int)$id));

    $sql = "select * from cncat_cat where parent = ? order by name asc;";
    $categ = $app['db']->fetchAll($sql, array((int)$id));

    $sql = "select * from cncat_cat where cid = ? order by name asc;";
    $actual= $app['db']->fetchAssoc($sql, array((int)$id));

    $sql = "select * from cncat_cat where cid = ? order by name asc;";
    $parent = $app['db']->fetchAssoc($sql, array((int)$actual['parent']));

    return $app['twig']->render(
        'child.html.twig',
        array(
            'title' => $parent['name'] ." - ". $actual['name'],
            'links' => $links,
            'categ' => $categ,
            'parent' => $parent
        )
    );
})->value('cat1', false)->value('cat2', false)->bind('child');

return $child;