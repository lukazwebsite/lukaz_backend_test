<script setup lang="ts">

import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue'
import { Icon } from '@iconify/vue';
import Drawer from 'primevue/drawer';
import { useDataDate } from '@/composables/useDataDate';
import Pagination from '@/components/Pagination.vue';
import Currency from '@/components/Currency/index.vue';
import { useToast } from "primevue/usetoast";

const toast = useToast();


const props = defineProps({
    videos: Object,
    checkPermission: Boolean
});

const page = usePage()
const user = page.props.auth.user
const { dateFunction, dateMonthFunction } = useDataDate();

// Explicitly type menuAccess as MenuAccessItem[]
const menuAccess = computed(() => usePage().props.menuAccess);

onMounted(() => {

    add.value = menuAccess.value.find(access => access.action_id == 2) ?? null;
    edit.value = menuAccess.value.find(access => access.action_id == 3) ?? null;
    deleted.value = menuAccess.value.find(access => access.action_id == 4) ?? null;

});


const add = ref(null);
const edit = ref(null);
const deleted = ref(null);

const loading = ref(false)
const title = ref('');
const status = ref('');
const description = ref('');
const fromDate = ref('');
const toDate = ref('');


const pageNumber = ref(props.videos?.current_page);

const breadcrumbs = ref([
    { title: 'Dashboard', href: '/' },
    { title: 'Videos', href: '/video/feature' }
]);


const visibleRight = ref(false);


const submit = () => {

    const formData = new FormData();
    formData.append('title', title.value);
    formData.append('description', description.value);
    formData.append('fromDate', fromDate.value);
    formData.append('toDate', toDate.value);
    formData.append('status', status.value);


    router.post('/video/feature/paginate/filters', formData, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        forceFormData: true,
    });

    visibleRight.value = false;
};


const goToPage = () => {
    if (pageNumber.value < 1) {
        pageNumber.value = 1;
    }

    router.get('/video/feature?page=' + pageNumber.value, {}, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

};



</script>

<template>

    <Head title="All Videos" />

    <AppLayout :breadcrumbs="breadcrumbs" :loading="loading">
        <Toast position="top-right" />
        <div class="w-full mx-auto p-2 flex flex-row place-content-center">
            <div className="w-5/6 mt-4 shadow-lg bg-white rounded-md pb-1">
                <div class="flex justify-between bg-gray-300 px-2 py-1 items-center rounded-t-md">
                    <div class="text-lg font-semibold">Video List</div>
                    <div class="flex">
                        <!-- <div :class="{ 'rounded-md': !add?.action_id }"
                            class="bg-sky-600 items-center p-2 flex px-4 rounded-l-md cursor-pointer"
                            @click="visibleRight = true">
                            <Icon icon="mdi:filter-outline" width="1.5rem" height="1.5rem" class="text-white" />
                            <div class="px-2 text-white text-lg ">Filter</div>

                        </div> -->

                        <!-- <Link v-if="add?.action_id" href="/video/feature/create"
                            class="bg-green-600 p-2 rounded-r-md text-lg px-3 flex text-black text-white">
                        <Icon icon="fluent:add-12-filled" class="mr-2" width="1.5rem" height="1.5erm" /> Add Video
                        </Link> -->
                    </div>
                </div>

                <!-- main content goes here -->

                <div class="px-3 h-[calc(100vh-13rem)] overflow-auto pb-3">
                    <div class="w-full border-t mt-4">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-600 text-white">
                                    <th>SL</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Video Link</th>
                                    <th>Button Text</th>
                                    <th>Status</th>
                                    <th>...</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(video, index) in Object.values(videos?.data)"
                                    class="odd:bg-white even:bg-gray-200 text-gray-800">
                                    <td class="text-center">{{ ((videos?.current_page - 1) * videos?.per_page) +
                                        (index + 1) }}</td>
                                    <td>{{ video?.title }}</td>
                                    <td>{{ video?.description }}</td>
                                    <td>{{ video?.video_link }}</td>
                                    <td>{{ video?.button_text }}</td>
                                    <td class="p-2">{{ (video?.status) ? 'Active' : 'Inactive' }}</td>
                                    <td class="flex gap-1 place-content-center">
                                        <Link v-if="edit?.action_id || checkPermission" :href="`/video/feature/${video?.id}/edit`">
                                        <Icon icon="icon-park-outline:pencil"
                                            class="bg-red-500 hover:bg-red-600 p-1 w-auto h-8 cursor-pointer text-white rounded-sm"
                                            width="1.3rem" />
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-between w-full px-3 py-2 border-t">

                    <div class="flex">
                        <input
                            class="ring-0 border border-r-0 ring-0 rounded-l-md p-1 px-3 focus:outline-none focus:ring-0"
                            v-model="pageNumber" type="number" />
                        <div class="bg-gray-100 p-2 rounded-r-md">
                            <Icon @click="goToPage" icon="nonicons:go-16" />
                        </div>
                    </div>
                    <div>
                        <!-- Pagination Component -->
                        <Pagination :links="videos?.links" />
                    </div>


                </div>

                <!-- main content goes here -->

                <Drawer v-model:visible="visibleRight" header="Video Feature Filter" position="right">
                    <form @submit.prevent="submit">
                        <div class="grid grid-col-1 mt-4">

                            <div class="card w-full justify-center mt-2">
                                <label for="dd-city" class="text-md w-full content-center font-semibold">Title</label>
                                <div class="card flex justify-center">
                                    <input v-model="title"
                                        class="w-full text-sm border py-1 px-2 rounded-md outline-none focus:border-green-200" />
                                </div>
                            </div>

                            <div class="card w-full justify-center">
                                <label for="dd-city" class="text-md w-full content-center font-semibold">Status</label>
                                <select v-model="status"
                                    class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            <div class="card w-full justify-center mt-2">
                                <label for="dd-city" class="text-md w-full content-center font-semibold">From
                                    Date</label>
                                <div class="card flex justify-center">
                                    <input v-model="fromDate" type="date"
                                        class="w-full border rounded-md outline-none focus:border-green-20 text-sm p-2" />
                                </div>
                            </div>

                            <div class="card w-full justify-center mt-2">
                                <label for="dd-city" class="text-md w-full content-center font-semibold">To Date</label>
                                <div class="card flex justify-center">
                                    <input v-model="toDate" type="date"
                                        class="w-full border rounded-md outline-none focus:border-green-20 text-sm p-2" />
                                </div>
                            </div>

                            <div class="card w-full justify-center mt-2">

                                <button type="submit"
                                    class="bg-green-600 py-1 px-4 font-semibold text-white rounded-sm flex cursor-pointer">Filter
                                    <Icon icon="fa7-solid:magnifying-glass" class="m-1 mr-2" />
                                </button>

                            </div>

                        </div>
                    </form>
                </Drawer>

            </div>

        </div>
    </AppLayout>
</template>
<style scoped>
table tr td,
th {
    padding: 4px;
    text-align: left;
}
</style>
