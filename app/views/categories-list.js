module.exports = {
    el: '#categorieslist',
    name: 'categorieslist',
    data() {
        return _.merge({
            categories: false,
            config: {
                filter: this.$session.get('categories.filter', { order: 'date desc', limit: 25 })
            },
            pages: 0,
            count: '',
            selected: [],
        }, window.$data)
    },
    mixins: [Theme.Mixins.Helper],
    theme: {
        hiddenHtmlElements: ['#categorieslist > div:first-child'],
        elements() {
            var vm = this;
            return {
                addpost: {
                    scope: 'topmenu-left',
                    type: 'button',
                    caption: 'Add Category',
                    attrs: {
                        href: vm.$url.route('admin/news/categories/edit')
                    },
                    class: 'uk-button uk-button-primary',
                    priority: 0,
                },
                'selected': {
                    scope: 'topmenu-right',
                    type: 'caption',
                    caption: () => {
                        if (!vm.selected.length)
                            return vm.$transChoice('{0} %count% Category|{1} %count% Category|]1,Inf[ %count% Category', vm.count, {
                                count: vm.count
                            });
                        return vm.$transChoice('{1} %count% Category selected|]1,Inf[ %count% Categories selected', vm.selected.length, {
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
        this.resource = this.$resource('api/news/categories{/id}');
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
                this.$session.set('categories.filter', filter);
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
                return { text: author.username, value: author.user_id };
            });
            return [{ label: this.$trans('Filter by'), options: options }];
        }
    },

    methods: {
        active: function(category) {
            return this.selected.indexOf(category.id) != -1;
        },
        save: function(category) {
            this.resource.save({ id: category.id }, { category: category }).then(function() {
                this.load();
                this.$notify('Category saved.');
            });
        },
        status: function(status) {
            var categories = this.getSelected();
            categories.forEach(function(categories) {
                categories.status = status;
            });
            this.resource.save({ id: 'bulk' }, { categories: categories }).then(function() {
                this.load();
                this.$notify('Categories saved.');
            });
        },
        load: function() {
            this.resource.query({ filter: this.config.filter, page: this.config.page }).then(function(res) {
                var data = res.data;
                this.$set(this, 'categories', data.categories);
                this.$set(this, 'pages', data.pages);
                this.$set(this, 'count', data.count);
                this.$set(this, 'selected', []);
            });
        },
        toggleStatus: function(category) {
            category.status = category.status === 2 ? 0 : 2;
            this.save(category);
        },
        copy: function() {
            if (!this.selected.length) {
                return;
            }
            this.resource.save({ id: 'copy' }, { ids: this.selected }).then(function() {
                this.load();
                this.$notify('Categories copied.');
            });
        },
        remove: function() {
            this.resource.delete({ id: 'bulk' }, { ids: this.selected }).then(function() {
                this.load();
                this.$notify('Categories deleted.');
            });
        },
        getSelected: function() {
            return this.categories.filter(function(category) { return this.selected.indexOf(category.id) !== -1; }, this);
        },
        getStatusText: function(category) {
            return this.statuses[category.status];
        }
    }
}
Vue.ready(module.exports);