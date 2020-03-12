<?php

namespace Pagekit\News;

use Pagekit\Application as App;
use Pagekit\News\Model\Tags;
use Pagekit\Routing\ParamsResolverInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class TagResolver implements ParamsResolverInterface
{
    const CACHE_KEY = 'news.tag.routing';

    /**
     * @var bool
     */
    protected $cacheDirty = false;

    /**
     * @var array
     */
    protected $cacheEntries;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->cacheEntries = App::cache()->fetch(self::CACHE_KEY) ?: [];
    }

    /**
     * {@inheritdoc}
     */
    public function match(array $parameters = [])
    {
        if (isset($parameters['tag_id'])) {
            return $parameters;
        }

        if (!isset($parameters['slug'])) {
            App::abort(404, 'Tag not found.');
        }

        $slug = $parameters['slug'];

        $id = false;
        foreach ($this->cacheEntries as $entry) {
            //print_r($entry);
            if ($entry['slug'] === $slug) {
                $id = $entry['id'];
            }
        }

        if (!$id) {

            if (!$tag = Tags::where(compact('slug'))->first()) {
                App::abort(404, 'Tag not found.');
            }

            $this->addCache($tag);
            $id = $tag->id;
        }

        $parameters['tag_id'] = $id;
        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $parameters = [])
    {
        $id = $parameters['tag_id'];
        if (!isset($this->cacheEntries[$id])) {

            if (!$tag = Tags::where(compact('id'))->first()) {
                throw new RouteNotFoundException('Tag not found!');
            }

            $this->addCache($tag);
        }

        $meta = $this->cacheEntries[$id];

        preg_match_all('#{([a-z]+)}#i', self::getPermalink(), $matches);

        if ($matches) {
            foreach($matches[1] as $attribute) {
                if (isset($meta[$attribute])) {
                    $parameters[$attribute] = $meta[$attribute];
                }
            }
        }

        unset($parameters['tag_id']);
        
        return $parameters;
    }

    public function __destruct()
    {
        if ($this->cacheDirty) {
            App::cache()->save(self::CACHE_KEY, $this->cacheEntries);
        }
    }

    /**
     * Gets the news's permalink setting.
     *
     * @return string
     */
    public static function getPermalink()
    {
        static $permalink;

        if (null === $permalink) {

            $news = App::module('news');
            $permalink = $news->config('permalink.type');

            if ($permalink == 'custom') {
                $permalink = $news->config('permalink.custom');
            }

        }

        return $permalink;
    }

    protected function addCache($tag)
    {
        $this->cacheEntries[$tag->id] = [
            'id'     => $tag->id,
            'slug'   => $tag->slug,
            'year'   => $tag->date->format('Y'),
            'month'  => $tag->date->format('m'),
            'day'    => $tag->date->format('d'),
            'hour'   => $tag->date->format('H'),
            'minute' => $tag->date->format('i'),
            'second' => $tag->date->format('s'),
        ];

        $this->cacheDirty = true;
    }
}