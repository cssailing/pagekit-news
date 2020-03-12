<template>
    <div>
        <div v-if="editing" class="uk-card-header pk-panel-teaser">
            <form class="uk-form-stacked">
                <div class="uk-margin">
                    <label class="uk-form-label">{{ 'News Type' | trans }}</label>
                    <div class="uk-form-controls uk-form-controls-text">
                        <p class="uk-margin-small">
                            <label><input v-model="widget.show" class="uk-radio" type="radio" value="news"><span class="uk-margin-small-left">{{ 'News' | trans }}</span></label>
                        </p>
                        <p class="uk-margin-small">
                            <label><input v-model="widget.show" class="uk-radio" type="radio" value="categories"><span class="uk-margin-small-left">{{ 'Categories' | trans }}</span></label>
                        </p>
                        <p class="uk-margin-small">
                            <label><input v-model="widget.show" class="uk-radio" type="radio" value="comments"><span class="uk-margin-small-left">{{ 'Comments' | trans }}</span></label>
                        </p>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label">{{ 'Total News' | trans }}</label>
                    <div class="uk-form-controls uk-form-controls-text">
                        <p class="uk-margin-small">
                            <label><input v-model="widget.total" class="uk-radio" type="radio" value="1"><span class="uk-margin-small-left">{{ 'Show' | trans }}</span></label>
                        </p>
                        <p class="uk-margin-small">
                            <label><input v-model="widget.total" class="uk-radio" type="radio" value=""><span class="uk-margin-small-left">{{ 'Hide' | trans }}</span></label>
                        </p>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="-label" for="form-news-number">{{ 'Number of News' | trans }}</label>
                    <div class="uk-form-controls">
                        <select id="form-news-number" v-model="widget.count" class="uk-select uk-width-1-1" number>
                            <option value="6">
                                6
                            </option>
                            <option value="12">
                                12
                            </option>
                            <option value="18">
                                18
                            </option>
                            <option value="24">
                                24
                            </option>
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <div class="uk-card-body">
            <div v-if="widget.show == 'news' && widget.total" class="pk-text-large">
                {{ newscount }}
            </div>
            <div v-if="widget.show == 'categories' && widget.total" class="pk-text-large">
                {{ categoriescount }}
            </div>
            <div v-if="widget.show == 'comments' && widget.total" class="pk-text-large">
                {{ tagscount }}
            </div>

            <h3 v-if="widget.show == 'news' && widget.total" class="uk-card-title uk-margin-small-top">
                {{ 'News' | trans }}
            </h3>

            <h3 v-if="widget.show == 'categories' && widget.total" class="uk-card-title uk-margin-small-top">
                {{ 'Categories' | trans }}
            </h3>

            <h3 v-if="widget.show == 'comments' && widget.total" class="uk-card-title uk-margin-small-top">
                {{ 'Comment' | trans }}
            </h3>

            <ul v-if="widget.show == 'news'" data-news class="uk-list uk-list-divider">
                <li v-for="post in news" :key="post.id" class="uk-flex uk-flex-middle">
                    <img v-gravatar="post.title" class="uk-border-circle uk-margin-right" width="40" height="40" :alt="post.title">
                    <div class="uk-flex-1 uk-text-truncate">
                        <a :href="$url.route('admin/news/posts/edit', {id: post.id})" :title="post.title">{{ post.title }}</a>
                        <br><label>{{ post.authorsInformation.name }}</label> <label>{{ post.date | relativeDate }}</label>
                    </div>
                </li>
            </ul>

            <ul v-if="widget.show == 'categories'" data-categories class="uk-list uk-list-divider">
                <li v-for="category in categories" :key="category.id" class="uk-flex uk-flex-middle">
                    <img v-gravatar="category.title" class="uk-border-circle uk-margin-right" width="40" height="40" :alt="category.title">
                    <div class="uk-flex-1 uk-text-truncate">
                        <a :href="$url.route('admin/news/categories/edit', {id: category.id})" :title="category.title">{{ category.title }}</a>
                        <br><label>{{ category.date | relativeDate }}</label>
                    </div>
                </li>
            </ul>

            <ul v-if="widget.show == 'comments'" data-tags class="uk-list uk-list-divider">
                <li v-for="tag in tags" :key="tag.id" class="uk-flex uk-flex-middle">
                    <img v-gravatar="tag.email" class="uk-border-circle uk-margin-right" width="40" height="40" :alt="tag.content">
                    <div class="uk-flex-1 uk-text-truncate">
                        <a :href="$url.route('admin/news/comment/edit', {id: tag.id})" :title="tag.content">{{ tag.content }}</a>
                        <br><label>{{ tag.created | relativeDate }}</label>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
// dashboard link widget
module.exports = {

    type: {

        id: 'news',
        label: 'News',
        description() {

        },
        defaults: {
            show: 'news',
            total: true,
            count: 6,
        },

    },

    replace: false,

    props: ['widget', 'editing'],

    data() {
        return {
            news: [],
            newscount: null,
            categories: [],
            categoriescount: null,
            tags: [],
            tagscount: null,
        };
    },

    watch: {

        'widget.show': {
            handler: 'load',
            immediate: true,
        },

        'widget.count': 'load',

    },

    methods: {

        load() {
            const query = {params: {filter: {limit:this.widget.count, order: 'id DESC' }}};

            this.$http.get('api/news/posts', query).then(function (res) {
                this.news = res.data.posts;
                this.newscount = res.data.count;
                });
            this.$http.get('api/news/categories', query).then(function (res) {
                this.categories = res.data.categories;
                this.categoriescount = res.data.count;
                });
            this.$http.get('api/news/comment', query).then(function (res) {
                this.tags = res.data.comments;
                this.tagscount = res.data.count;
                });
        }
    }
};

window.Dashboard.components.news = module.exports;

</script>