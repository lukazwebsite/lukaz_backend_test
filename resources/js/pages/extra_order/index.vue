<script setup lang="ts">
    import AppLayout from '@/layouts/AppLayout.vue';
    import { Head, usePage, Link, router } from '@inertiajs/vue3';
    import { computed, onMounted, ref } from 'vue'
    import Currency from '@/components/Currency/index.vue';
    import { Icon } from '@iconify/vue';
    import Drawer from 'primevue/drawer';
    import AutoComplete from 'primevue/autocomplete';

    import { useDataDate } from '@/composables/useDataDate';


    import Pagination from '@/components/Pagination.vue';



    const props = defineProps({
        orders: Object,
        branches: Object,
        status: Object,
        filters: Object,
        categories: Array,
        brands: Array,
        sizes: Array,
        colors: Array,
        append: Array
    });


const { dateFunction, dateMonthFunction } = useDataDate();
const breadcrumbs = ref([
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Orders', href: '/orders' },
]);

const page = usePage()
const user = page.props.auth.user
const loading = ref(false);
const visibleRight = ref(false);

const add = ref(null);
const edit = ref(null);
const deleted = ref(null);


const brands = ref([]);
const categories = ref([]);
const sizes = ref([]);
const colors = ref([]);
const branches = ref([]);



const status_id = ref(props.append?.status)
const order_type = ref(props.append?.order_type ?? '');
const name = ref(props.append?.name);
const toDate = ref(props.append?.toDate);
const fromDate = ref(props.append?.fromDate);
const selectedBrand = ref(props.append?.brand);
const selectedCategory = ref(props.append?.category);
const selectedColor = ref(props.append?.color);
const selectedSize = ref(props.append?.size);
const selectedBranch = ref(props.append?.branch);

const orderNo = "Order Number ls-20000"
const pageNumber = ref(props.orders?.current_page);

const colorClassMap = {
    1: 'text-orange-500',
    2: 'text-blue-300',
    3: 'text-blue-400',
    4: 'text-blue-500',
    5: 'text-blue-600',
    7: 'text-red-700',
    8: 'text-green-500',
    6: 'text-red-600'
};



// Explicitly type menuAccess as MenuAccessItem[]
const menuAccess = computed(() => usePage().props.menuAccess);

onMounted(() => {

    add.value = menuAccess.value.find(access => access.action_id == 2) ?? null;
    edit.value = menuAccess.value.find(access => access.action_id == 3) ?? null;
    deleted.value = menuAccess.value.find(access => access.action_id == 4) ?? null;
});

const categorySearch = (event) => {

    const query = event.query.toLowerCase();
    categories.value = props.categories ? props.categories.filter(p => p.name.toLowerCase().includes(query)) : [];

}

const brandSearch = (event) => {

    const query = event.query.toLowerCase();
    brands.value = props.brands ? props.brands.filter(p => p.name.toLowerCase().includes(query)) : [];
}


const colorSearch = (event) => {

    const query = event.query.toLowerCase();
    colors.value = props.colors ? props.colors.filter(p => p.color.toLowerCase().includes(query)) : [];
}


const sizeSearch = (event) => {

    const query = event.query.toLowerCase();
    sizes.value = props.sizes ? props.sizes.filter(p => p.size.toLowerCase().includes(query)) : [];
}

const branchSearch = (event) => {

    const query = event.query.toLowerCase();
    branches.value = props.branches ? props.branches.filter(p => p.name.toLowerCase().includes(query)) : [];
}



// Get filter data


const submit = () => {

    loading.value = true;

    const filters = {
        name: name.value,
        category: selectedCategory.value,
        brand: selectedBrand.value,
        branch: selectedBranch.value,
        color: selectedColor.value?.color,
        size: selectedSize.value?.size,
        status: status_id.value,
        order_type: order_type.value,
        fromDate: fromDate.value,
        toDate: toDate.value,
    };

    router.get('/order/paginate/filters/extra', filters, {
        preserveState: true,
        replace: true,
        only: ['orders'], // fetch only updated orders
        onFinish: () => {
            loading.value = false;
        },
    });

};

