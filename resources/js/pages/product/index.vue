<script setup lang="ts">
    import AppLayout from '@/layouts/AppLayout.vue';
    import { Head, Link, usePage, router } from '@inertiajs/vue3';
    import { computed, onMounted, ref } from 'vue'
    import { Icon } from '@iconify/vue';
    import Drawer from 'primevue/drawer';
    import Pagination from '@/components/Pagination.vue';
    import Currency from '@/components/Currency/index.vue';
    import AutoComplete from 'primevue/autocomplete';


const breadcrumbs = ref([
    { title: 'Dashboard', href: '/' },
    { title: 'Product', href: '/product' },
]);

const props = defineProps({
    products: Object,
    filters: Object,
    categories: Array,
    brands: Array,
    sizes: Array,
    colors: Array,
    append: Array
});


const add = ref(null);
const edit = ref(null);
const deleted = ref(null);

const visibleRight = ref(false);


const pageNumber = ref(props.products?.current_page);
const serialNumber = ref(1);

// Explicitly type menuAccess as MenuAccessItem[]
const menuAccess = computed(() => usePage().props.menuAccess);

onMounted(() => {
    add.value = menuAccess.value.find(access => access.action_id == 2) ?? null;
    edit.value = menuAccess.value.find(access => access.action_id == 3) ?? null;
    deleted.value = menuAccess.value.find(access => access.action_id == 4) ?? null;
});


const goToPage = () => {
    if (pageNumber.value < 1) {
        pageNumber.value = 1;
    }

    router.get('/product?page=' + pageNumber.value, {}, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

};



const loading = ref(false)
const brands = ref([]);
const categories = ref([]);
const sizes = ref([]);
const colors = ref([]);

const name = ref(props.append?.name);
const status = ref(props.append?.status);
const toDate = ref(props.append?.toDate);
const fromDate = ref(props.append?.fromDate);
const selectedBrand = ref(props.append?.brand);
const selectedCategory = ref(props.append?.category);
const selectedColor = ref(props.append?.color);
const selectedSize = ref(props.append?.size);


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




const submit = () => {

    loading.value = true;
    const filters = {
        name: name.value,
        category: selectedCategory.value,
        brand: selectedBrand.value,
        color: selectedColor.value?.color,
        size: selectedSize.value?.size,
        status: status.value,
        fromDate: fromDate.value,
        toDate: toDate.value,
    };

    router.get('/product/paginate/filters', filters, {
        preserveState: true,
        replace: true
    });

    loading.value = false;
};


const reset = () => {

    loading.value = true;
    name.value = null;
    status.value = null;
    toDate.value = null;
    fromDate.value = null;
    selectedBrand.value = null;
    selectedCategory.value = null;
    selectedColor.value = null;
    selectedSize.value = null;

    const filters = {};

    router.get('/products/paginate/filters', filters, {
        preserveState: true,
        replace: true
    });

    loading.value = false;
};





</script>
<template>
    <Head title="Product" />

    <AppLayout :breadcrumbs="breadcrumbs" :loading="loading">
        <div class="w-full mx-auto p-2 flex flex-row place-content-center">
            <div class="w-11/12 mt-4 shadow-lg bg-white rounded-md">
                <div class="flex justify-between bg-gray-300 px-2 py-1 items-center rounded-t-md">
                    <div class="text-lg font-semibold">Product List</div>
                    <div class="flex">
                        <div :class="{'rounded-md': !add}" class="bg-sky-600 items-center p-2 flex px-4 rounded-l-md cursor-pointer" @click="visibleRight = true">
                            <Icon icon="mdi:filter-outline" width="1.5rem" height="1.5rem" class="text-white"  />
                            <div class="px-2 text-white text-lg unded-md">Filter</div>

                        </div>

                        <Link v-if="add" href="/product/create" class="bg-green-600 p-2 rounded-r-md text-lg px-3 flex text-black text-white"> <Icon icon="fluent:add-12-filled" class="" width="1.5rem" height="1.5erm"/> Add Product </Link>
                    </div>
                </div>

                <!-- main content goes here -->

                <div class="border rounded-b-md px-2 py-1 min-h-[calc(100vh-13rem)]">
                    <table class="table-fixed w-full">
                        <thead>
                            <tr class="odd:bg-gray-100">
                            <th width="5%">Sl</th>
                            <th width="5%">Icon</th>
                            <th width="20%">Name</th>
                            <th>Price</th>
                            <th>Discount</th>
                            <th>Brand</th>
                            <th width="10%">Categories</th>
                            <th>Size</th>
                            <th>Color</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th width="10%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(product, index ) in Object.values(products?.data)" class="even:bg-gray-100" :key="product?.id">
                                <td>{{ ((products?.current_page - 1) * products?.per_page) + (index + 1) }}</td>
                                <td class="w-full !p-0 object-cover">
                                     <img :src="product?.media?.color_icon_small
                                            ? `/products/${product?.media?.color_icon_small}`
                                            : `/assets/sites/sample.webp`" />

                                </td>
                                <td>{{ product?.product?.name }}</td>
                                <td><Currency :amount="product?.current_price"/>  <del class="text-red-600"><Currency v-if="product?.current_price < product?.regular_price" :amount="product?.regular_price"/></del> </td>
                                <td>{{ product?.discount }}<span>{{ product?.dicount_type ? "%" : "" }}</span> </td>
                                <td>{{ product?.product?.brand?.name }}</td>
                                <td>
                                    <span v-for="(category, idx) in product?.product?.categories">
                                        {{ category.name }}<span v-if="idx !== product.product.categories.length - 1">, </span>
                                    </span>

                                </td>
                                <td>{{ product?.size }}</td>
                                <td>{{ product?.color }}</td>
                                <td>{{ product?.stocks_sum_stock }}</td>
                                <td>{{ product?.product?.status ? "Active" : "Inactive" }}</td>
                                <td>
                                    <div class="grid grid-cols-3 gap-1">
                                        <div v-if="edit"><Link class="bg-red-500 hover:bg-red-600 px-2 text-white rounded-sm flex items-center font-lg py-1" title="Edit" :href="`/product/${product?.product_id}/edit`"><Icon icon="icon-park-outline:pencil" width="1.3rem"/></Link></div>
                                        <div v-if="add"><Link class="bg-sky-500 hover:bg-sky-600 px-2 text-white rounded-sm flex items-center font-lg py-1" title="Additional" :href="`/product/${product?.product_id}/additional`"><Icon icon="gravity-ui:branches-right-arrow-right" width="1.3rem"/></Link></div>
                                        <div v-if="add"><Link class="bg-blue-900 hover:bg-blue-900 px-2 text-white rounded-sm flex items-center font-lg py-1" title="Medias" :href="`/product/${product?.product_id}/medias`"><Icon icon="material-symbols:perm-media" width="1.3rem"/></Link></div>
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
                        <Pagination :links="products.links" />
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

                    <div class="card w-full justify-center">
                        <label for="dd-city" class="text-sm w-full content-center font-semibold">Status</label>
                        <select v-model="status" class="w-full text-md border border-gray-400 py-2 px-2 outline-none focus:border-green-200 rounded-md">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
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
                        <div @click="reset" class="bg-red-600 hover:bg-red-700 py-1 px-4 font-semibold text-white rounded-sm flex w-full cursor-pointer items-center justify-center">Reset <Icon icon="fa:refresh"  class="m-1 mr-2"/></div>

                    </div>

                </div>
            </form>
        </Drawer>




        </div>
    </AppLayout>

</template>

 <style scoped>
        table tr td, th {
            padding:8px;
            text-align: left;
        }
    </style>
