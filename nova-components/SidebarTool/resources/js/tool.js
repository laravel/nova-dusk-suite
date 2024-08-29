import SidebarTool from './pages/Tool'
import SidebarToolLogo from './components/Logo'
import { filled } from 'laravel-nova-util'
import { Errors } from 'laravel-nova'

const Nova = window.Nova 

const e = new Errors

Nova.booting((app, store) => {
  Nova.inertia('SidebarTool', SidebarTool)
  app.component('SidebarToolLogo', SidebarToolLogo)
})
