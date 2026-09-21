<script setup lang="ts">

import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { Icon } from '@iconify/vue';
import { useToast } from "primevue/usetoast";

const props = defineProps({
    roles: Object,
    branches: Object,
});

const toast = useToast();

const page = usePage()
const user = page.props.auth.user
const name = ref('');
const password = ref('');
const confirm = ref('');
const email = ref('');
const mobile = ref('');
const roleId = ref(5);
const status = ref(1);
const description = ref();
const errors = ref({});
const loading = ref(false)

const breadcrumbs = ref([
    { title: 'Dashboard', href: '/' },
    { title: 'Customer', href: '/customer' },
    { title: 'Add New', href: '' },
]);


const submit = () => {

    loading.value = true;

    const formData = new FormData();
    formData.append('name', name.value);
    formData.append('email', email.value);
    formData.append('password', password.value);
    formData.append('password_confirmation', confirm.value);
    formData.append('mobile', mobile.value);
    formData.append('role_id', roleId.value);
    formData.append('status', status.value);
    formData.append('description', description.value);


    router.post('/customer', formData, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        forceFormData: true,
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Success Message', detail: 'Customer store successfully!', life: 3000 });
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

    <Head title="Create Customer" />

    <AppLayout :breadcrumbs="breadcrumbs" :loading="loading">
        <Toast position="top-right" />
        <div class="w-full mx-auto p-2 flex flex-row place-content-center">
            <div className="w-5/6 mt-4 shadow-lg bg-white rounded-md pb-1">

                <div class="flex justify-between bg-gray-300 px-2 items-center rounded-t-md p-1 ">
                    <div class="text-lg font-semibold">Add Customer</div>
                    <div class="flex">
                        <Link href="/customer"
                            class="bg-rose-600 p-2 px-3 rounded-md text-lg flex items-center text-white cursor-pointer">
                            <Icon icon="line-md:arrow-left" class="mr-2" width="1.5rem" height="1.5rem" />
                            Back
                        </Link>
                    </div>
                </div>

                <!-- main content goes here -->
                <form @submit.prevent="submit" class="px-3 min-h-[calc(100vh-12rem)] justify-items-center mt-2">

                    <div class="w-3/4 rounded-md  p-2 px-4 grid grid-cols-2 gap-2">

                        <div class="w-full mb-1">
                            <label :class="{ 'text-red-500': errors?.name }" for="dd-city"
                                class="text-md font-semibold w-full content-center"> Name:</label>
                            <input :class="{ 'border': true, 'border-red-500 text-red-500': errors?.name }" type="text"
                                v-model="name"
                                class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md"
                                placeholder="Name" />
                            <p v-if="errors?.name" class="text-red-500 text-sm mt-1">{{ errors?.name }}</p>
                        </div>

                        <div class="w-full mb-1">
                            <label :class="{ 'text-red-500': errors?.email }" for="dd-city"
                                class="text-md font-semibold w-full content-center"> Email:</label>
                            <input :class="{ 'border': true, 'border-red-500 text-red-500': errors?.email }"
                                type="email" v-model="email"
                                class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md"
                                placeholder="Email" />
                            <p v-if="errors?.email" class="text-red-500 text-sm mt-1">{{ errors?.email }}</p>
                        </div>



                        <div class="w-full mb-1">
                            <label for="dd-city" :class="{ 'text-red-500': errors?.mobile }"
                                class="text-md font-semibold w-full content-center">Mobile:</label>
                            <input :class="{ 'border': true, 'border-red-500 text-red-500': errors?.mobile }"
                                type="text" v-model="mobile"
                                class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md"
                                placeholder="Mobile" />
                            <p v-if="errors?.mobile" class="text-red-500 text-sm mt-1">{{ errors?.mobile }}</p>
                        </div>


                        <div class="w-full mb-1">
                            <label for="dd-city" :class="{ 'text-red-500': errors?.password }"
                                class="text-md font-semibold w-full content-center">Password:</label>
                            <input v-model="password" type="password"
                                class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" />
                            <p v-if="errors?.password" class="text-red-500 text-sm mt-1">{{ errors?.password }}</p>
                        </div>


                        <div class="w-full mb-1">
                            <label for="dd-city" :class="{ 'text-red-500': errors?.password }"
                                class="text-md font-semibold w-full content-center">Confirm Password:</label>
                            <input v-model="confirm" type="password"
                                class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" />
                            <p v-if="errors?.password" class="text-red-500 text-sm mt-1">{{ errors?.password }}</p>
                        </div>


                        <div class="w-full mb-1">
                            <label for="dd-city" :class="{ 'text-red-500': errors?.status }"
                                class="text-md font-semibold w-full content-center">Status:</label>
                            <select :class="{ 'border': true, 'border-red-500 text-red-500': errors?.status }"
                                v-model="status"
                                class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <p v-if="errors?.status" class="text-red-500 text-sm mt-1">{{ errors?.status }}</p>
                        </div>

                        <div class="grid mb-1 col-span-2">
                            <label for="dd-city"
                                class="text-md font-semibold w-full content-center">Description:</label>
                            <textarea v-model="description"
                                class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" />
                        </div>

                    </div>

                    <div class="mb-1 w-3/4 flex justify-end mt-2">
                        <button
                            class="justify-items-end cursor-pointer bg-sky-500 hover:bg-sky-600 text-md border py-1 px-2 rounded-md font-semibold text-white">Add
                            Customer</button>
                    </div>

                </form>

            </div>

        </div>
    </AppLayout>
</template>
