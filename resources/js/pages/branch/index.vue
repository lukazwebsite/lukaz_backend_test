<script setup lang="ts">

    import AppLayout from '@/layouts/AppLayout.vue';
    import { Head, Link, usePage } from '@inertiajs/vue3';
    import { computed, onMounted, ref } from 'vue'
    import { Icon } from '@iconify/vue';
    import Drawer from 'primevue/drawer';
    import Pagination from '@/components/Pagination.vue';
    import { useToast } from "primevue/usetoast";

    const toast = useToast();

    const page = usePage()
    const user = page.props.auth.user


    const props = defineProps({
        branches: Object,
    });


    const add = ref(null);
    const edit = ref(null);
    const deleted = ref(null);
    const loading = ref(false)

    const brand = ref(null);
    const items = ref([]);
    const pageNumber = ref(props.branches?.current_page);

    const breadcrumbs = ref([
        { title: 'Dashboard', href: '/' },
        { title: 'Branch', href: '/branch' }
    ]);


const visibleRight = ref(false);



</script>

<template>

    <Head title="All Branch" />

    <AppLayout :breadcrumbs="breadcrumbs" :laoding="loading">
        <Toast position="top-right" />
        <div class="w-full mx-auto p-2 flex flex-row place-content-center">
            <div className="w-5/6 mt-4 shadow-lg bg-white rounded-md pb-1">
                <div class="flex justify-between bg-gray-300 px-2 py-1 items-center rounded-t-md">
                    <div class="text-lg font-semibold">Branch List</div>
                    <div class="flex">
                        <div class="bg-sky-600 items-center p-2 flex px-4 rounded-l-md cursor-pointer" @click="visibleRight = true">
                            <Icon icon="mdi:filter-outline" width="1.5rem" height="1.5rem" class="text-white"  />
                            <div class="px-2 text-white text-lg ">Filter</div>

                        </div>

                        <Link href="/branches/create" class="bg-green-600 p-2 rounded-r-md text-lg px-3 flex text-black text-white">
                            <Icon icon="fluent:add-12-filled" class="mr-2" width="1.5rem" height="1.5erm"/> Add Branch </Link>
                    </div>
                </div>

                <!-- main content goes here -->

                <div class="px-3 h-[calc(100vh-13rem)] overflow-auto pb-3">
                    <div class="w-full border-t mt-4">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-600 text-white">
                                    <th>SL</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Contact Number</th>
                                    <th>Address</th>
                                    <th>Status</th>
                                    <th>...</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(branch, index) in Object.values(branches?.data)" class="odd:bg-white even:bg-gray-200 text-gray-800">
                                    <td class="text-center">{{ index+1 }}</td>
                                    <td><img v-if="branch.icon" :src="`/branch/${branch.icon}`" /></td>
                                    <td>{{ branch?.name }}</td>
                                    <td>{{ branch?.contact }}</td>
                                    <td class="p-2">{{ branch?.address }}</td>
                                    <td class="p-2">{{ (branch?.status) ? 'Active' : 'Inactive' }}</td>
                                    <td class="flex gap-1 place-content-center">
                                        <Link :href="`/branches/${branch?.id}/edit`">
                                            <Icon icon="icon-park-outline:pencil" class="bg-red-500 hover:bg-red-600 p-1 w-auto h-8 cursor-pointer text-white rounded-sm" width="1.3rem"/>
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-between w-full px-3 py-2 border-t">

                    <div class="flex">
                        <input class="ring-0 border border-r-0 ring-0 rounded-l-md p-1 px-3 focus:outline-none focus:ring-0" v-model="pageNumber" type="number"/>
                        <div class="bg-gray-100 p-2 rounded-r-md">
                            <Icon icon="nonicons:go-16"/>
                        </div>
                    </div>
                    <div>
                        <!-- Pagination Component -->
                        <Pagination :links="branches?.links" />
                    </div>


                </div>

                <!-- main content goes here -->

                <Drawer v-model:visible="visibleRight" header="Transfer Filter" position="right">
                    <div class="grid grid-col-1 mt-4">

                        <div class="card w-full justify-center mt-2">
                            <label for="dd-city" class="text-md w-full content-center font-semibold">Product</label>
                            <div class="card flex justify-center">
                                <input class="w-full text-sm border py-1 px-2 rounded-md outline-none focus:border-green-200" />
                            </div>
                        </div>

                        <div class="card w-full justify-center">
                            <label for="dd-city" class="text-md w-full content-center font-semibold">Stock From</label>
                            <select class="w-full border py-1 px-2 rounded-md outline-none focus:border-green-200">
                                <option value="1">Corporate</option>
                                <option value="2">Khulna</option>
                                <option value="3">Syllet</option>
                                <option value="4">Dhaka</option>
                            </select>
                        </div>

                        <div class="card w-full justify-center mt-2">
                            <label for="dd-city" class="text-md w-full content-center font-semibold">Stock To</label>
                            <select class="w-full border py-1 px-2 rounded-md outline-none focus:border-green-200">
                                <option value="1">Corporate</option>
                                <option value="2">Khulna</option>
                                <option value="3">Syllet</option>
                                <option value="4">Dhaka</option>
                            </select>
                        </div>



                        <div class="card w-full justify-center mt-2">
                            <label for="dd-city" class="text-md w-full content-center font-semibold">From Date</label>
                            <div class="card flex justify-center">
                                <input type="date" class="w-full border rounded-md outline-none focus:border-green-20 text-sm p-2" />
                            </div>
                        </div>

                        <div class="card w-full justify-center mt-2">
                            <label for="dd-city" class="text-md w-full content-center font-semibold">To Date</label>
                            <div class="card flex justify-center">
                                <input type="date" class="w-full border rounded-md outline-none focus:border-green-20 text-sm p-2" />
                            </div>
                        </div>

                        <div class="card w-full justify-center mt-2">

                            <button class="bg-green-600 py-1 px-4 font-semibold text-white rounded-sm flex">Filter <Icon icon="fa7-solid:magnifying-glass"  class="m-1 mr-2"/></button>

                        </div>

                    </div>
                </Drawer>

            </div>

        </div>
    </AppLayout>
</template>
<style scoped>
    table tr td, th {
        padding:4px;
        text-align: left;

    }
</style>
