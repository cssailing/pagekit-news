<?php if ($images == 1) { ?>

    <?php foreach ($posts as $post) : ?>
        <li>
            <img src="<?= $post->data['image']['src']; ?>" alt="" uk-cover uk-img="target: !.uk-slideshow-items">
            <div class="uk-overlay uk-overlay-info uk-position-bottom uk-text-center uk-transition-slide-bottom">
                <h3 class="uk-margin-remove uk-visible@s"><a href="<?= $view->url('@newslist/id', ['id' => $post->id]) ?>"><?= $post->title ?></a></h3>

            </div>
        </li>
    <?php endforeach; ?>

<?php } else { ?>

    <?php if ($posts) : ?>
        <ul class="uk-list uk-list-small uk-list-striped">
            <?php foreach ($posts as $post) : ?>
                <li>
                    <a href="<?= $view->url('@newslist/id', ['id' => $post->id]) ?>"><?= $post->title ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <?= __('Posts Not Found') ?>
    <?php endif; ?>
<?php } ?>