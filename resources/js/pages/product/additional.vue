<script setup lang="ts">

    import AppLayout from '@/layouts/AppLayout.vue';
    import { Head, Link, usePage, router } from '@inertiajs/vue3';
    import { computed, onMounted, ref } from 'vue'
    import { Icon } from '@iconify/vue';
    import Drawer from 'primevue/drawer';
    import AdditionalCard from '@/components/additional/index.vue';
    import { useToast } from "primevue/usetoast";



    const toast = useToast();

    const props = defineProps({
        additionals: Object,
        menuAccess: Array
    });


    const errors = ref({});


    const add = ref(null);
    const edit = ref(null);
    const deleted = ref(null);
    const AccessEdit = ref(false);

    edit.value = props.menuAccess.find(access => access.action_id == 3) ?? null;

    AccessEdit.value = edit?.value.action_id == 3 ? true : false;



    // edit data from database


    const breadcrumbs = ref([
        { title: 'Dashboard', href: '/' },
        { title: 'Product', href: '/products' },
        { title: 'Additional', href: '' },
    ]);

    const loading = ref(false);


    const getUpdate = async (product) => {


        loading.value = true;
        router.post('/product/'+product.id+'/additional', product, {
            headers: {
                Accept: 'application/json',
            },
            forceFormData: true,
            preserveScroll: true,
            only: ['additionals'], // fetch only updated additionals

            onSuccess: () => {
                toast.add({ severity: 'success', summary: 'Success Message', detail: 'Stock Updated successfully!', life: 3000 });
                loading.value = false;
            },
            onError: (e) => {
                errors.value = e;
                toast.add({ severity: 'error', summary: 'Error Message', detail: 'Oops! Something wrongs', life: 3000 });
                loading.value = false;
            },
        });

    }


</script>

<template>

    <Head title="Product Additional" />

    <AppLayout :breadcrumbs="breadcrumbs" :loading="loading">
        <Toast position="top-right" />
        <div class="w-full mx-auto p-2 flex flex-row place-content-center">
            <div className="w-5/6 mt-4 shadow-lg bg-white rounded-md">
                <div class="flex justify-between bg-gray-300 px-2 py-1 items-center rounded-t-md">
                    <div class="text-lg font-semibold">Product Additional</div>
                    <div class="flex">
                        <Link href="/product" class="bg-rose-600 p-2 px-3 rounded-md text-lg flex items-center text-white cursor-pointer">  <Icon icon="line-md:arrow-left" class="mr-2" width="1.5rem" height="1.5rem" />
                            Back
                        </Link>
                    </div>
                </div>

                <!-- main content goes here -->

                <div class="border rounded-b-md px-3 py-1 h-[calc(100vh-10rem)] overflow-auto pb-3">

                    <div class="grid grid-cols-4 gap-4">

                        <AdditionalCard  @update="getUpdate" v-for="(product, index) in additionals" :addionalData="product" :permission="AccessEdit" :key="index" :errors="errors"/>



                    </div>

                </div>

                <!-- main content goes here -->

            </div>

        </div>
    </AppLayout>
</template>
