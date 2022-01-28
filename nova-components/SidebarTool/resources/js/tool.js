Nova.booting((app, store) => {
  Nova.inertia('SidebarTool', require('./pages/Tool').default)
  app.component('SidebarToolLogo', require('./components/Logo').default)
})
