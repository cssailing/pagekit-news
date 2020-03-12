<?php

namespace Pagekit\News\Controller\Api;

use Pagekit\Application as App;
use Pagekit\News\Kernel;
use Pagekit\News\Model\Posts;

/**
 * @Access("news: manage own posts || news: manage all posts")
 * @Route("posts", name="posts")
 */
class PostsController extends Kernel
{
    /**
     * @Route("/", methods="GET")
     * @Request({"filter":"array" , "page":"integer"})
     */

    public function indexAction(array $filter = [], int $page = 0)
    {
        $query  = Posts::query();
        $user = App::user();
        $filter = array_merge(array_fill_keys(['status', 'search', 'author', 'order', 'limit', 'category','tag'], ''), $filter);
        extract($filter, EXTR_SKIP);

        if (is_numeric($status)) {
            $query->where(['status' => (int) $status]);
        }

        if ($category) {
            $query->where('categories IN (' . $category . ')');
        }

        if ($tag) {
            $query->where('tags IN (' . $tag . ')');
        }

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->orWhere(['title LIKE :search', 'slug LIKE :search'], ['search' => "%{$search}%"]);
            });
        }

        if(!$user->hasAccess('news: manage all posts')) {
            $query->where('user_id = :user_id' , ['user_id' => $user->id]);
        }else{
            if ($author) {
                $query->where(function ($query) use ($author) {
                    $query->orWhere(['user_id' => (int) $author]);
                });
            }
        }

        if (!preg_match('/^(id|date|title|comment_count)\s(asc|desc)$/i', $order, $order)) {
            $order = [1 => 'date', 2 => 'desc'];
        }

        $limit = (int) $limit ?: 25;
        $count = $query->count();
        $pages = ceil($count / $limit);
        $page  = max(0, min($pages - 1, $page));
        $posts = array_values($query->offset($page * $limit)->limit($limit)->related(['user'])->orderBy($order[1], $order[2])->get());
        return compact('posts', 'pages', 'count');
    }

    /**
     * @Route("/{id}", methods="GET", requirements={"id"="\d+"})
     */
    public function getAction($id)
    {
        return Posts::where(compact('id'))->related('user', 'comments')->first();
    }


    /**
     * @Route("/", methods="POST")
     * @Route("/{id}", methods="POST", requirements={"id"="\d+"})
     * @Request({"posts":"array" , "id":"integer"} , csrf=true)
     */
    public function saveAction(array $posts = [], int $id = 0)
    {
        if (!$query = Posts::where('id')->first()) {
            if ($id) {
                return App::abort(404, __('Not Found %name%', ['%name%' => __('Posts')]));
            }
            $query = Posts::create();
        }
        if (!$posts['slug'] = App::filter($posts['slug'] ?: $posts['title'], 'slugify')) {
            App::abort(400, __('Invalid slug.'));
        }
        $query->save($posts);
        return compact('query');
    }

    public function deleteAction($id)
    {
        if ($post = Posts::find($id)) {
            if(!App::user()->hasAccess('news: manage all posts') && !App::user()->hasAccess('news: manage own posts') && $post->user_id !== App::user()->id) {
                App::abort(400, __('Access denied.'));
            }
            $post->delete();
        }
        return ['message' => 'success'];
    }

    /**
     * @Route(methods="POST")
     * @Request({"ids": "int[]"}, csrf=true)
     */
    public function copyAction($ids = [])
    {
        foreach ($ids as $id) {
            if ($post = Posts::find((int) $id)) {
                if(!App::user()->hasAccess('news: manage all posts') && !App::user()->hasAccess('news: manage own posts') && $post->user_id !== App::user()->id) {
                    continue;
                }
                $post = clone $post;
                $post->id = null;
                $post->status = Posts::STATUS_DRAFT;
                $post->title = $post->title.' - '.__('Copy');
                $post->date = new \DateTime();
                $post->save();
            }
        }
        return ['message' => 'success'];
    }

    /**
     * @Route("/bulk", methods="POST")
     * @Request({"posts": "array"}, csrf=true)
     */
    public function bulkSaveAction($posts = [])
    {
        foreach ($posts as $data) {
            $this->saveAction($data, isset($data['id']) ? $data['id'] : 0);
        }
        return ['message' => 'success'];
    }

    /**
     * @Route("/bulk", methods="DELETE")
     * @Request({"ids": "array"}, csrf=true)
     */
    public function bulkDeleteAction($ids = [])
    {
        foreach (array_filter($ids) as $id) {
            $this->deleteAction($id);
        }
        return ['message' => 'success'];
    }
}
