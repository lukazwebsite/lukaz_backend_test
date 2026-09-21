<script setup lang="ts">

    import AutoComplete from 'primevue/autocomplete';
    import ToggleSwitch from 'primevue/toggleswitch';
    import AppLayout from '@/layouts/AppLayout.vue';
    import { Head, Link, router, usePage } from '@inertiajs/vue3';
    import { ref, computed, onMounted } from 'vue';
    import { Icon } from '@iconify/vue';
    import { useToast } from "primevue/usetoast";


    const props = defineProps({
        parents: Array,
    });

    const toast = useToast();
    const page = usePage()
    const user = page.props.auth.user
    const name = ref('');
    const parent_id = ref('');
    const icon = ref('');
    const thumbnail = ref('');
    const banner = ref('');
    const menuImage = ref('');
    const description = ref('');
    const status = ref(1);
    const isActive = ref(1);
    const featured = ref(false);
    const errors = ref({});
    const items = ref([]);

    const breadcrumbs = ref([
        { title: 'Dashboard', href: '/' },
        { title: 'Category', href: '/categories' },
        { title: 'Add New', href: '' },
    ]);


    const loading = ref(false)

    // Filter Category Parent

    const search = (event) => {

        const query = event.query.toLowerCase();
        items.value = props.parents.filter(p => p.name.toLowerCase().includes(query));

    }


    const iconChange = (event) => {
        icon.value = event.target.files[0];
    };

    const thumbnailChange = (event) => {
        thumbnail.value = event.target.files[0];
    };

    const bannerChange = (event) => {
        banner.value = event.target.files[0];
    };

    const menuImageChange = (event) => {
        menuImage.value = event.target.files[0];
    };


    const submit = () => {


        loading.value = true;

        const formData = new FormData();

        if (parent_id.value) {
            formData.append('parent_id', parent_id?.value?.id);
        } else {
            formData.append('parent_id', '');
        }

        formData.append('name', name.value);
        formData.append('icon', icon.value);
        formData.append('thumbnail', thumbnail.value);
        formData.append('banner', banner.value);
        formData.append('status', status.value);
        formData.append('isActive', isActive.value);
        formData.append('featured', featured.value ? 1 : 0);
        formData.append('description', description.value);

        if (menuImage.value) {
            formData.append('menuImage', menuImage.value);
        }


        router.post('/categories', formData, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            forceFormData: true,
            onSuccess: () => {
                toast.add({ severity: 'success', summary: 'Success Message', detail: 'Category store successfully!', life: 3000 });
                errors.value = {};
                loading.value = false;
            },
            onError: (e) => {
                errors.value = e;
                toast.add({ severity: 'error', summary: 'Error Message', detail: 'Oops! Something wrongs', life: 3000 });
                loading.value = false;

            },
        });
    };






</script>

