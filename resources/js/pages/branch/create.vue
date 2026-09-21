<script setup lang="ts">

    import AppLayout from '@/layouts/AppLayout.vue';
    import { Head, Link, router, usePage } from '@inertiajs/vue3';
    import { ref, computed } from 'vue';
    import { Icon } from '@iconify/vue';
    import { useToast } from "primevue/usetoast";

    const toast = useToast();
    const page = usePage()
    const user = page.props.auth.user

    const name = ref('');
    const nameBn = ref('');
    const contact = ref('');
    const managerPhone = ref('');
    const address = ref('');
    const addressBn = ref('');
    const mapLink = ref('');
    const mapEmbed = ref('');
    const status = ref('');
    const image = ref('');
    const loading = ref(false)
    const errors = ref({});

    const breadcrumbs = ref([
        { title: 'Dashboard', href: '/' },
        { title: 'Product', href: '/branch' },
        { title: 'Add New', href: '' },
    ]);


    const handleFileChange = (event) => {
        image.value = event.target.files[0];
    };


    const submit = () => {

        loading.value = false;

        const formData = new FormData();
        formData.append('name', name.value);
        formData.append('name_bn', nameBn.value);
        formData.append('contact', contact.value);
        formData.append('manager_phone', managerPhone.value);
        formData.append('address', address.value);
        formData.append('address_bn', addressBn.value);
        formData.append('map_link', mapLink.value);
        formData.append('map_embed', mapEmbed.value);
        formData.append('status', status.value);
        formData.append('outlet', image.value);


        router.post('/branches', formData, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            forceFormData: true,
            onSuccess: () => {
                toast.add({ severity: 'success', summary: 'Success Message', detail: 'Branch store successfully!', life: 3000 });
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

    <Head title="Branch" />

    <AppLayout :breadcrumbs="breadcrumbs" :loading="loading">
        <Toast position="top-right" />
        <div class="w-full mx-auto p-2 flex flex-row place-content-center">
            <div className="w-5/6 mt-4 shadow-lg bg-white rounded-md pb-1">

                <div class="flex justify-between bg-gray-300 px-2 items-center rounded-t-md p-1 ">
                    <div class="text-lg font-semibold">Add Branch</div>
                    <div class="flex">
                        <Link href="/branches" class="bg-rose-600 p-2 px-3 rounded-md text-lg flex items-center text-white cursor-pointer">  <Icon icon="line-md:arrow-left" class="mr-2" width="1.5rem" height="1.5rem" />
                            Back
                        </Link>
                    </div>

                </div>

                <!-- main content goes here -->



                    <form @submit.prevent="submit" class="px-3 h-[calc(100vh-10rem)] justify-items-center mt-2">

                        <div class="w-1/2 shadow-sm rounded-md border-t p-2 px-4">
                            <div class="w-full mb-1">
                                <label for="dd-city" class="text-md font-semibold w-full content-center">Branch Name:</label>
                                <input type="text" v-model="name" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" placeholder="Branch Name"/>
                            </div>

                            <div class="w-full mb-1">
                                <label for="dd-city" class="text-md font-semibold w-full content-center">Branch Name (Bangla):</label>
                                <input type="text" v-model="nameBn" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" placeholder="ঢাকা আউটলেট"/>
                            </div>

                            <div class="w-full mb-1 ">
                                <label for="dd-city" class="text-md font-semibold w-full content-center">Contact Number:</label>
                                <input type="text" v-model="contact" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" placeholder="Contact Number"/>
                            </div>

                            <div class="w-full mb-1 ">
                                <label for="dd-city" class="text-md font-semibold w-full content-center">Manager Phone:</label>
                                <input type="text" v-model="managerPhone" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" placeholder="01XXXXXXXXX (shown on storefront, call & WhatsApp)"/>
                            </div>

                            <div class="w-full mb-1">
                                <label for="dd-city" class="text-md font-semibold w-full content-center">Address:</label>
                                <textarea v-model="address" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md"></textarea>
                            </div>

                            <div class="w-full mb-1">
                                <label for="dd-city" class="text-md font-semibold w-full content-center">Address (Bangla):</label>
                                <textarea v-model="addressBn" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md"></textarea>
                            </div>

                            <div class="w-full mb-1">
                                <label for="dd-city" class="text-md font-semibold w-full content-center">Google Map Link:</label>
                                <input type="text" v-model="mapLink" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" placeholder="https://maps.app.goo.gl/..."/>
                            </div>

                            <div class="w-full mb-1">
                                <label for="dd-city" class="text-md font-semibold w-full content-center">Google Map Embed URL:</label>
                                <input type="text" v-model="mapEmbed" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" placeholder="https://www.google.com/maps?q=...&output=embed"/>
                            </div>

                            <div class="w-full mb-1">
                                <label for="dd-city" class="text-md font-semibold w-full content-center">Outlet Image:</label>
                                <input type="file" @change="handleFileChange" accept="image/*" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" />
                            </div>


                            <div class="w-full mb-1">
                                <label for="dd-city" class="text-md font-semibold w-full content-center">Status:</label>
                                <select v-model="status" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            <div class="w-full mb-1 grid grid-flow-col justify-items-end mt-2">

                                <button class="justify-items-end cursor-pointer bg-sky-500 hover:bg-sky-600 text-md border py-1 px-2 rounded-md font-semibold text-white">Add Branch</button>
                            </div>

                        </div>

                    </form>






            </div>

        </div>
    </AppLayout>
</template>

