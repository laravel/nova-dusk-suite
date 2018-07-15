Nova.booting((Vue, router) => {
    Vue.component('index-custom-field', require('./components/IndexField'));
    Vue.component('detail-custom-field', require('./components/DetailField'));
    Vue.component('form-custom-field', require('./components/FormField'));
})
