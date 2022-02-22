import kebabCase from 'lodash/kebabCase'

export default {
  mounted() {
    if (this.$el && this.$el.classList !== undefined) {
      this.$el.classList.add(`nova-${kebabCase(this.$options.name)}`)
    }
  },
}
