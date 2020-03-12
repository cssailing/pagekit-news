<?php $view->script('tags-edit' , 'news:app/bundle/tags-edit.js' , ['vue']) ?>
<validation-observer tag="form" id="tagsedit" ref="observer" @submit.prevent="submit" v-cloak>
    <div class="uk-margin uk-flex uk-flex-between uk-flex-wrap">
        <div class="uk-flex uk-flex-middle uk-flex-wrap">

            <h2 class="uk-margin-remove" v-if="tags.id">{{ 'Edit Tag' | trans }}</h2>
            <h2 class="uk-margin-remove" v-else>{{ 'Add Tag' | trans }}</h2>

        </div>
        <div uk-margin>

            <a v-if="!processing" class="uk-button uk-margin-small-right" :href="$url.route('admin/news/tags')">{{ tags.id ? 'Close' : 'Cancel' | trans }}</a>
            <button class="uk-button uk-button-primary" type="submit" :disabled="processing">
                <span v-if="processing" uk-spinner ratio=".8" class="uk-margin-small-right"></span>
                <span class="uk-text-middle">{{ 'Save' | trans }}</span>
            </button>

        </div>
    </div>

    <ul id="tags-tab" class="uk-tab" ref="tab" v-show="sections.length > 1">
        <li v-for="section in sections" :key="section.name"><a>{{ section.label | trans }}</a></li>
    </ul>

    <div class="uk-switcher uk-margin" id="tags-content">
        <div v-for="section in sections" :key="section.name">
            <component :is="section.name" :form="form" :tags.sync="tags" :data.sync="data"></component>
        </div>
    </div>
</validation-observer>
