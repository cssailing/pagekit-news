import { ValidationObserver, VInput } from 'SystemApp/components/validation.vue';
import Settings from '../components/posts-settiing.vue';

window.Post = {
    el: '#posteidt',
    name: 'posteidt',
    components: {
        Settings,
        ValidationObserver
    },
    mixins: [Theme.Mixins.Helper],
    data() {
        return {
            data: window.$data.data,
            post: _.merge({
                data: {
                    meta: {
                        'og:title': '',
                        'og:description': '',
                    }
                },
            }, window.$data.post),
            sections: [],
            active: this.$session.get('news.tab.active', 0),
            form: {},
            processing: false,
        };
    },

    //components 以对象方式传递数据到子组建
    provide: {
        '$components': {
            'v-input': VInput
        }
    },

    theme: {
        hiddenHtmlElements: ['#posteidt > div:first-child'],
        elements() {
            var vm = this;
            return {
                'title': {
                    scope: 'breadcrumbs',
                    type: 'caption',
                    caption: () => {
                        let trans = this.$options.filters.trans;
                        return vm.post.id && trans ? trans('Edit Posts') : trans('Add Posts');
                    }
                },
                'savepost': {
                    scope: 'topmenu-left',
                    type: 'button',
                    caption: 'Save',
                    class: 'uk-button tm-button-success',
                    spinner: () => vm.processing,
                    on: {
                        click: () => vm.submit()
                    },
                    priority: 1,
                },
                'close': {
                    scope: 'topmenu-left',
                    type: 'button',
                    caption: vm.post.id ? 'Close' : 'Cancel',
                    class: 'uk-button uk-button-text',
                    attrs: {
                        href: () => vm.$url.route('admin/news/posts')
                    },
                    disabled: () => vm.processing,
                    priority: 0,
                }
            }
        }
    },

    created() {
        const sections = [];

        _.forIn(this.$options.components, (component, name) => {

            if (component.section) {
                sections.push(_.extend({
                    name,
                    priority: 0
                }, component.section));
            }
        });

        this.$set(this, 'sections', _.sortBy(sections, 'priority'));
    },

    mounted() {
        const vm = this;
        this.tab = UIkit.tab('#news-tab', { connect: '#news-content' });
        UIkit.util.on(this.tab.connects, 'show', (e, tab) => {
            if (tab != vm.tab) return;
            for (const index in tab.toggles) {
                if (tab.toggles[index].classList.contains('uk-active')) {
                    vm.$session.set('news.tab.active', index);
                    vm.active = index;
                    break;
                }
            }
        });
        this.tab.show(this.active);
        this.resource = this.$resource('api/news/posts{/id}');
    },

    methods: {

        async submit() {
            const isValid = await this.$refs.observer.validate();
            if (isValid) {
                this.processing = true;
                this.save();
            }
        },

        save() {
            const vm = this;

            // this.$broadcast('save', data);
            this.$trigger('save:post', { posts: this.post, id: this.post.id });
            if (!this.post.title) {
                this.form.title.invalid = true;
                this.$notify('Title need', 'danger');
                return false;
            }
            if (!this.post.excerpt) {
                this.$notify(this.$trans('Excerpt need'), 'warning');
                return false;
            }
            if (!this.post.tags.length) {
                this.$notify(this.$trans('Select at least one tag'), 'warning');
                this.$notify('Tags need', 'danger');
                return false;
            }
            if (!this.post.categories.length) {
                this.$notify(this.$trans('Select at least one category'), 'warning');
                this.$notify('Categories need', 'danger');
                return false;
            }
            if (this.post.categories.length > 1) {
                this.$notify(this.$trans('Category only one'), 'warning');
                return false;
            }
            this.resource.save({ id: this.post.id }, { posts: this.post, id: this.post.id }).then(function(res) {
                if (!this.post.id) {
                    window.history.replaceState({}, '', this.$url.route('admin/news/posts/edit', { id: res.data.query.id }));
                }
                this.post = res.data.query;
                this.$notify('Post saved.');
                setTimeout(() => {
                    vm.processing = false;
                }, 500);
            }, function(res) {
                this.processing = false;
                this.$notify(res.data, 'danger');
            });
        },

    }
}

Vue.ready(window.Post);