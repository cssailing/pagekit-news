<?php

namespace Pagekit\News\Controller\Admin;

use Pagekit\Application as App;
use Pagekit\News\Model\Posts;
use Pagekit\News\Model\Categories;
use Pagekit\News\Kernel;
use Pagekit\User\Model\User;
use Pagekit\User\Model\Role;
use Pagekit\News\Model\Tags;

/**
 * @Access(admin=true)
 * @Access("news: manage all posts")
 * @Route("posts" , name="posts")
 */
class PostsController extends Kernel
{

    /**
     * @Route("/")
     * @Request({"filter":"array" , "page":"integer"})
     */
    public function indexAction(array $filter = [], int $page = 0)
    {
        $categories = Categories::where('status = ?', [Categories::STATUS_PUBLISHED])->get();
        $user = App::user();
        $util = App::db()->createQueryBuilder();

        $postsAuthors = $util->select('user_id')
            ->from('@news_posts')
            ->groupBy('user_id')->get();

        $implodePost = [];
        foreach ($postsAuthors as $value) {
            array_push($implodePost, $value['user_id']);
        }
        if ($implodePost) {
            $authors = User::where('id IN (' . implode(',', $implodePost) .  ')')->get();
        } else {
            $authors = false;
        }
        return [
            '$view' => [
                'title' => __('Posts List'),
                'name' => 'news:views/admin/posts-list.php'
            ],
            '$data' => [
                'config' => [
                    'filter' => (object) $filter,
                    'page' => $page
                ],
                'categories' => $categories,
                'authors' => $authors,
                'statuses' => Posts::getStatuses(),
                'canEditAll' => $user->hasAccess('news: manage all posts')
            ]
        ];
    }

    /**
     * @Route("/edit")
     * @Request({"id":"integer"})
     */
    public function editAction(int $id = 0)
    {
        $config = App::module('news')->config();
        if (!$query = Posts::where(compact('id'))->first()) {
            if ($id) {
                return App::abort(404, __('Not Found Id'));
            }
            $query = Posts::create([
                'date' => new \DateTime(),
                'user_id' => App::user()->id,
                'status' => Posts::STATUS_PUBLISHED,
                'style' => Posts::STYLE_DEFAULT,
                'slug' =>  Kernel::secretString(),
                'categories' => [],
                'comment_status' => $config['posts']['comments_enabled'],
                'tags' => [],
                'data' => ['markdown' => $config['posts']['markdown_enabled']]
            ]);
        }

        $user = App::user();
        if (!$user->hasAccess('news: manage all posts') && $query->user_id !== $user->id) {
            App::abort(403, __('Insufficient User Rights.'));
        }

        $roles = App::db()->createQueryBuilder()
            ->from('@system_role')
            ->where(['id' => Role::ROLE_ADMINISTRATOR])
            ->whereInSet('permissions', ['news: manage all posts', 'news: manage own posts'], false, 'OR')
            ->execute('id')
            ->fetchAll(\PDO::FETCH_COLUMN);
        $authors = App::db()->createQueryBuilder()
            ->from('@system_user')
            ->whereInSet('roles', $roles)
            ->execute('id, username, name')
            ->fetchAll();
        $tags = App::db()->createQueryBuilder()
            ->from('@news_tags')
            ->execute('id, title')
            ->fetchAll();
        $categories = App::db()->createQueryBuilder()
            ->from('@news_categories')
            ->execute('id, title')
            ->fetchAll();

        return [
            '$view' => [
                'title' => $query->id ? __('%title% edit' , ['%title%' => $query->title]) : __('New Post'),
                'name' => 'news:views/admin/posts-edit.php'
            ],
            '$data' => [
                'post' => $query,
                'data' => [
                    'statuses' => Posts::getStatuses(),
                    'styles' => Posts::getStyles(),
                    'tags' => $tags,
                    'categories' => $categories,
                    'roles'    => array_values(Role::findAll()),
                    'authors'  => $authors,
                    'canEditAll' => $user->hasAccess('news: manage all posts'),
                ]
            ]
        ];
    }
}
