<template>
  <div>
    <Head title="Icons Viewer" />
      <Heading class="mb-6">Heroicons</Heading>

    <div class="icons-viewer-type-grid">
      <div>
        <Heading :level="2" class="mb-6">Outline ({{ props.icons.outline.length }} icons)</Heading>

        <div class="icons-viewer-set-grid">
          <Card
            v-for="icon in props.icons.outline"
            class="mx-2 p-2 flex items-center justify-center"
            v-tooltip="`${icon}`"
          >
            <component
              :is="`heroicons-outline-${icon}`"
              height="48"
              width="48"
            />
          </Card>
        </div>
      </div>

      <div>
        <Heading :level="2" class="mb-6">Solid ({{ props.icons.solid.length }} icons)</Heading>

        <div class="icons-viewer-set-grid">
          <Card
            v-for="icon in props.icons.solid"
            class="mx-2 p-2 flex items-center justify-center"
            v-tooltip="`${icon}`"
          >
            <component
              :is="`heroicons-solid-${icon}`"
              height="48"
              width="48"
            />
          </Card>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>

const props = defineProps({
  icons: { type: Object, default: {solid: [], outline: []} },
})
</script>

<style lang="postcss" scoped>
.icons-viewer-type-grid {
  @apply grid grid-cols-2 gap-12;
}

.icons-viewer-set-grid {
  @apply grid grid-cols-10 gap-x-2 gap-y-8;
}
</style>
