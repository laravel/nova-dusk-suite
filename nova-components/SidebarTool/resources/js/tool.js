import SidebarTool from './pages/Tool'
import SidebarToolLogo from './components/Logo'

const Nova = window.Nova 

Nova.booting((app, store) => {
  Nova.inertia('SidebarTool', SidebarTool)
  app.component('SidebarToolLogo', SidebarToolLogo)
})
