<template>
  <div>
    <Heading :level="2" class="mb-6">{{ name }} ({{ icons.length }} icons)</Heading>

    <div class="icons-viewer-set-grid">
      <Card
        v-for="icon in icons"
        class="mx-2 p-2 flex items-center justify-center"
        v-tooltip="icon"
      >
        <Icon
          :name="icon"
          :type="iconType"
          class="!w-[48] !h-[48]"
        />
      </Card>
    </div>
  </div>
</template>

<script setup>
import { Icon } from 'laravel-nova-ui'
import { computed } from 'vue'

const props = defineProps({
  name: { type: String, required: true },
  icons: { type: Array, default: [] },
})

const iconType = computed(() => {
  return props.name === 'Solid' ? 'solid' : 'outline'
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
