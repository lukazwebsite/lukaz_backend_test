<script setup lang="ts">

    import AppLayout from '@/layouts/AppLayout.vue';
    import { Head, Link, usePage, router } from '@inertiajs/vue3';
    import { computed, onMounted, ref } from 'vue'
    import { Icon } from '@iconify/vue';
    import Drawer from 'primevue/drawer';
    import Fieldset from 'primevue/fieldset';
    import { useToast } from "primevue/usetoast";


    const toast = useToast();
    const props = defineProps({
        medias: Object,
        menuAccess: Array
    });


    const add = ref(null);
    const edit = ref(null);
    const deleted = ref(null);

    const loading = ref(false);

    const icon = ref('');
    const thumbnail = ref('');
    const galleries = ref([]);

    const errors = ref({});
    const editId = ref();
    const visibleRight = ref(false);

    const breadcrumbs = ref([
        { title: 'Dashboard', href: '/' },
        { title: 'Product', href: '/products' },
        { title: 'Additional Media', href: '' },
    ]);




    const openWindow = (id) => {
        visibleRight.value = true
        editId.value = id;
    }


    const iconChange = (event) => {
        icon.value = event.target.files[0];
    };

    const thumbnailChange = (event) => {
        thumbnail.value = event.target.files[0];
    };

    const galleriesChange = (event) => {

        const newFiles = Array.from(event.target.files); // only keep names
        galleries.value = [...galleries.value, ...newFiles]; // append names only

    };


    const submit = () => {

        loading.value = true;


        const fromData = new FormData();
        fromData.append('thumbnail', thumbnail.value);
        fromData.append('icon', icon.value);

        // galleries.value.forEach(name => {
        //     fromData.append('galleries[]', name);
        // });

        galleries.value.forEach((file, index) => {
            if (file instanceof File) {
                fromData.append(`galleries[${index}]`, file);
            }
        });

        router.post('/product/' + editId.value + '/medias',

           fromData,
            {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            forceFormData: true,
            onSuccess: () => {
                toast.add({ severity: 'success', summary: 'Success Message', detail: 'Media store successfully!', life: 3000 });
                errors.value = {};
                visibleRight.value = false
                loading.value = false;
                galleries.value = '';
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

    <Head title="Product Additional Media" />

    <AppLayout :breadcrumbs="breadcrumbs" :loading="loading">
        <Toast position="top-right" />
        <div class="w-full mx-auto p-2 flex flex-row place-content-center">
            <div className="w-5/6 mt-4 shadow-lg bg-white rounded-md">
                <div class="flex justify-between bg-gray-300 px-2 py-1 items-center rounded-t-md">
                    <div class="text-lg font-semibold">Product Additional Media</div>
                    <div class="flex">
                        <Link href="/product" class="bg-rose-600 p-2 px-3 rounded-md text-lg flex items-center text-white cursor-pointer">  <Icon icon="line-md:arrow-left" class="mr-2" width="1.5rem" height="1.5rem" />
                            Back
                        </Link>
                    </div>
                </div>

                <!-- main content goes here -->

                <div class="border rounded-b-md px-3 py-1 h-[calc(100vh-10rem)] overflow-auto pb-3">

                    <div class="grid grid-cols-4 gap-4">
                        <div class="card" v-for="media in medias">
                            <Fieldset>
                                <template #legend>
                                    <div class="flex items-center">
                                        <span class="font-bold p-2 text-2xl">{{ media?.color  }} </span>
                                        <button class="bg-blue-600 hover:bg-blue-500 text-gray-100 transform rounded-sm hover:text-black px-2 py-1 text-sm cursor-pointer" @click="openWindow(media?.id)">
                                            <Icon icon="icon-park-outline:pencil" width="1.3rem" />
                                        </button>
                                    </div>
                                </template>
                                <div class="grid grid-cols-4 gap-2 content-center">
                                    <div class="border rounded-md content-center overflow-hidden p-1">
                                        <img :src="media?.color_icon_small
                                            ? `/products/${media?.color_icon_small}`
                                            : `/assets/sites/sample.webp`" />
                                    </div>

                                    <div class="border rounded-md content-center overflow-hidden p-1">
                                        <img :src="media?.color_thumbnails_small
                                            ? `/products/${media?.color_thumbnails_small}`
                                            : `/assets/sites/sample.webp`" />
                                    </div>

                                    <div v-for="(img, index) in (media?.color_galleries_small)" :key="index" class="border rounded-md content-center overflow-hidden p-1">
                                        <img :src="`/products/${img}`" />
                                    </div>
                                </div>

                            </Fieldset>
                        </div>

                    </div>

                </div>
            </div>

            <Drawer v-model:visible="visibleRight" header="Feature Images" position="right">
                <form @submit.prevent="submit" enctype="multipart/form-data">
                    <div class="w-full mb-2">
                        <label for="dd-city" class="text-md w-full content-center">Color Icon:</label>
                        <input type="file" @change="iconChange" class="w-full text-white text-md border bg-sky-600 py-2 px-2 outline-none focus:border-green-200 rounded-md cursor-pointer hover:bg-sky-700"/>
                        <p v-if="errors?.icon" class="text-red-500 text-sm mt-1">{{ errors?.icon }}</p>
                    </div>
                    <div class="w-full mb-2">
                        <label for="dd-city" class="text-md w-full content-center">Feature Image:</label>
                        <input type="file" @change="thumbnailChange" class="w-full text-white text-md border bg-sky-600 py-2 px-2 outline-none focus:border-green-200 rounded-md cursor-pointer hover:bg-sky-700"/>
                        <p v-if="errors?.thumbnail" class="text-red-500 text-sm mt-1">{{ errors?.thumbnail }}</p>
                    </div>
                    <div class="w-full mb-2">
                        <label for="dd-city" class="text-md w-full content-center">Gallary Images:</label>
                        <input type="file" @change="galleriesChange" multiple class="w-full text-white text-md border bg-sky-600 py-2 px-2 outline-none focus:border-green-200 rounded-md cursor-pointer hover:bg-sky-700"/>

                        <div v-if="errors">
                            <p v-if="errors && Object.keys(errors).some(k => k.startsWith('galleries.'))" class="text-red-500 text-sm mt-1">
                                Some gallery images are invalid. Only JPEG, PNG, JPG, GIF, or WEBP allowed.
                            </p>
                        </div>

                    </div>

                    <div class="w-full mb-1 mt-2">
                        <small class="text-gray-700 text-sm mt-1 font-semibold">Note: <span class="italic">Format: jpeg,png,jpg,gif,webp</span></small>
                        <button class="w-full text-white text-md border bg-yellow-500 py-2 px-2 outline-none focus:border-green-200 rounded-md cursor-pointer hover:bg-yellow-700">Upload</button>
                    </div>

                </form>

            </Drawer>

        </div>
    </AppLayout>
</template>
