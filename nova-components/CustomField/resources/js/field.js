Nova.booting((Vue, store) => {
  Vue.component('index-custom-field', require('./components/IndexField').default);
  Vue.component('detail-custom-field', require('./components/DetailField').default);
  Vue.component('form-custom-field', require('./components/FormField').default);
})
