<?php 
namespace Pagekit\News;

use Pagekit\Application as App;
use Pagekit\News\Model\Posts;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * update for cookies use
 * delete composer require of josantonius/cookie
 * update composer
 * update the hit instead trend,use from commentapi,the trend is not working well //by sailing
 */
class Trend
{

    public function __construct( int $id = 0 ){
        if (!$post = Posts::where(['id = ?', 'status = ?', 'date < ?'], [$id, Posts::STATUS_PUBLISHED, new \DateTime])->first()) {
            return;
        }

        $cookieName = 'newsCookieTrend'.$id;
        $response = new Response();
        
        if( !is_array($response->headers->get($cookieName)) ){
            return;
        }
        $response->headers->setCookie(Cookie::create($cookieName , 'readed' , 1));
        $post->trend = $post->trend + 1;
        $post->save();
        return;
    }

}
?>