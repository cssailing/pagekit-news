<template>

    <div class="pk-grid-large pk-width-sidebar-large" uk-grid>
        <div class="pk-width-content uk-form-horizontal">
            
            <div class="uk-margin">
                <label for="form-title" class="uk-form-label">{{ 'Title' | trans }}</label>
                <v-input id="form-title" type="text" name="title" placeholder="Enter Title" view="class: uk-form-width-large uk-input" rules="required" v-model="widget.title" message="Title cannot be blank."/>
            </div>

            <div class="uk-margin">
                <label for="form-field" class="uk-form-label">{{ 'Tags type of Subnav' | trans }}</label>

                <div class="uk-form-controls">
                    <select id="form-field" class="uk-select" v-model="widget.data.field" >
                        <option value="date">Latest</option>
                        <option value="id">ID</option>
                    </select>
                </div>
            </div>

            <div class="uk-margin">
                <label for="form-order" class="uk-form-label">{{ 'Tags list ORDER by' | trans }}</label>

                <div class="uk-form-controls">
                    <select id="form-order" class="uk-select" v-model="widget.data.order" >
                         <option value="asc">ASC</option>
                        <option value="desc">DESC</option>
                    </select>
                </div>
            </div>

            <div class="uk-margin">
                <label for="form-list_limit" class="uk-form-label">{{ 'Numbers of result to frontend' | trans }}</label>
                <div class="uk-form-controls">
                    <p class="uk-form-controls-condensed">
                        <input class="uk-input" type="number" placeholder="10" v-model="widget.data.list_limit">
                    </p>
                </div>
            </div>

            <div class="uk-margin">
                <label for="form-css_enabled" class="uk-form-label">{{ 'Appearance' | trans }}</label>
                <div class="uk-form-controls uk-form-controls-text">
                    <p class="uk-form-controls-condensed">
                        <label><input class="uk-checkbox" type="checkbox" v-model="widget.data.css_enabled"> {{ 'Use self CSS' | trans }}</label>
                    </p>
                </div>
            </div>

        </div>
        <div class="pk-width-sidebar">
            <component :is="'template-settings'" :widget.sync="widget" :config.sync="config" :form="form" />
        </div>
    </div>

</template>

<script>

var WidgetNewsTag=  {
    name: 'widget-news-tags',
    section: {label: 'Settings',},
    replace: false,
    props: ['widget', 'config', 'form'],
    inject: ['$components'],
    created() {
        _.extend(this.$options.components, this.$components);
    },
};

export default WidgetNewsTag;
window.Widgets.components['news-tags-widget.settings'] = WidgetNewsTag;

</script>