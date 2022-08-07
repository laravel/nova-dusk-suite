<template>
  <span>{{ fieldValue }}</span>
</template>

<script>
import { FieldValue } from 'laravel-nova'

export default {
  mixins: [FieldValue],

  props: ['resourceName', 'field'],
}
</script>
