<script setup lang="ts">

    import AutoComplete from 'primevue/autocomplete';
    import AppLayout from '@/layouts/AppLayout.vue';
    import { Head, Link, router, usePage } from '@inertiajs/vue3';
    import { ref, computed, onMounted } from 'vue';
    import { Icon } from '@iconify/vue';
    import { useToast } from "primevue/usetoast";

    const props = defineProps({
        courier: Object,
    });

    const toast = useToast();
    const page = usePage()
    const user = page.props.auth.user
    const id = ref(props.courier?.id);
    const name = ref(props.courier?.name);
    const charge = ref(props.courier?.courier_charge);
    const errors = ref({});


    const loading = ref(false)


    const breadcrumbs = ref([
        { title: 'Dashboard', href: '/' },
        { title: 'Courier', href: '/courier' },
        { title: 'Edit', href: '' },
    ]);


    const submit = () => {

        loading.value = true
        const formData = new FormData();
        formData.append('charge', charge.value);

        router.post('/courier/'+id.value+'/edit', formData, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            forceFormData: true,
            onSuccess: () => {
                toast.add({ severity: 'success', summary: 'Success Message', detail: 'Update successfully!', life: 3000 });
                errors.value = {};
                loading.value = false
            },
            onError: (e) => {
                toast.add({ severity: 'error', summary: 'Error Message', detail: 'Oops! Something wrongs', life: 3000 });
                errors.value = e;
                loading.value = false

            },
        });
    };



</script>

<template>

    <Head :title="`Courier Edit ${name}`" />

    <AppLayout :breadcrumbs="breadcrumbs" :loading="loading">
        <Toast position="top-right" />
        <div class="w-full mx-auto p-2 flex flex-row place-content-center">
            <div className="w-5/6 mt-4 shadow-lg bg-white rounded-md pb-1">

                <div class="flex justify-between bg-gray-300 px-2 items-center rounded-t-md p-1 ">
                    <div class="text-lg font-semibold">Update {{ name }}</div>
                    <div class="flex">
                        <Link href="/courier" class="bg-rose-600 p-2 px-3 rounded-md text-lg flex items-center text-white cursor-pointer">  <Icon icon="line-md:arrow-left" class="mr-2" width="1.5rem" height="1.5rem" />
                            Back
                        </Link>
                    </div>
                </div>

                <!-- main content goes here -->
                <form @submit.prevent="submit" class="px-3 h-[calc(100vh-10rem)] justify-items-center mt-2">

                    <div class="w-1/2 shadow-sm rounded-md border-t p-2 px-4">
                        <div class="w-full mb-1">
                            <label :class="{'text-red-500': errors?.charge }" for="dd-city" class="text-md font-semibold w-full content-center"> Name:</label>
                            <input :class="{'border': true, 'border-red-500 text-red-500': errors?.charge }" type="text" v-model="charge" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" placeholder="Category Name"/>
                            <p v-if="errors?.charge" class="text-red-500 text-sm mt-1">{{ errors?.charge }}</p>
                        </div>


                        <div class="w-full mb-1 grid grid-flow-col justify-items-end mt-2">
                            <button class="justify-items-end cursor-pointer bg-orange-500 hover:bg-orange-600 text-md border py-1 px-2 rounded-md font-semibold text-white">Update Charge</button>
                        </div>
                    </div>

                </form>

            </div>

        </div>
    </AppLayout>
</template>
