Nova.booting(function (app, store) {
  app.component('resource-tool', require('./components/Tool').default);
  app.component('detail-resource-tool', require('./components/Tool').default);
})
