<script setup lang="ts">

    import AppLayout from '@/layouts/AppLayout.vue';
    import { Head, Link, router, usePage } from '@inertiajs/vue3';
    import { ref, computed, onMounted } from 'vue';
    import { Icon } from '@iconify/vue';
    import { useToast } from "primevue/usetoast";

    const page = usePage()
    const user = page.props.auth.user

    const name = ref('');
    const icon = ref('');
    const thumbnail = ref('');
    const banner = ref('');
    const description = ref('');
    const status = ref(1);
    const errors = ref({});
    const toast = useToast();
    const loading = ref(false)


    const breadcrumbs = ref([
        { title: 'Dashboard', href: '/' },
        { title: 'Role', href: '/roles' },
        { title: 'Add New', href: '' },
    ]);





    const submit = () => {

        loading.value = true;

        const formData = new FormData();
        formData.append('name', name.value);
        formData.append('status', status.value);
        formData.append('description', description.value);


        router.post('/role', formData, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            forceFormData: true,
            onSuccess: () => {
                toast.add({ severity: 'success', summary: 'Success Message', detail: 'Role store successfully!', life: 3000 });
                errors.value = {};
                loading.value = false;
            },
            onError: (e) => {
                toast.add({ severity: 'error', summary: 'Error Message', detail: 'Oops! Something wrongs', life: 3000 });
                errors.value = e;
                loading.value = false;

            },
        });
    };



</script>

<template>

    <Head title="Role" />

    <AppLayout :breadcrumbs="breadcrumbs" :loading="loading">
        <Toast position="top-right" />
        <div class="w-full mx-auto p-2 flex flex-row place-content-center">
            <div className="w-5/6 mt-4 shadow-lg bg-white rounded-md pb-1">

                <div class="flex justify-between bg-gray-300 px-2 items-center rounded-t-md p-1 ">
                    <div class="text-lg font-semibold">Add roles</div>
                    <div class="flex">
                        <Link href="/role" class="bg-rose-600 p-2 px-3 rounded-md text-lg flex items-center text-white cursor-pointer">  <Icon icon="line-md:arrow-left" class="mr-2" width="1.5rem" height="1.5rem" />
                            Back
                        </Link>
                    </div>
                </div>

                <!-- main content goes here -->
                <form @submit.prevent="submit" class="px-3 h-[calc(100vh-10rem)] justify-items-center mt-2">

                    <div class="w-1/2 shadow-sm rounded-md border-t p-2 px-4">
                        <div class="w-full mb-1">
                            <label :class="{'text-red-500': errors?.name }" for="dd-city" class="text-md font-semibold w-full content-center"> Name:</label>
                            <input :class="{'border': true, 'border-red-500 text-red-500': errors?.name }" type="text" v-model="name" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" placeholder="Role Name"/>
                            <p v-if="errors?.name" class="text-red-500 text-sm mt-1">{{ errors?.name }}</p>
                        </div>

                        <div class="w-full mb-1">
                            <label for="dd-city" class="text-md font-semibold w-full content-center">Description:</label>
                            <textarea v-model="description" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md"></textarea>
                        </div>


                        <div class="w-full mb-1">
                            <label for="dd-city" :class="{'text-red-500': errors?.status }" class="text-md font-semibold w-full content-center">Status:</label>
                            <select :class="{'border': true, 'border-red-500 text-red-500': errors?.status }" v-model="status" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <p v-if="errors?.status" class="text-red-500 text-sm mt-1">{{ errors?.status }}</p>
                        </div>

                        <div class="w-full mb-1 grid grid-flow-col justify-items-end mt-2">
                            <button class="justify-items-end cursor-pointer bg-sky-500 hover:bg-sky-600 text-md border py-1 px-2 rounded-md font-semibold text-white">Add Role</button>
                        </div>
                    </div>

                </form>

            </div>

        </div>
    </AppLayout>
</template>

