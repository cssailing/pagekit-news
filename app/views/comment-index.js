
import { ValidationObserver, VInput } from '@system/app/components/validation.vue';
var Comments  = {

    name: 'comments',
    el: '#comments',
    data: function() {
        return _.merge({
            posts: [],
            config: {
                filter: this.$session.get('comments.filter', {})
            },
            comments: false,
            pages: 0,
            count: '',
            selected: [],
            user: window.$pagekit.user,
            replyComment: {},
            editComment: {}
        }, window.$data);
    },
    mixins: [Theme.Mixins.Helper],
    theme:{
        hiddenHtmlElements: ['#comments > div:first-child'],
        elements() {
            var vm = this;
            return {
                search: {
                    scope: 'navbar-right',
                    type: 'search',
                    class: 'uk-text-small',
                    domProps: {
                        value: () => vm.config.filter.search || ''
                    },
                    on: {
                        input: function(e) {
                            !vm.config.filter.search && vm.$set(vm.config.filter, 'search', '');
                            vm.config.filter.search = e.target.value
                        }
                    }
                },
                'selected': {
                    scope: 'topmenu-right',
                    type: 'caption',
                    caption: () => {
                        if (!this.selected.length)
                        return vm.$transChoice('{0} %count% Comments|{1} %count% Comment|]1,Inf[ %count% Comments', this.count, {count: this.count});
                        return vm.$transChoice('{1} %count% Comment selected|]1,Inf[ %count% Comments selected', this.selected.length, {count:this.selected.length})
                    },
                    class: 'uk-text-small',
                    priority: 1
                },
                'actions': {
                    scope: 'topmenu-left',
                    type: 'dropdown',
                    caption: 'Actions',
                    class: 'uk-button uk-button-text',
                    icon: {
                        attrs:{ 'uk-icon': 'triangle-down' },
                    },
                    dropdown: { options: () => 'mode:click' },
                    actionIcons: true,
                    items:() => {
                        return {
                            publish: {
                                caption: 'Approve',
                                on: {click: () => vm.status(1)},
                            },
                            unpublish: {
                                caption: 'Unapprove',
                                on: {click: () => vm.status(0)}
                            },
                            spam: {
                                on: {click: () => vm.status(2)},
                            },
                            delete: {
                                on: {click: (e) => vm.remove(e)},
                                directives: [
                                    {
                                        name: 'confirm',
                                        value: 'Delete Comments?'
                                    }
                                ]
                            }
                        }
                    },
                    priority: 2,
                    disabled: () => !vm.selected.length,
                },
            }
        }
    },
    mounted() {

        this.Comments = this.$resource('api/news/comment{/id}');
        this.$watch('config.page', this.load, { immediate: true });

    },

    watch: {

        'config.filter': {
            handler(filter) {
                if (this.config.page) {
                    this.config.page = 0;
                } else {
                    this.load();
                }

                this.$session.set('comments.filter', filter);
            },
            deep: true
        }

    },

    computed: {
        statusOptions: function() {
            const options = _.map(this.$data.statuses, (status, id) => ({ text: status, value: id }));
            return [{ label: this.$trans('Filter by'), options }];
        }

    },

    methods: {
        postUri(post, comment) {
            console.log(post.url);
            console.log(this.$url.route(post.url.substr(1))+'#comment-'+comment.id);
        },

        active: function(comment) {
            return this.selected.indexOf(comment.id) != -1;
        },

        submit: function() {
            this.save(this.editComment.id ? this.editComment : this.replyComment);
        },

        save: function(comment) {
            return this.Comments.save({ id: comment.id }, { comment: comment }).then(function() {
                this.load();
                this.$notify('Comment saved.');
            }, function(res) {
                this.$notify(res.data, 'danger');
            });
        },

        status: function(status) {

            var comments = this.getSelected();

            comments.forEach(function(comment) {
                comment.status = status;
            });

            this.Comments.save({ id: 'bulk' }, { comments: comments }).then(function() {
                this.load();
                this.$notify('Comments saved.');
            });
        },

        remove: function() {
            this.Comments.delete({ id: 'bulk' }, { ids: this.selected }).then(function() {
                this.load();
                this.$notify('Comments deleted.');
            });
        },

        load: function() {

            this.Comments.query({ filter: this.config.filter, post: this.config.post && this.config.post.id || 0, page: this.config.page, limit: this.config.limit }).then(function(res) {
    
                this.posts = res.data.posts;
                this.comments = res.data.comments;
                this.pages = res.data.pages;
                this.count = res.data.count;
                this.selected = [];
            });
        },

        getSelected: function() {
            var vm = this;
            return this.comments.filter(function(comment) {
                return vm.selected.indexOf(comment.id) !== -1;
            });
        },

        getStatusText: function(comment) {
            return this.statuses[comment.status];
        },

        cancel: function() {
            this.replyComment = {};
            this.editComment = {};
        },

        reply: function(comment) {
            this.cancel();
            this.replyComment = { parent_id: comment.id, post_id: comment.post_id, author: this.user.name, email: this.user.email };
        },

        edit: function(comment) {
            this.cancel();
            this.editComment = _.merge({}, comment);
        },

        toggleStatus: function(comment) {
            comment.status = comment.status === 1 ? 0 : 1;
            this.save(comment);
        }

    },

    components: {
        ValidationObserver,
        VInput
    }

};

export default Comments;
Vue.ready(Comments);
