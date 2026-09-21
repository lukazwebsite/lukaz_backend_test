<script setup lang="ts">
    import AppLayout from '@/layouts/AppLayout.vue';
    import { Head, usePage, Link, router } from '@inertiajs/vue3';
    import { computed, onMounted, ref } from 'vue'
    import Currency from '@/components/Currency/index.vue';
    import { useDataDate } from '@/composables/useDataDate';
    import { Icon } from '@iconify/vue';
    import Drawer from 'primevue/drawer';
    import Dialog from 'primevue/dialog';

    import AutoComplete from 'primevue/autocomplete';
    const { dateFunction, dateMonthFunction } = useDataDate();


    import Pagination from '@/components/Pagination.vue';
    import { useToast } from "primevue/usetoast";

    const toast = useToast();

    const breadcrumbs = ref([
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'International', href: '/international' },
    ]);

    const props = defineProps({
        internationals: Object,
        single: Object,
        status: Object,
        filters: Object,
        countries: Array,
        brands: Array,
        sizes: Array,
        colors: Array,
        append: Array
    });

    const page = usePage()
    const user = page.props.auth.user
    const currentUrl = ref(page.url);
    const loading = ref(false);
    const visibleRight = ref(false);
    const pageNumber = ref(props.internationals?.current_page);
    const description = ref();
    const selectStatus = ref();

    const brands = ref([]);
    const countries = ref([]);
    const sizes = ref([]);
    const colors = ref([]);

    const status_id = ref(props.append?.status)
    const name = ref(props.append?.name);
    const toDate = ref(props.append?.toDate);
    const fromDate = ref(props.append?.fromDate);
    const selectedBrand = ref(props.append?.brand);
    const selectedCategory = ref(props.append?.category);
    const selectedColor = ref(props.append?.color);
    const selectedSize = ref(props.append?.size);

    const goToPage = () => {
    if (pageNumber.value < 1) {
        pageNumber.value = 1;
    }

    router.get('/orders?page=' + pageNumber.value, {}, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        onFinish: () => {
            loading.value = false;
        },
    });

};


const countrySearch = (event) => {

    const query = event.query.toLowerCase();
    countries.value = props.countries ? props.countries.filter(p => p.name.toLowerCase().includes(query)) : [];

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


const position = ref('center');
const visible = ref(false);

const openPosition = (orderNumber, pos) => {
    loading.value = true;
    router.get('/international/' + orderNumber+'/show', {}, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        preserveState: true,
        replace: true,
        only: ['single', 'status'], // fetch only updated single
        onFinish: () => {

            selectStatus.value = props.single.status
            description.value = props.single?.description
            loading.value = false;
            position.value = pos;
            visible.value = true;
        },
    });
}


const addNote = (orderNumber) => {
    loading.value = true;

    // const formData = new FormData();
    // formData.append('name', name.value);

    const notes = ref({
        status: selectStatus.value,
        description: description.value,
    })

    router.post('/international_order/' + orderNumber, notes.value, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        forceFormData: true,
        only: ['single', 'status'], // fetch only updated single
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Success Message', detail: orderNumber+' Order Status Updated!', life: 3000 });
            visible.value = false;
            loading.value = false;
            refresh();
        },
        onError: (e) => {
            toast.add({ severity: 'error', summary: 'Error Message', detail: 'Oops! Something wrongs', life: 3000 });
            loading.value = false;

        },
    });
}


// Get filter data
const refresh = () => {

    loading.value = true;

    console.log("hello");

    const filters = {};

    router.get('/international_order', filters, {
        preserveState: true,
        replace: true,
        only: ['internationals'], // fetch only updated orders
        onFinish: () => {
            loading.value = false;
        },
    });

};


const submit = () => {

    loading.value = true;

    const filters = {
        name: name.value,
        country: selectedCategory.value?.id,
        brand: selectedBrand.value?.id,
        color: selectedColor.value?.color,
        size: selectedSize.value?.size,
        status: status_id.value,
        fromDate: fromDate.value,
        toDate: toDate.value,
    };

    router.get('/international_order', filters, {
        preserveState: true,
        replace: true,
        only: ['internationals'], // fetch only updated orders
        onFinish: () => {
            loading.value = false;
        },
    });

};


