import find from 'lodash/find'

export default {
  computed: {
    /**
     * Get the resource information object for the current resource.
     */
    resourceInformation() {
      return find(Nova.config('resources'), resource => {
        return resource.uriKey == this.resourceName
      })
    },

    /**
     * Get the resource information object for the current resource.
     */
    viaResourceInformation() {
      if (!this.viaResource) {
        return
      }

      return find(Nova.config('resources'), resource => {
        return resource.uriKey == this.viaResource
      })
    },

    /**
     * Determine if the user is authorized to create the current resource.
     */
    authorizedToCreate() {
      if (
        ['hasOneThrough', 'hasManyThrough'].indexOf(this.relationshipType) >= 0
      ) {
        return false
      }

      return this.resourceInformation?.authorizedToCreate || false
    },
  },
}