<template>

    <Head title="Category" />

    <AppLayout :breadcrumbs="breadcrumbs" :loading="loading">
        <Toast position="top-right" />
        <div class="w-full mx-auto p-2 flex flex-row place-content-center">
            <div className="w-5/6 mt-4 shadow-lg bg-white rounded-md pb-1">

                <div class="flex justify-between bg-gray-300 px-2 items-center rounded-t-md p-1 ">
                    <div class="text-lg font-semibold">Add category</div>
                    <div class="flex">
                        <Link href="/categories" class="bg-rose-600 p-2 px-3 rounded-md text-lg flex items-center text-white cursor-pointer">  <Icon icon="line-md:arrow-left" class="mr-2" width="1.5rem" height="1.5rem" />
                            Back
                        </Link>
                    </div>
                </div>

                <!-- main content goes here -->
                <form @submit.prevent="submit" class="px-3 h-[calc(100vh-10rem)] justify-items-center mt-2">

                    <div class="w-1/2 shadow-sm rounded-md border-t p-2 px-4">

                        <div class="w-full mb-1">
                            <label :class="{'text-red-500': errors?.name }" for="dd-city" class="text-md font-semibold w-full content-center"> Name:</label>
                            <input :class="{'border': true, 'border-red-500 text-red-500': errors?.name }" type="text" v-model="name" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" placeholder="Category Name"/>
                            <p v-if="errors?.name" class="text-red-500 text-sm mt-1">{{ errors?.name }}</p>
                        </div>

                        <div class="w-full flex flex-col mb-1">
                            <label :class="{'text-red-500': errors?.name }" for="dd-city" class="text-md font-semibold w-full content-center"> Parent Category:</label>
                            <AutoComplete v-model="parent_id" dropdown :suggestions="items" size="small" inputClass="w-full" optionLabel="name" @complete="search" />

                        </div>

                        <div class="w-full mb-1 ">
                            <label :class="{'text-red-500': errors?.icon }" for="dd-city" class="text-md font-semibold w-full content-center">Icon:</label>
                            <input :class="{'border': true, 'border-red-500 text-red-500': errors?.icon }" type="file" @change="iconChange" accept="image/*" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" />
                            <p v-if="errors?.icon" class="text-red-500 text-sm mt-1">{{ errors?.icon }}</p>
                        </div>

                        <div class="w-full mb-1">
                            <label for="dd-city" :class="{'text-red-500': errors?.thumbnail }"  class="text-md font-semibold w-full content-center">Thumbnail:</label>
                            <input :class="{'border': true, 'border-red-500 text-red-500': errors?.thumbnail }"  type="file" @change="thumbnailChange" accept="image/*" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" />
                            <p v-if="errors?.thumbnail" class="text-red-500 text-sm mt-1">{{ errors?.thumbnail }}</p>
                        </div>

                        <div class="w-full mb-1">
                            <label for="dd-city" class="text-md font-semibold w-full content-center">Banner:</label>
                            <input type="file" @change="bannerChange" accept="image/*" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" />
                        </div>

                        <div class="w-full mb-1">
                            <label for="dd-city" :class="{'text-red-500': errors?.menuImage }" class="text-md font-semibold w-full content-center">Menu Image:</label>
                            <input :class="{'border': true, 'border-red-500 text-red-500': errors?.menuImage }" type="file" @change="menuImageChange" accept="image/*" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" />
                            <p v-if="errors?.menuImage" class="text-red-500 text-sm mt-1">{{ errors?.menuImage }}</p>
                        </div>

                        <div class="w-full mb-1">
                            <label for="dd-city" :class="{'text-red-500': errors?.featured }" class="text-md font-semibold w-full content-center">Featured:</label>
                            <div class="flex items-center gap-2 py-1">
                                <ToggleSwitch v-model="featured" inputId="featured" />
                                <label for="featured" class="text-md cursor-pointer select-none">{{ featured ? 'Yes' : 'No' }}</label>
                            </div>
                            <p v-if="errors?.featured" class="text-red-500 text-sm mt-1">{{ errors?.featured }}</p>
                        </div>

                         <div class="w-full mb-2">
                            <label for="dd-city" :class="{'text-red-500': errors?.status }" class="text-md font-semibold w-full content-center">Status:</label>
                            <select :class="{'border': true, 'border-red-500 text-red-500': errors?.status }" v-model="status" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <p v-if="errors?.status" class="text-red-500 text-sm mt-1">{{ errors?.status }}</p>
                        </div>

                         <div class="w-full mb-1">
                            <label for="dd-city" :class="{'text-red-500': errors?.isActive }" class="text-md font-semibold w-full content-center">Status:</label>
                            <select :class="{'border': true, 'border-red-500 text-red-500': errors?.isActive }" v-model="isActive" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <p v-if="errors?.isActive" class="text-red-500 text-sm mt-1">{{ errors?.isActive }}</p>
                        </div>

                        <div class="w-full mb-1">
                            <label for="dd-city" class="text-md font-semibold w-full content-center">Description:</label>
                            <textarea v-model="description" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md"></textarea>
                        </div>




                        <div class="w-full mb-1 grid grid-flow-col justify-items-end mt-2">
                            <button class="justify-items-end cursor-pointer bg-sky-500 hover:bg-sky-600 text-md border py-1 px-2 rounded-md font-semibold text-white">Add Category</button>
                        </div>
                    </div>

                </form>

            </div>

        </div>
    </AppLayout>
</template>

