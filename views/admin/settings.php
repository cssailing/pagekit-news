<?php $view->script('news-settings', 'news:app/bundle/settings.js', 'vue') ?>
<div id="settings" class="uk-form-horizontal" v-cloak>

    <div class="pk-grid-large" uk-grid>
        <div class="pk-width-sidebar">

            <div class="uk-panel">

                <ul class="uk-nav uk-nav-default pk-nav-large" uk-switcher="connect: #tab-content">
                    <li><a><i class="pk-icon-large-settings uk-margin-right"></i> {{ 'General' | trans }}</a></li>
                    <li><a><i class="pk-icon-large-comment uk-margin-right"></i> {{ 'Comments' | trans }}</a></li>
                </ul>

            </div>

        </div>
        <div class="pk-width-content">

            <ul id="tab-content" class="uk-switcher uk-margin">
                <li>

                    <div class="uk-margin uk-flex uk-flex-between uk-flex-wrap">
                        <div>
                            <h2 class="uk-h3 uk-margin-remove">{{ 'General' | trans }}</h2>
                        </div>
                        <div>
                            <button class="uk-button uk-button-primary" @click.prevent="save">{{ 'Save' | trans }}</button>
                        </div>
                    </div>

                    <div class="uk-margin">
                        <span class="uk-form-label">{{ 'Permalink' | trans }}</span>
                        <div class="uk-form-controls uk-form-controls-text">
                            <p class="uk-form-controls-condensed">
                                <label>
                                    <input class="uk-radio" type="radio" v-model="config.permalink.type" value="">
                                    {{ 'Numeric' | trans }} <code>{{ '/123' | trans }}</code>
                                </label>
                            </p>
                            <p class="uk-form-controls-condensed">
                                <label>
                                    <input class="uk-radio" type="radio" v-model="config.permalink.type" value="{slug}">
                                    {{ 'Name' | trans }} <code>{{ '/sample-post' | trans }}</code>
                                </label>
                            </p>
                            <p class="uk-form-controls-condensed">
                                <label>
                                    <input class="uk-radio" type="radio" v-model="config.permalink.type" value="{year}/{month}/{day}/{slug}">
                                    {{ 'Day and name' | trans }} <code>{{ '/2014/06/12/sample-post' | trans }}</code>
                                </label>
                            </p>
                            <p class="uk-form-controls-condensed">
                                <label>
                                    <input class="uk-radio" type="radio" v-model="config.permalink.type" value="{year}/{month}/{slug}">
                                    {{ 'Month and name' | trans }} <code>{{ '/2014/06/sample-post' | trans }}</code>
                                </label>
                            </p>
                            <p class="uk-form-controls-condensed">
                                <label>
                                    <input class="uk-radio" type="radio" v-model="config.permalink.type" value="custom">
                                    {{ 'Custom' | trans }}
                                </label>
                                <input class="uk-input" class="uk-form-small" type="text" v-model="config.permalink.custom">
                            </p>
                        </div>
                    </div>

                    <div class="uk-margin">
                        <label class="uk-form-label">{{ 'Posts per page' | trans }}</label>
                        <div class="uk-form-controls uk-form-controls-text">
                            <p class="uk-form-controls-condensed">
                                <input class="uk-input" type="number" v-model="config.posts.posts_per_page" class="uk-form-width-small">
                            </p>
                        </div>
                    </div>

                    <div class="uk-margin">
                        <span class="uk-form-label">{{ 'Default post settings' | trans }}</span>
                        <div class="uk-form-controls uk-form-controls-text">
                            <p class="uk-form-controls-condensed">
                                <label><input class="uk-checkbox" type="checkbox" v-model="config.posts.markdown_enabled"> {{ 'Enable Markdown' | trans }}</label>
                            </p>
                            <p class="uk-form-controls-condensed">
                                <label><input class="uk-checkbox" type="checkbox" v-model="config.posts.comments_enabled"> {{ 'Enable Comments' | trans }}</label>
                            </p>
                        </div>
                    </div>

                    <div class="uk-margin">
                        <span class="uk-form-label">
                            {{ 'Sitemap XML' | trans }}
                            <a target="_blank" :href="$url('news/other/sitemaps')">
                                <i class="uk-icon-external-link"></i>
                            </a>
                        </span>
                        <pre><code>{{$url('news/other/sitemaps')}}</code></pre>
                    </div>

                    <div class="uk-margin">
                        <span class="uk-form-label">{{ 'Auto Webp Convert' | trans }}</span>
                        <div class="uk-form-controls uk-form-controls-text">
                            <p class="uk-form-controls-condensed">
                                <label><input class="uk-checkbox" type="checkbox" v-model="config.image.autoConvertWebp"> {{ 'Enable Webp' | trans }}</label>
                            </p>
                        </div>
                    </div>

                </li>
                <li>

                    <div class="uk-margin uk-flex uk-flex-between uk-flex-wrap">
                        <div>
                            <h2 class="uk-h3 uk-margin-remove">{{ 'Comments' | trans }}</h2>
                        </div>
                        <div>
                            <button class="uk-button uk-button-primary" @click.prevent="save">{{ 'Save' | trans }}</button>
                        </div>
                    </div>

                    <div class="uk-form-row">
                        <span class="uk-form-label">{{ 'Comments' | trans }}</span>
                        <div class="uk-form-controls uk-form-controls-text">
                            <p class="uk-form-controls-condensed">
                                <label><input class="uk-checkbox" type="checkbox" v-model="config.comments.require_email"> {{ 'Require e-mail.' | trans }}</label>
                            </p>
                            <p class="uk-form-controls-condensed">
                                <input class="uk-checkbox" type="checkbox" v-model="config.comments.autoclose"> {{ 'Close comments on articles older than' | trans }}
                                <input class="uk-input uk-input-width-mini" type="number" v-model="config.comments.autoclose_days" min="1"> {{ 'days.' | trans }}
                            </p>
                        </div>
                    </div>

                    <div class="uk-form-row">
                        <span class="uk-form-label">{{ 'Appearance' | trans }}</span>
                        <div class="uk-form-controls uk-form-controls-text">
                            <p class="uk-form-controls-condensed">
                                <label><input class="uk-checkbox" type="checkbox" v-model="config.comments.gravatar"> {{ 'Show Avatars from Gravatar.' | trans }}</label>
                            </p>
                            <p class="uk-form-controls-condensed">
                                <label>{{ 'Order comments by' | trans }}
                                    <select class="uk-select" v-model="config.comments.order">
                                        <option value="ASC">{{ 'latest last' | trans }}</option>
                                        <option value="DESC">{{ 'latest first' | trans }}</option>
                                    </select>
                                </label>
                            </p>
                            <p class="uk-form-controls-condensed">
                                <input class="uk-checkbox" type="checkbox" v-model="config.comments.nested"> {{ 'Enable nested comments of' | trans }}
                                <input class="uk-input uk-input-width-mini" type="number" v-model="config.comments.max_depth" min="2" max="10"> {{ 'levels deep.' | trans }}
                            </p>
                        </div>
                    </div>

                </li>
            </ul>

        </div>
    </div>

</div>