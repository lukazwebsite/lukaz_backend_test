<script setup lang="ts">
import type { HTMLAttributes } from 'vue';


defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <img
        src="/assets/sites/lukaz_icon-192x192.png"
        alt="Logo"
        class="rounded-full bg-white p-1"
        :class="className"
    />
</template>
