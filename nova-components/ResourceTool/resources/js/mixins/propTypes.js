import pick from 'lodash/pick'

const propTypes = {
  preventInitialLoading: {
    type: Boolean,
    default: false,
  },

  showHelpText: {
    type: Boolean,
    default: false,
  },

  shownViaNewRelationModal: {
    type: Boolean,
    default: false,
  },

  resourceId: { type: [Number, String] },

  resourceName: { type: String },

  relatedResourceId: { type: [Number, String] },

  relatedResourceName: { type: String },

  field: {
    type: Object,
    required: true,
  },

  viaResource: {
    type: String,
    required: false,
  },

  viaResourceId: {
    type: [String, Number],
    required: false,
  },

  viaRelationship: {
    type: String,
    required: false,
  },

  relationshipType: {
    type: String,
    default: '',
  },

  shouldOverrideMeta: {
    type: Boolean,
    default: false,
  },

  disablePagination: {
    type: Boolean,
    default: false,
  },
}

export function mapProps(attributes) {
  return pick(propTypes, attributes)
}
