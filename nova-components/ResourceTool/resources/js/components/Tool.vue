<template>
  <div>
    Resource Tool for <span class="field-name">{{ fieldName }}</span>
  </div>
</template>

<script>
import _ from 'lodash'

export default {
  props: ['resourceName', 'resourceId', 'resource', 'panel'],

  data() {
    return {
      fieldName: null,
    }
  },

  mounted() {
    this.fieldName = _.find(this.resource.fields, (resource) => {
      return resource.attribute == 'name'
    }).value || 'Unknown'
  }
}
</script>

<style lang="postcss" scoped>
.field-name {
  @apply text-indigo-900 dark:text-indigo-300 font-semibold;
}
</style>
