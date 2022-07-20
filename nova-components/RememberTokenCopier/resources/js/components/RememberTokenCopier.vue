<template>
  <Modal :show="true">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
      <ModalHeader v-text="data.title" />

      <div class="p-4">
        <span v-if="data.token !== null">{{ data.token }}</span>
        <span v-else>&mdash;</span>
      </div>

      <ModalFooter>
        <div class="flex items-center ml-auto">
          <LoadingButton
            component="DefaultButton"
            type="button"
            dusk="close-action-button"
            @click="$emit('close')"
          >
            {{ __('Close') }}
          </LoadingButton>
        </div>
      </ModalFooter>
    </div>
  </Modal>
</template>

<script setup>
const emit = defineEmits(['close'])

const props = defineProps({
  data: { type: Object },
})
</script>
