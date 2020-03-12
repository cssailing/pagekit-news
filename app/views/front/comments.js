import Comment from './comment.vue';
import Reply from './reply.vue';

var Comments = {

    el: '#comments',

    name: 'comments',

    template: `
        <div id="comments" class="uk-margin-large-top" v-show="config.enabled || comments.length">

            <template v-if="comments.length">

                <h2 class="uk-h4">{{ 'Comments (%count%)' | trans({count:count}) }}</h2>

                <ul class="uk-comment-list">
                    <comment v-for="comment in tree[0]" :key="comment.id" :comment="comment" :config="config" :tree="tree" :comments="comments" :root="root"></comment>
                </ul>

            </template>

            <div class="uk-alert" v-for="message in messages">{{ message }}</div>

            <div ref="reply" v-if="config.enabled"></div>

            <p v-else>{{ 'Comments are closed.' | trans }}</p>

        </div>`,

    data() {
        return _.extend({
            post: {},
            tree: {},
            comments: [],
            messages: [],
            count: 0,
            replyForm: false,
            root: this,
        }, window.$comments);
    },


    beforeCreate() {},

    created() {
        this.load();
    },

    mounted() {

    },

    methods: {

        load() {
            return this.$http.get('api/news/comment{/id}', { params: { post: this.config.post } }).then(function (res) {
                const { data } = res;

                this.comments = data.comments;
                this.tree = _.groupBy(data.comments, 'parent_id');
                this.post = data.posts[0];
                this.count = data.count;

                this.$nextTick(() => {
                    const anchor = window.location.hash;

                    if (anchor) {
                        document.querySelector(anchor).scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                });

                this.reply();
            });
        },

        reply(parent) {
            parent = parent || this;

            if (this.replyForm) {
                this.replyForm.$el.parentNode.removeChild(this.replyForm.$el);
                this.replyForm.$destroy(true);
            }

            const { reply } = this.$options.components;
            const ReplyComponent = Vue.extend({
                parent,
                mixins: [reply],
                props: ['root'],
                data() {
                    return {
                        config: parent.config,
                        parent: parent.comment && parent.comment.id || 0,
                    };
                },
            });
            const instance = new ReplyComponent({
                propsData: {
                    root: this,
                },
            });
            this.replyForm = instance.$mount();
            parent.$refs.reply.appendChild(this.replyForm.$el);
        },

    },

    components: {
        comment: Comment,
        reply: Reply,
    }
};

export default Comments;

Vue.ready(Comments);
