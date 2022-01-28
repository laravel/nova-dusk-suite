import { CancelToken } from 'axios'
import debounce from 'lodash/debounce'
import forIn from 'lodash/forIn'
import get from 'lodash/get'
import identity from 'lodash/identity'
import isNil from 'lodash/isNil'
import pickBy from 'lodash/pickBy'
import FormField from './FormField'
import { mapProps } from './propTypes'

const debouncer = debounce(callback => callback(), 1000)
let watchedEvents = {}

export default {
  extends: FormField,
  props: mapProps([
    'shownViaNewRelationModal',
    'field',
    'viaResource',
    'viaResourceId',
    'viaRelationship',
    'resourceName',
    'resourceId',
    'relatedResourceName',
    'relatedResourceId',
  ]),

  data: () => ({
    canceller: null,
    watchedFields: {},
    syncedField: null,
    pivot: false,
    editMode: 'create',
  }),

  mounted() {
    if (this.relatedResourceName !== '' && !isNil(this.relatedResourceName)) {
      this.pivot = true

      if (this.relatedResourceId !== '' && !isNil(this.relatedResourceId)) {
        this.editMode = 'update-attached'
      } else {
        this.editMode = 'attach'
      }
    } else {
      if (this.resourceId !== '' && !isNil(this.resourceId)) {
        this.editMode = 'update'
      }
    }

    if (this.dependsOn.length > 0) {
      this.dependsOn.forEach(dependsOn => {
        Nova.$on(
          this.getFieldAttributeChangeEventName(dependsOn),
          (watchedEvents[dependsOn] = value => {
            this.watchedFields[dependsOn] = value

            debouncer(() => this.syncField())
          })
        )
      })
    }
  },

  beforeUnmount() {
    if (this.dependsOn.length > 0) {
      forIn(watchedEvents, (event, dependsOn) => {
        Nova.$off(`${dependsOn}-change`, event)
      })
    }
  },

  methods: {
    /*
     * Set the initial value for the field
     */
    setInitialValue() {
      this.value = !(
        this.currentField.value === undefined ||
        this.currentField.value === null
      )
        ? this.currentField.value
        : this.value
    },

    syncField() {
      if (this.canceller !== null) this.canceller()

      Nova.request()
        .patch(this.syncFieldEndpoint, this.watchedFields, {
          params: pickBy(
            {
              editing: true,
              editMode: this.editMode,
              viaResource: this.viaResource,
              viaResourceId: this.viaResourceId,
              viaRelationship: this.viaRelationship,
              field: this.field.attribute,
            },
            identity
          ),
          cancelToken: new CancelToken(canceller => {
            this.canceller = canceller
          }),
        })
        .then(response => {
          this.syncedField = response.data

          if (isNil(this.syncedField.value)) {
            this.syncedField.value = this.field.value
          } else {
            this.setInitialValue()
          }

          this.onSyncedField()
        })
    },

    onSyncedField() {
      //
    },
  },

  computed: {
    /**
     * Determine if the field is in readonly mode
     */
    currentField() {
      return this.syncedField || this.field
    },

    /**
     * Determine if the field is in readonly mode
     */
    currentlyIsReadonly() {
      if (this.syncedField !== null) {
        return Boolean(
          this.syncedField.readonly ||
            get(this.syncedField, 'extraAttributes.readonly')
        )
      }

      return Boolean(
        this.field.readonly || get(this.field, 'extraAttributes.readonly')
      )
    },

    dependsOn() {
      return this.field.dependsOn || []
    },

    syncFieldEndpoint() {
      if (this.editMode === 'update-attached') {
        return `/nova-api/${this.resourceName}/${this.resourceId}/update-pivot-fields/${this.relatedResourceName}/${this.relatedResourceId}`
      } else if (this.editMode == 'attach') {
        return `/nova-api/${this.resourceName}/${this.resourceId}/creation-pivot-fields/${this.relatedResourceName}`
      } else if (this.editMode === 'update') {
        return `/nova-api/${this.resourceName}/${this.resourceId}/update-fields`
      }

      return `/nova-api/${this.resourceName}/creation-fields`
    },
  },
}
