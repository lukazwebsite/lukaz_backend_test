<script setup lang="ts">

    import AppLayout from '@/layouts/AppLayout.vue';
    import { Head, Link, usePage, router } from '@inertiajs/vue3';
    import { computed, onMounted, ref } from 'vue'
    import { Icon } from '@iconify/vue';
    import Drawer from 'primevue/drawer';
    import Currency from '@/components/Currency/index.vue';
    import Pagination from '@/components/Pagination.vue';
    import { useToast } from "primevue/usetoast";
    import { useDataDate } from '@/composables/useDataDate';

    const { dateFunction, dateMonthFunction } = useDataDate();



    const page = usePage()
    const toast = useToast();
    const user = page.props.auth.user
    const add = ref({});
    const edit = ref();
    const deleted = ref(null);
    const loading = ref(false)

    const props = defineProps({
        couriers: Object,
        append: Array
    });



    // Explicitly type menuAccess as MenuAccessItem[]
    const menuAccess = computed(() => usePage().props.menuAccess);

    onMounted(() => {

        add.value = menuAccess.value.find(access => access.action_id == 2) ?? null;
        edit.value = menuAccess.value.find(access => access.action_id == 3) ?? null;
        deleted.value = menuAccess.value.find(access => access.action_id == 4) ?? null;

    });



    const name = ref(props.append?.name);
    const fromDate = ref(props.append?.fromDate);
    const toDate = ref(props.append?.toDate);

    const pageNumber = ref(props.couriers?.current_page);

    const breadcrumbs = ref([
        { title: 'Dashboard', href: '/' },
        { title: 'Courier Charge', href: '/courier' }
    ]);


    const visibleRight = ref(false);


    const submit = () => {

        const filter = {
            name : name.value,
            fromDate : fromDate.value,
            toDate : toDate.value
        }

        router.get('/courier/paginate/filters', filter, {
            preserveState: true,
            replace: true
        });

    };




    const goToPage = () => {
        if (pageNumber.value < 1) {
            pageNumber.value = 1;
        }

        const filter = {
            name : name.value,
            fromDate : fromDate.value,
            toDate : toDate.value
        }

        router.get('/courier?page=' + pageNumber.value, filter, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

    };

</script>

<template>

    <Head title="Courier Charge List" />

    <AppLayout :breadcrumbs="breadcrumbs" :loading="loading">
        <Toast position="top-right" />
        <div class="w-full mx-auto p-2 flex flex-row place-content-center">
            <div className="w-5/6 mt-4 shadow-lg bg-white rounded-md pb-1">
                <div class="flex justify-between bg-gray-300 px-2 py-1 items-center rounded-t-md">
                    <div class="text-lg font-semibold">Courier Charge List</div>
                    <div class="flex">
                        <div class="bg-sky-600 items-center p-2 flex px-4 rounded-md cursor-pointer" @click="visibleRight = true">
                            <Icon icon="mdi:filter-outline" width="1.5rem" height="1.5rem" class="text-white"  />
                            <div class="px-2 text-white text-lg ">Filter</div>

                        </div>
                    </div>
                </div>

                <!-- main content goes here -->
                <div class="px-3 h-[calc(100vh-13rem)] overflow-auto pb-3">
                    <div class="w-full border-t mt-4">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-600 text-white">
                                    <th>SL</th>
                                    <th>Name</th>
                                    <th>Charge</th>
                                    <th>Created at</th>
                                    <th>Updated at</th>
                                    <th class="w-16">...</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr class="even:bg-gray-200 text-gray-600" v-for="(courier, index) in Object.values(couriers?.data)">
                                    <td>{{ ((couriers?.current_page - 1) * couriers?.per_page) + (index + 1) }}</td>
                                    <td>{{ courier?.name }}</td>
                                    <td> <Currency :amount="courier?.courier_charge"/> </td>
                                    <td> {{ dateFunction(courier?.created_at) }} </td>
                                    <td> {{ dateFunction(courier?.updated_at) }} </td>
                                    <td>
                                        <div v-if="edit?.action_id || (user.role_id == 1)"><Link class="bg-red-500 hover:bg-red-600 px-2 text-white rounded-sm flex items-center font-lg py-1" title="Edit" :href="`/courier/${courier.id}/edit`"><Icon icon="icon-park-outline:pencil" width="1.3rem"/></Link></div>
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
                            <Icon @click="goToPage" icon="nonicons:go-16"/>
                        </div>
                    </div>
                    <div>
                        <!-- Pagination Component -->
                        <Pagination :links="couriers?.links" />
                    </div>


                </div>

                <!-- main content goes here -->

                <Drawer v-model:visible="visibleRight" header="Filter Options" position="right">
                    <form @submit.prevent="submit">
                        <div class="grid grid-col-1 mt-4">

                            <div class="card w-full justify-center mt-2">
                                <label for="dd-city" class="text-md w-full content-center font-semibold">Name</label>
                                <div class="card flex justify-center">
                                    <input v-model="name" class="w-full text-sm border py-1 px-2 rounded-md outline-none focus:border-green-200" />
                                </div>
                            </div>


                            <div class="card w-full justify-center mt-2">
                                <label for="dd-city" class="text-md w-full content-center font-semibold">From Date</label>
                                <div class="card flex justify-center">
                                    <input v-model="fromDate" type="date" class="w-full border rounded-md outline-none focus:border-green-20 text-sm p-2" />
                                </div>
                            </div>

                            <div class="card w-full justify-center mt-2">
                                <label for="dd-city" class="text-md w-full content-center font-semibold">To Date</label>
                                <div class="card flex justify-center">
                                    <input v-model="toDate" type="date" class="w-full border rounded-md outline-none focus:border-green-20 text-sm p-2" />
                                </div>
                            </div>

                            <div class="card w-full justify-center mt-2">

                                <button type="submit" class="bg-green-600 py-1 px-4 font-semibold text-white rounded-sm flex cursor-pointer">Filter <Icon icon="fa7-solid:magnifying-glass"  class="m-1 mr-2"/></button>

                            </div>

                        </div>
                    </form>
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
