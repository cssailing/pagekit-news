<template>
    <div class="uk-margin">
        <label class="uk-form-label">{{ 'View' | trans }}</label>
        <div class="uk-form-controls">
            <select v-model="url" class="uk-width-1-1 uk-select">
                <option value="@newslist">
                    {{ 'Posts View' | trans }}
                </option>
                <optgroup :label="'Posts' | trans">
                    <option v-for="p in posts" :value="p.id">
                        {{ p.title }}
                    </option>
                </optgroup>
            </select>
        </div>
    </div>
</template>

<script>

var LinkCategories = {

    link: {
        label: 'Categories',
    },

    props: ['link'],

    data() {
        return {
            posts: [],
            url: '',
        };
    },

    created() {
        // TODO: Implement pagination or search
        this.$http.get('api/news/categories', { params: { filter: { limit: 20 } } }).then(function (res) {
            this.posts = res.data.categories;
        });
    },

    mounted() {
        this.url = '@newslist';
    },

    watch: {

        url(option) {
            this.$parent.link = '@newslist/category_id?category_id='+option;
        },

    },



};

export default LinkCategories;

window.Links.default.components['link-categories'] = LinkCategories;

</script>
