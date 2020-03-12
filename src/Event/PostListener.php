<?php

namespace Pagekit\News\Event;

use Pagekit\News\Model\Posts;
use Pagekit\Comment\Model\Comment;
use Pagekit\Event\EventSubscriberInterface;

class PostListener implements EventSubscriberInterface
{
    public function onCommentChange($event, Comment $comment)
    {
        Posts::updateCommentInfo($comment->post_id);
    }

    public function onRoleDelete($event, $role)
    {
        Posts::removeRole($role);
    }

    /**
     * {@inheritdoc}
     */
    public function subscribe()
    {
        return [
            'model.comment.saved' => 'onCommentChange',
            'model.comment.deleted' => 'onCommentChange',
            'model.role.deleted' => 'onRoleDelete'
        ];
    }
}