Nova.booting((Vue, router, store) => {
    router.addRoute({
        name: 'sidebar-tool',
        path: '/sidebar-tool',
        component: require('./components/Tool').default,
    })
})
