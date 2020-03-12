<?php $view->script('news-posts-list', 'news:app/bundle/posts-list.js', ['vue']) ?>
<?php $view->style('news-theme', 'news:dist/css/news.min.css') ?>

<div id="postslist" v-cloak>
    <div class="uk-margin uk-flex uk-flex-between uk-flex-wrap">
        <div class="uk-flex uk-flex-middle uk-flex-wrap">
            <h2 class="uk-margin-remove" v-if="!selected.length">{{ count }} {{ count > 1 ? 'Post':'Post' | trans }} </h2>
            <template v-else>
                <h2 class="uk-margin-remove">{{ '{1} %count% News selected|]1,Inf[ %count% News selected' | transChoice(selected.length, {count:selected.length}) }}</h2>
                <div class="uk-margin-left">
                    <ul class="uk-subnav pk-subnav-icon">
                        <li><a class="pk-icon-check pk-icon-hover" title="Publish" data-uk-tooltip="{delay: 500}" @click="status(2)"></a></li>
                        <li><a class="pk-icon-block pk-icon-hover" title="Unpublish" data-uk-tooltip="{delay: 500}" @click="status(0)"></a></li>
                        <li><a class="pk-icon-copy pk-icon-hover" title="Copy" data-uk-tooltip="{delay: 500}" @click="copy"></a></li>
                        <li><a class="pk-icon-delete pk-icon-hover" title="Delete" data-uk-tooltip="{delay: 500}" @click="remove" v-confirm="'Delete Posts?'"></a></li>
                    </ul>
                </div>
            </template>
            <div class="uk-search uk-search-default pk-search">
                <span uk-search-icon></span>
                <input class="uk-search-input" type="search" v-model="config.filter.search" debounce="300">
            </div>
        </div>
        <div>
            <a class="uk-button uk-button-primary" :href="$url.route('admin/news/posts/edit')">{{'Add Posts'| trans }}</a>
        </div>
    </div>

    <div class="uk-overflow-auto">
        <table class="uk-table uk-table-hover uk-table-middle">
            <thead>
                <tr>
                    <th class="pk-table-width-minimum"><input class="uk-checkbox" type="checkbox" v-check-all:selected="{ selector: 'input[name=id]' }" number></th>
                    <th class="pk-table-min-width-200" v-order:title="config.filter.order">{{ 'Title' | trans }}</th>
                    <th class="pk-table-width-100 uk-text-center">
                        <input-filter :title="$trans('Status')" :value.sync="config.filter.status" :options="statusOptions" v-model="config.filter.status"></input-filter>
                    </th>
                    <th class="pk-table-width-100 uk-text-center">
                        <input-filter :title="$trans('Author')" :value.sync="config.filter.author" :options="authorsOptions" v-model="config.filter.author"></input-filter>
                    </th>
                    <th class="pk-table-width-100 uk-text-center">
                        <input-filter :title="$trans('Category')" :value.sync="config.filter.category" :options="categoriesOptions" v-model="config.filter.category"></input-filter>
                    </th>
                    <th class="pk-table-width-100 uk-text-center" v-order:comment_count="config.filter.order">{{ 'Comments' | trans }}</th>
                    <th class="pk-table-width-100" v-order:date="config.filter.order">{{'Date'| trans }}</th>
                    <th class="pk-table-width-200 pk-table-min-width-200">{{'URL'| trans }}</th>
                </tr>
            </thead>
            <tbody class="uk-text-small">
                <tr class="check-item" v-for="post in posts" :key="post.id" :class="{'uk-active': active(post)}">
                    <td><input class="uk-checkbox" type="checkbox" name="id" :value="post.id"></td>
                    <td><a :href="$url.route('admin/news/posts/edit', { id: post.id })">{{ post.title }}</a></td>
                    <td class="uk-text-center">
                        <a :title="getStatusText(post)" :class="{
                            'pk-icon-circle': post.status == 0,
                            'pk-icon-circle-warning': post.status == 1,
                            'pk-icon-circle-success': post.status == 2 && post.published,
                            'pk-icon-circle-danger': post.status == 3,
                            'pk-icon-schedule': post.status == 2 && !post.published
                        }" @click="toggleStatus(post)"></a>
                    </td>
                    <td><a :href="$url.route('admin/user/edit', { id: post.user_id })">{{ post.author }}</a></td>
                    <td>
                        <span v-for="category in post.getcategories"> <a :href="$url.route('admin/news/categories', { id: category.id })">{{ category.title }}</a>  <br /></span>
                    </td>
                    <td class="uk-text-center">
                        <a class="uk-text-nowrap" :class="{'pk-link-icon': !post.comments_pending}" :href="$url.route('admin/news/comment', { post: post.id })" :uk-tooltip="'{0} No pending|{1} One pending|]1,Inf[ %comments% pending' | transChoice(post.comments_pending, {comments:post.comments_pending})"><i class="pk-icon-comment uk-margin-small-right" :class="{'pk-icon-primary': post.comments_pending}"></i> {{ post.comment_count }}</a>
                    </td>
                    <td>{{ post.date | date }}</td>
                    <td class="pk-table-text-break">
                        <a target="_blank" v-if="post.accessible && post.url" :href="$url.route(post.url.substr(1))">{{ decodeURI(post.url) }}</a>
                        <span v-if="!post.accessible && post.url">{{ decodeURI(post.url) }}</span>
                        <span v-if="!post.url">{{ 'Disabled' | trans }}</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3 class="uk-h2 uk-text-muted uk-text-center" v-show="posts && !posts.length">{{ 'No posts found.' | trans }}</h3>
    <v-pagination v-model="config.page" :pages="pages" v-show="pages > 1 || config.page > 0"></v-pagination>
</div>