<?php

namespace Pagekit\News\Event;

use Pagekit\Application as App;
use Pagekit\News\PostResolver;
use Pagekit\News\CategoryResolver;
use Pagekit\News\TagResolver;
use Pagekit\Event\EventSubscriberInterface;

class RoutePostListener implements EventSubscriberInterface
{
    /**
     * Adds cache breaker to router.
     */
    public function onAppRequest()
    {
        App::router()->setOption('news.permalink', PostResolver::getPermalink());
    }

    /**
     * Registers permalink route alias.
     */
    public function onConfigureRoute($event, $route)
    {
        if ($route->getName() == '@newslist/id' && PostResolver::getPermalink()) {
            App::routes()->alias(dirname($route->getPath()).'/'.ltrim(PostResolver::getPermalink(), '/'), '@newslist/id', ['_resolver' => 'Pagekit\News\PostResolver']);
        }

        if ($route->getName() == '@newslist/category_id' && CategoryResolver::getPermalink()) {
            App::routes()->alias(dirname($route->getPath()).'/'.ltrim(CategoryResolver::getPermalink(), '/'), '@newslist/category_id', ['_resolver' => 'Pagekit\News\CategoryResolver']);
        }

        if ($route->getName() == '@newslist/tag_id' && TagResolver::getPermalink()) {
            App::routes()->alias(dirname($route->getPath()).'/'.ltrim(TagResolver::getPermalink(), '/'), '@newslist/tag_id', ['_resolver' => 'Pagekit\News\TagResolver']);
        }
    }

    /**
     * Clears resolver cache.
     */
    public function clearCache()
    {
        App::cache()->delete(PostResolver::CACHE_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function subscribe()
    {
        return [
            'request' => ['onAppRequest', 130],
            'route.configure' => 'onConfigureRoute',
            'model.posts.saved' => 'clearCache',
            'model.posts.deleted' => 'clearCache'
        ];
    }
}