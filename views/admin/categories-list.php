<?php $view->script('news-categories-list' , 'news:app/bundle/categories-list.js' , 'vue') ?>
<?php $view->style('news-theme', 'news:dist/css/news.min.css') ?>

<div id="categorieslist" v-cloak>
    <div class="uk-margin uk-flex uk-flex-between uk-flex-wrap">
        <div class="uk-flex uk-flex-middle uk-flex-wrap">
            <h2 class="uk-margin-remove" v-if="!selected.length">{{ count }} {{ count > 1 ? 'Categories':'Category' | trans }} </h2>
            <template v-else>
                <h2 class="uk-margin-remove">{{ '{1} %count% Category selected|]1,Inf[ %count% Categories selected' | transChoice(selected.length, {count:selected.length}) }}</h2>
                <div class="uk-margin-left">
                    <ul class="uk-subnav pk-subnav-icon">
                        <li><a class="pk-icon-check pk-icon-hover" title="Publish" data-uk-tooltip="{delay: 500}" @click="status(2)"></a></li>
                        <li><a class="pk-icon-block pk-icon-hover" title="Unpublish" data-uk-tooltip="{delay: 500}" @click="status(0)"></a></li>
                        <li><a class="pk-icon-copy pk-icon-hover" title="Copy" data-uk-tooltip="{delay: 500}" @click="copy"></a></li>
                        <li><a class="pk-icon-delete pk-icon-hover" title="Delete" data-uk-tooltip="{delay: 500}" @click="remove" v-confirm="'Delete categories?'"></a></li>
                    </ul>
                </div>
            </template>
            <div class="uk-search uk-search-default pk-search">
                <span uk-search-icon></span>
                <input class="uk-search-input" type="search" v-model="config.filter.search" debounce="300">
            </div>
        </div>
        <div uk-margin>
            <a class="uk-button uk-button-primary" :href="$url.route('admin/news/categories/edit')">{{'Add Category'| trans }}</a>
        </div>
    </div>

    <div class="uk-overflow-auto">
        <table class="uk-table uk-table-hover uk-table-middle">
            <thead>
                <tr>
                    <th class="pk-table-width-minimum"><input class="uk-checkbox" type="checkbox" v-check-all:selected="{ selector: 'input[name=id]' }"  number></th>
                    <th class="pk-table-min-width-150" v-order:title="config.filter.order">{{ 'Title' | trans }}</th>
                    <th class="pk-table-width-100 uk-text-center">
                        <input-filter :title="$trans('Status')" :value.sync="config.filter.status" :options="statusOptions"></input-filter>
                    </th>
                    <th class="pk-table-width-100">
                        <input-filter :title="$trans('Author')" :value.sync="config.filter.author" :options="authorsOptions"></input-filter>
                    </th>
                    <th class="pk-table-width-100" v-order:date="config.filter.order">{{'Date'| trans }}</th>
                    <th class="pk-table-width-100 pk-table-min-width-100">{{'Slug'| trans }}</th>
                    <th class="pk-table-width-100 pk-table-min-width-100">{{'URL'| trans }}</th>
                </tr>
            </thead>
            <tbody>
                <tr class="check-item" v-for="category in categories" :class="{'uk-active': active(category)}">
                    <td><input class="uk-checkbox" type="checkbox" name="id" :value="category.id"></td>
                    <td><a :href="$url.route('admin/news/categories/edit', { id: category.id })">{{ category.title }}</a></td>
                    <td class="uk-text-center">
                    <a :title="getStatusText(category)" :class="{
                            'pk-icon-circle': category.status == 0,
                            'pk-icon-circle-warning': category.status == 1,
                            'pk-icon-circle-success': category.status == 2 && category.published,
                            'pk-icon-schedule': category.status == 2 && !category.published
                        }" @click="toggleStatus(category)"></a>
                    </td>
                    <td><a :href="$url.route('admin/user/edit', { id: category.user_id })">{{ category.author }}</a></td>
                    <td>{{ category.date | date }}</td>
                    <td>{{ category.slug }}</td>
                    <td class="pk-table-text-break">
                        <a target="_blank" v-if="category.accessible && category.url" :href="$url.route(category.url.substr(1))">{{ decodeURI(category.url) }}</a>
                        <span v-if="!category.accessible && category.url">{{ decodeURI(category.url) }}</span>
                        <span v-if="!category.url">{{ 'Disabled' | trans }}</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3 class="uk-h1 uk-text-muted uk-text-center" v-show="categories && !categories.length">{{ 'No categories found.' | trans }}</h3>
    <v-pagination v-model="config.page" :pages="pages" v-show="pages > 1 || config.page > 0"></v-pagination>
</div>