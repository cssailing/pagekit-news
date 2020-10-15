<?php

namespace Pagekit\News\Controller;

use Pagekit\Application as App;
use Pagekit\News\Kernel;
use Pagekit\News\Model\Categories;
use Pagekit\News\Model\Posts;
use Pagekit\News\Model\Tags;

class SiteController extends Kernel
{
    /**
     * @var Module
     */
    protected $news;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->news = App::module('news');
    }

    /**
     * @Route("/" , methods="GET")
     * @Route("/category/{category_id}" , name="category_id")
     * @Route("/tags/{tag_id}" , name="tag_id")
     * @Request({"page":"int"})
     * news list page
     */
    public function indexAction( int $page = 1 , int $category_id = 0 , int $tag_id = 0 )
    {
        $query = Posts::where(['status = ?', 'date < ?'], [Posts::STATUS_PUBLISHED, new \DateTime])->where(function ($query) {
            return $query->where('roles IS NULL')->whereInSet('roles', App::user()->roles, false, 'OR');
        })->related('user');

        if (!$limit = $this->news->config('posts.posts_per_page')) {
            $limit = 10;
        }
        // added for category to list
        if($category_id){
            $query->where(function ($query) use ($category_id) {
                return $query->whereInSet('categories', $category_id);
            });
        }
        // added for tags to list
        if($tag_id){
            $query->where(function ($query) use ($tag_id) {
                return $query->whereInSet('tags', $tag_id);
            });
        }

        $count = $query->count('id');
        $total = ceil($count / $limit);
        $page = max(1, min($total, $page));

        $query->offset(($page - 1) * $limit)->limit($limit)->orderBy('date', 'DESC');

        foreach ($posts = $query->get() as $post) {
            $post->excerpt = App::content()->applyPlugins($post->excerpt, ['post' => $post, 'markdown' => $post->get('markdown')]);
            $post->content = App::content()->applyPlugins($post->content, ['post' => $post, 'markdown' => $post->get('markdown'), 'readmore' => true]);
        }

        $title = '';    //for list page title
        // $node = '';     //for list page pagination url, sent error , use directly at posts list pagination
        //2020/2-03-01
        if($category_id){
            //@newslist/category_id?category_id=2
            // $node = (string) '@newslist/category_id';
            $title = Categories::where('id = ?', [$category_id])->execute('id, title')->fetchAll();
            if($title){$title = $title[0]['title'];}else{$title = '';}
        }elseif($tag_id){
            //@newslist/tag_id?tag_id=1
            // $node = (string) '@newslist/tags_id';
            $title = Tags::where('id = ?', [$tag_id])->execute('id, title')->fetchAll();
            if($title){$title = $title[0]['title'];}else{$title = '';}
        }else{
            //@newslist/id?id=2
            // $node = (string) '@newslist';
            $title = __('News');
        }

        return [
            '$view' => [
                'title' =>  $title,
                'name' => 'views/posts.php',
                'link:feed' => [
                    'rel' => 'alternate',
                    'href' => App::url('@news/feed'),
                    'title' => App::module('system/site')->config('title'),
                    'type' => App::feed()->create($this->news->config('feed.type'))->getMIMEType()
                ]
            ],
            'posts' => $posts,
            'total' => $total,
            'page' => $page,
            'page_style' => [
                'category_id' => $category_id,
                'tag_id' => $tag_id,
                'search_list' => false
            ]
        ];
    }

     /**
     * @Route("/{id}", name="id")
     * @Captcha(route="@news/api/comment/save")
     * @Captcha(route="@news/api/comment/save_1")
     * news content page
     */
    public function postAction($id = 0)
    {
        if (!$post = Posts::where(['id = ?', 'status = ?', 'date < ?'], [$id, Posts::STATUS_PUBLISHED, new \DateTime])->related('user')->first()) {
            App::abort(404, __('Post not found!'));
        }
        if (!$post->hasAccess(App::user())) {
            App::abort(403, __('Insufficient User Rights.'));
        }
        $post->excerpt = App::content()->applyPlugins($post->excerpt, ['post' => $post, 'markdown' => $post->get('markdown')]);
        $post->content = App::content()->applyPlugins($post->content, ['post' => $post, 'markdown' => $post->get('markdown')]);
        $user = App::user();
        $description = $post->get('meta.og:description');
        if (!$description) {
            $description = strip_tags($post->excerpt ?: $post->content);
            $description = rtrim(mb_substr($description, 0, 150), " \t\n\r\0\x0B.,") . '...';
        }
        return [
            '$view' => [
                'title' => __($post->title),
                'name' => $post->getPage(),
                'og:type' => 'article',
                'article:published_time' => $post->date->format(\DateTime::ATOM),
                'article:modified_time' => $post->modified->format(\DateTime::ATOM),
                'article:author' => $post->user->name,
                'og:title' => $post->get('meta.og:title') ?: $post->title,
                'og:description' => $description,
                'og:image' =>  $post->get('style.image.src') ? App::url()->getStatic($post->get('style.image.src'), [], 0) : false
            ],
            '$comments' => [
                'config' => [
                    'post' => $post->id,
                    'enabled' => $post->isCommentable(),
                    'requireinfo' => $this->news->config('comments.require_email'),
                    'max_depth' => $this->news->config('comments.max_depth'),
                    'user' => [
                        'name' => $user->name,
                        'isAuthenticated' => $user->isAuthenticated(),
                        'canComment' => $user->hasAccess('news: post comments'),
                        'skipApproval' => $user->hasAccess('news: skip comment approval')
                    ]
                ]
            ],
            'blog' => $this->news,
            'post' => $post
        ];
    }

    /**
     * @Route("/search" , name="search")
     * @Request({"q":"string","page":"int"})
     * url /news/search?q=ipsum
     */
    public function searchAction( string $q = '' , int $page = 0 )
    {
        $query = Posts::where(['status = :status', 'date < :date'], ['status' =>Posts::STATUS_PUBLISHED, 'date' => new \DateTime])->where(function ($query) {
            return $query->where('roles IS NULL')->whereInSet('roles', App::user()->roles, false, 'OR');
        })->related('user');

        if (!$limit = $this->news->config('posts.posts_per_page')) {
            $limit = 10;
        }

        if($q){
            $query->where(function ($query) use ($q) {
                $query->orWhere(['title LIKE :q', 'slug LIKE :q' , 'content LIKE :q'], [ 'q' => "%{$q}%"]);
            });
        }

        $count = $query->count('id');
        $total = ceil($count / $limit);
        $page = max(1, min($total, $page));

        $query->offset(($page - 1) * $limit)->limit($limit)->orderBy('date', 'DESC');

        foreach ($posts = $query->get() as $post) {
            $post->excerpt = App::content()->applyPlugins($post->excerpt, ['post' => $post, 'markdown' => $post->get('markdown')]);
            $post->content = App::content()->applyPlugins($post->content, ['post' => $post, 'markdown' => $post->get('markdown'), 'readmore' => true]);
        }

        // $node = (string) '@newslist/search';    //not used at the list page ,use search_list =1 

        return [
            '$view' => [
                'title' => __('News Search'),
                'name' => 'views/posts.php',
                'link:feed' => [
                    'rel' => 'alternate',
                    'href' => App::url('@news/feed'),
                    'title' => App::module('system/site')->config('title'),
                    'type' => App::feed()->create($this->news->config('feed.type'))->getMIMEType()
                ]
            ],
            'posts' => $posts,
            'total' => $total,
            'page' => $page,
            'page_style' => [
                'category_id' => false,
                'tag_id' => false,
                'search_list' => true
            ]
        ];
    }
}
