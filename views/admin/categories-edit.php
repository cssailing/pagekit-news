<?php $view->script('categories-edit' , 'news:app/bundle/categories-edit.js' , ['vue']) ?>

<validation-observer tag="form" id="categoryedit" ref="observer" @submit.prevent="submit" v-cloak>
    <div class="uk-margin uk-flex uk-flex-middle uk-flex-between uk-flex-wrap">
        <div>
            <h2 class="uk-margin-remove" v-if="categories.id">{{ 'Edit Category' | trans }}</h2>
            <h2 class="uk-margin-remove" v-else>{{ 'Add Category' | trans }}</h2>
        </div>
        <div class="uk-margin">
            <a v-if="!processing" class="uk-button uk-button-text uk-margin-right" :href="$url.route('admin/news/categories')">{{ categories.id ? 'Close' : 'Cancel' | trans }}</a>
            <button class="uk-button uk-button-primary" type="submit" :disabled="processing">
                <span v-if="processing" uk-spinner ratio=".8" class="uk-margin-small-right"></span>
                <span class="uk-text-middle">{{ 'Save' | trans }}</span>
            </button>
        </div>
    </div>

    <ul ref="tab" v-show="sections.length > 1" id="category-tab">
        <li v-for="section in sections" :key="section.name"><a>{{ section.label | trans }}</a></li>
    </ul>

    <div class="uk-switcher uk-margin" ref="content" id="category-content">
        <div v-for="section in sections" :key="section.name">
            <component :is="section.name" :form="form" :categories.sync="categories" :data.sync="data"></component>
        </div>
    </div>

</validation-observer>