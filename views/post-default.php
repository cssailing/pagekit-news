<?php $view->script('post', 'news:app/bundle/front/post.js', 'vue') ?>
<?php $view->style('news-theme', 'news:dist/css/news.min.css') ?>
<article class="uk-article">

    <?php if ($image = $post->get('style.image.src')) : ?>
        <img src="<?= $post->getImage() ?>" alt="<?= $post->get('style.image.alt') ?>">
    <?php endif ?>

    <h1 class="uk-article-title"><?= $post->title ?></h1>

    <div class="uk-article-meta">
        <?= __('Written by %name% on %date%', ['%name%' => $this->escape($post->user->name), '%date%' => '<time datetime="' . $post->date->format(\DateTime::ATOM) . '" v-cloak>{{ "' . $post->date->format(\DateTime::ATOM) . '" | date("longDate") }}</time>']) ?>
    </div>
    <div class="uk-article-meta">
        <?= __('Categories') ?>:
        <?php foreach ($post->getCategories() as $category) : ?>
            <a href="<?= $view->url('@newslist/category_id', ['category_id' => $category->id]) ?>" class="tm-link"><?= $category->title ?></a>
        <?php endforeach ?>
    </div>

    <div class="uk-margin"><?= $post->content ?></div>

    <p class="uk-article-meta">
        <?php if ($post->getTags()) : ?>
            <?= __('Tags') ?>:
            <?php foreach ($post->getTags() as $tags) : ?>
                <a href="<?= $view->url('@newslist/tag_id', ['tag_id' => $tags->id]) ?>" class="tm-link"><?= $tags->title ?></a>
            <?php endforeach ?>
        <?php endif ?>
    </p>

    <?= $view->render('news/comments.php') ?>

</article>