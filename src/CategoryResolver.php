<?php

namespace Pagekit\News;

use Pagekit\Application as App;
use Pagekit\News\Model\Categories;
use Pagekit\Routing\ParamsResolverInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class CategoryResolver implements ParamsResolverInterface
{
    const CACHE_KEY = 'news.category.routing';

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
        if (isset($parameters['category_id'])) {
            return $parameters;
        }

        if (!isset($parameters['slug'])) {
            App::abort(404, 'Category not found.');
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

            if (!$category = Categories::where(compact('slug'))->first()) {
                App::abort(404, 'Category not found.');
            }

            $this->addCache($category);
            $id = $category->id;
        }

        $parameters['category_id'] = $id;
        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $parameters = [])
    {
        $id = $parameters['category_id'];
        if (!isset($this->cacheEntries[$id])) {

            if (!$category = Categories::where(compact('id'))->first()) {
                throw new RouteNotFoundException('Category not found!');
            }

            $this->addCache($category);
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

        unset($parameters['category_id']);
        
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

    protected function addCache($category)
    {
        $this->cacheEntries[$category->id] = [
            'id'     => $category->id,
            'slug'   => $category->slug,
            'year'   => $category->date->format('Y'),
            'month'  => $category->date->format('m'),
            'day'    => $category->date->format('d'),
            'hour'   => $category->date->format('H'),
            'minute' => $category->date->format('i'),
            'second' => $category->date->format('s'),
        ];

        $this->cacheDirty = true;
    }
}