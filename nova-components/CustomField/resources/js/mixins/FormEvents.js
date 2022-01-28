import isNil from 'lodash/isNil'
import { mapProps } from './propTypes'

export default {
  props: {
    formUniqueId: {
      type: String,
    },
  },

  methods: {
    emitFieldValue(attribute, value) {
      Nova.$emit(`${attribute}-value`, value)

      if (this.hasFormUniqueId === true) {
        Nova.$emit(`${this.formUniqueId}-${attribute}-value`, value)
      }
    },

    emitFieldValueChange(attribute, value) {
      Nova.$emit(`${attribute}-change`, value)

      if (this.hasFormUniqueId === true) {
        Nova.$emit(`${this.formUniqueId}-${attribute}-change`, value)
      }
    },

    /**
     * Get field attribue value event name.
     */
    getFieldAttributeValueEventName(attribute) {
      return this.hasFormUniqueId === true
        ? `${this.formUniqueId}-${attribute}-value`
        : `${attribute}-value`
    },

    /**
     * Get field attribue value event name.
     */
    getFieldAttributeChangeEventName(attribute) {
      return this.hasFormUniqueId === true
        ? `${this.formUniqueId}-${attribute}-change`
        : `${attribute}-change`
    },
  },

  computed: {
    /**
     * Determine if the field has Form Unique ID.
     */
    hasFormUniqueId() {
      return !isNil(this.formUniqueId) && this.formUniqueId !== ''
    },

    /**
     * Get field attribue value event name.
     */
    fieldAttributeValueEventName() {
      return this.getFieldAttributeValueEventName(this.field.attribute)
    },

    /**
     * Get field attribue value event name.
     */
    fieldAttributeChangeEventName() {
      return this.getFieldAttributeChangeEventName(this.field.attribute)
    },
  },
}
