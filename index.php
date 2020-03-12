<?php
use Pagekit\News\Event\PostListener;
use Pagekit\News\Event\RoutePostListener;
use Pagekit\News\Trend as Trend;
return [
    'name' => 'news',

    'autoload' => [
        'Pagekit\\News\\' => 'src'
    ],

    'resources' => [
        'news:' => '',
        'views:news' => 'views'
    ],


    'menu' => [
        'news' => [
            'label' => 'News',
            'url' => '@news/posts',
            'active' => '@news/posts*',
            'priority' => 105,
            'icon' => 'news:icon.svg',
            'access' => 'news: manage own posts'
        ],
        'news:posts' => [
            'parent' => 'news',
            'label' => 'Posts',
            'url' => '@news/posts',
            'active' => '@news/posts*',
            'priority' => 10,
            'access' => 'news: manage own posts'
        ],
        'news:categories' => [
            'parent' => 'news',
            'label' => 'Categories',
            'url' => '@news/categories',
            'active' => '@news/categories*',
            'priority' => 20,
            'access' => 'news: manage all posts'
        ],
        'news:tags' => [
            'parent' => 'news',
            'label' => 'Tags',
            'url' => '@news/tags',
            'active' => '@news/tags*',
            'priority' => 30,
            'access' => 'news: manage all posts'
        ],
        'news:comments' => [
            'parent' => 'news',
            'label' => 'Comment',
            'url' => '@news/comment',
            'active' => '@news/comment*',
            'priority' => 40,
            'access' => 'news: manage comments'
        ],
        'news:settings' => [
            'parent' => 'news',
            'label' => 'Settings',
            'url' => '@news/settings',
            'active' => '@news/settings',
            'priority' => 50,
            'access' => 'news: manage settings'
        ]
    ],

    'nodes' => [
        'newslist' => [
            'name' => '@newslist',
            'label' => 'News',
            'active' => '@newslist*',
            'controller' => 'Pagekit\\News\\Controller\\SiteController',
            'protected' => true,
            'frontpage' => true
        ]
    ],

    'routes' => [
        '/news' => [
            'name' => '@news',
            'controller' => [
                'Pagekit\\News\\Controller\\Admin\\PostsController',
                'Pagekit\\News\\Controller\\Admin\\CategoriesController',
                'Pagekit\\News\\Controller\\Admin\\TagsController',
                'Pagekit\\News\\Controller\\Admin\\SettingsController',
                'Pagekit\\News\\Controller\\Admin\\CommentController',
                'Pagekit\\News\\Controller\\OtherController'
            ]
        ],
        '/api/news' => [
            'name' => '@api/news',
            'controller' => [
                'Pagekit\\News\\Controller\\Api\\CategoriesController',
                'Pagekit\\News\\Controller\\Api\\TagsController',
                'Pagekit\\News\\Controller\\Api\\PostsController',
                'Pagekit\\News\\Controller\\Api\\CommentController'
            ]
        ]
    ],

    'permissions' => [
        'news: manage own posts' => [
            'title' => 'Manage own posts',
            'description' => 'Create, edit, delete and publish posts of their own'
        ],
        'news: manage all posts' => [
            'title' => 'Manage all posts',
            'description' => 'Create, edit, delete and publish posts by all users'
        ],
        'news: manage comments' => [
            'title' => 'Manage comments',
            'description' => 'Approve, edit and delete comments'
        ],
        'news: post comments' => [
            'title' => 'Post comments',
            'description' => 'Allowed to write comments on the site'
        ],
        'news: skip comment approval' => [
            'title' => 'Skip comment approval',
            'description' => 'User can write comments without admin approval'
        ],
        'news: comment approval required once' => [
            'title' => 'Comment approval required only once',
            'description' => 'First comment needs to be approved, later comments are approved automatically'
        ],
        'news: skip comment min idle' => [
            'title' => 'Skip comment minimum idle time',
            'description' => 'User can write multiple comments without having to wait in between'
        ],
        'news: manage settings' => [
            'title' => 'Manage Settings',
            'description' => 'Manage news settings and options, must be the developer'
        ]
    ],

    'widgets' => [

        'widgets/news-hot.php',
        'widgets/news-tags.php',
        'widgets/news-categories.php'

    ],

    'settings' => '@news/settings',

    'config' => [
        'image' => [
            'autoConvertWebp' => true,
            'byDevicesRenderWebp' => false
        ],
        'comments' => [
            'autoclose' => false,
            'autoclose_days' => 14,
            'blacklist' => '',
            'comments_per_page' => 20,
            'gravatar' => true,
            'max_depth' => 5,
            'maxlinks' => 2,
            'minidle' => 120,
            'nested' => true,
            'notifications' => 'always',
            'order' => 'ASC',
            'replymail' => true,
            'require_email' => true
        ],
        'posts' => [
            'posts_per_page' => 20,
            'comments_enabled' => true,
            'markdown_enabled' => true
        ],
        'permalink' => [
            'type' => '',
            'custom' => '{slug}'
        ],
        'feed' => [
            'type' => 'rss2',
            'limit' => 20
        ]
    ],

    'events' => [
        'boot' => function ($event, $app) {
            $app->subscribe(
                new PostListener(),
                new RoutePostListener()
            );
        },
        'view.scripts' => function ($event, $scripts) use ($app) {
            // //link news widget to dashboard
            if ($app['user']->hasAccess('news: manage all posts')) {
            $scripts->register('widget-news', 'news:app/bundle/widget-news.js', '~dashboard');
            }
            //link to site menu
            $scripts->register('link-news', 'news:app/bundle/link-news.js', '~panel-link');
            $scripts->register('link-categories', 'news:app/bundle/link-categories.js', '~panel-link');
            //edit posts second tab content
            $scripts->register('posts-meta', 'news:app/bundle/posts-meta.js', '~news-posts-edit');
            $scripts->register('tags-meta', 'news:app/bundle/tags-meta.js', '~tags-edit');
            $scripts->register('categories-meta', 'news:app/bundle/categories-meta.js', '~categories-edit');
        },
        'site' => function ($event, $app) {
            $router     = $app['router'];
            $request    = $router->getRequest();

            if ($request->attributes->get('_route', '') == '@newslist/id') {
                new Trend($request->attributes->get('id')?1: $request->attributes->get('id'));
            }
        },
        //add admin main menu top add news 
        'view.data' => function ($event, $data) use ($app) {
            if (!$app->isAdmin()) {
                return;
            }
            $data->add('Theme', [
                'SidebarItems' => [
                    'additem' => [
                        'addnews' => [
                            'caption' => 'Add Posts',
                            'attrs' => [
                                'href' => $app['url']->get('admin/news/posts/edit')
                            ],
                            'priority' => 2
                        ]
                    ]
                ]
            ]);
        }
    ]
];
