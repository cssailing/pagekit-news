import { ValidationObserver, VInput } from 'SystemApp/components/validation.vue';
import Settings from '../components/categories-settings.vue';
window.Categories = {
    name: 'categoryedit',
    el: '#categoryedit',
    components: {
        Settings,
        ValidationObserver
    },
    provide: {
        '$components': {
            'v-input': VInput
        }
    },
    data() {
        return {
            data: window.$data.data,
            categories: _.merge({
                data: {
                    meta: {
                        'og:title': '',
                        'og:description': '',
                    },
                },
            }, window.$data.category),
            sections: [],
            active: this.$session.get('category.tab.active', 0),
            form: {},
            tags:{},
            processing: false,
        };
    },
    mixins: [Theme.Mixins.Helper],
    theme: {
        hiddenHtmlElements: ['#categoryedit > div:first-child'],
        elements() {
            return {
                'title': {
                    scope: 'breadcrumbs',
                    type: 'caption',
                    caption: () => {
                        let trans = this.$options.filters.trans;
                        return this.categories.id && trans ? trans('Edit Categories') : trans('Add Categories');
                    }
                },
                'savepost': {
                    scope: 'topmenu-left',
                    type: 'button',
                    caption: 'Save',
                    class: 'uk-button tm-button-success',
                    spinner: () => this.processing,
                    on: { click: () => this.submit() },
                    priority: 1,
                },
                'close': {
                    scope: 'topmenu-left',
                    type: 'button',
                    caption: this.categories.id ? 'Close' : 'Cancel',
                    class: 'uk-button uk-button-text',
                    attrs: {
                        href: () => this.$url.route('admin/news/categories')
                    },
                    disabled: () => this.processing,
                    priority: 0,
                }
            }
        }
    },


    created() {
        const sections = [];
        _.forIn(this.$options.components, (component, name) => {
            if (component.section) {
                sections.push(_.extend({ name, priority: 0 }, component.section));
            }
        });
        this.$set(this, 'sections', _.sortBy(sections, 'priority'));
    },

    mounted() {
        const vm = this;
        this.tab = UIkit.tab('#category-tab', { connect: '#category-content' });
        UIkit.util.on(this.tab.connects, 'show', (e, tab) => {
            if (tab != vm.tab) return;
            for (const index in tab.toggles) {
                if (tab.toggles[index].classList.contains('uk-active')) {
                    vm.$session.set('category.tab.active', index);
                    vm.active = index;
                    break;
                }
            }
        });
        this.tab.show(this.active);
        this.resource = this.$resource('api/news/categories{/id}');
    },

    methods: {
        async submit() {
            const isValid = await this.$refs.observer.validate();
            if (isValid) {
                this.processing = true;
                this.save();
            }
        },
        save: function() {
            const vm = this;
            this.resource.save({ id: this.categories.id }, { category: this.categories, id: this.categories.id }).then((res) => {
                if (!vm.categories.id) {
                    window.history.replaceState({}, '', this.$url.route('admin/news/categories', { id: res.data.id }));
                }
                this. category = res.data;
                this.$notify(this.$trans('Saved'));
                setTimeout(() => {
                    vm.processing = false;
                }, 500);
            }, (err) => {
                this.processing = false;
                this.$notify(res.data, 'danger');
            })
        }
    }
}

Vue.ready(window.Categories);