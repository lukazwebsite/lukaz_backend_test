<script setup lang="ts">
    import AppLayout from '@/layouts/AppLayout.vue';
    import { Head, router, usePage } from '@inertiajs/vue3';
    import { computed, ref } from 'vue'
    import { Icon } from '@iconify/vue';
    import Drawer from 'primevue/drawer';
    import AutoComplete from 'primevue/autocomplete';
    import { useDataDate } from '@/composables/useDataDate';

    import Pagination from '@/components/Pagination.vue';

    const { dayFunction } = useDataDate();

    const breadcrumbs = ref([
        { title: 'Reports', href: '/reports' },
        { title: 'Sale Reports', href: '/report/sale' },
    ]);

    const page = usePage();

    const queryString = computed(() => {
        const url = new URL(page.url, window.location.origin);
        return url.search;
    });

    const loading = ref(false);
    const visibleRight = ref(false);


    const props = defineProps({
        sales: Object,
        sizes: Object,
        colors: Array,
        branches: Object,
        categories: Object,
        append: Object,
    });

    const name = ref('');
    const fromDate = ref(props.append?.fromDate);
    const toDate = ref(props.append?.toDate);
    const branchItems = ref([]);
    const branchSelected = ref(null);

    const categoryItems = ref([]);
    const categorySelected = ref(props.append?.categories ? props.append.categories : null);

    const sizeItems = ref([]);
    const sizeSelected = ref(null);

    const categorySearch = (event) => {
        const query = event.query.toLowerCase();
        categoryItems.value = props.categories ? props.categories.filter(p => p.name.toLowerCase().includes(query)) : [];

    }

    const branchesSearch = (event) => {
        const query = event.query.toLowerCase();
        branchItems.value = props.branches ? props.branches.filter(p => p.name.toLowerCase().includes(query)) : [];
    }

    const sizeSearch = (event) => {
        const query = event.query.toLowerCase();
        sizeItems.value = props.sizes ? props.sizes.filter(p => p.size.toLowerCase().includes(query)) : [];
    }



    const pageNumber = ref(props.sales?.current_page);

    // Go pagination
    const goToPage = () => {
        if (pageNumber.value < 1) {
            pageNumber.value = 1;
        }

        router.get('/report/sale/extra?page=' + pageNumber.value, {}, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

    };


    const submit = () => {

        loading.value = true;
        const filters = {
            name: name.value,
            categories: categorySelected.value,
            sizes: sizeSelected.value,
            branch: branchSelected.value,
            fromDate: fromDate.value,
            toDate: toDate.value,

        };

        router.post('/report/sale/extra', filters, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            forceFormData: true,

        });


        loading.value = false;
        visibleRight.value = false;

    };


    const downloadExcel = () => {
    const params = new URLSearchParams();

    // 1. Simple text fields
    if (name.value) params.append('name', name.value);
    if (fromDate.value) params.append('fromDate', fromDate.value);
    if (toDate.value) params.append('toDate', toDate.value);

    // 2. PrimeVue AutoComplete Object (Branch)
    // We send it as branch[id] so Laravel sees it as an associative array
    if (branchSelected.value && branchSelected.value.id) {
        params.append('branch[id]', branchSelected.value.id);
    }

    // 3. PrimeVue AutoComplete Object (Size)
    if (sizeSelected.value && sizeSelected.value.size) {
        params.append('sizes[size]', sizeSelected.value.size);
    }

    // 4. PrimeVue Multi-Select AutoComplete (Categories)
    if (categorySelected.value && Array.isArray(categorySelected.value)) {
        categorySelected.value.forEach((cat, index) => {
            // This creates categories[0][id]=X, categories[1][id]=Y
            params.append(`categories[${index}][id]`, cat.id);
        });
    }

    params.append('excel', 'download');

    // Redirect to the download URL
    window.location.href = `/report/sale/extra?${params.toString()}`;
};






