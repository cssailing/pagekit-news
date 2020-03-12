<template>
    <div class="pk-grid-large pk-width-sidebar-large uk-form-stacked uk-grid">
        <div class="pk-width-content">
            <div class="uk-margin">
                <label for="form-title" class="uk-form-label">{{ 'Title' | trans }}</label>
                <div class="uk-form-controls">
                    <v-input 
                        id="form-title" 
                        name="title" 
                        type="text" 
                        placeholder="Enter Title" 
                        view="class: uk-width-1-1 uk-input"
                        rules="required" 
                        v-model="post.title" 
                        message="Title cannot be blank." 
                    ></v-input>
                </div>
            </div>
            <div class="uk-margin">
                <label for="form-post-excerpt" class="uk-form-label">{{ 'Excerpt' | trans }}</label>
                <div class="uk-form-controls">
                    <v-editor id="post-excerpt" v-model="post.excerpt" :options="{markdown: post.data.markdown, height: 150}" />
                </div>
            </div>

            <div class="uk-margin">
                <label for="form-tags" class="uk-form-label">{{ 'Tags' | trans }}</label>
                <div class="uk-form-controls">
                    <div v-if="!data.tags && !data.tags.length >0">
                        <p class="uk-margin uk-text-muted uk-text-center">{{'Not Found Tags' | trans}}</p>
                    </div>
                    <div class="uk-margin" v-else>
                        <label v-for="(tag,index) in data.tags" :key="index" class="uk-margin">
                             <input class="uk-checkbox" type="checkbox" :value="tag.id" v-model="post.tags"> {{tag.title}} &nbsp; 
                        </label>
                    </div>
                </div>
            </div>

            <div class="uk-margin">
                <label for="form-post-content" class="uk-form-label">{{ 'Content' | trans }}</label>
                <div class="uk-form-controls">
                    <v-editor id="post-content" v-model="post.content" :options="{markdown: post.data.markdown}" />
                </div>
            </div>
        </div>
        <div class="pk-width-sidebar">
            <div class="uk-panel">
                <div class="uk-margin">
                    <label for="form-image" class="uk-form-label">{{ 'Image' | trans }}</label>
                    <div class="uk-form-controls">
                        <input-image-meta v-model="post.data.image" :image.sync="post.data.image" class="pk-image-max-height" />
                    </div>
                </div>

                <div class="uk-margin">
                    <label for="form-slug" class="uk-form-label">{{ 'Slug' | trans }}</label>
                    <div class="uk-form-controls">
                        <input id="form-slug" v-model="post.slug" class="uk-width-1-1 uk-input" type="text">
                    </div>
                </div>
                <div class="uk-margin">
                    <label for="form-style"  class="uk-form-label">{{'Style' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="form-style" v-model="post.style" class="uk-width-1-1 uk-select">
                            <option v-for="(style,index) in data.styles" :key="index" :value="index">{{ style }}</option>
                        </select>
                    </div>
                </div>

                <div class="uk-margin">
                    <label for="form-category" class="uk-form-label">{{ 'Categories' | trans }}</label>
                    <div v-if="!data.categories && !data.categories.length >0">
                        <p class="uk-form-controls uk-form-controls-text uk-text-muted uk-text-center">{{'Not Found Categories' | trans}}</p>
                    </div>
                    <div class="uk-form-controls uk-form-controls-text" style="max-height:200px;overflow: auto;" v-else>
                        <div v-for="(category,index) in data.categories" :key="index" class="uk-margin-small uk-form-controls">
                            <label><input class="uk-checkbox" type="checkbox" :value="category.id" v-model="post.categories"> {{category.title}}</label>
                        </div>
                    </div>
                </div>

                <div class="uk-margin">
                    <label for="form-status" class="uk-form-label">{{ 'Status' | trans }}</label>
                    <div class="uk-form-controls">
                        <select id="form-status" v-model="post.status" class="uk-width-1-1 uk-select">
                            <option v-for="(status, index) in data.statuses" :key="index" :value="index">{{ status }}</option>
                        </select>
                    </div>
                </div>
                <div v-if="data.canEditAll" class="uk-margin">
                    <label for="form-author" class="uk-form-label">{{ 'Author' | trans }}</label>
                    <div class="uk-form-controls">
                        <select id="form-author" v-model="post.user_id" class="uk-width-1-1 uk-select">
                            <option v-for="author in data.authors" :value="author.id">{{ author.name }}</option>
                        </select>
                    </div>
                </div>
                <div class="uk-margin">
                    <label class="uk-form-label">{{ 'Publish on' | trans }}</label>
                    <div class="uk-form-controls">
                        <input-date v-model="post.date" />
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label">{{ 'Restrict Access' | trans }}</label>
                    <div class="uk-form-controls uk-form-controls-text">
                        <p v-for="role in data.roles" class="uk-margin-small">
                            <label><input v-model="post.roles" class="uk-checkbox" type="checkbox" :value="role.id" number><span class="uk-margin-small-left">{{ role.name }}</span></label>
                        </p>
                    </div>
                </div>
                <div class="uk-margin">
                    <label class="uk-form-label">{{ 'Options' | trans }}</label>
                    <div class="uk-form-controls uk-form-controls-text">
                        <p class="uk-margin-small">
                            <label><input v-model="post.data.markdown" class="uk-checkbox" type="checkbox" value="1"><span class="uk-margin-small-left">{{ 'Enable Markdown' | trans }}</span></label>
                        </p>
                        <p class="uk-margin-small">
                            <label><input v-model="post.comment_status" class="uk-checkbox" type="checkbox" value="1"><span class="uk-margin-small-left">{{ 'Enable Comments' | trans }}</span></label>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

export default {
    name: "settings",
    props: ['post', 'data', 'form'],
    section: {
        label: 'Post',
        priority: 100,
    },

    inject: ['$components'],
    created() {
        _.extend(this.$options.components, this.$components);
    }
};

</script>
