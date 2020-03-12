<template>
    <div class="uk-grid pk-grid-large">
        <div class="uk-form pk-width-content">
            <div class="uk-margin">
                <div class="uk-form-controls">
                    <input id="form-title" type="text" class="uk-input" :placeholder="'Title' | trans" v-model="tags.title">
                </div>
            </div>
        </div>
        <div class="pk-width-sidebar">
            <div class="uk-margin">
                <label for="form-slug" class="uk-form-label">{{ 'Slug' | trans }}</label>
                <div class="uk-form-controls">
                    <input id="form-slug" class="uk-input" type="text" placeholder="/slug" v-model="tags.slug">
                </div>
            </div>
            <div class="uk-margin">
                <label for="form-status" class="uk-form-label">{{ 'Status' | trans }}</label>
                <div class="uk-form-controls">
                    <select id="form-status" class="uk-select" v-model="tags.status">
                        <option v-for="(status,index) in data.statuses" :key=index :value=index>{{status}}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    name: "settings",
    props: ['tags', 'data', 'form'],

    section: {
        label: 'Tags',
        priority: 100,
    },
    inject: ['$components'],

    created() {
        _.extend(this.$options.components, this.$components);
    }
}
</script>