<?php

namespace Pagekit\News\Controller\Api;

use Pagekit\Application as App;
use Pagekit\News\Model\Tags;

/**
 * @Access("news: manage own posts || news: manage all posts")
 * @Route("tags", name="tags")
 */
class TagsController 
{

    /**
     * @Route("/", methods="GET")
     * @Request({"filter":"array" , "page":"integer"})
     */
    public function indexAction(array $filter = [], int $page = 0)
    {
        $query  = Tags::query();
        $pattern = Tags::create([
            'date' => new \DateTime(),
            'user_id' => App::user()->id,
            'status' => Tags::STATUS_PUBLISHED
        ]);
        $filter = array_merge(array_fill_keys(['status', 'search', 'author', 'order', 'limit', 'tags'], ''), $filter);
        extract($filter, EXTR_SKIP);

        if (is_numeric($status)) {
            $query->where(['status' => (int) $status]);
        }

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->orWhere(['title LIKE :search', 'slug LIKE :search'], ['search' => "%{$search}%"]);
            });
        }

        if ($author) {
            $query->where(function ($query) use ($author) {
                $query->orWhere(['user_id' => (int) $author]);
            });
        }
        if (!preg_match('/^(id|date|title|comment_count)\s(asc|desc)$/i', $order, $order)) {
            $order = [1 => 'date', 2 => 'desc'];
        }

        $limit = (int) $limit ?: 10;
        $count = $query->count();
        $pages = ceil($count / $limit);
        $page  = max(0, min($pages - 1, $page));
        $tags = array_values($query->offset($page * $limit)->limit($limit)->related(['user'])->orderBy($order[1], $order[2])->get());
        return compact('tags', 'pattern', 'pages', 'count');
    }

    /**
     * @Route(methods="GET")
     * @Request(csrf=true)
     */
    public function getAction()
    {
        $query = Tags::where('status = ?', [Tags::STATUS_PUBLISHED])->orderBy('title', 'asc')->get();
        $pattern = Tags::create([
            'date' => new \DateTime(),
            'user_id' => App::user()->id,
            'status' => Tags::STATUS_PUBLISHED
        ]);
        return compact(['query', 'pattern']);
    }

    /**
     * @Route("/" , methods="POST")
     * @Route("/{id}", methods="POST", requirements={"id"="\d+"})
     * @Request({"tags":"array" , "id":"integer"} , csrf=true)
     */
    public function saveAction(array $tags = [], int $id = 0)
    {
        if (!$query = Tags::where('id')->first()) {
            if ($id) {
                return App::abort(404, __('Not Found %name%', ['%name%' => __('Tags')]));
            }
            $query = Tags::create();
        }
        if (empty($tags['slug'])) {
            $tags['slug'] = App::filter(!empty($tags['slug']) ? $tags['slug'] : $tags['title'], 'slugify');
        }
        $query->save($tags);
        return compact('query');
    }

    public function deleteAction($id)
    {
        if ($tag = Tags::find($id)) {
            if (!App::user()->hasAccess('news: manage all posts') && !App::user()->hasAccess('news: manage own posts') && $tag->user_id !== App::user()->id) {
                App::abort(400, __('Access denied.'));
            }
            $tag->delete();
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
            if ($tag = Tags::find((int) $id)) {
                if (!App::user()->hasAccess('news: manage all posts') && !App::user()->hasAccess('news: manage own posts') && $tag->user_id !== App::user()->id) {
                    continue;
                }
                $tag = clone $tag;
                $tag->id = null;
                $tag->status = Tags::STATUS_DRAFT;
                $tag->title = $tag->title . ' - ' . __('Copy');
                $tag->date = new \DateTime();
                $tag->save();
            }
        }
        return ['message' => 'success'];
    }

    /**
     * @Route("/bulk", methods="POST")
     * @Request({"tags": "array"}, csrf=true)
     */
    public function bulkSaveAction($tags = [])
    {
        foreach ($tags as $data) {
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
