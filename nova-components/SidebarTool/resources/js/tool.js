Nova.booting((Vue, store) => {
  Nova.inertia('SidebarTool', require('./pages/Tool').default)
  Vue.component('SidebarToolLogo', require('./components/Logo').default)
})
