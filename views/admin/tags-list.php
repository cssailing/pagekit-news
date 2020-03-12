<?php $view->script('news-tags-list' , 'news:app/bundle/tags-list.js' , 'vue') ?>
<?php $view->style('news-theme', 'news:dist/css/news.min.css') ?>

<div id="tagslist" v-cloak>
    <div class="uk-margin uk-flex uk-flex-between uk-flex-wrap">
        <div class="uk-flex uk-flex-middle uk-flex-wrap">
            <h2 class="uk-margin-remove" v-if="!selected.length">{{ count }} {{ count > 1 ? 'Tags':'Tag' | trans }} </h2>
            <template v-else>
                <h2 class="uk-margin-remove">{{ '{1} %count% Tag selected|]1,Inf[ %count% Tags selected' | transChoice(selected.length, {count:selected.length}) }}</h2>
                <div class="uk-margin-left">
                    <ul class="uk-subnav pk-subnav-icon">
                        <li><a class="pk-icon-check pk-icon-hover" title="Publish" data-uk-tooltip="{delay: 500}" @click="status(2)"></a></li>
                        <li><a class="pk-icon-block pk-icon-hover" title="Unpublish" data-uk-tooltip="{delay: 500}" @click="status(0)"></a></li>
                        <li><a class="pk-icon-copy pk-icon-hover" title="Copy" data-uk-tooltip="{delay: 500}" @click="copy"></a></li>
                        <li><a class="pk-icon-delete pk-icon-hover" title="Delete" data-uk-tooltip="{delay: 500}" @click="remove" v-confirm="'Delete tags?'"></a></li>
                    </ul>
                </div>
            </template>
            <div class="uk-search uk-search-default pk-search">
                <span uk-search-icon></span>
                <input class="uk-search-input" type="search" v-model="config.filter.search" debounce="300">
            </div>
        </div>
        <div data-uk-margin>
            <a class="uk-button uk-button-primary" :href="$url.route('admin/news/tags/edit')">{{'Add Tag'| trans }}</a>
        </div>
    </div>

    <div class="uk-overflow-auto">
        <table class="uk-table uk-table-hover uk-table-middle">
            <thead>
                <tr>
                    <th class="pk-table-width-minimum"><input class="uk-checkbox" type="checkbox" v-check-all:selected="{ selector: 'input[name=id]' }"  number></th>
                    <th class="pk-table-min-width-200" v-order:title="config.filter.order">{{ 'Title' | trans }}</th>
                    <th class="pk-table-width-100 uk-text-center">
                        <input-filter :title="$trans('Status')" :value.sync="config.filter.status" :options="statusOptions"></input-filter>
                    </th>
                    <th class="pk-table-width-100">
                        <input-filter :title="$trans('Author')" :value.sync="config.filter.author" :options="authorsOptions"></input-filter>
                    </th>
                    <th class="pk-table-width-100" v-order:date="config.filter.order">{{'Date'| trans }}</th>
                    <th class="pk-table-width-200 pk-table-min-width-200">{{'URL'| trans }}</th>
                </tr>
            </thead>
            <tbody class="uk-text-small">
                <tr class="check-item" v-for="tag in tags" :class="{'uk-active': active(tag)}">
                    <td><input class="uk-checkbox" type="checkbox" name="id" :value="tag.id"></td>
                    <td><a :href="$url.route('admin/news/tags/edit', { id: tag.id })">{{ tag.title }}</a></td>
                    <td class="uk-text-center">
                    <a :title="getStatusText(tag)" :class="{
                            'pk-icon-circle': tag.status == 0,
                            'pk-icon-circle-warning': tag.status == 1,
                            'pk-icon-circle-success': tag.status == 2 && tag.published,
                            'pk-icon-schedule': tag.status == 2 && !tag.published
                        }" @click="toggleStatus(tag)"></a>
                    </td>
                    <td><a :href="$url.route('admin/user/edit', { id: tag.user_id })">{{ tag.author }}</a></td>
                    <td>{{ tag.date | date }}</td>
                    <td class="pk-table-text-break">
                        <a target="_blank" v-if="tag.accessible && tag.url" :href="$url.route(tag.url.substr(1))">{{ decodeURI(tag.url) }}</a>
                        <span v-if="!tag.accessible && tag.url">{{ decodeURI(tag.url) }}</span>
                        <span v-if="!tag.url">{{ 'Disabled' | trans }}</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3 class="uk-h1 uk-text-muted uk-text-center" v-show="tags && !tags.length">{{ 'No tags found.' | trans }}</h3>
    <v-pagination v-model="config.page" :pages="pages" v-show="pages > 1 || config.page > 0"></v-pagination>
</div>