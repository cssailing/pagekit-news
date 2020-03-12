<?php if ($tags): ?>
    <ul class="uk-grid uk-grid-small uk-padding-small">
        <?php foreach ($tags as $tag): ?>
            <li>
                <a href="<?= $view->url('@newslist/tag_id', ['tag_id' => $tag->id]) ?>"><?= $tag->title ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <?= __('Tags Not Found') ?>
<?php endif; ?>