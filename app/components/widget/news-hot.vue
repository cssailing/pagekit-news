<template>

    <div class="pk-grid-large pk-width-sidebar-large" uk-grid>
        <div class="pk-width-content uk-form-horizontal">
            
            <div class="uk-margin">
                <label for="form-title" class="uk-form-label">{{ 'Title' | trans }}</label>
                <v-input id="form-title" type="text" name="title" placeholder="Enter Title" view="class: uk-form-width-large uk-input" rules="required" v-model="widget.title" message="Title cannot be blank."/>
            </div>

            <div class="uk-margin">
                <label for="form-result_per_page" class="uk-form-label">{{ 'Number of search results' | trans }}</label>
				
                <div class="uk-form-controls">
                    <select id="form-result_per_page" class="uk-select" v-model="widget.data.result_per_page" >
                        <option value="3">3</option>
						<option value="4">4</option>
						<option value="5">5</option>
						<option value="6">6</option>
						<option value="8">8</option>
						<option value="10">10</option>
						<option value="12">12</option>
						<option value="15">15</option>
						<option value="20">20</option>
					</select>
                </div>
            </div>
	
		    <div class="uk-margin">
                <label for="form-triggering_chars" class="uk-form-label">{{ 'Min. input length before triggering autocomplete' | trans }}</label>

                <div class="uk-form-controls">
                    <select id="form-triggering_chars" class="uk-select" v-model="widget.data.triggering_chars" >
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                    </select>
                </div>
            </div>
	
			<div class="uk-margin">
				<label for="form-list_limit" class="uk-form-label">{{ 'ID of tag(only for TAGS)' | trans }}</label>
				<div class="uk-form-controls">
					<p class="uk-form-controls-condensed">
						<input class="uk-input" type="number" placeholder="0" v-model="widget.data.tags_id">
					</p>
				</div>
			</div>

            <div class="uk-margin">
                <label for="form-field" class="uk-form-label">{{ 'News type of Subnav' | trans }}</label>

                <div class="uk-form-controls">
                    <select id="form-field" class="uk-select" v-model="widget.data.field" >
                        <option value="hit">Hits</option>
                        <option value="tags">TAGS</option>
                        <option value="images">Images</option>
                        <option value="comment_count">Comment</option>
                        <option value="date">Latest</option>
                        <option value="id">ID</option>
                    </select>
                </div>
            </div>

            <div class="uk-margin">
                <label for="form-order" class="uk-form-label">{{ 'News list ORDER by' | trans }}</label>

                <div class="uk-form-controls">
                    <select id="form-order" class="uk-select" v-model="widget.data.order" >
                         <option value="asc">ASC</option>
                        <option value="desc">DESC</option>
                    </select>
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

var WidgetNewsHot =  {
    name: 'widget-news-hot',
    section: {label: 'Settings',},
    replace: false,
    props: ['widget', 'config', 'form'],
    inject: ['$components'],
    created() {
        _.extend(this.$options.components, this.$components);
    },
};

export default WidgetNewsHot;
window.Widgets.components['news-hot-widget.settings'] = WidgetNewsHot;

</script>