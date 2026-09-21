<script setup lang="ts">

import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { Icon } from '@iconify/vue';
import { useToast } from "primevue/usetoast";
import AutoComplete from 'primevue/autocomplete';

const props = defineProps({
    shopby: Object,
    categories: Array
});


const toast = useToast();

const page = usePage()
const user = page.props.auth.user
const shopbyId = ref(props.shopby?.id);
const name = ref(props.shopby?.name);
const url = ref(props.shopby?.url);
const thumbnail = ref('');
const categories = ref(props.shopby?.categories);
const selectedCategories = ref(props.shopby?.categories);
const description = ref(props.shopby?.description);
const status = ref(props.shopby?.status);
const errors = ref({});
const loading = ref(false)

const breadcrumbs = ref([
    { name: 'Dashboard', href: '/' },
    { name: 'Shop By', href: '/shop_by' },
    { name: 'Edit', href: '' },
]);

const categorySearch = (event) => {
    const query = event.query.toLowerCase();
    categories.value = props.categories ? props.categories.filter(p => p.name.toLowerCase().includes(query)) : [];
};

const thumbnailChange = (event) => {
    thumbnail.value = event.target.files[0];
};


const submit = () => {

    loading.value = true;

    const formData = new FormData();
    formData.append('name', name.value);
    formData.append('url', url.value);
    formData.append('categories', JSON.stringify(selectedCategories.value));
    formData.append('thumbnail', thumbnail.value);
    formData.append('status', status.value);
    formData.append('description', description.value);


    router.post('/shop_by/' + shopbyId?.value, formData, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        forceFormData: true,
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Success Message', detail: 'Shop By store successfully!', life: 3000 });
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

    <Head name="Create Shop by" />

    <AppLayout :breadcrumbs="breadcrumbs" :loading="loading">
        <Toast position="top-right" />
        <div class="w-full mx-auto p-2 flex flex-row place-content-center">
            <div className="w-5/6 mt-4 shadow-lg bg-white rounded-md pb-1">

                <div class="flex justify-between bg-gray-300 px-2 items-center rounded-t-md p-1 ">
                    <div class="text-lg font-semibold">Edit Shop by</div>
                    <div class="flex">
                        <Link href="/shop_by"
                            class="bg-rose-600 p-2 px-3 rounded-md text-lg flex items-center text-white cursor-pointer">
                            <Icon icon="line-md:arrow-left" class="mr-2" width="1.5rem" height="1.5rem" />
                            Back
                        </Link>
                    </div>
                </div>

                <!-- main content goes here -->
                <form @submit.prevent="submit" class="px-3 min-h-[calc(100vh-12rem)] justify-items-center mt-2">

                    <div class="w-3/4 rounded-md  p-2 px-4 grid grid-cols-2 gap-2">

                        <div class="w-full mb-1 col-span-2">
                            <label :class="{ 'text-red-500': errors?.name }" for="dd-city"
                                class="text-md font-semibold w-full content-center"> Name:</label>
                            <input :class="{ 'border': true, 'border-red-500 text-red-500': errors?.name }" type="text"
                                v-model="name"
                                class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md"
                                placeholder="Name" />

                            <p v-if="errors?.name" class="text-red-500 text-sm mt-1">{{ errors?.name }}</p>
                        </div>


                        <div class="w-full mb-1 col-span-2">
                            <label for="categories" class="text-sm w-full">Tag</label>
                            <AutoComplete
                                :class="{ 'border': true, 'border-red-500 text-red-500': errors?.category_id }"
                                v-model="selectedCategories" inputId="multiple-ac-1" optionLabel="name"
                                inputClass="w-full h-5 text-sm" multiple fluid :suggestions="categories"
                                @complete="categorySearch" />
                            <p v-if="errors?.category_id" class="text-red-500 text-sm mt-1">{{ errors?.category_id }}
                            </p>
                        </div>


                        <div class="w-full mb-1 col-span-2">
                            <label :class="{ 'text-green-500': props.shopby?.thumbnail }" for="dd-city"
                                class="text-md font-semibold w-full content-center">Image:</label>
                            <input :class="{ 'border': true, 'border-red-500 text-red-500': errors?.thumbnail }"
                                type="file" @change="thumbnailChange" accept="thumbnail/*"
                                class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" />
                            <p v-if="errors?.thumbnail" class="text-red-500 text-sm mt-1">{{ errors?.thumbnail }}</p>
                        </div>

                        <div class="w-full mb-1 ">
                            <label :class="{ 'text-red-500': errors?.url }" for="dd-city"
                                class="text-md font-semibold w-full content-center"> Link:</label>
                            <input :class="{ 'border': true, 'border-red-500 text-red-500': errors?.url }" type="text"
                                v-model="url"
                                class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md"
                                placeholder="Link" />
                            <p v-if="errors?.url" class="text-red-500 text-sm mt-1">{{ errors?.url }}</p>
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
                            <textarea v-model="description" class="w-full text-md border py-1 px-2 outline-none
                                focus:border-green-200 rounded-md"></textarea>
                        </div>

                    </div>

                    <div class="mb-1 w-3/4 flex justify-end mt-2">
                        <button
                            class="justify-items-end cursor-pointer bg-sky-500 hover:bg-sky-600 text-md border py-1 px-2 rounded-md font-semibold text-white">Update</button>
                    </div>

                </form>

            </div>

        </div>
    </AppLayout>
</template>
