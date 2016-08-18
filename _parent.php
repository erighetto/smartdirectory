<?php
/**
 * parent
 */
$parent = $app['controllers_factory'];
$parent->get('/{cat}', function (Silex\Application $app, $cat) {

    $cat = explode("-", $cat);
    $id = $cat[count($cat) - 1];

    $sql = "select * from cncat_main where cat1 = ? order by moder_vote desc;";
    $links = $app['db']->fetchAll($sql, array((int)$id));

    $sql = "select * from cncat_cat where parent = ? order by name asc;";
    $categ = $app['db']->fetchAll($sql, array((int)$id));

    $sql = "select * from cncat_cat where cid = ? order by name asc;";
    $whereiam = $app['db']->fetchAssoc($sql, array((int)$id));

    return $app['twig']->render(
        'parent.html.twig',
        array(
            'title' => $whereiam['name'],
            'links' => $links,
            'categ' => $categ,
            'whereiam' => $whereiam
        )
    );
})->value('cat', false)->bind('parent');

return $parent;
