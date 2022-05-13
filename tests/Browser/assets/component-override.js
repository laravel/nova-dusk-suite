Nova.booting(app => {
  app.component('HelpText', {
    template: `
      <p class="help-text cursor-pointer custom-help-component" @click="displayWarning">
        <slot />
      </p>
    `,
    methods: {
      displayWarning() {
        window.alert('HelpText was overriden using component-override.js')
      },
    },
  })
})
