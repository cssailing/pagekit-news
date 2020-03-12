module.exports = [{
    entry: {
        "posts-edit": "./app/views/posts-edit",
        "posts-list": "./app/views/posts-list",
        "categories-list": "./app/views/categories-list",
        "categories-edit": "./app/views/categories-edit",
        "tags-list": "./app/views/tags-list",
        "tags-edit": "./app/views/tags-edit",
        "settings": "./app/views/settings",
        "comment-index": "./app/views/comment-index",
        "front/posts": "./app/views/front/posts",
        "front/post": "./app/views/front/post",
        "front/comments": "./app/views/front/comments",

        "widget/news-hot": "./app/components/widget/news-hot.vue",
        "widget/news-tags": "./app/components/widget/news-tags.vue",
        "widget/news-categories": "./app/components/widget/news-categories.vue",

        'link-news': './app/components/link-news.vue',
        'link-categories': './app/components/link-categories.vue',
        'widget-news': './app/components/widget-news.vue',

        'posts-meta': './app/components/posts-meta.vue',
        'tags-meta': './app/components/tags-meta.vue',
        'categories-meta': './app/components/categories-meta.vue'
    },
    output: {
        filename: "./app/bundle/[name].js",
    },

    module: {
        rules: [{ test: /\.vue$/, use: 'vue-loader' }]
    }
}];