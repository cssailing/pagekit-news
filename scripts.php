<?php
use Pagekit\Application as App;
use Doctrine\DBAL\Schema\Comparator;

return [
    'install' => function ($app) {
        $util = $app['db']->getUtility();

        if (!$util->tableExists('@news_posts')) {
            $util->createTable('@news_posts', function ($table) {
                $table->addColumn('id', 'integer', ['autoincrement' => true, 'unsigned' => true, 'length' => 50]);
                $table->addColumn('title', 'string');
                $table->addColumn('slug', 'string');
                $table->addColumn('user_id', 'integer');
                $table->addColumn('date', 'datetime');
                $table->addColumn('modified', 'datetime', ['notnull' => false]);
                $table->addColumn('content', 'text', ['notnull' => false]);
                $table->addColumn('categories', 'simple_array', ['notnull' => false]);
                $table->addColumn('tags', 'simple_array', ['notnull' => false]);
                $table->addColumn('trend', 'integer' , ['default' => 0]);
                $table->addColumn('style', 'integer');
                $table->addColumn('hit', 'integer', ['default' => 0]);
                $table->addColumn('status', 'integer');
                $table->addColumn('excerpt', 'text', ['notnull' => false]);
                $table->addColumn('comment_status', 'boolean', ['default' => false]);
                $table->addColumn('comment_count', 'integer', ['default' => 0]);
                $table->addColumn('roles', 'simple_array', ['notnull' => false]);
                $table->addColumn('data', 'json_array');
                $table->setPrimaryKey(['id']);
                $table->addIndex(['title'], '@NEWS_POSTS_TITLE');
                $table->addIndex(['slug'], '@NEWS_POSTS_SLUG');
            });
        }

        if (!$util->tableExists('@news_categories')) {
            $util->createTable('@news_categories', function ($table) {
                $table->addColumn('id', 'integer', ['autoincrement' => true, 'unsigned' => true, 'length' => 10]);
                $table->addColumn('title', 'string');
                $table->addColumn('slug', 'string');
                $table->addColumn('user_id', 'integer');
                $table->addColumn('date', 'datetime');
                $table->addColumn('modified', 'datetime', ['notnull' => false]);
                $table->addColumn('status', 'integer');
                $table->addColumn('data', 'json_array');
                $table->setPrimaryKey(['id']);
                $table->addIndex(['title'], '@NEWS_CATEGORIES_TITLE');
                $table->addIndex(['slug'], '@NEWS_CATEGORIES_SLUG');
            });
        }

        if (!$util->tableExists('@news_tags')) {
            $util->createTable('@news_tags', function ($table) {
                $table->addColumn('id', 'integer', ['autoincrement' => true, 'unsigned' => true, 'length' => 10]);
                $table->addColumn('title', 'string');
                $table->addColumn('slug', 'string');
                $table->addColumn('user_id', 'integer');
                $table->addColumn('date', 'datetime');
                $table->addColumn('modified', 'datetime', ['notnull' => false]);
                $table->addColumn('status', 'integer');
                $table->addColumn('data', 'json_array');
                $table->setPrimaryKey(['id']);
                $table->addIndex(['title'], '@NEWS_TAGS_TITLE');
                $table->addIndex(['slug'], '@NEWS_TAGS_SLUG');
            });
        }

        if ($util->tableExists('@news_comment') === false) {
            $util->createTable('@news_comment', function ($table) {
                $table->addColumn('id', 'integer', ['unsigned' => true, 'length' => 10, 'autoincrement' => true]);
                $table->addColumn('parent_id', 'integer', ['unsigned' => true, 'length' => 10]);
                $table->addColumn('post_id', 'integer', ['unsigned' => true, 'length' => 10]);
                $table->addColumn('user_id', 'string', ['length' => 255]);
                $table->addColumn('author', 'string', ['length' => 255]);
                $table->addColumn('email', 'string', ['length' => 255]);
                $table->addColumn('url', 'string', ['length' => 255, 'notnull' => false]);
                $table->addColumn('ip', 'string', ['length' => 255]);
                $table->addColumn('created', 'datetime');
                $table->addColumn('content', 'text');
                $table->addColumn('status', 'smallint');
                $table->setPrimaryKey(['id']);
                $table->addIndex(['author'], '@NEWS_COMMENT_AUTHOR');
                $table->addIndex(['created'], '@NEWS_COMMENT_CREATED');
                $table->addIndex(['status'], '@NEWS_COMMENT_STATUS');
                $table->addIndex(['post_id'], '@NEWS_COMMENT_POST_ID');
                $table->addIndex(['post_id', 'status'], '@NEWS_COMMENT_POST_ID_STATUS');
            });
        }
    },

    'uninstall' => function ($app) {
        $util = $app['db']->getUtility();

        if ($util->tableExists('@news_posts')) {
            $util->dropTable('@news_posts');
        }

        if ($util->tableExists('@news_categories')) {
            $util->dropTable('@news_categories');
        }

        if ($util->tableExists('@news_tags')) {
            $util->dropTable('@news_tags');
        }

        if ($util->tableExists('@news_comment')) {
            $util->dropTable('@news_comment');
        }
        // remove the config
        $app['config']->remove('pagekit/news');
    },
    
    'enable' => function ($app) { 
        
    },

    'disable' => function ($app) { },

    'updates' => [
        '1.0.2' => function ($app) { 
            $db = $app['db'];
            $util = $db->getUtility();

            if ($util->tableExists('@news_posts')) {
                $table =  $util->listTableDetails('@news_posts');
                if (!$table->hasColumn('trend')) {
                    $table->addColumn('trend', 'integer' , ['default' => 0]);
                    $util->alterTable((new Comparator())->diffTable($util->listTableDetails('@news_posts'), $table));
                    $app['db']->executeQuery('UPDATE @news_posts SET trend = 0');
                }
            }
        }
    ]
];
