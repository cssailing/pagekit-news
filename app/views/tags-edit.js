import { ValidationObserver, VInput } from 'SystemApp/components/validation.vue';
import Settings from '../components/tags-settings.vue';
window.Tags = {
    name: 'tagsEdit',
    el: '#tagsedit',
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
            tags: _.merge({
                data: {
                    meta: {
                        'og:title': '',
                        'og:description': '',
                    },
                },
            }, window.$data.tag),
            sections: [],
            active: this.$session.get('tag.tab.active', 0),
            form: {},
            processing: false,
        };
    },
    mixins: [Theme.Mixins.Helper],
    theme: {
        hiddenHtmlElements: ['#tagsedit > div:first-child'],
        elements() {
            return {
                'title': {
                    scope: 'breadcrumbs',
                    type: 'caption',
                    caption: () => {
                        let trans = this.$options.filters.trans;
                        return this.tags.id && trans ? trans('Edit Tag') : trans('Add Tag');
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
                    caption: this.tags.id ? 'Close' : 'Cancel',
                    class: 'uk-button uk-button-text',
                    attrs: {
                        href: () => this.$url.route('admin/news/tags')
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
        this.tab = UIkit.tab('#tags-tab', { connect: '#tags-content' });
        UIkit.util.on(this.tab.connects, 'show', (e, tab) => {
            if (tab != vm.tab) return;
            for (const index in tab.toggles) {
                if (tab.toggles[index].classList.contains('uk-active')) {
                    vm.$session.set('tag.tab.active', index);
                    vm.active = index;
                    break;
                }
            }
        });
        this.tab.show(this.active);
        this.resource = this.$resource('api/news/tags{/id}');
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
            this.resource.save({ id: this.tags.id }, { tags: this.tags, id: this.tags.id }).then((res) => {
                if (!vm.tags.id) {
                    window.history.replaceState({}, '', this.$url.route('admin/news/tags', { id: res.data.id }));
                }
                this. tag = res.data;
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

Vue.ready(window.Tags);