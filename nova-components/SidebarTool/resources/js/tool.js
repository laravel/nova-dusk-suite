Nova.booting((Vue, router) => {
    router.addRoutes([
        {
            name: 'sidebar-tool',
            path: '/sidebar-tool',
            component: require('./components/Tool'),
        },
    ])
})
