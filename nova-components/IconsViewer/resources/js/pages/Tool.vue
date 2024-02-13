<template>
  <div>
    <Head title="Icons Viewer" />
    <Heading class="mb-6">Heroicons</Heading>

    <div class="icons-viewer-type-grid">
      <IconsCollectionCard
        name="Outline"
        :icons="props.icons.outline"
      />

      <IconsCollectionCard
        name="Solid"
        :icons="props.icons.solid"
      />
    </div>
  </div>
</template>

<script setup>
import IconsCollectionCard from '@/components/IconsCollectionCard'

const props = defineProps({
  icons: { type: Object, default: {solid: [], outline: []} },
})
</script>

<style lang="postcss" scoped>
.icons-viewer-type-grid {
  @apply grid grid-cols-2 gap-12;
}
</style>
