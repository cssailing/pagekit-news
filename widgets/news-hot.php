<?php
use Pagekit\News\Model\Posts;
return [

    'name' => 'news-hot/widget',

    'label' => 'News List Widget',
    'defaults' => [
        'result_per_page' => 5,
        'triggering_chars' => 3,
        'tags_id' => '',
        'field' => 'id',
        'order' => 'desc',
        'css_enabled' => false,
    ],

    'events' => [

        'view.scripts' => function ($event, $scripts) use ($app) {
            $scripts->register('widget-news-hot', 'news:app/bundle/widget/news-hot.js', ['~widgets']);
        }

    ],

    'render' => function ($widget) use ($app) {
        $query = Posts::where(['status = :status', 'date < :date'], ['status' => Posts::STATUS_PUBLISHED, 'date' => new \DateTime]);
        $images = 0;
        if ($widget->get('field') == 'tags') {
            $tag_id = $widget->get('tags_id');
            $query->where(function ($query) use ($tag_id) {
                return $query->whereInSet('tags', $tag_id);
            });
        }

        if ($widget->get('field') == 'images') {
            $images = 1;
            $query->where(function ($query) use ($images) {
            return $query->where('JSON_EXTRACT(data,"$.image") IS NOT NULL');
            });
        }

        if($widget->get('field') == 'tags' || $widget->get('field') == 'images'){
            $order_by = 'id';
        }else{
            $order_by = $widget->get('field');
        }

        $query->limit((int) $widget->get('result_per_page') ?: 10)->orderBy($order_by, $widget->get('order'));
        $posts = $query->get(['id','date', 'title', 'slug', 'data', 'excerpt']);

        $layout = true;
		$css_enabled		= (int)((!$widget->get('css_enabled')) ? false : $widget->get('css_enabled'));
		return $app['view']('news:views/widget/news-hot.php', compact('widget', 'posts', 'images', 'css_enabled', 'layout'));
    }
];