<?php

namespace Pagekit\News\Controller\Admin;

use Pagekit\Application as App;
use Pagekit\User\Model\User;
use Pagekit\News\Kernel;
use Pagekit\News\Model\Tags;

/**
 * @Access(admin=true)
 * @Route("/tags" , name="tags")
 * @Access("news: manage all posts")
 */
class TagsController extends Kernel
{
    /**
     * @Route("/")
     * @Request({"filter":"array" , "page":"integer"})
     */
    public function indexAction(array $filter = [], int $page = 0)
    {
        $util = App::db()->createQueryBuilder();

        $postsAuthors = $util->select('user_id')
        ->from('@news_tags')
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
                'title' => __('Tags List'),
                'name' => 'news:views/admin/tags-list.php'
            ],
            '$data' => [
                'config' => [
                    'filter' => (object) $filter,
                    'page' => $page
                ],
                'statuses' => Tags::getStatuses(),
                'authors' => $authors
            ]
        ];
    }

    /**
     * @Route("/edit")
     * @Request({"id":"integer"})
     */
    public function editAction( int $id = 0 ){
        if( !$query = Tags::where(compact('id'))->first() ){
            if($id){
                return App::abort(404 , __('Not Found Id'));
            }
            $query = Tags::create([
                'date' => new \DateTime(),
                'user_id' => App::user()->id,
                'slug' =>  Kernel::secretString(),
                'status' => Tags::STATUS_PUBLISHED   
            ]);
        }

        return [
            '$view' => [
                'title' => $query->id ? __('%title% edit' , ['%title%' => $query->title]) : __('New Tags'),
                'name' => 'news:views/admin/tags-edit.php'
            ],
            '$data' => [
                'tag' => $query,
                'data' => [
                    'statuses' => Tags::getStatuses()
                ]
            ]
        ];
    }
}
