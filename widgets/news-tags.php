<?php
use Pagekit\News\Model\Tags;
return [

    'name' => 'news-tags/widget',

    'label' => 'News Tags Widget',
	
	'defaults' => [
        'list_limit' => 10,
        'field' => 'id',
        'order' => 'desc',
		'css_enabled' => false,
    ],

    'events' => [

        'view.scripts' => function ($event, $scripts) use ($app) {
            $scripts->register('widget-news-tags', 'news:app/bundle/widget/news-tags.js', ['~widgets']);
        }

    ],

    'render' => function ($widget) use ($app) {
    	$tags = Tags::query()->where('status = ?' , [Tags::STATUS_PUBLISHED])
    	->limit((int) $widget->get('list_limit') ?: 10)
        ->orderBy($widget->get('field') ?: 'id', $widget->get('order'))
        ->get('id','title');
		//$layout = false;
		$layout = true;
		$list_limit			= (int)((!$widget->get('list_limit')) ? 10 : $widget->get('list_limit'));
		$css_enabled		= (int)((!$widget->get('css_enabled')) ? false : $widget->get('css_enabled'));
		return $app['view']('news:views/widget/news-tags.php', compact('widget', 'tags', 'list_limit', 'css_enabled', 'layout'));
    }
];