<template>
  <div class="flex flex-col md:flex-row -mx-6 px-6 break-all lg:break-words">
    <p>
      Resource Tool for <span class="field-name">{{ fieldName }}</span>
    </p>
  </div>
</template>

<script>
export default {
  props: ['index', 'resourceName', 'resourceId', 'resource', 'panel'],

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
