<?php

namespace App\Provider;

use Fw\LastBundle\Router\RouteProvider;
use Symfony\Component\HttpFoundation\Request;

class SmartDirectoryRouteProvider implements RouteProvider
{
    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        return [
            Request::create('blog/article/1'),
            Request::create('blog/article/2'),
            Request::create('blog/article/3'),
        ];
    }
}