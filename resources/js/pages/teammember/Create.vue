<script setup lang="ts">

import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { Icon } from '@iconify/vue';
import { useToast } from "primevue/usetoast";

const props = defineProps({
    nextSortOrder: Number
});

const toast = useToast();

const page = usePage()
const user = page.props.auth.user
const name = ref('');
const designation = ref('');
const whatsapp = ref('');
const sortOrder = ref(props.nextSortOrder ?? 10);
const image = ref('');
const imagePreview = ref('');
const status = ref(1);
const errors = ref({});
const loading = ref(false)

const breadcrumbs = ref([
    { name: 'Dashboard', href: '/' },
    { name: 'Team Member', href: '/team_member' },
    { name: 'Add New', href: '' },
]);

const imageChange = (event) => {
    const file = event.target.files[0];
    image.value = file;
    imagePreview.value = file ? URL.createObjectURL(file) : '';
};

const submit = () => {

    loading.value = true;

    const formData = new FormData();
    formData.append('name', name.value);
    formData.append('designation', designation.value);
    formData.append('whatsapp', whatsapp.value);
    formData.append('sort_order', sortOrder.value);
    formData.append('image', image.value);
    formData.append('status', status.value);

    router.post('/team_member', formData, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        forceFormData: true,
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Success Message', detail: 'Team Member store successfully!', life: 3000 });
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

    <Head name="Create Team Member" />

    <AppLayout :breadcrumbs="breadcrumbs" :loading="loading">
        <Toast position="top-right" />
        <div class="w-full mx-auto p-2 flex flex-row place-content-center">
            <div className="w-5/6 mt-4 shadow-lg bg-white rounded-md pb-1">

                <div class="flex justify-between bg-gray-300 px-2 items-center rounded-t-md p-1 ">
                    <div class="text-lg font-semibold">Add Team Member</div>
                    <div class="flex">
                        <Link href="/team_member"
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
                            <label :class="{ 'text-red-500': errors?.designation }" for="dd-city"
                                class="text-md font-semibold w-full content-center"> Designation:</label>
                            <input :class="{ 'border': true, 'border-red-500 text-red-500': errors?.designation }"
                                type="text" v-model="designation"
                                class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md"
                                placeholder="Founder &amp; CEO" />
                            <p v-if="errors?.designation" class="text-red-500 text-sm mt-1">{{ errors?.designation }}</p>
                        </div>

                        <div class="w-full mb-1">
                            <label :class="{ 'text-red-500': errors?.whatsapp }" for="dd-city"
                                class="text-md font-semibold w-full content-center"> WhatsApp Number:</label>
                            <input :class="{ 'border': true, 'border-red-500 text-red-500': errors?.whatsapp }"
                                type="text" v-model="whatsapp"
                                class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md"
                                placeholder="8801700000000" />
                            <p class="text-gray-500 text-xs mt-1">Country code first, no plus sign. Spaces and dashes
                                are removed automatically.</p>
                            <p v-if="errors?.whatsapp" class="text-red-500 text-sm mt-1">{{ errors?.whatsapp }}</p>
                        </div>

                        <div class="w-full mb-1">
                            <label :class="{ 'text-red-500': errors?.sort_order }" for="dd-city"
                                class="text-md font-semibold w-full content-center"> Display Order:</label>
                            <input :class="{ 'border': true, 'border-red-500 text-red-500': errors?.sort_order }"
                                type="number" min="0" v-model="sortOrder"
                                class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md"
                                placeholder="10" />
                            <p class="text-gray-500 text-xs mt-1">Lower number shows first. Use 10, 20, 30 so you can
                                insert people later.</p>
                            <p v-if="errors?.sort_order" class="text-red-500 text-sm mt-1">{{ errors?.sort_order }}</p>
                        </div>

                        <div class="w-full mb-1">
                            <label for="dd-city" class="text-md font-semibold w-full content-center">Image:</label>
                            <input type="file" @change="imageChange" accept="image/*"
                                class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" />
                            <p v-if="errors?.image" class="text-red-500 text-sm mt-1">{{ errors?.image }}</p>
                            <img v-if="imagePreview" :src="imagePreview" class="mt-2 h-24 w-24 object-cover rounded-md"
                                alt="Preview" />
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

                    </div>

                    <div class="mb-1 w-3/4 flex justify-end mt-2">
                        <button
                            class="justify-items-end cursor-pointer bg-sky-500 hover:bg-sky-600 text-md border py-1 px-2 rounded-md font-semibold text-white">Add
                            Team Member</button>
                    </div>

                </form>

            </div>

        </div>
    </AppLayout>
</template>