</script>

<template>
    <Head title="Order" />

    <AppLayout :breadcrumbs="breadcrumbs" :loading="loading">
        <Toast position="top-right" />
        <div class="w-full mx-auto p-2 flex flex-row place-content-center">
            <div class="w-5/6 mt-4 shadow-lg bg-white rounded-md">
                <div class="flex justify-between bg-gray-300 px-2 py-1 items-center rounded-t-md">
                    <div class="text-lg font-semibold">International Order List</div>
                    <div class="flex">
                        <div :class="{'rounded-md': !add}" class="bg-sky-600 items-center p-2 flex px-4 rounded-md cursor-pointer" @click="visibleRight = true">
                            <Icon icon="mdi:filter-outline" width="1.5rem" height="1.5rem" class="text-white"  />
                            <div class="px-2 text-white text-lg unded-md">Filter</div>

                        </div>
                    </div>
                </div>

                <div class="border rounded-b-md px-2 py-1 min-h-[calc(100vh-13rem)]">
                    <table class="table-fixed w-full">
                        <thead>
                            <tr class="bg-gray-400">
                                <th class="w-16">SL</th>
                                <th class="text-left">Order No</th>
                                <th class="text-left">Country</th>
                                <th class="text-left">Name / Contact</th>
                                <th class="text-left">Items</th>
                                <th class="text-left">Color / Size</th>
                                <th class="text-left">Full Address</th>
                                <th class="text-right pr-2">Amount</th>
                                <th class="text-right pr-2">Status</th>
                                <th>...</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(inter, index) in Object.values(internationals?.data)" class="bg-gray-100 even:bg-white cursor-pointer hover:bg-gray-300" :key="index">
                                <td class="text-center text-sm">{{ ((internationals?.current_page - 1) * internationals?.per_page) + (index + 1) }}</td>
                                <td class="text-sm">{{ inter?.order_no }} </td>
                                <td class="text-sm">{{ inter?.country?.name }}</td>
                                <td class="text-sm">{{ inter?.full_name }} <br/> {{ inter?.whats_app_no }}</td>
                                <td class="text-sm">{{ inter?.item_name }}</td>
                                <td class="text-sm">{{ inter?.color }} / {{ inter?.size }}</td>
                                <td class="text-sm">{{ inter?.full_address }}</td>
                                <td class="justify-end flex pr-2 text-sm">
                                    <Currency class="text-right" :amount="inter?.current_price "/>
                                </td>

                                <td class="text-sm text-center">
                                    {{ inter?.order_status?.name }}
                                </td>

                                <td class="text-center p-1 w-16">
                                    <div class="flex gap-2 justify-end">
                                        <Link :href="`/international/${inter.order_no}/print`" class="bg-sky-600 p-2 rounded-sm"><Icon icon="subway:print" class="text-white" width="1rem" height="1erm"/></Link>

                                        <div class="bg-green-600 p-2 rounded-sm" @click="openPosition(inter.order_no, 'top')" severity="secondary">
                                            <Icon icon="mdi:eye" class="text-white" width="1rem" height="1erm"/>
                                        </div>
                                    </div>

                                </td>
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
                        <Pagination :links="internationals?.links" />
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

                        <div class="flex flex-col col-span-2 w-full pt-1">
                            <label for="multiple-ac-1" class="block text-sm font-semibold">Brand</label>
                            <AutoComplete v-model="selectedBrand" dropdown :suggestions="brands" optionLabel="name" size="small" inputClass="w-full text-sm border-gray-400" @complete="brandSearch"/>
                        </div>

                        <div class="flex flex-col col-span-2 w-full pt-1">
                            <label for="categories" class="text-sm w-full font-semibold">Country</label>
                            <AutoComplete v-model="selectedCategory" inputId="multiple-ac-1" optionLabel="name" size="small" inputClass="w-full text-sm" dropdown :suggestions="countries" @complete="countrySearch"
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
                            <div @click="reset" class="bg-red-600 hover:bg-red-700 py-1 px-4 font-semibold text-white rounded-sm flex w-full cursor-pointer items-center justify-center">Reset <Icon icon="fa:refresh"  class="m-1 mr-2"/></div>

                        </div>

                    </div>
                </form>
            </Drawer>


            <Dialog v-model:visible="visible" :header='single?.order_no' :style="{ width: '50rem' }" :position="position" :modal="true" :draggable="false">

                <div class="border-b border-t py-6 border-gray-200">
                    <div class="flex justify-between">
                        <div class="w-1/2">
                            <table class="[&_td]:px-2 [&_th]:px-2 [&_th]:text-left">
                                <tr>
                                    <th colspan="2" class="text-left underline">Order Information</th>
                                </tr>
                                <tr>
                                    <th>Order</th>
                                    <td>: {{ single?.order_no }}</td>
                                </tr>
                                <tr>
                                    <th>Date</th>
                                    <td>: {{ dateMonthFunction(single?.created_at) }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>:

                                        <select v-model="selectStatus">
                                            <option v-for="sts in status" :value="sts?.id">{{ sts?.name }}</option>
                                        </select>



                                    </td>
                                </tr>
                                <tr>
                                    <th>Note</th>
                                    <td>: {{ single?.note }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="w-1/2">
                            <table class="[&_td]:px-2 [&_th]:px-2 [&_th]:text-left">
                                <tr>
                                    <th colspan="2" class="text-left underline">Shipping Address</th>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td>: {{ single?.full_name }}</td>
                                </tr>
                                <tr>
                                    <th>WhatsApp</th>
                                    <td>: {{ single?.whats_app_no }}</td>
                                </tr>
                                <tr>
                                    <th>Country</th>
                                    <td>: {{ single?.country?.name }}</td>
                                </tr>
                                <tr>
                                    <th>Address</th>
                                    <td>: {{ single?.full_address }}</td>
                                </tr>
                            </table>
                        </div>


                    </div>
                    <div class="w-full">
                        <div class="w-full">
                            <label for="dd-city" class="text-sm w-full content-center font-semibold">Order Note:</label>
                            <textarea v-model="description" class="w-full text-md border border-gray-400 py-2 px-2 outline-none focus:border-green-200 rounded-md"></textarea>
                        </div>
                        <div class="w-full text-right">
                            <button class="bg-sky-500 p-2 rounded-md text-white cursor-pointer" @click="addNote(single?.order_no)" >Add Note</button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4 mb-4 mt-6">
                    <div for="username" class="font-semibold w-full text-lg text-center underline">Item</div>
                </div>
                <div class="flex items-center gap-4 mb-8">
                    <table class="[&_td]:px-2 [&_th]:px-2 [&_th]:text-left w-full border-collapse [&_td]:border [&_td]:border-gray-400 [&_th]:border [&_th]:border-gray-400 ">
                        <tr>
                            <th class="text-left">Icon</th>
                            <th class="text-left">Product Name</th>
                            <th class="text-left">Color</th>
                            <th class="text-left">Size</th>
                            <th class="text-left">Amount</th>
                            <th class="text-left">Status</th>
                        </tr>

                        <tr>
                            <td class="text-left w-16">
                                <img class="w-full h-auto" :src="single?.icon
                                    ? `/products/${single?.icon}`
                                    : `/assets/sites/sample.webp`" />
                            </td>
                            <td class="text-left">{{ single?.item_name }}</td>
                            <td class="text-left">{{ single?.color }}</td>
                            <td class="text-left">{{ single?.size }}</td>
                            <td class="text-left">{{ single?.current_price }}</td>
                            <td class="text-left">{{ single?.order_status?.name }}</td>
                        </tr>

                    </table>
                </div>

            </Dialog>

        </div>

    </AppLayout>
</template>
