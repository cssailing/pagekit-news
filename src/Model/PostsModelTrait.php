<?php 
namespace Pagekit\News\Model;

use Pagekit\Database\ORM\ModelTrait;

trait PostsModelTrait{
    use ModelTrait;

    /**
     * Updates the comments info on post.
     *
     * @param int $id
     */
    public static function updateCommentInfo($id)
    {
        $query = Comment::where(['post_id' => $id, 'status' => Comment::STATUS_APPROVED]);
        self::where(compact('id'))->update(['comment_count' => $query->count()]);
    }

    /**
     * @Saving
     */
    public static function saving($event, Posts $posts)
    {
        $i  = 2;
        $a = 2;
        $id = $posts->id;

        $posts->modified = new \DateTime();
        
        while (self::where('title = ?', [$posts->title])->where(function ($query) use ($id) {
            if ($id) {
                $query->where('id <> ?', [$id]);
            }
        })->first()) {
            $posts->title = preg_replace('/-\d+$/', '', $posts->title).'-'.$a++;
        }

        while (self::where('slug = ?', [$posts->slug])->where(function ($query) use ($id) {
            if ($id) {
                $query->where('id <> ?', [$id]);
            }
        })->first()) {
            $posts->slug = preg_replace('/-\d+$/', '', $posts->slug).'-'.$i++;
        }
    }

    /**
     * @Deleting
     */
    public static function deleting($event, Posts $post)
    {
        self::getConnection()->delete('@news_comment', ['post_id' => $post->id]);
    }
}
?>