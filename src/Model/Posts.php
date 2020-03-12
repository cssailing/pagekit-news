<?php

namespace Pagekit\News\Model;

use Pagekit\Application as App;
use Pagekit\System\Model\DataModelTrait;
use Pagekit\News\Model\PostsModelTrait;
use Pagekit\User\Model\AccessModelTrait;
use Pagekit\User\Model\User;
use Pagekit\News\Kernel;
use Pagekit\News\Model\Categories;
use Pagekit\News\Model\Tags;

/**
 * @Entity(tableClass="@news_posts")
 */
class Posts implements \JSONSerializable
{
    use DataModelTrait, AccessModelTrait, PostsModelTrait;

    const STATUS_TRASH = 0;
    const STATUS_DRAFT = 1;
    const STATUS_PUBLISHED = 2;

    const STYLE_DEFAULT = 0;
    const STYLE_VIDEO = 1;
    const STYLE_CORNER_POST = 2;

    /**
     * @Column(type="integer") @Id
     */
    public $id;

    /**
     * @Column(type="string")
     */
    public $title;

    /**
     * @Column(type="string")
     */
    public $slug;

    /**
     * @Column(type="integer")
     */
    public $user_id;

    /**
     * @Column(type="datetime")
     */
    public $date;

    /**
     * @Column(type="datetime")
     */
    public $modified;

    /**
     * @Column(type="text")
     */
    public $content;

    /**
     * @Column(type="simple_array")
     */
    public $categories;

    /**
     * @Column(type="simple_array")
     */
    public $tags;

    /**
     * @Column(type="integer")
     */
    public $style;

    /**
     * @Column(type="integer")
     */
    public $hit;

    /**
     * @Column(type="integer")
     */
    public $status;

    /**
     * @Column(type="text")
     */
    public $excerpt;

    /** 
     * @Column(type="boolean")
     */
    public $comment_status;

    /** 
     * @Column(type="integer") 
     */
    public $comment_count = 0;

    /**
     * @Column(type="integer")
     */
    public $trend = 0;

    /**
     * @HasMany(targetEntity="Comment", keyFrom="id", keyTo="post_id")
     * @OrderBy({"created" = "DESC"})
     */
    public $comments;

    /**
     * @BelongsTo(targetEntity="Pagekit\User\Model\User", keyFrom="user_id")
     */
    public $user;

    protected static $properties = [
        'author' => 'getAuthor',
        'published' => 'isPublished',
        'getcategories' => 'getCategories',
        'gettags' => 'getTags',
        'accessible' => 'isAccessible',
        'authorsInformation' => 'getFullAuthors',
        'isCommentable' => 'isCommentable'
    ];

    public static function getStatuses()
    {
        return [
            self::STATUS_TRASH => __('Trash'),
            self::STATUS_DRAFT => __('Draft'),
            self::STATUS_PUBLISHED => __('Published')
        ];
    }

    public static function getStyles()
    {
        return [
            self::STYLE_DEFAULT => __('Default'),
            self::STYLE_VIDEO => __('Video'),
            self::STYLE_CORNER_POST => __('Corner Post')
        ];
    }

    public function getAuthor()
    {
        return $this->user ? $this->user->username : null;
    }

    public function getFullAuthors()
    {
        return $this->user ? [
            'id' => $this->user->id,
            'name' => $this->user->name,
            'email' => $this->user->email
        ] : [];
    }

    public function isAccessible(User $user = null)
    {
        return $this->isPublished() && $this->hasAccess($user ?: App::user());
    }

    public function isPublished()
    {
        return $this->status === self::STATUS_PUBLISHED && $this->date < new \DateTime;
    }

    public function getCategories()
    {
        if ($this->categories) {
            $query = Categories::where('id IN (' . implode(',', $this->categories) . ')')->get();
            return $query;
        }
        return null;
    }

    public function getTags()
    {
        if ($this->tags) {
            $query = Tags::where('id IN (' . implode(',', $this->tags) . ')')->get();
            return $query;
        }
        return null;
    }

    public function isCommentable()
    {
        $blog      = App::module('news');
        $autoclose = $blog->config('comments.autoclose') ? $blog->config('comments.autoclose_days') : 0;
        return $this->comment_status && (!$autoclose or $this->date >= new \DateTime("-{$autoclose} day"));
    }

    public function getPage()
    {
        $page = '';
        switch ($this->style) {
            case self::STYLE_CORNER_POST:
                $page = 'news/post-corner.php';
                break;

            case self::STYLE_VIDEO;
                $page = 'news/post-video.php';
                break;

            default:
                $page = 'news/post-default.php';
        }
        return $page;
    }

    public function getUrl()
    {
        return App::url('@newslist/id', ['id' => $this->id ?: 0], 'base');
    }

    public function getImage()
    {
        if ($this->get('style.image.src')) {
            $webp = Kernel::webpConvert($this->get('style.image.src'));
            return $webp;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $data = [
            'url' => self::getUrl()
        ];
        if ($this->comments) {
            $data['comments_pending'] = count(array_filter($this->comments, function ($comment) {
                return $comment->status == Comment::STATUS_PENDING;
            }));
        }
        return $this->toArray($data);
    }
}
