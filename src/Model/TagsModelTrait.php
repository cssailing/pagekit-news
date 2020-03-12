<?php 
namespace Pagekit\News\Model;

use Pagekit\Database\ORM\ModelTrait;

trait TagsModelTrait{
    use ModelTrait;

    /**
     * @Saving
     */
    public static function saving($event, Tags $tags)
    {
        $i  = 2;
        $a = 2;
        $id = $tags->id;

        $tags->modified = new \DateTime();
        
        while (self::where('title = ?', [$tags->title])->where(function ($query) use ($id) {
            if ($id) {
                $query->where('id <> ?', [$id]);
            }
        })->first()) {
            $tags->title = preg_replace('/-\d+$/', '', $tags->title).'-'.$a++;
        }

        while (self::where('slug = ?', [$tags->slug])->where(function ($query) use ($id) {
            if ($id) {
                $query->where('id <> ?', [$id]);
            }
        })->first()) {
            $tags->slug = preg_replace('/-\d+$/', '', $tags->slug).'-'.$i++;
        }
    }
}
?>