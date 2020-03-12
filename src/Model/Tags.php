<?php

namespace Pagekit\News\Model;

use Pagekit\Application as App;
use Pagekit\System\Model\DataModelTrait;
use Pagekit\News\Model\TagsModelTrait;

/**
 * @Entity(tableClass="@news_tags")
 */
class Tags implements \JSONSerializable
{

    use DataModelTrait, TagsModelTrait;

    const STATUS_TRASH = 0;
    const STATUS_DRAFT = 1;
    const STATUS_PUBLISHED = 2;

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
     * @Column(type="integer")
     */
    public $status;

    /**
     * @BelongsTo(targetEntity="Pagekit\User\Model\User", keyFrom="user_id")
     */
    public $user;

    protected static $properties = [
        'author' => 'getAuthor',
        'published' => 'isPublished',
        'accessible' => 'isAccessible'
    ];

    public static function getStatuses()
    {
        return [
            self::STATUS_TRASH => __('Trash'),
            self::STATUS_DRAFT => __('Draft'),
            self::STATUS_PUBLISHED => __('Published')
        ];
    }

    public function getAuthor()
    {
        return $this->user ? $this->user->username : null;
    }

    public function isAccessible(User $user = null)
    {
        return $this->isPublished();
    }

    public function isPublished()
    {
        return $this->status === self::STATUS_PUBLISHED && $this->date < new \DateTime;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $data = [
            'url' => App::url('@newslist/tag_id', ['tag_id' => $this->id ?: 0], 'base')
        ];
        return $this->toArray($data);
    }
}
