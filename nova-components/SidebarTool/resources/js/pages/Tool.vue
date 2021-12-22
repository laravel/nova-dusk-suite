<template>
  <div id="sidebar-tool">
    <Heading class="mb-6">Sidebar Tool</Heading>

    <Card class="flex flex-col items-center justify-center" style="min-height: 300px">
      <SidebarToolLogo />
      <h1 class="dark:text-white text-4xl font-light mb-6">We're in a black hole.</h1>
      <h1 class="dark:text-white text-4xl font-light mb-6">{{ sidebarResponse }}</h1>
      <p class="dark:text-white text-lg opacity-70">You can edit this tool's component at: <code class="ml-1 border text-sm font-mono text-white bg-black rounded px-2 py-1">/nova-components/SidebarTool/resources/js/components/Tool.vue</code></p>
    </Card>
  </div>
</template>

<script>
import { layout } from '@/mixins'

export default {
  ...layout('AppLayout'),

  metaInfo() {
    return {
      title: 'Sidebar Tool',
    }
  },

  data() {
    return {sidebarResponse: null}
  },

  mounted() {
    Nova.request().get('/nova-vendor/custom-sidebar-tool').then(response => {
      this.sidebarResponse = response.data
    })
  },
}
</script>

<style>
  /* Scopes Styles */
</style>
