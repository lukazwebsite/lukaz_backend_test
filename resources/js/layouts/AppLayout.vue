<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    loading : false
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    loading: false,
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs" :loaing="loading">
        <div class="bg-black/25 min-h-screen w-full grid place-items-center absolute z-2" v-if="loading">
            <div>
                <img src="/assets/loading.gif" style="width: 60px;"/>
                <h1 class="text-xl">Loding...</h1>
            </div>

        </div>
        <slot />
    </AppLayout>
</template>
