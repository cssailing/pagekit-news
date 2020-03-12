<?= '<?xml version="1.0" encoding="UTF-8"?>'?>
<?php if($sitemap): ?>
    <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
        <?php foreach($posts as $post): ?>
            <url>
                <loc><?= $current.$post->getUrl() ?></loc>
                <lastmod><?= $post->date->format('Y-m-d') ?></lastmod>
                <changefreq>daily</changefreq>
                <priority>1.0</priority>
            </url>
        <?php endforeach ?>
    </urlset>
<?php else: ?>
    <sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
        <?php for($i = 1; $i <= $total; $i++): ?>
            <sitemap>
                <loc><?= $current.$view->url('@news/other/sitemap' , ['sitemap' => "sitemap-{$i}.xml"]) ?></loc>
            </sitemap>
        <?php endfor ?>
    </sitemapindex>
<?php endif ?>