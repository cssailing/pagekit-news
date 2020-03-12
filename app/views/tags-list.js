module.exports = {
    el: '#tagslist',
    name: 'tagslist',
    data() {
        return _.merge({
            tags: false,
            config: {
                filter: this.$session.get('tags.filter', { order: 'date desc', limit: 25 })
            },
            pages: 0,
            count: '',
            selected: [],
        }, window.$data)
    },
    mixins: [Theme.Mixins.Helper],
    theme: {
        hiddenHtmlElements: ['#tagslist > div:first-child'],
        elements() {
            var vm = this;
            return {
                addpost: {
                    scope: 'topmenu-left',
                    type: 'button',
                    caption: 'Add Tag',
                    attrs: {
                        href: vm.$url.route('admin/news/tags/edit')
                    },
                    class: 'uk-button uk-button-primary',
                    priority: 0,
                },
                'selected': {
                    scope: 'topmenu-right',
                    type: 'caption',
                    caption: () => {
                        if (!vm.selected.length)
                            return vm.$transChoice('{0} %count% Tag|{1} %count% Tag|]1,Inf[ %count% Tag', vm.count, {
                                count: vm.count
                            });
                        return vm.$transChoice('{1} %count% Tag selected|]1,Inf[ %count% Tags selected', vm.selected.length, {
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
        this.resource = this.$resource('api/news/tags{/id}');
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
                this.$session.set('tags.filter', filter);
            },
            deep: true
        }
    },

    computed: {
        statusOptions() {
            var options = _.map(this.$data.statuses, function(status, id) {
                return { text: status, value: id };
            });
            return [{ label: this.$trans('Filter by'), options: options }];
        },
        authorsOptions: function() {
            var options = _.map(this.$data.authors, function(author) {
                return { text: author.username, value: author.user_id };
            });
            return [{ label: this.$trans('Filter by'), options: options }];
        }
    },

    methods: {
        active: function(tag) {
            return this.selected.indexOf(tag.id) != -1;
        },
        save: function(tag) {
            this.resource.save({ id: tag.id }, { tags: tag }).then(function() {
                this.load();
                this.$notify('Tag saved.');
            });
        },
        status: function(status) {
            var tags = this.getSelected();
            tags.forEach(function(tags) {
                tags.status = status;
            });
            this.resource.save({ id: 'bulk' }, { tags: tags }).then(function() {
                this.load();
                this.$notify('Tags saved.');
            });
        },
        load: function() {
            this.resource.query({ filter: this.config.filter, page: this.config.page }).then(function(res) {
                var data = res.data;
                this.$set(this, 'tags', data.tags);
                this.$set(this, 'pages', data.pages);
                this.$set(this, 'count', data.count);
                this.$set(this, 'selected', []);
            });
        },
        toggleStatus: function(tag) {
            tag.status = tag.status === 2 ? 0 : 2;
            this.save(tag);
        },
        copy: function() {
            if (!this.selected.length) {
                return;
            }
            this.resource.save({ id: 'copy' }, { ids: this.selected }).then(function() {
                this.load();
                this.$notify('Tags copied.');
            });
        },
        remove: function() {
            this.resource.delete({ id: 'bulk' }, { ids: this.selected }).then(function() {
                this.load();
                this.$notify('Tags deleted.');
            });
        },
        getSelected: function() {
            return this.tags.filter(function(tag) { return this.selected.indexOf(tag.id) !== -1; }, this);
        },
        getStatusText: function(tag) {
            return this.statuses[tag.status];
        }
    }
}
Vue.ready(module.exports);