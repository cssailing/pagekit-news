<?php

namespace Pagekit\News\Controller\Admin;

use Pagekit\Application as App;
use Pagekit\News\Kernel;
use Pagekit\News\Model\Comment;
use Pagekit\News\Model\Posts;

/**
 * @Access(admin=true)
 * @Route("/comment" , name="comment")
 * @Access("news: manage all posts")
 */
class CommentController extends Kernel
{
    /**
     * @Access("news: manage comments")
     * @Request({"filter": "array", "post":"int", "page":"int"})
     */
    public function indexAction($filter = [], $post = 0, $page = null)
    {
        $post = Posts::find($post);
        $filter['order'] = 'created DESC';
        return [
            '$view' => [
                'title' => $post ? __('Comments on %title%', ['%title%' => $post->title]) : __('Comments'),
                'name' => 'news:views/admin/comment.php'
            ],
            '$data'   => [
                'statuses' => Comment::getStatuses(),
                'config'   => [
                    'filter' => (object) $filter,
                    'page'   => $page,
                    'post'   => $post,
                    'limit'  => App::module('news')->config('comments.comments_per_page')
                ]
            ]
        ];
    }
}
