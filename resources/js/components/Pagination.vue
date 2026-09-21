<!-- components/Pagination.vue -->
<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
  links: Array<{
    url: string | null,
    label: string,
    active: boolean
  }>
}>();
</script>

<template>
  <div class="w-full flex items-center justify-end gap-2 flex-wrap bottom-0 right-0 dark:bg-gray-900">
    <template v-for="(link, i) in links" :key="i">
      <Link
        v-if="link.url"
        :href="link.url"
        class="px-3 py-1 text-sm rounded dark:border-gray-600"
        :class="{
          'border-green-700 border-b-3 text-green-700 font-bold': link.active,
        }"
        v-html="link.label"
        preserve-scroll
      />
      <span
        v-else
        class="px-3 py-1 text-sm border rounded text-gray-400"
        v-html="link.label"
      />
    </template>
  </div>
</template>
