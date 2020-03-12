<template>

    <div class="uk-grid pk-grid-large">

        <div class="uk-form pk-width-content">
            <div class="uk-margin">
                <label for="form-title" class="uk-form-label">{{ 'Title' | trans }}</label>
                <div class="uk-form-controls">
                <input id="form-title" name="title" type="text" placeholder="Enter Title" class="uk-input"  v-model="categories.title" />
                </div>
            </div>
        </div>

        <div class="pk-width-sidebar">
            <div class="uk-margin">
                <label for="form-slug" class="uk-form-label">{{ 'Slug' | trans }}</label>
                <div class="uk-form-controls">
                    <input id="form-slug" class="uk-input uk-width-1-1" type="text" v-model="categories.slug">
                </div>
            </div>

            <div class="uk-margin">
                <label for="form-status" class="uk-form-label">{{ 'Status' | trans }}</label>
                <div class="uk-form-controls">
                    <select id="form-status" class="uk-select" v-model="categories.status">
                        <option v-for="(status, index) in data.statuses" :key=index :value=index>{{status}}</option>
                    </select>
                </div>
            </div>
        </div>

    </div>

</template>

<script>
    export default {
    name: "settings",
    props: ['categories', 'data', 'form'],
    section: {
        label:'Category',
        priority:10
    },
    inject: ['$components'],
    created() {
        _.extend(this.$options.components, this.$components);
    }
};
</script>