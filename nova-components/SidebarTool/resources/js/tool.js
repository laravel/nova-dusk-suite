Nova.booting((Vue, router, store) => {
    router.addRoutes([
        {
            name: 'sidebar-tool',
            path: '/sidebar-tool',
            component: require('./components/Tool').default,
        },
    ])
})
