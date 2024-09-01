import IconsViewer from './pages/Tool'

const Nova = window.Nova

Nova.booting((app, store) => {
  Nova.inertia('IconsViewer', IconsViewer)
})
