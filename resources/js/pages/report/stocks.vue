<script setup lang="ts">
    import AppLayout from '@/layouts/AppLayout.vue';
    import { Head, usePage, router } from '@inertiajs/vue3';
    import { computed, ref } from 'vue'
    import { Icon } from '@iconify/vue';
    import Drawer from 'primevue/drawer';
    import AutoComplete from 'primevue/autocomplete';
    import Currency from '@/components/Currency/index.vue';

    import Pagination from '@/components/Pagination.vue';

    const page = usePage();

    const queryString = computed(() => {
        const url = new URL(page.url, window.location.origin);
        return url.search;
    });



    const breadcrumbs = ref([
        { title: 'Reports', href: '/reports' },
        { title: 'Stocks', href: '/report/stock' },
    ]);

    const loading = ref(false);
    const visibleRight = ref(false);


    const props = defineProps({
        branchWiseStocks: Object,
        sizes: Object,
        colors: Array,
        branches: Object,
        categories: Object,
        append: Object,
    });


    const branches = ref([]);
    const categories = ref([]);
    const colors = ref([]);


    const name = ref(props.append?.name);
    // const toDate = ref(props.append?.toDate);
    // const fromDate = ref(props.append?.fromDate);
    const selectedCategory = ref(props.append?.category);
    const selectedColor = ref(props.append?.color);
    const selectedBranch = ref(props.append?.branch);





    const categorySearch = (event) => {

        const query = event.query.toLowerCase();
        categories.value = props.categories ? props.categories.filter(p => p.name.toLowerCase().includes(query)) : [];

    }

    const branchesSearch = (event) => {

        const query = event.query.toLowerCase();
        branches.value = props.branches ? props.branches.filter(p => p.name.toLowerCase().includes(query)) : [];
    }


    const colorSearch = (event) => {

        const query = event.query.toLowerCase();
        colors.value = props.colors ? props.colors.filter(p => p.color.toLowerCase().includes(query)) : [];
    }



    const submit = () => {

        loading.value = true;
        const filters = {
            name: name.value,
            category: selectedCategory.value,
            color: selectedColor.value,
            branch: selectedBranch.value,

        };

        router.get('/report/stock', filters, {
            preserveState: true,
            replace: true,

        });

        loading.value = false;
        visibleRight.value = false;

    };


    const reset = () => {

        loading.value = true;
        name.value = null;
        selectedCategory.value = null;
        selectedColor.value = null;
        const filters = {};

        router.get('/report/stock', filters, {
            preserveState: true,
            replace: true
        });

        loading.value = false;

    };






    const pageNumber = ref(props.branchWiseStocks?.current_page);

    // Go pagination
    const goToPage = () => {
        if (pageNumber.value < 1) {
            pageNumber.value = 1;
        }

        router.get('/report/stock?page=' + pageNumber.value, {}, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

    };


    // Get size quantity

    const getColumn = (size) => {
        return "size_" + size.replace(/[^a-zA-Z0-9]/g, "_")
    }

    // Get total quantity size wise
    const getTotal = (row) =>   {
            let total = 0;
            for (const key in row) {
                if (key.startsWith("size_")) {
                    total += Number(row[key]);
                }
            }

        return total;
    }

</script>
<template>
    <Head title="Stocks" />

    <AppLayout :breadcrumbs="breadcrumbs" :loading="loading">
        <div class="w-full mx-auto flex flex-row place-content-center">
            <div class="w-5/6 mt-4 shadow-lg bg-white rounded-md">
                <div class="flex justify-between bg-gray-300 px-2 py-1 items-center rounded-t-md">
                    <div class="text-lg font-semibold">Stocks</div>
                    <div class="flex">

                        <a v-if="queryString != ''" :href="`/report/stock/exports${queryString.replace(/^\?/, '?')}`" class="bg-green-600 items-center p-2 flex px-4 rounded-l-md cursor-pointer">
                            <Icon icon="file-icons:microsoft-excel" width="1.5rem" height="1.5rem" class="text-white"  />
                            <div class="px-2 text-white text-lg unded-md">Download </div>

                        </a>

                        <div :class="[queryString != '' ? 'rounded-r-md' : 'rounded-md' ]" class="bg-sky-600 items-center p-2 flex px-4 rounded-r-md cursor-pointer" @click="visibleRight = true">
                            <Icon icon="mdi:filter-outline" width="1.5rem" height="1.5rem" class="text-white"  />
                            <div class="px-2 text-white text-lg unded-md">Filter</div>

                        </div>
                    </div>
                </div>

                <div class="border rounded-b-md px-2 py-1 min-h-[calc(100vh-13rem)]">
                    <table class="table-fixed w-full [&>tbody>tr>td]:px-2 [&>tbody>tr>td]:py-1 [&>tbody>tr>td]:cursor-pointer [&>tbody>tr]:hover:bg-gray-300 [&>tbody>tr>td]:border [&>tbody>tr>td]:border-gray-400">
                        <thead>
                            <tr class="bg-gray-400">
                                <th class="text-center">SL</th>
                                <th class="text-center">Branch</th>
                                <th class="text-center">Category</th>
                                <th class="text-left px-2">Product Name</th>
                                <th class="text-left px-2">Product Code</th>
                                <th class="text-left w-12 px-2">Photo Link</th>
                                <th class="text-center">Color</th>
                                <th class="text-center">Regular Price</th>
                                <th class="text-center">Current Price</th>
                                <!-- <th class="text-center" v-for="size in props?.sizes" :key="size?.size">{{ size }}</th> -->
                                <th class="text-center">Total Quantity</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="(stock, index) in  Object.values(branchWiseStocks?.data)" :key="stock?.id">
                                <td class="text-center">{{ ((branchWiseStocks?.current_page - 1) * branchWiseStocks?.per_page) + (index + 1) }}</td>
                                <td>{{ stock?.branchs?.name }}</td>
                                <td>
                                    <span v-for="(category, idx) in stock?.product?.categories">
                                        {{ category.name }}<span v-if="idx !== stock.product.categories.length - 1">, </span>
                                    </span>
                                </td>
                                <td>{{ stock?.product?.name }}</td>
                                <td style="overflow-wrap: break-word; word-break: break-word; word-wrap: break-word;">{{ stock?.product?.sku }}</td>
                                <td class="w-12 !p-0 object-cover">
                                    <img :src="stock?.media?.color_icon_small
                                            ? `/products/${stock?.media?.color_icon_small}`
                                            : `/assets/sites/sample.webp`" />
                                </td>

                                <td>{{ stock?.color }} {{ stock?.size }}</td>
                                <td class="text-center"><Currency :amount="stock?.regular_price"/></td>
                                <td class="text-center"><Currency :amount="stock?.current_price"/></td>

                                <td>{{ getTotal(stock) }}</td>
                            </tr>

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
                        <Pagination :links="branchWiseStocks?.links" />
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
                            <AutoComplete v-model="selectedBranch" inputId="multiple-ac-1" optionLabel="name" size="small" inputClass="w-full text-sm" dropdown :suggestions="branches" @complete="branchesSearch"
                            />
                        </div>


                        <div class="flex flex-col col-span-2 w-full pt-1">
                            <label for="categories" class="text-sm w-full font-semibold">Category</label>
                            <AutoComplete v-model="selectedCategory" multiple inputId="multiple-ac-1" optionLabel="name" size="small" inputClass="w-full text-sm" dropdown :suggestions="categories" @complete="categorySearch"
                            />
                        </div>

                        <div class="flex flex-col col-span-2 w-full pt-1">
                            <label for="multiple-ac-1" class="block text-sm font-semibold">Color</label>
                            <AutoComplete v-model="selectedColor" optionLabel="color" size="small" inputClass="w-full text-sm" dropdown :suggestions="colors" @complete="colorSearch" />
                        </div>

                        <!-- <div class="card w-full justify-center mt-2">
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
                        </div> -->

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
