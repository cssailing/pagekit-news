<?php

namespace Pagekit\News\Controller\Admin;

use Pagekit\Application as App;
use Pagekit\News\Kernel;

/**
 * @Access(admin=true)
 * @Route("/settings" , name="settings")
 * @Access("news: manage settings")
 */
class SettingsController extends Kernel
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        return [
            '$view' => [
                'title' => __('News Settings'),
                'name' => 'news:views/admin/settings.php'
            ],
            '$data' => [
                'config' => App::module('news')->config()
            ]
        ];
    }
}
