<?php
use Pagekit\News\Model\Categories;
return [

    'name' => 'news-categories/widget',

    'label' => 'News Categories Widget',
	
	'defaults' => [
        'list_limit' => 10,
        'field' => 'id',
        'order' => 'desc',
		'css_enabled' => false,
    ],

    'events' => [

        'view.scripts' => function ($event, $scripts) use ($app) {
            $scripts->register('widget-news-categories', 'news:app/bundle/widget/news-categories.js', ['~widgets']);
        }

    ],

    'render' => function ($widget) use ($app) {
    	$categories = Categories::query()->where('status = ?' , [Categories::STATUS_PUBLISHED])
    	->limit((int) $widget->get('list_limit') ?: 10)
        ->orderBy($widget->get('field') ?: 'id', $widget->get('order'))
        ->get();
		//$layout = false;
		$layout = true;
		$result_per_page	= (int)((!$widget->get('result_per_page')) ? 6 : $widget->get('result_per_page'));
		$triggering_chars 	= (int)((!$widget->get('triggering_chars')) ? 3 : $widget->get('triggering_chars'));
		$list_limit			= (int)((!$widget->get('list_limit')) ? 10 : $widget->get('list_limit'));
		$css_enabled		= (int)((!$widget->get('css_enabled')) ? false : $widget->get('css_enabled'));
		return $app['view']('news:views/widget/news-categories.php', compact('widget', 'categories', 'list_limit', 'css_enabled', 'layout'));
    }
];