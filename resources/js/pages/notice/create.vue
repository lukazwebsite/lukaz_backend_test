<script setup lang="ts">

import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { Icon } from '@iconify/vue';
import { useToast } from "primevue/usetoast";

const toast = useToast();

const page = usePage()
const user = page.props.auth.user

const title = ref('');
const description = ref('');
const href = ref('');
const buttonText = ref('');
const status = ref(1);
const loading = ref(false);

const errors = ref({});

const breadcrumbs = ref([
    { title: 'Dashboard', href: '/' },
    { title: 'Notices', href: '/notices' },
    { title: 'Add New', href: '' },
]);



const submit = () => {

    loading.value = true;

    const formData = new FormData();
    formData.append('title', title.value);
    formData.append('description', description.value);
    formData.append('buttonText', buttonText.value);
    formData.append('href', href.value);
    formData.append('status', status.value);


    router.post('/notices', formData, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        forceFormData: true,
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Success Message', detail: 'Notice store successfully!', life: 3000 });
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

    <Head title="Notice" />

    <AppLayout :breadcrumbs="breadcrumbs" :loading="laoding">
        <Toast position="top-right" />
        <div class="w-full mx-auto p-2 flex flex-row place-content-center">
            <div className="w-5/6 mt-4 shadow-lg bg-white rounded-md pb-1">

                <div class="flex justify-between bg-gray-300 px-2 items-center rounded-t-md p-1 ">
                    <div class="text-lg font-semibold">Add Notices</div>
                    <div class="flex">
                        <Link href="/notices"
                            class="bg-rose-600 p-2 px-3 rounded-md text-lg flex items-center text-white cursor-pointer">
                        <Icon icon="line-md:arrow-left" class="mr-2" width="1.5rem" height="1.5rem" />
                        Back
                        </Link>
                    </div>
                </div>

                <!-- main content goes here -->
                <form @submit.prevent="submit" class="px-3 h-[calc(100vh-10rem)] justify-items-center mt-2">

                    <div class="w-1/2 shadow-sm rounded-md border-t p-2 px-4">
                        <div class="w-full mb-1">
                            <label :class="{ 'text-red-500': errors?.title }" for="dd-city"
                                class="text-md font-semibold w-full content-center"> Title:</label>
                            <input :class="{ 'border': true, 'border-red-500 text-red-500': errors?.title }" type="text"
                                v-model="title"
                                class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md"
                                placeholder="Title" />
                            <p v-if="errors?.title" class="text-red-500 text-sm mt-1">{{ errors?.title }}</p>
                        </div>
                        <div class="w-full mb-1">
                            <label :class="{ 'text-red-500': errors?.description }" for="dd-city"
                                class="text-md font-semibold w-full content-center"> Description:</label>
                            <textarea :class="{ 'border': true, 'border-red-500 text-red-500': errors?.description }"
                                v-model="description" rows="4"
                                class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md"
                                placeholder="Description"></textarea>
                            <p v-if="errors?.description" class="text-red-500 text-sm mt-1">{{ errors?.description }}
                            </p>
                        </div>
                        <div class="w-full mb-1">
                            <label :class="{ 'text-red-500': errors?.href }" for="dd-city"
                                class="text-md font-semibold w-full content-center"> Href:</label>
                            <input :class="{ 'border': true, 'border-red-500 text-red-500': errors?.href }" type="text"
                                v-model="href"
                                class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md"
                                placeholder="Href" />
                            <p v-if="errors?.href" class="text-red-500 text-sm mt-1">{{ errors?.href }}</p>
                        </div>
                         <div class="w-full mb-1">
                            <label for="status" :class="{ 'text-red-500': errors?.status }"
                                class="text-md font-semibold w-full content-center  ">Status</label>
                            <select v-model="status" id="status" name="status" required
                                class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <div v-if="errors.status" class="text-red-600 text-sm mt-1">{{ errors.status }}</div>
                        </div>
                        <div class="w-full mb-1">
                            <label :class="{ 'text-red-500': errors?.buttonText }" for="dd-city"
                                class="text-md font-semibold w-full content-center"> Button Text:</label>
                            <input :class="{ 'border': true, 'border-red-500 text-red-500': errors?.buttonText }"
                                type="text" v-model="buttonText"
                                class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md"
                                placeholder="Button Text" />
                            <p v-if="errors?.buttonText" class="text-red-500 text-sm mt-1">{{ errors?.buttonText }}</p>
                        </div>



                        <div class="w-full mb-1 grid grid-flow-col justify-items-end mt-2">
                            <button
                                class="justify-items-end cursor-pointer bg-sky-500 hover:bg-sky-600 text-md border py-1 px-2 rounded-md font-semibold text-white">Add
                                Notice</button>
                        </div>
                    </div>

                </form>

            </div>

        </div>
    </AppLayout>
</template>
