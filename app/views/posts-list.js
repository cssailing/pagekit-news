var PostsList = {
    el: '#postslist',
    name: 'postslist',
    data() {
        return _.merge({
            posts: false,
            config: {
                filter: this.$session.get('posts.filter', { order: 'date desc', limit: 20 })
            },
            pages: 0,
            count: '',
            selected: [],
        }, window.$data)
    },
    mixins: [Theme.Mixins.Helper],
    theme: {
        hiddenHtmlElements: ['#postslist > div:first-child'],
        elements() {
            var vm = this;
            return {
                addpost: {
                    scope: 'topmenu-left',
                    type: 'button',
                    caption: 'Add Posts',
                    attrs: {
                        href: vm.$url.route('admin/news/posts/edit')
                    },
                    class: 'uk-button uk-button-primary',
                    priority: 0,
                },
                'selected': {
                    scope: 'topmenu-right',
                    type: 'caption',
                    caption: () => {
                        if (!vm.selected.length)
                            return vm.$transChoice('{0} %count% News|{1} %count% News|]1,Inf[ %count% News', vm.count, {
                                count: vm.count
                            });
                        return vm.$transChoice('{1} %count% News selected|]1,Inf[ %count% News selected', vm.selected.length, {
                            count: vm.selected.length
                        })
                    },
                    class: 'uk-text-small',
                    priority: 1
                },
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
                'actions': {
                    scope: 'topmenu-left',
                    type: 'dropdown',
                    caption: 'Actions',
                    class: 'uk-button uk-button-text',
                    icon: {
                        attrs: {
                            'uk-icon': 'triangle-down'
                        },
                    },
                    dropdown: {
                        options: () => 'mode:click'
                    },
                    actionIcons: true,
                    items: () => {
                        return {
                            publish: {
                                on: {
                                    click: () => vm.status(2)
                                },
                            },
                            unpublish: {
                                on: {
                                    click: () => vm.status(3)
                                }
                            },
                            copy: {
                                on: {
                                    click: (e) => vm.copy(e)
                                },
                            },
                            delete: {
                                on: {
                                    click: (e) => vm.remove(e)
                                },
                                directives: [{
                                    name: 'confirm',
                                    value: 'Delete Posts?'
                                }]
                            }
                        }
                    },
                    priority: 2,
                    disabled: () => !vm.selected.length,
                },
                pagination: {
                    scope: 'topmenu-right',
                    type: 'pagination',
                    caption: 'Pages',
                    props: {
                        value: () => vm.config.page,
                        pages: () => vm.pages,
                        name: () => vm.$options.name,
                        options: () => ({
                            lblPrev: '<span uk-pagination-previous></span>',
                            lblNext: '<span uk-pagination-next></span>',
                            displayedPages: 3,
                            edges: 1,
                        })
                    },
                    on: {
                        input: (e) => {
                            if (typeof e === 'number') {
                                vm.config.page = e;
                            }
                        }
                    },
                    watch: () => vm.posts,
                    vif: () => (vm.pages > 1 || vm.config.page > 0),
                    priority: 0,
                }
            }
        }
    },

    mounted() {
        this.resource = this.$resource('api/news/posts{/id}');
        this.$watch('config.page', this.load, { immediate: true });
    },

    watch: {
        'config.filter': {
            handler: function(filter) {
                if (this.config.page) {
                    this.config.page = 0;
                } else {
                    this.load();
                }
                this.$session.set('posts.filter', filter);
            },
            deep: true
        }
    },

    computed: {
        statusOptions: function() {
            var options = _.map(this.$data.statuses, function(status, id) {
                return { text: status, value: id };
            });
            return [{ label: this.$trans('Filter by'), options: options }];
        },
        authorsOptions: function() {
            var options = _.map(this.$data.authors, function(author) {
                return { text: author.name, value: author.id };
            });
            return [{ label: this.$trans('Filter by'), options: options }];
        },
        categoriesOptions: function() {
            var options = _.map(this.$data.categories, function(category) {
                return { text: category.title, value: category.id };
            });
            return [{ label: this.$trans('Filter by'), options: options }];
        }
    },
    methods: {
        active: function(post) {
            return this.selected.indexOf(post.id) != -1;
        },
        save: function(post) {
            this.resource.save({ id: post.id }, { posts: post }).then(function() {
                this.load();
                this.$notify('Post saved.');
            });
        },
        status: function(status) {
            var posts = this.getSelected();
            posts.forEach(function(post) {
                post.status = status;
            });
            this.resource.save({ id: 'bulk' }, { posts: posts }).then(function() {
                this.load();
                this.$notify('Posts saved.');
            });
        },
        load: function() {
            this.resource.query({ filter: this.config.filter, page: this.config.page }).then(function(res) {
                const { data } = res;
                this.$set(this, 'posts', data.posts);
                this.$set(this, 'pages', data.pages);
                this.$set(this, 'count', data.count);
                this.$set(this, 'selected', []);
            });
        },
        toggleStatus: function(post) {
            post.status = post.status === 2 ? 0 : 2;
            this.save(post);
        },
        copy: function() {
            if (!this.selected.length) {
                return;
            }
            this.resource.save({ id: 'copy' }, { ids: this.selected }).then(function() {
                this.load();
                this.$notify('Posts copied.');
            });
        },
        remove: function() {
            this.resource.delete({ id: 'bulk' }, { ids: this.selected }).then(function() {
                this.load();
                this.$notify('Posts deleted.');
            });
        },
        getSelected: function() {
            return this.posts.filter(function(post) { return this.selected.indexOf(post.id) !== -1; }, this);
        },
        getStatusText: function(post) {
            return this.statuses[post.status];
        }
    }
}

export default PostsList;

Vue.ready(PostsList);