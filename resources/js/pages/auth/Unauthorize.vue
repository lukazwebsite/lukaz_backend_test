<script setup lang="ts">
    import { Head, Link } from '@inertiajs/vue3';
    import { Icon } from '@iconify/vue';
</script>

<template>
    <Head title="Access Denied" />
    <div class="flex h-screen w-full items-center justify-center bg-gray-100 px-4">
        <div class="text-center">
            <h1 class="text-[120px] font-bold text-red-500 leading-none">403</h1>
            <h2 class="text-3xl font-semibold text-gray-800 mb-2">Access Denied</h2>
            <p class="text-gray-600 mb-6">
                You don’t have permission to view this page.<br />
                Please contact your administrator or go back to Dashboard.
            </p>
            <Link href="/dashboard" class="w-64 inline-block rounded-lg bg-green-500 px-6 py-2 text-white hover:bg-green-600 transition">

                <div class="font-semibold text-md flex">
                    <Icon icon="gravity-ui:arrow-left" width="2em" height="2em" class="mr-2 -mt-1"/> Back to Dashboard</div>
            </Link>
        </div>
    </div>
</template>
