<?php $view->script('posts', 'news:app/bundle/front/posts.js', 'vue') ?>
<?php $view->style('news-theme', 'news:dist/css/news.min.css') ?>
<?php foreach ($posts as $post) : ?>
    <article class="uk-article">

        <?php if ($image = $post->get('style.image.src')) : ?>
            <a class="uk-display-block" href="<?= $view->url('@newslist/id', ['id' => $post->id]) ?>">
                <img src="<?= $post->getImage() ?>" alt="<?= $post->get('style.image.alt') ?>">
            </a>
        <?php endif ?>

        <h1 class="uk-article-title"><a href="<?= $view->url('@newslist/id', ['id' => $post->id]) ?>"><?= $post->title ?></a></h1>

        <div class="uk-article-meta">
            <?php foreach ($post->getCategories() as $category) : ?>
                <a href="<?= $view->url('@newslist/category_id', ['category_id' => $category->id]) ?>" class="tm-link"><?= $category->title ?></a>
            <?php endforeach ?>
            <?= __('Written by %name% on %date%', ['%name%' => $this->escape($post->user->name), '%date%' => '<time datetime="' . $post->date->format(\DateTime::ATOM) . '" v-cloak>{{ "' . $post->date->format(\DateTime::ATOM) . '" | date("longDate") }}</time>']) ?>
        </div>

        <div><?= $post->excerpt ?: $post->content ?></div>

        <ul class="uk-subnav">

            <?php if (isset($post->readmore) && $post->readmore || $post->excerpt) : ?>
                <li><a href="<?= $view->url('@newslist/id', ['id' => $post->id]) ?>"><?= __('Read more') ?></a></li>
            <?php endif ?>

            <?php if ($post->isCommentable() || $post->comment_count) : ?>
                <li><a href="<?= $view->url('@newslist/id#comments', ['id' => $post->id]) ?>"><?= _c('{0} No comments|{1} %num% Comment|]1,Inf[ %num% Comments', $post->comment_count, ['%num%' => $post->comment_count]) ?></a></li>
            <?php endif ?>

        </ul>

    </article>
<?php endforeach ?>

<?php if (!$posts) : ?>
    <div class="uk-article">
        <div class="uk-text-center">
            <h2><?= __('Not Found Posts') ?></h2>
        </div>
    </div>
<?php endif ?>

<?php
$range     = 3;
$total     = intval($total);
$page      = intval($page);
$pageIndex = $page - 1;

?>

<?php if ($total > 1) : ?>
    <ul class="uk-flex-center uk-pagination">
        <?php for ($i = 1; $i <= $total; $i++) : ?>
            <?php if ($i <= ($pageIndex + $range) && $i >= ($pageIndex - $range)) : ?>
                <?php if ($i == $page) : ?>
                    <li class="uk-active"><span><?= $i ?></span></li>
                <?php else : ?>
                    <li>
                        <?php if ($page_style['category_id']) : ?>
                            <a href="<?= $view->url('@newslist/category_id', ['category_id' => $page_style['category_id'], 'page' => $i]) ?>"><?= $i ?></a>
                        <?php elseif ($page_style['tag_id']) : ?>
                            <a href="<?= $view->url('@newslist/tag_id', ['tag_id' => $page_style['tag_id'], 'page' => $i]) ?>"><?= $i ?></a>
                        <?php elseif ($page_style['search_list']) : ?>
                            <a href="<?= $view->url('@newslist/search', ['page' => $i]) ?>"><?= $i ?></a>
                        <?php else : ?>
                            <a href="<?= $view->url('@newslist', ['page' => $i]) ?>"><?= $i ?></a>
                        <?php endif ?>
                    </li>
                <?php endif ?>
            <?php elseif ($i == 1) : ?>
                <li>
                    <?php if ($page_style['category_id']) : ?>
                        <a href="<?= $view->url('@newslist/category_id', ['category_id' => $page_style['category_id'], 'page' => 1]) ?>">1</a>
                    <?php elseif ($page_style['tag_id']) : ?>
                        <a href="<?= $view->url('@newslist/tag_id', ['tag_id' => $page_style['tag_id'], 'page' => 1]) ?>">1</a>
                    <?php elseif ($page_style['search_list']) : ?>
                        <a href="<?= $view->url('@newslist/search', ['page' => 1]) ?>">1</a>
                    <?php else : ?>
                        <a href="<?= $view->url('@newslist', ['page' => 1]) ?>">1</a>
                    <?php endif ?>
                </li>
                <li><span>...</span></li>
            <?php elseif ($i == $total) : ?>
                <li><span>...</span></li>
                <li>
                    <?php if ($page_style['category_id']) : ?>
                        <a href="<?= $view->url('@newslist/category_id', ['category_id' => $page_style['category_id'], 'page' => $total]) ?>"><?= $total ?></a>
                    <?php elseif ($page_style['tag_id']) : ?>
                        <a href="<?= $view->url('@newslist/tag_id', ['tag_id' => $page_style['tag_id'], 'page' => $total]) ?>"><?= $total ?></a>
                    <?php elseif ($page_style['search_list']) : ?>
                        <a href="<?= $view->url('@newslist/search', ['page' => $total]) ?>"><?= $total ?></a>
                    <?php else : ?>
                        <a href="<?= $view->url('@newslist', ['page' => $total]) ?>"><?= $total ?></a>
                    <?php endif ?>
                </li>
            <?php endif ?>
        <?php endfor ?>
    </ul>
<?php endif ?>