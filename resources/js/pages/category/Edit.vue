<script setup lang="ts">

    import AutoComplete from 'primevue/autocomplete';
    import ToggleSwitch from 'primevue/toggleswitch';
    import AppLayout from '@/layouts/AppLayout.vue';
    import { Head, Link, router, usePage } from '@inertiajs/vue3';
    import { ref, computed, onMounted } from 'vue';
    import { Icon } from '@iconify/vue';
    import { useToast } from "primevue/usetoast";

    const props = defineProps({
        category: Object,
        categories: Array,
    });

    const toast = useToast();
    const page = usePage()
    const user = page.props.auth.user
    const id = ref(props.category?.id);
    const name = ref(props.category?.name);
    const parentId = ref(props.category?.parent);
    const icon = ref(props.category?.icon);
    const thumbnail = ref(props.category?.thumbnail);
    const banner = ref(props.category?.banner);
    const menuImage = ref(props.category?.menuImage);
    const status = ref(props.category?.status);
    const isActive = ref(props.category?.isActive);
    const featured = ref(!!props.category?.featured);
    const description = ref(props.category?.description);
    const errors = ref({});
    const items = ref([]);
    const loading = ref(false)

    const breadcrumbs = ref([
        { title: 'Dashboard', href: '/' },
        { title: 'Category', href: '/categories' },
        { title: 'Edit', href: '' },
    ]);


     // Filter Category Parent

    const search = (event) => {

        const query = event.query.toLowerCase();
        items.value = props.categories.filter(p => p.name.toLowerCase().includes(query));

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

        let parent_id = parentId.value ? parentId.value.id : "";

        const formData = new FormData();
        formData.append('name', name.value);
        formData.append('parent_id', parent_id);
        formData.append('icon', icon.value);
        formData.append('thumbnail', thumbnail.value);
        formData.append('banner', banner.value);
        formData.append('status', status.value);
        formData.append('isActive', isActive.value);
        formData.append('featured', featured.value ? 1 : 0);
        formData.append('description', description.value);

        if (menuImage.value instanceof File) {
            formData.append('menuImage', menuImage.value);
        }


        router.post('/categories/'+id.value, formData, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            forceFormData: true,
            onSuccess: () => {
                toast.add({ severity: 'success', summary: 'Success Message', detail: 'Category updated successfully!', life: 3000 });
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

    <Head :title="`Category Edit ${name}`" />

    <AppLayout :breadcrumbs="breadcrumbs" :loading="loading">
        <Toast position="top-right" />
        <div class="w-full mx-auto p-2 flex flex-row place-content-center">
            <div className="w-5/6 mt-4 shadow-lg bg-white rounded-md pb-1">

                <div class="flex justify-between bg-gray-300 px-2 items-center rounded-t-md p-1 ">
                    <div class="text-lg font-semibold">Add {{ name }}</div>
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
                            <label :class="{'text-red-500': errors?.parentId }" for="dd-city" class="text-md font-semibold w-full content-center"> Parent Category:</label>
                            <AutoComplete v-model="parentId" dropdown :suggestions="items" size="small" inputClass="w-full" optionLabel="name" @complete="search" />

                        </div>

                        <div class="w-full mb-1 ">
                            <label :class="['text-md font-semibold w-full content-center',  { 'text-red-500': errors?.icon }, { 'text-green-500': icon != null && !errors?.icon }]" class="text-md font-semibold w-full content-center">Icon:</label>
                            <input :class="{'border': true, 'border-red-500 text-red-500': errors?.icon }" type="file" @change="iconChange" accept="image/*" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" />
                            <p v-if="errors?.icon" class="text-red-500 text-sm mt-1">{{ errors?.icon }}</p>
                        </div>

                        <div class="w-full mb-1">
                            <label for="dd-city" :class="['text-md font-semibold w-full content-center',  { 'text-red-500': errors?.thumbnail }, { 'text-green-500': thumbnail != null && !errors?.thumbnail }]"  class="text-md font-semibold w-full content-center">Thumbnail:</label>
                            <input :class="{'border': true, 'border-red-500 text-red-500': errors?.thumbnail }"  type="file" @change="thumbnailChange" accept="image/*" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" />
                            <p v-if="errors?.thumbnail" class="text-red-500 text-sm mt-1">{{ errors?.thumbnail }}</p>
                        </div>

                        <div class="w-full mb-1">
                            <label for="dd-city" :class="['text-md font-semibold w-full content-center', { 'text-green-500': banner != null }]"  class="text-md font-semibold w-full content-center">Banner:</label>
                            <input type="file" @change="bannerChange" accept="image/*" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" />
                        </div>

                        <div class="w-full mb-1">
                            <label for="dd-city" :class="['text-md font-semibold w-full content-center', { 'text-red-500': errors?.menuImage }, { 'text-green-500': menuImage != null && !errors?.menuImage }]" class="text-md font-semibold w-full content-center">Menu Image:</label>
                            <img v-if="props.category?.menuImage" :src="`/category/${props.category.menuImage}`" class="h-16 w-16 object-cover rounded-md border mb-1" />
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
                            <label for="dd-city" :class="{'text-red-500': errors?.isActive }" class="text-md font-semibold w-full content-center">Main Menu:</label>
                            <select :class="{'border': true, 'border-red-500 text-red-500': errors?.isActive }" v-model="isActive" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <p v-if="errors?.status" class="text-red-500 text-sm mt-1">{{ errors?.status }}</p>
                        </div>

                         <div class="w-full mb-1">
                            <label for="dd-city" class="text-md font-semibold w-full content-center">Description:</label>
                            <textarea v-model="description" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md"></textarea>
                        </div>

                        <div class="w-full mb-1 grid grid-flow-col justify-items-end mt-2">
                            <button class="justify-items-end cursor-pointer bg-orange-500 hover:bg-orange-600 text-md border py-1 px-2 rounded-md font-semibold text-white">Update Category</button>
                        </div>
                    </div>

                </form>

            </div>

        </div>
    </AppLayout>
</template>
