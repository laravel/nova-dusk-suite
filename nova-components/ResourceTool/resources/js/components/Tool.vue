<template>
    <div>
        Resource Tool for {{ fieldName }}
    </div>
</template>

<script>
    import _ from 'lodash'

    export default {
        props: ['resourceName', 'resourceId', 'resource', 'panel'],

        data() {
            return {
                fieldName: null,
            }
        },

        mounted() {
            this.fieldName = _.find(this.resource.fields, (resource) => {
                return resource.attribute == 'name'
            }).value || 'Unknown'
        }
    }
</script>
