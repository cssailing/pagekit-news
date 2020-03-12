<?php $view->script('news-posts-edit', 'news:app/bundle/posts-edit.js', ['vue', 'editor', 'uikit']) ?>
<?php $view->style('news-theme', 'news:dist/css/news.min.css') ?>

<validation-observer tag="form" id="posteidt" ref="observer" @submit.prevent="submit" v-cloak>

    <div class="uk-margin uk-flex uk-flex-middle uk-flex-between uk-flex-wrap">
        <div>

            <h2 class="uk-margin-remove" v-if="post.id">{{ 'Edit Posts' | trans }}</h2>
            <h2 class="uk-margin-remove" v-else>{{ 'Add Posts' | trans }}</h2>

        </div>
        <div class="uk-margin">

            <a v-if="!processing" class="uk-button uk-button-text uk-margin-right" :href="$url.route('admin/news/posts')">{{ post.id ? 'Close' : 'Cancel' | trans }}</a>
            <button class="uk-button uk-button-primary" type="submit" :disabled="processing">
                <span v-if="processing" uk-spinner ratio=".8" class="uk-margin-small-right"></span>
                <span class="uk-text-middle">{{ 'Save' | trans }}</span>
            </button>

        </div>
    </div>

    <ul ref="tab" v-show="sections.length > 1" id="news-tab">
        <li v-for="section in sections" :key="section.name"><a>{{ section.label | trans }}</a></li>
    </ul>

    <div class="uk-switcher uk-margin" ref="content" id="news-content">
        <div v-for="section in sections" :key="section.name">
            <component :is="section.name" :post.sync="post" :data.sync="data" :form="form"></component>
        </div>
    </div>

</validation-observer>