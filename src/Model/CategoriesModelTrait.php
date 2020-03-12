<?php 
namespace Pagekit\News\Model;

use Pagekit\Database\ORM\ModelTrait;

trait CategoriesModelTrait{
    use ModelTrait;

    /**
     * @Saving
     */
    public static function saving($event, Categories $categories)
    {
        $i  = 2;
        $a = 2;
        $id = $categories->id;

        $categories->modified = new \DateTime();
        
        while (self::where('title = ?', [$categories->title])->where(function ($query) use ($id) {
            if ($id) {
                $query->where('id <> ?', [$id]);
            }
        })->first()) {
            $categories->title = preg_replace('/-\d+$/', '', $categories->title).'-'.$a++;
        }

        while (self::where('slug = ?', [$categories->slug])->where(function ($query) use ($id) {
            if ($id) {
                $query->where('id <> ?', [$id]);
            }
        })->first()) {
            $categories->slug = preg_replace('/-\d+$/', '', $categories->slug).'-'.$i++;
        }
    }

}
?>