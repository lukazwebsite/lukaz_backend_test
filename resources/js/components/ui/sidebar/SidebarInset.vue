<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <main
    data-slot="sidebar-inset"
    :class="cn(
      'bg-radial-[at_50%_75%] from-green-700 via-green-500 to-green-200 to-90% relative flex w-full flex-1 flex-col',
      'md:peer-data-[variant=inset]:ml-0  md:peer-data-[variant=inset]:shadow-sm md:peer-data-[variant=inset]:peer-data-[state=collapsed]:ml-0',
      props.class,
    )"
  >
    <slot />
  </main>
</template>