// Get filter data


const refresh = () => {

    loading.value = true;

    const filters = {};

    router.get('/order/paginate/filters/extra', filters, {
        preserveState: true,
        replace: true,
        only: ['orders'], // fetch only updated orders
        onFinish: () => {
            loading.value = false;
        },
    });

};



// Go pagination


const goToPage = () => {
    if (pageNumber.value < 1) {
        pageNumber.value = 1;
    }

    router.get('/orders/extra?page=' + pageNumber.value, {}, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

};


const orderTypeMap = {
  0: 'Pre Order',
  1: 'Regular',
  2: 'POS',
  3: 'Manual'
};





</script>
<template>
    <Head title="Order" />

    <AppLayout :breadcrumbs="breadcrumbs" :loading="loading">
        <Toast position="top-right" />
        <div class="w-full mx-auto p-2 flex flex-row place-content-center">
            <div class="w-5/6 mt-4 shadow-lg bg-white rounded-md">
                <div class="flex justify-between bg-gray-300 px-2 py-1 items-center rounded-t-md">
                    <div class="text-lg font-semibold">Order List</div>
                    <div class="flex">
                        <div :class="{'rounded-md': !add}" class="bg-sky-600 items-center p-2 flex px-4 rounded-md cursor-pointer" @click="visibleRight = true">
                            <Icon icon="mdi:filter-outline" width="1.5rem" height="1.5rem" class="text-white"  />
                            <div class="px-2 text-white text-lg unded-md">Filter</div>

                        </div>
                    </div>
                </div>

                <div class="border rounded-b-md px-2 py-1 min-h-[calc(100vh-13rem)]">
                    <table class="table-fixed w-full">
                       <tr class="bg-gray-400">
                                <th class="w-16">SL</th>
                                <th class="text-left">Order No</th>
                                <th class="text-left">Name / Contact</th>
                                <th class="text-left">Items</th>
                                <th class="text-left">Qty</th>
                                <th class="text-left">Total Amount</th>
                                <th>Order Type</th>
                                <th>Status</th>
                                <th>...</th>
                            </tr>

                            <tr v-for="(order, index) in Object.values(orders?.data)" class="bg-gray-100 even:bg-white cursor-pointer hover:bg-gray-300" :class="colorClassMap[order.status.id]" :key="index">
                                <td class="text-center">{{ ((orders?.current_page - 1) * orders?.per_page) + (index + 1) }}</td>
                                <td>
                                    <Link :href="`/orders/${order.order_no}/view/extra`" class="hover:text-blue-700 text-blue-400">
                                        {{ order?.order_no }}
                                        <br/>
                                        <span class="text-xs text-gray-500">{{ dateFunction(order?.created_at) }}</span>
                                    </Link>
                                </td>
                                <td>{{ order?.user?.name }} <br/> {{ order?.user?.mobile }}</td>
                                <td>{{ order?.items_count }}</td>
                                <td>{{ order?.quantity }}</td>
                                <td class="text-right">
                                    <Currency :amount="order?.grand_total"/>
                                </td>

                                <td class="text-center">{{ orderTypeMap[order?.order_type] }}</td>
                                <td class="text-center">{{ order?.status?.name }}</td>
                                <td class="text-center p-1">
                                    <div class="flex gap-2">
                                        <div class="bg-sky-600 p-2 rounded-sm">
                                            <Link :href="`/orders/${order.order_no}/print/extra`"><Icon icon="subway:print" class="text-white" width="1rem" height="1erm"/></Link>
                                        </div>
                                         <div class="bg-green-600 p-2 rounded-sm">
                                            <Link :href="`/orders/${order.order_no}/view/extra`"><Icon icon="mdi:eye" class="text-white" width="1rem" height="1erm"/></Link>

                                        </div>
                                    </div>

                                </td>
                            </tr>
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
                        <Pagination :links="orders?.links" />
                    </div>

                </div>

            </div>


            <Drawer v-model:visible="visibleRight" header="Filter Options" position="right">
                <form @submit.prevent="submit">
                    <div class="flex flex-col mt-4">

                        <div class="card w-full justify-center mt-2">
                            <label for="dd-city" class="text-smw-full content-center font-semibold">Filter By</label>
                            <div class="card flex justify-center">
                                <input v-model="name" class="w-full text-sm border border-gray-400 py-2 px-2 rounded-md outline-none focus:border-green-200" placeholder="Price,Name,Order No"/>
                            </div>
                            <div class="text-xs text-gray-400">Price,Name,Order No</div>
                        </div>

                        <div class="card w-full justify-center">
                            <label for="dd-city" class="text-sm w-full content-center font-semibold">Status</label>
                            <select v-model="status_id" class="w-full text-md border border-gray-400 py-2 px-2 outline-none focus:border-green-200 rounded-md">
                                <option value="">Select Status</option>
                                <option v-for="sta in status" :value=sta.id class="text-red-500">{{ sta.name }}</option>
                            </select>
                        </div>

                        <div class="card w-full justify-center">
                            <label for="dd-city" class="text-sm w-full content-center font-semibold">Order Type</label>
                            <select v-model="order_type" class="w-full text-md border border-gray-400 py-2 px-2 outline-none focus:border-green-200 rounded-md">
                                <option value="">Select Order Type</option>
                                <option value="0">Pre Order</option>
                                <option value="1">Regular</option>

                            </select>
                        </div>


                        <div class="flex flex-col col-span-2 w-full pt-1">
                            <label for="multiple-ac-1" class="block text-sm font-semibold">Branch</label>
                            <AutoComplete v-model="selectedBranch" dropdown :suggestions="branches" optionLabel="name" size="small" inputClass="w-full text-sm border-gray-400" @complete="branchSearch"/>
                        </div>

                        <div class="flex flex-col col-span-2 w-full pt-1">
                            <label for="multiple-ac-1" class="block text-sm font-semibold">Brand</label>
                            <AutoComplete v-model="selectedBrand" dropdown :suggestions="brands" optionLabel="name" size="small" inputClass="w-full text-sm border-gray-400" @complete="brandSearch"/>
                        </div>


                        <div class="flex flex-col col-span-2 w-full pt-1">
                            <label for="categories" class="text-sm w-full font-semibold">Category</label>
                            <AutoComplete v-model="selectedCategory" inputId="multiple-ac-1" optionLabel="name" size="small" inputClass="w-full text-sm" dropdown :suggestions="categories" @complete="categorySearch"
                            />
                        </div>


                        <div class="flex flex-col col-span-2 w-full pt-1">
                            <label for="categories" class="text-sm w-full font-semibold">Size</label>
                            <AutoComplete v-model="selectedSize" optionLabel="size" size="small" inputClass="w-full text-sm" dropdown :suggestions="sizes" @complete="sizeSearch" />
                        </div>

                        <div class="flex flex-col col-span-2 w-full pt-1">
                            <label for="multiple-ac-1" class="block text-sm font-semibold">Color</label>
                            <AutoComplete v-model="selectedColor" optionLabel="color" size="small" inputClass="w-full text-sm" dropdown :suggestions="colors" @complete="colorSearch" />
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
                            <div @click="refresh" class="bg-red-600 hover:bg-red-700 py-1 px-4 font-semibold text-white rounded-sm flex w-full cursor-pointer items-center justify-center">Reset <Icon icon="fa:refresh"  class="m-1 mr-2"/></div>

                        </div>

                    </div>
                </form>
            </Drawer>

        </div>

    </AppLayout>
</template>
