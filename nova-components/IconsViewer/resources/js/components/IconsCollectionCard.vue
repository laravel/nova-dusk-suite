<template>
  <div>
    <Heading :level="2" class="mb-6">{{ props.name }} ({{ props.icons.length }} icons)</Heading>

    <div class="icons-viewer-set-grid">
      <Card
        v-for="icon in props.icons"
        class="mx-2 p-2 flex items-center justify-center"
        v-tooltip="`${icon}`"
      >
        <component
          :is="`heroicons-${icon}`"
          height="48"
          width="48"
        />
      </Card>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  name: { type: String, required: true },
  icons: { type: Array, default: [] },
})
</script>

<script>
export default {
  inheritAttrs: false,
}
</script>

<style lang="postcss" scoped>
.icons-viewer-set-grid {
  @apply grid grid-cols-10 gap-x-2 gap-y-8;
}
</style>
