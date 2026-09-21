<script setup lang="ts">

    import AppLayout from '@/layouts/AppLayout.vue';
    import { Head, Link, usePage, router } from '@inertiajs/vue3';
    import { computed, ref } from 'vue'
    import { Icon } from '@iconify/vue';
    import Drawer from 'primevue/drawer';
    import Pagination from '@/components/Pagination.vue';
    import { useToast } from "primevue/usetoast";
    import { useDataDate } from '@/composables/useDataDate';
    import axios from 'axios';

    import AutoComplete from 'primevue/autocomplete';

    const page = usePage()
    const { dateFunction } = useDataDate();


    const queryString = computed(() => {
        const url = new URL(page.url, window.location.origin);
        return url.search;
    });

    const user = page.props.auth.user

    const props = defineProps({
        additionals: Object,
        transfers: Object,
        branchs: Object,
        colors: Array,
        sizes: Array,
        append: Object,
    });



    const toast = useToast();
    const loadingStatus = ref<Record<number, boolean>>({});
    const loading = ref(false);
    const pageNumber = ref(props?.transfers?.current_page);

    const breadcrumbs = ref([
        { title: 'Dashboard', href: '/' },
        { title: 'Product', href: '/products' },
        { title: 'Additional', href: '' },
    ]);

    const sizes = ref([]);
    const colors = ref([]);

    const name = ref(props?.append?.name);
    const fromBranchId = ref(props?.append?.fromBranchId);
    const toBranchId = ref(props?.append?.toBranchId);
    const toDate = ref(props?.append?.toDate);
    const fromDate = ref(props?.append?.fromDate);
    const selectedColor = ref(props.append?.color);
    const selectedSize = ref(props.append?.size);


    const visibleRight = ref(false);


    const colorSearch = (event) => {
        const query = event.query.toLowerCase();
        colors.value = props.colors ? props.colors.filter(p => p.color.toLowerCase().includes(query)) : [];
    }


    const sizeSearch = (event) => {

        const query = event.query.toLowerCase();
        sizes.value = props.sizes ? props.sizes.filter(p => p.size.toLowerCase().includes(query)) : [];
    }




    const goToPage = () => {
        if (pageNumber.value < 1) {
            pageNumber.value = 1;
        }

        router.get('/stock_transfer?page=' + pageNumber.value, {}, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

    };


    // Status Update function goes here

    const updateStatus = (id: number, status: number) => {

        loadingStatus.value[id] = true;

        axios.post(`/stock_transfer/${id}`,

            {
                status: status
            }
        )

            .then(res => {

                if(res.data.success){
                    toast.add({ severity: 'success', summary: 'Success', detail: res.data.message, life: 3000 });
                }else{

                    toast.add({ severity: 'error', summary: 'Error', detail: res.data.message, life: 3000 });
                }

            })
            .catch(err => {
                toast.add({ severity: 'error', summary: 'Error', detail: err.response.data.message, life: 3000 });
            })
            .finally(() => {

                loadingStatus.value[id] = false;

                router.get(`/stock_transfer?${queryString?.value.replace(/^\?/, '?')}&page=${pageNumber.value}`, {},
                    {
                        preserveScroll: true,
                        preserveState: true,
                        only: ['transfers'], // 👈 ONLY reload data
                    }
                );
            });
    }


    // Fileter function goes here

    const submit = () => {

        loading.value  = true;
        const filters = {
            name: name.value,
            fromBranchId: fromBranchId.value,
            toBranchId: toBranchId.value,
            color: selectedColor.value,
            size: selectedSize.value,
            fromDate: fromDate.value,
            toDate: toDate.value,
        };

        router.get('/stock_transfer', filters, {
            preserveState: true,
            replace: true
        });

        loading.value = false;
    };



</script>

<template>

    <Head title="Product Additional" />

    <AppLayout :breadcrumbs="breadcrumbs" :loading="loading">
        <Toast position="top-right" />
        <div class="w-full mx-auto p-2 flex flex-row place-content-center">
            <div className="w-5/6 mt-4 shadow-lg bg-white rounded-md pb-1">
                <div class="flex justify-between bg-gray-300 px-2 py-1 items-center rounded-t-md">
                    <div class="text-lg font-semibold">Transfer / Request Product List</div>
                    <div class="flex">
                        <div class="bg-sky-600 items-center p-2 flex px-4 rounded-l-md cursor-pointer" @click="visibleRight = true">
                            <Icon icon="mdi:filter-outline" width="1.5rem" height="1.5rem" class="text-white"  />
                            <div class="px-2 text-white text-lg ">Filter</div>

                        </div>

                        <Link href="/stock_transfer/create" class="bg-green-600 p-2 rounded-r-md text-lg px-3 flex text-black text-white">
                            <Icon icon="material-symbols:delivery-truck-speed" class="mr-2" width="1.5rem" height="1.5erm"/> Stock Transfer </Link>
                    </div>
                </div>

                <!-- main content goes here -->

                <div class="border px-3 h-[calc(100vh-13rem)] overflow-auto pb-3">

                    <div class="w-full border-t mt-4">
                        <table class="w-full">
                            <thead>

                                <tr class="bg-gray-600 text-white">
                                    <th>SL</th>
                                    <th>Product Name</th>
                                    <th>SKU</th>
                                    <th>From Branch</th>
                                    <th>To Branch</th>
                                    <th>Color & Size</th>
                                    <th>Transfer Request</th>
                                    <th>Status</th>
                                    <th>Date & Time</th>
                                    <th>...</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">

                                <!-- 'fromBranch', 'toBranch', 'product' -->

                                <tr v-for="(tran, index ) in Object.values(transfers?.data)" class="odd:bg-white even:bg-gray-200 text-gray-800 " :class="tran?.status == 5 ? 'text-red-500': ''">
                                    <td>{{ ((transfers?.current_page - 1) * transfers?.per_page) + (index + 1) }}</td>
                                    <td>
                                        {{ tran?.product?.name }} || {{ tran?.avaiable_stock }}
                                    </td>
                                    <td>{{ tran?.sku }}</td>
                                    <td>{{ tran?.from_branch?.name }}</td>
                                    <td>{{ tran?.to_branch?.name  }}</td>
                                    <td>Color: {{ tran?.color }} | Size: {{ tran?.size }}</td>
                                    <td class="p-2">{{ tran?.transfer_request }}</td>
                                    <td class="p-2">
                                        <p v-if="tran?.status == 1"> Request</p>

                                        <p v-else-if="tran?.status == 3"> Approved</p>
                                         <p v-else-if="tran?.status == 2"> Send</p>
                                       <p v-else-if="tran?.status == 4"> Received</p>
                                        <p v-else> Cancel</p>

                                    </td>
                                    <td>{{ dateFunction(tran?.created_at) }}</td>
                                    <td class="flex gap-1 place-content-center" >
                                        <template v-if="loadingStatus[tran.id]">
                                            <Icon icon="eos-icons:loading" class="animate-spin text-gray-500 w-8 h-8 p-1 rounded-sm "/>
                                        </template>
                                        <template v-else>
                                            <div v-if="tran?.status == 1" class="flex gap-1">

                                                <Icon icon="pepicons-pop:times" title="Remove" class="content-center cursor-pointer bg-red-500 w-8 h-8 p-1 text-white rounded-sm " @click="updateStatus(tran?.id, 5)"/>
                                                <Icon icon="hugeicons:tick-01" title="Approved" class="content-center cursor-pointer bg-sky-500 w-8 h-8 p-1 text-white rounded-sm " @click="updateStatus(tran?.id, 3)"/>

                                            </div>
                                            <div v-else-if="(tran?.status == 2 && user?.branch_id == tran?.to_branch_id) || (user?.role_id == 1 && tran?.status < 4  ) " class="flex gap-1">

                                                <Icon icon="ic:round-download-done" title="Recevied" class="content-center cursor-pointer bg-green-500 w-8 h-8 p-1 text-white rounded-sm "  @click="updateStatus(tran?.id, 4)"/>

                                            </div>
                                            <div v-else-if="tran?.status == 3" class="flex gap-1">

                                                <Icon icon="streamline:send-email-solid" title="Approved" class="content-center cursor-pointer bg-sky-500 w-8 h-8 p-1 text-white rounded-sm " @click="updateStatus(tran?.id, 2)"/>

                                            </div>
                                            <div v-else-if="tran?.status == 4" class="flex gap-1">
                                                ---
                                            </div>
                                            <div v-else>
                                                ---
                                            </div>
                                        </template>
                                    </td>
                                </tr>

                            </tbody>

                        </table>
                    </div>

                </div>
                    <div class="flex justify-between w-full p-1 mt-2">
                            <div class="flex">
                                <input class="ring-0 border border-r-0 ring-0 rounded-l-md p-1 px-3 focus:outline-none focus:ring-0" v-model="pageNumber" type="number"/>
                                <div class="bg-gray-100 p-2 rounded-r-md">
                                    <Icon @click="goToPage" icon="nonicons:go-16"/>
                                </div>
                            </div>
                        <div>
                            <!-- Pagination Component -->
                            <Pagination :links="transfers.links" />
                        </div>

                    </div>

                <!-- main content goes here -->

                <Drawer v-model:visible="visibleRight" header="Transfer Filter" position="right">
                    <div class="grid grid-col-1 mt-4">

                        <div class="card w-full justify-center mt-2">
                            <label for="dd-city" class="text-md w-full content-center font-semibold">Product</label>
                            <div class="card flex justify-center">
                                <input v-model="name" class="w-full text-sm border py-1 px-2 rounded-md outline-none focus:border-green-200" />
                            </div>
                        </div>

                        <div class="card w-full justify-center">
                            <label for="dd-city" class="text-md w-full content-center font-semibold">Stock From</label>
                            <select v-model="fromBranchId" class="w-full border py-1 px-2 rounded-md outline-none focus:border-green-200">
                                <option value="">---</option>
                                <option v-for="branch in branchs" :value="branch?.id" :key="branch?.id">{{ branch?.name }}</option>
                            </select>
                        </div>

                        <div class="card w-full justify-center mt-2">
                            <label for="dd-city" class="text-md w-full content-center font-semibold">Stock To</label>
                            <select v-model="toBranchId" class="w-full border py-1 px-2 rounded-md outline-none focus:border-green-200">
                                <option value="">---</option>
                                <option v-for="branch in branchs" :value="branch?.id" :key="branch?.id">{{ branch?.name }}</option>
                            </select>
                        </div>

                        <div class="card w-full justify-center mt-2">
                            <div class="flex flex-col col-span-2 w-full pt-1">
                                <label for="categories" class="text-sm w-full font-semibold">Size</label>
                                <AutoComplete v-model="selectedSize" optionLabel="size" size="small" inputClass="w-full text-sm" dropdown :suggestions="sizes" @complete="sizeSearch" />
                            </div>
                        </div>

                        <div class="card w-full justify-center mt-2">
                            <div class="flex flex-col col-span-2 w-full pt-1">
                                <label for="multiple-ac-1" class="block text-sm font-semibold">Color</label>
                                <AutoComplete v-model="selectedColor" optionLabel="color" size="small" inputClass="w-full text-sm" dropdown :suggestions="colors" @complete="colorSearch" />
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

                            <button @click="submit" class="bg-green-600 py-1 px-4 font-semibold text-white rounded-sm flex cursor-pointer">Filter <Icon icon="fa7-solid:magnifying-glass"  class="m-1 mr-2"/></button>

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