</script>
<template>
    <Head title="Sale Reports" />

    <AppLayout :breadcrumbs="breadcrumbs" :loading="loading">
        <div class="w-full mx-auto flex flex-row place-content-center">
            <div class="w-5/6 mt-4 shadow-lg bg-white rounded-md">
                <div class="flex justify-between bg-gray-300 px-2 py-1 items-center rounded-t-md">
                    <div class="text-lg font-semibold">Reports</div>
                    <div class="flex gap-2">
                        <div @click="downloadExcel" class="bg-green-600 items-center p-2 flex px-4 rounded-md cursor-pointer">
                            <Icon icon="file-icons:microsoft-excel" width="1.5rem" height="1.5rem" class="text-white"  />
                            <div class="px-2 text-white text-lg unded-md">Download </div>
                        </div>
                        <div :class="{'rounded-md': !add}" class="bg-sky-600 items-center p-2 flex px-4 rounded-md cursor-pointer" @click="visibleRight = true">
                            <Icon icon="mdi:filter-outline" width="1.5rem" height="1.5rem" class="text-white"  />
                            <div class="px-2 text-white text-lg unded-md">Filter</div>

                        </div>
                    </div>
                </div>

                <div class="border rounded-b-md px-2 py-1 min-h-[calc(100vh-13rem)]">
                    <table class="table-fixed w-full [&>tbody>tr>td]:px-2 [&>tbody>tr>td]:py-1 [&>tbody>tr>td]:cursor-pointer [&>tbody>tr]:hover:bg-gray-300 [&>tbody>tr>td]:border [&>tbody>tr>td]:border-gray-400">
                        <thead>
                            <tr class="bg-gray-400">
                                <th class="text-center">Date</th>
                                <th class="text-center w-1/10">Invoice No.</th>
                                <th class="text-left w-1/4 px-2">Product Name/Code</th>
                                <th class="text-center w-1/8">Category</th>
                                <th class="text-center">Size</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-center">Price</th>
                                <th class="text-center">Discount</th>
                                <th class="text-center">Payable</th>
                                <th class="text-center">Pay By</th>
                            </tr>
                        </thead>

                        <tbody>
                            <template v-for="(order, index) in Object.values(sales?.data)" :key="index">

                                <tr class="bg-gray-100 even:bg-white cursor-pointer hover:bg-gray-300" v-for="(item, iIndex) in Object.values(order?.items)" :key="item?.iIndex">
                                    <td v-if="iIndex === 0" class="text-center" :rowspan="(order?.items_count)">{{ dayFunction(order?.created_at) }}</td>
                                    <td v-if="iIndex === 0" class="text-center" :rowspan="(order?.items_count)">{{ order?.order_no }}</td>
                                    <td class="text-left">{{ item?.item_name }} | {{ item?.additional?.product?.sku }}</td>
                                    <td class="text-center">
                                        <span v-for="cate in item?.additional?.product?.categories" :key="cate?.id">{{ cate?.name }}</span>
                                    </td>
                                    <td class="text-center">{{ item?.size }}</td>
                                    <td class="text-center">{{ item?.quantity }}</td>
                                    <td class="text-center">{{ item?.regular_price }}</td>
                                    <td v-if="iIndex === 0" :rowspan="(order?.items_count)" class="text-center">{{ order?.discount ?? 0 }}</td>
                                    <td v-if="iIndex === 0" :rowspan="(order?.items_count)" class="text-center">{{ ((order?.total) - (order?.discount ?? 0)).toFixed() }}</td>
                                    <td v-if="iIndex === 0" class="text-center" :rowspan="(order?.items_count)">{{ order?.payment_method }}</td>

                                </tr>
                            </template>
                        </tbody>

                    </table>
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
                        <Pagination :links="sales?.links" />
                    </div>

                </div>
            </div>


            <Drawer v-model:visible="visibleRight" header="Filter Options" position="right">
                <form @submit.prevent="submit">
                    <div class="flex flex-col mt-4">

                        <div class="card w-full justify-center mt-2">
                            <label for="dd-city" class="text-smw-full content-center font-semibold">Filter By</label>
                            <div class="card flex justify-center">
                                <input v-model="name" class="w-full text-sm border border-gray-400 py-2 px-2 rounded-md outline-none focus:border-green-200" placeholder="Price,Name,Stock"/>
                            </div>
                            <div class="text-xs text-gray-400">Price,Name,Stock</div>
                        </div>


                        <div class="flex flex-col col-span-2 w-full pt-1">
                            <label for="categories" class="text-sm w-full font-semibold">Branch</label>
                            <AutoComplete v-model="branchSelected" inputId="multiple-ac-1" optionLabel="name" size="small" inputClass="w-full text-sm" dropdown :suggestions="branchItems" @complete="branchesSearch"
                            />
                        </div>


                        <div class="flex flex-col col-span-2 w-full pt-1">
                            <label for="categories" class="text-sm w-full font-semibold">Category</label>
                            <AutoComplete v-model="categorySelected" inputId="multiple-ac-1" multiple optionLabel="name" size="small" inputClass="w-full text-sm" dropdown :suggestions="categoryItems" @complete="categorySearch"
                            />
                        </div>

                        <div class="flex flex-col col-span-2 w-full pt-1">
                            <label for="multiple-ac-1" class="block text-sm font-semibold">Size</label>
                            <AutoComplete v-model="sizeSelected" optionLabel="size" size="small" inputClass="w-full text-sm" dropdown :suggestions="sizeItems" @complete="sizeSearch" />
                        </div>

                        <div class="card w-full justify-center mt-2">
                            <label for="dd-city" class="text-md w-full content-center font-semibold">From Date</label>
                            <div class="card flex justify-center">
                                <input v-model="fromDate" type="date" class="w-full border border-gray-400 rounded-md outline-none focus:border-green-20 text-sm p-2" />
                            </div>
                        </div>

                        <div class="card w-full justify-center mt-2">
                            <label for="dd-city" class="text-md w-full content-center font-semibold">To Date</label>
                            <div class="card flex justify-center">
                                <input v-model="toDate" type="date" class="w-full border border-gray-400 rounded-md outline-none focus:border-green-20 text-sm p-2" />
                            </div>
                        </div>

                        <div class="card w-full justify-center mt-2 flex gap-2">

                            <button type="submit" class="bg-green-500 hover:bg-green-700 py-1 px-4 font-semibold text-white rounded-sm flex w-full cursor-pointer items-center justify-center">Filter <Icon icon="fa7-solid:magnifying-glass"  class="m-1 mr-2"/></button>
                            <div @click="reset" class="bg-red-600 hover:bg-red-700 py-1 px-4 font-semibold text-white rounded-sm flex w-full cursor-pointer items-center justify-center">Reset <Icon icon="fa:refresh"  class="m-1 mr-2"/></div>

                        </div>

                    </div>
                </form>
            </Drawer>


        </div>

    </AppLayout>
</template>
