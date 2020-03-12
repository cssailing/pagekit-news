<?php

namespace Pagekit\News\Controller;

use Pagekit\Application as App;
use Pagekit\News\Kernel;
use Pagekit\News\Model\Posts;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/other" , name="other")
 */
class OtherController extends Kernel
{

    /**
     * @Route("/sitemaps" , methods="GET")
     * @Route("/sitemaps/{sitemap}" , name="sitemap", methods="GET")
     * @Request({"page":"int"})
     */
    public function sitemapsAction(int $page = 1 , string $sitemap = null)
    {

        if($sitemap){
            $pattern = '/^sitemap-(.+).xml$/';
            preg_match_all($pattern , $sitemap , $result);
            $page = $result[1][0];
        }

        $query = Posts::where(['status = ?', 'date < ?'], [Posts::STATUS_PUBLISHED, new \DateTime])->where(function ($query) {
            return $query->where('roles IS NULL')->whereInSet('roles', App::user()->roles, false, 'OR');
        });

        $limit = 50;

        $count = $query->count('id');
        $total = ceil($count / $limit);
        $page = max(1, min($total, $page));
        
        $query->offset(($page - 1) * $limit)->limit($limit)->orderBy('date', 'DESC');

        $posts = $query->get();

        $current = App::url()->base(0);
        $response = new Response(App::view('news:views/sitemap.php' , compact(['posts' , 'current' , 'total' , 'sitemap'])));
        $response->headers->set('Content-Type', 'xml');
        return $response;
    }
}
