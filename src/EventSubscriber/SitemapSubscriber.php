<?php

namespace App\EventSubscriber;

use App\Provider\SmartDirectoryRouteProvider;
use Fw\LastBundle\Router\FileSuffixUrlGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\UrlContainerInterface;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Symfony\Component\Routing\RouterInterface;

class SitemapSubscriber implements EventSubscriberInterface
{
    /**
     * @var SmartDirectoryRouteProvider
     */
    private $smartDirectoryRouteProvider;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @inheritDoc
     */
    public function __construct(SmartDirectoryRouteProvider $smartDirectoryRouteProvider, RouterInterface $router)
    {
        $this->smartDirectoryRouteProvider = $smartDirectoryRouteProvider;
        $this->router = $router;
    }

    /**
     * @param SitemapPopulateEvent $event
     */
    public function onPrestaSitemapPopulate(SitemapPopulateEvent $event)
    {
        $this->registerSmartDirectoryUrls($event->getUrlContainer());
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            SitemapPopulateEvent::ON_SITEMAP_POPULATE => 'onPrestaSitemapPopulate',
        ];
    }

    /**
     * @param UrlContainerInterface $urls
     */
    private function registerSmartDirectoryUrls(UrlContainerInterface $urls)
    {
        $routeCollection = $this->router->getRouteCollection();
        $context = $this->router->getContext();
        $baseUrl = $context->getScheme() . "://" . $context->getHost();

        foreach ($routeCollection->all() as $routeName => $route) {
            $controller = $route->getDefault('_controller');
            if (strpos($controller, 'App\Controller\DefaultController') !== false) {
                $urls->addUrl(
                    new UrlConcrete(
                        $baseUrl . FileSuffixUrlGenerator::appendSuffix($route->getPath())
                    ),
                    'default'
                );
            }
        }

        /** @var \Symfony\Component\HttpFoundation\Request[] $routes */
        $routes = $this->smartDirectoryRouteProvider->getRoutes();
        foreach ($routes as $route) {
            $urls->addUrl(
                new UrlConcrete(
                    $baseUrl . FileSuffixUrlGenerator::appendSuffix($route->getPathInfo())
                ),
                'default'
            );
        }
    }
}
