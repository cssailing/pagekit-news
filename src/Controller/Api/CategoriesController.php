<?php

namespace Pagekit\News\Controller\Api;

use Pagekit\Application as App;
use Pagekit\News\Kernel;
use Pagekit\News\Model\Categories;

/**
 * @Access("news: manage own posts || news: manage all posts")
 * @Route("categories", name="categories")
 */
class CategoriesController extends Kernel
{

    /**
     * @Route("/", methods="GET")
     * @Request({"filter":"array" , "page":"integer"})
     */
    public function indexAction(array $filter = [], int $page = 0)
    {
        $query  = Categories::query();
        $pattern = Categories::create([
            'date' => new \DateTime(),
            'user_id' => App::user()->id,
            'status' => Categories::STATUS_PUBLISHED
        ]);
        $filter = array_merge(array_fill_keys(['status', 'search', 'author', 'order', 'limit', 'category'], ''), $filter);
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

        $limit = (int) $limit ?: 25;
        $count = $query->count();
        $pages = ceil($count / $limit);
        $page  = max(0, min($pages - 1, $page));
        $categories = array_values($query->offset($page * $limit)->limit($limit)->related(['user'])->orderBy($order[1], $order[2])->get());
        return compact('categories', 'pages', 'count');
    }

    /**
     * @Route(methods="GET")
     * @Request(csrf=true)
     */
    public function getAction()
    {
        $query = Categories::where('status = ?', [Categories::STATUS_PUBLISHED])->orderBy('title', 'asc')->get();
        $pattern = Categories::create([
            'date' => new \DateTime(),
            'user_id' => App::user()->id,
            'status' => Categories::STATUS_PUBLISHED
        ]);
        return compact(['query', 'pattern']);
    }

    /**
     * @Route("/" , methods="POST")
     * @Route("/{id}", methods="POST", requirements={"id"="\d+"})
     * @Request({"category":"array" , "id":"integer"} , csrf=true)
     */
    public function saveAction(array $category = [], int $id = 0)
    {
        if (!$query = Categories::where('id')->first()) {
            if ($id) {
                return App::abort(404, __('Not Found %name%', ['%name%' => __('Category')]));
            }
            $query = Categories::create();
        }
        if(empty($category['slug'])){
            $category['slug'] = App::filter( !empty($category['slug']) ? $category['slug']:$category['title'] , 'slugify');
        }
        $query->save($category);
        return compact('query');
    }

    public function deleteAction($id)
    {
        if ($category = Categories::find($id)) {
            if(!App::user()->hasAccess('news: manage all posts') && !App::user()->hasAccess('news: manage own posts') && $category->user_id !== App::user()->id) {
                App::abort(400, __('Access denied.'));
            }
            $category->delete();
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
            if ($category = Categories::find((int) $id)) {
                if(!App::user()->hasAccess('news: manage all categories') && !App::user()->hasAccess('news: manage own categories') && $category->user_id !== App::user()->id) {
                    continue;
                }
                $category = clone $category;
                $category->id = null;
                $category->status = Categories::STATUS_DRAFT;
                $category->title = $category->title.' - '.__('Copy');
                $category->date = new \DateTime();
                $category->save();
            }
        }
        return ['message' => 'success'];
    }

    /**
     * @Route("/bulk", methods="POST")
     * @Request({"categories": "array"}, csrf=true)
     */
    public function bulkSaveAction($categories = [])
    {
        foreach ($categories as $data) {
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
