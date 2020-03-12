<?php

namespace Pagekit\News\Controller\Admin;

use Pagekit\Application as App;
use Pagekit\User\Model\User;
use Pagekit\News\Kernel;
use Pagekit\News\Model\Categories;

/**
 * @Access(admin=true)
 * @Access("news: manage all posts")
 * @Route("categories" , name="categories")
 */
class CategoriesController extends Kernel
{
    /**
     * @Route("/")
     * @Request({"filter":"array" , "page":"integer"})
     */
    public function indexAction(array $filter = [], int $page = 0)
    {
        $util = App::db()->createQueryBuilder();

        $postsAuthors = $util->select('user_id')
        ->from('@news_categories')
        ->groupBy('user_id')->get();

        $implodePost = [];
        foreach( $postsAuthors as $value){
            array_push($implodePost , $value['user_id']);
        }
        if($implodePost){
            $authors = User::where('id IN (' . implode(',' , $implodePost) .  ')')->get();
        }else{
            $authors = false;
        }
        return [
            '$view' => [
                'title' => __('Categories List'),
                'name' => 'news:views/admin/categories-list.php'
            ],
            '$data' => [
                'config' => [
                    'filter' => (object) $filter,
                    'page' => $page
                ],
                'statuses' => Categories::getStatuses(),
                'authors' => $authors
            ]
        ];
    }

    /**
     * @Route("/edit")
     * @Request({"id":"integer"})
     */
    public function editAction( int $id = 0 ){
        if( !$query = Categories::where(compact('id'))->first() ){
            if($id){
                return App::abort(404 , __('Not Found Id'));
            }
            $query = Categories::create([
                'date' => new \DateTime(),
                'user_id' => App::user()->id,
                'slug' =>  Kernel::secretString(),
                'status' => Categories::STATUS_PUBLISHED   
            ]);
        }

        return [
            '$view' => [
                'title' => $query->id ? __('%title% edit' , ['%title%' => $query->title]) : __('New Categories'),
                'name' => 'news:views/admin/categories-edit.php'
            ],
            '$data' => [
                'category' => $query,
                'data' => [
                    'statuses' => Categories::getStatuses()
                ]
            ]
        ];
    }
}
