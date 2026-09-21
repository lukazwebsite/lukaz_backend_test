<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head,  usePage } from '@inertiajs/vue3';


import { ref } from 'vue';

const page = usePage();

const props = defineProps({
    orders: Object,
    check_stocks: Object,
    graphs: Array,
    total_order: Number,
    today_orders: Number,
    dilivered: Number,
    cancel: Number,
    today_offline_sale: Number,
    international: Number,
    branch: Object,
});






const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const loading = ref(false);








</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs" :loading="loading">
        <div class="flex h-full items-center justify-center flex-col rounded-xl p-4 overflow-x-auto bg-white/50 backdrop-blur-md">
            <p class="text-xl font-semibold p-0 m-0">Branch Name: {{ branch?.name }}</p>
            <p class="text-lg p-0 m-0">Branch Short Code: {{ branch?.short_code }}</p>
            <p class="text-lg p-0 m-0">Branch Contact: {{ branch?.contact }}</p>
            <p class="text-lg p-0 m-0">Branch Address: {{ branch?.address }}</p>

        </div>

    </AppLayout>




</template>
