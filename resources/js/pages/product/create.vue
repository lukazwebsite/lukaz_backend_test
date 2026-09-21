<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, onMounted, ref, defineAsyncComponent } from 'vue'
import { Icon } from '@iconify/vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { useToast } from "primevue/usetoast";
const EditorToolbar = defineAsyncComponent(() => import('@/components/EditorToolbar.vue'))



import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';

import AutoComplete from 'primevue/autocomplete';

const toast = useToast();

const props = defineProps({
    menuAccess: Object,
    brands: Array,
    categories: Array
});


const breadcrumbs = ref([
    { title: 'Dashboard', href: '/' },
    { title: 'Product', href: '/product' },
    { title: 'Create', href: '/create' },
]);


const errors = ref({});


const brands = ref([]);
const categories = ref([]);

const name = ref("");
const currentPrice = ref("");
const regularPrice = ref("");
const discountType = ref("1");
const discount = ref("");
const sku = ref("");
const is_pre_order = ref(0); // 0 = no, 1 = yes
const status = ref(0);

const partial_amount = ref(0);
const delivery_express = ref(0);
const delivery_express_support = ref(0);
const payment_type = ref(0);

const selectedCategories = ref([]);
const selectedBrands = ref([]);
const description = ref("");

const color = ref("");
const size = ref("");
const international = ref(1);
const preOrderDays = ref("");
const preOrderNotes = ref("");

const seoTitle = ref("");
const seoPrimaryKeyword = ref("");
const seoDescription = ref("");

const startTime = ref('');
const endTime = ref('');
const startDate = ref('');
const endDate = ref('');
const waiting = ref(false);
const loading = ref(false);


const specialDiscount = ref(0);

const categorySearch = (event) => {

    const query = event.query.toLowerCase();
    categories.value = props.categories ? props.categories.filter(p => p.name.toLowerCase().includes(query)) : [];

}

const brandSearch = (event) => {

    const query = event.query.toLowerCase();
    brands.value = props.brands ? props.brands.filter(p => p.name.toLowerCase().includes(query)) : [];
}


const addProduct = () => {

    waiting.value = true;
    loading.value = true;

    const formData = new FormData();

    formData.append('name', name.value);
    formData.append('payment_type', payment_type.value);
    formData.append('partial_amount', partial_amount.value);
    formData.append('delivery_express', delivery_express.value);
    formData.append('delivery_express_support', delivery_express_support.value);
    formData.append('current_price', currentPrice.value);
    formData.append('regular_price', regularPrice.value);
    formData.append('discount_type', discountType.value);
    formData.append('discount', discount.value);
    formData.append('sku', sku.value);
    formData.append('status', status.value);
    formData.append('description', description.value);

    if (selectedCategories.value.length > 0) {
        selectedCategories.value.forEach((category) => {
            formData.append('categories[]', category.id);
        });
    }



    formData.append('color', color.value);
    formData.append('size', size.value);
    formData.append('international', international.value);
    formData.append('pre_order_days', preOrderDays.value);
    formData.append('pre_order_notes', preOrderNotes.value);
    formData.append('is_pre_order', is_pre_order.value);

    formData.append('seo_title', seoTitle.value);
    formData.append('seo_keywords', seoPrimaryKeyword.value);
    formData.append('seo_description', seoDescription.value);

    formData.append('start_time', startTime.value);
    formData.append('end_time', endTime.value);

    formData.append('start_date', startDate.value);
    formData.append('end_date', endDate.value);


    if (selectedBrands.value != null) {
        formData.append('brand_id', selectedBrands?.value.id);
    }

    formData.append('special_discount', specialDiscount.value);


    router.post('/product', formData, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        forceFormData: true,
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Success Message', detail: 'Product store successfully!', life: 3000 });
            errors.value = {};
            waiting.value = false;
            loading.value = false;
        },
        onError: (e) => {
            toast.add({ severity: 'error', summary: 'Error Message', detail: 'Oops! Something wrongs', life: 3000 });
            errors.value = e;
            waiting.value = false;
            loading.value = false;

        },
    });
};




</script>


<template>

    <Head title="Product Create" />

    <AppLayout :breadcrumbs="breadcrumbs" :loading="loading">
        <Toast position="top-right" />
        <div class="w-full mx-auto p-2 flex flex-row place-content-center">
            <div className="w-5/6 mt-4 shadow-lg bg-white rounded-md">
                <div class="flex justify-between bg-gray-300 px-2 py-1 items-center rounded-t-md">
                    <div class="text-lg font-semibold">Product Create</div>
                    <div class="flex">
                        <Link href="/product"
                            class="bg-rose-600 p-2 px-3 rounded-md text-lg flex items-center text-white cursor-pointer">
                            <Icon icon="line-md:arrow-left" class="mr-2" width="1.5rem" height="1.5rem" />
                            Back
                        </Link>
                    </div>
                </div>
                <!-- main content goes here -->
                <div class="border rounded-b-md px-2 py-1 min-h-[calc(100vh-10rem)] ">
                    <form @submit.prevent="addProduct" class="card">
                        <Tabs value="0">
                            <TabList>
                                <Tab value="0">Basic</Tab>
                                <Tab value="1">Additional</Tab>
                                <Tab value="3">SEO</Tab>
                                <!-- <Tab value="4">Offer</Tab> -->
                            </TabList>
                            <TabPanels>
                                <!-- Basic -->
                                <TabPanel value="0">
                                    <div class="w-full flex gap-2">
                                        <div class="w-1/2">
                                            <label for="dd-city" class="text-sm w-1/2">Product Name</label>
                                            <input v-model="name"
                                                :class="{ 'border': true, 'border-red-500 text-red-500': errors?.name }"
                                                class="w-full text-sm border py-2 px-2 outline-none focus:border-green-200 rounded-md"
                                                type="text" placeholder="Product Name" />
                                            <p v-if="errors?.name" class="text-red-500 text-sm mt-1">{{ errors?.name }}
                                            </p>
                                        </div>
                                        <div class="w-1/2 flex gap-2">
                                            <div class="w-1/2">
                                                <label class="text-sm w-full">Current Price</label>
                                                <input
                                                    :class="{ 'border': true, 'border-red-500 text-red-500': errors?.price }"
                                                    v-model="currentPrice"
                                                    class="w-full text-sm border py-2 px-2 outline-none focus:border-green-200 rounded-md"
                                                    type="number" placeholder="Current Price" />
                                                <p v-if="errors?.price" class="text-red-500 text-sm mt-1">{{
                                                    errors?.price }}</p>
                                            </div>

                                            <div class="w-1/2">
                                                <label class="text-sm w-full">Regular Price</label>
                                                <input v-model="regularPrice"
                                                    class="w-full text-sm border py-2 px-2 outline-none focus:border-green-200 rounded-md"
                                                    type="number" placeholder="Regular Price" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="w-full mt-2">

                                        <div class="grid grid-cols-4 gap-2">
                                            <div>
                                                <label class="text-sm w-full">Discount Type</label>
                                                <select v-model="discountType"
                                                    class="w-full text-sm border py-2 px-2 outline-none focus:border-green-200 rounded-md">
                                                    <option SELECTED value="1">Fixed</option>
                                                    <option value="0">Percentage</option>
                                                </select>
                                            </div>

                                            <div>
                                                <label class="text-sm w-full">Discount</label>
                                                <input v-model="discount"
                                                    class="w-full text-sm border py-2 px-2 outline-none focus:border-green-200 rounded-md"
                                                    type="number" placeholder="Discount" />
                                            </div>

                                            <div class="w-full mt-1">
                                                <label for="multiple-ac-1" class="block text-sm">Payment Type</label>
                                                <select v-model.number="payment_type" class="w-full border p-2 rounded-md">
                                                    <option value="0">Cash On Delivery</option>
                                                    <option value="1">Partial Payment</option>
                                                    <option value="2">Full Payment</option>
                                                </select>
                                            </div>

                                            <div v-if="payment_type === 1">
                                                <label class="text-sm w-full">Partial Amount</label>
                                                <input v-model="partial_amount"
                                                    class="w-full text-sm border py-2 px-2 outline-none focus:border-green-200 rounded-md"
                                                    type="number" placeholder="Partial Amount" />
                                            </div>

                                            <div class="w-full mt-1">
                                                <label for="multiple-ac-1" class="block text-sm">Brand</label>
                                                <AutoComplete
                                                    :class="{ 'border': true, 'border-red-500 text-red-500': errors?.brand_id }"
                                                    v-model="selectedBrands" dropdown :suggestions="brands"
                                                    optionLabel="name" inputClass="w-full h-9 text-sm"
                                                    @complete="brandSearch" class="w-full" />
                                                <p v-if="errors?.brand_id" class="text-red-500 text-sm mt-1">{{
                                                    errors?.brand_id }}</p>
                                            </div>

                                            <div class="w-full mt-1">
                                                <label for="multiple-ac-1" class="block text-sm">Delivery Express Support</label>
                                                <select v-model.number="delivery_express_support" class="w-full border p-2 rounded-md">
                                                    <option value="1">Yes</option>
                                                    <option value="0">No</option>
                                                </select>
                                            </div>

                                            <div class="w-full mt-1" v-if="delivery_express_support === 1">
                                                <label for="multiple-ac-1" class="block text-sm">Delivery Express Charge</label>
                                                <input v-model="delivery_express"
                                                    class="w-full text-sm border py-2 px-2 outline-none focus:border-green-200 rounded-md"
                                                    type="number" placeholder="Delivery Express Charge" />
                                            </div>

                                            <div class="w-full mt-1">
                                                <label for="multiple-ac-1" class="block text-sm">Status</label>
                                                <select v-model="status" class="w-full border p-2 rounded-md">
                                                    <option value="0">Inactive</option>
                                                    <option value="1">Active</option>
                                                </select>

                                            </div>

                                            <div>
                                                <label class="text-sm w-full">Allow Pre Order</label>
                                                <select v-model="is_pre_order" class="w-full border p-2 rounded-md">
                                                    <option value="1">YES</option>
                                                    <option value="0">NO</option>
                                                </select>
                                                <p v-if="errors?.is_pre_order" class="text-red-500 text-sm mt-1">{{
                                                    errors?.is_pre_order }}</p>
                                            </div>

                                            <div>
                                                <label class="text-sm w-full">SKU</label>
                                                <input v-model="sku"
                                                    :class="{ 'border': true, 'border-red-500 text-red-500': errors?.sku }"
                                                    class="w-full text-sm border py-2 px-2 outline-none focus:border-green-200 rounded-md"
                                                    type="text" placeholder="SKU" />
                                                <p v-if="errors?.sku" class="text-red-500 text-sm mt-1">{{ errors?.sku
                                                }}</p>
                                            </div>

                                            <div class="flex flex-col col-span-3 w-full pt-1">
                                                <label for="categories" class="text-sm w-full">Category</label>
                                                <AutoComplete
                                                    :class="{ 'border': true, 'border-red-500 text-red-500': errors?.category_id }"
                                                    v-model="selectedCategories" inputId="multiple-ac-1"
                                                    optionLabel="name" inputClass="w-full h-5 text-sm" multiple fluid
                                                    :suggestions="categories" @complete="categorySearch" />
                                                <p v-if="errors?.category_id" class="text-red-500 text-sm mt-1">{{
                                                    errors?.category_id }}</p>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="w-full mt-2">
                                        <div class="w-full mt-2">
                                            <label for="multiple-ac-1" class="block text-sm">Description</label>
                                            <EditorToolbar v-model="description" />
                                        </div>
                                    </div>
                                </TabPanel>


                                <!-- Additional -->
                                <TabPanel value="1">
                                    <div class="w-full mt-2">
                                        <div class="grid grid-cols-4 gap-2">
                                            <div>
                                                <label class="text-sm w-full">Color</label>
                                                <input v-model="color"
                                                    :class="{ 'border': true, 'border-red-500 text-red-500': errors?.color }"
                                                    class="w-full text-sm border py-2 px-2 outline-none focus:border-green-200 rounded-md"
                                                    type="text" placeholder="White,Orange" />
                                                <small class="text-gray-400">Color add by comma (,) separator</small>
                                                <p v-if="errors?.color" class="text-red-500 text-sm mt-1">{{
                                                    errors?.color }}</p>
                                            </div>

                                            <div>
                                                <label class="text-sm w-full">Size</label>
                                                <input v-model="size"
                                                    :class="{ 'border': true, 'border-red-500 text-red-500': errors?.size }"
                                                    class="w-full text-sm border py-2 px-2 outline-none focus:border-green-200 rounded-md"
                                                    type="text" placeholder="42,xl,ml,etc" />
                                                <small class="text-gray-400">Size add by comma (,) separator</small>
                                                <p v-if="errors?.size" class="text-red-500 text-sm mt-1">{{ errors?.size
                                                }}</p>
                                            </div>

                                            <div class="w-full mt-1">
                                                <label for="multiple-ac-1" class="block text-sm">International
                                                    Support</label>
                                                <select v-model="international" class="w-full border p-2 rounded-md">
                                                    <option value="1">YES</option>
                                                    <option value="0">NO</option>
                                                </select>
                                                <small class="text-gray-400"> International Support</small>
                                            </div>

                                            <div class="w-full mt-1">
                                                <label for="multiple-ac-1" class="block text-sm">Days</label>
                                                <input v-model="preOrderDays"
                                                    class="w-full text-sm border py-2 px-2 outline-none focus:border-green-200 rounded-md"
                                                    type="number" placeholder="7" />
                                                <small class="text-gray-400"> Pre-order for Stemate Days</small>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="w-full mt-2">
                                        <div class="grid grid-col-1 gap-2">
                                            <div class="flex-row">
                                                <label class="text-sm w-full">Pre Order Nots</label>
                                                <textarea v-model="preOrderNotes"
                                                    class="border rounded-md min-h-[120px] w-full p-2 focus:outline-none focus:ring-1 focus:ring-green-200"></textarea>
                                                <small class="text-gray-400">Note for pre-order customer</small>
                                            </div>
                                        </div>
                                    </div>
                                </TabPanel>

                                <!-- SEO -->

                                <TabPanel value="3">
                                    <div class="w-full mt-2">
                                        <div class="grid grid-cols-3 gap-2">
                                            <div>
                                                <label class="text-sm w-full">Title</label>
                                                <input v-model="seoTitle"
                                                    class="w-full text-sm border py-2 px-2 outline-none focus:border-green-200 rounded-md"
                                                    type="text" placeholder="Product short name goes here" />
                                            </div>

                                            <div>
                                                <label class="text-sm w-full">Primary Key Word</label>
                                                <input v-model="seoPrimaryKeyword"
                                                    class="w-full text-sm border py-2 px-2 outline-none focus:border-green-200 rounded-md"
                                                    type="text" placeholder="Shoe,tranding,etc" />
                                                <small class="text-red-400">Key Word add by comma (,) separator</small>
                                            </div>



                                            <div class="w-full mt-1">
                                                <label for="multiple-ac-1" class="block text-sm">SEO Description</label>
                                                <input v-model="seoDescription"
                                                    class="w-full text-sm border py-2 px-2 outline-none focus:border-green-200 rounded-md"
                                                    type="text" placeholder="product short description" />
                                                <small class="text-red-400">Facebook, meta kept under 100 characters, up
                                                    to 300</small>
                                            </div>
                                        </div>
                                    </div>
                                </TabPanel>
                                <TabPanel value="4">
                                    <div class="w-full mt-2">
                                        <div class="grid grid-cols-5 gap-2">
                                            <div>
                                                <label class="text-sm w-full">Start Date</label>
                                                <input type="date"
                                                    class="text-sm w-full border py-2 px-2 outline-none focus:border-green-200 rounded-md"
                                                    id="datepicker-12h" v-model="startDate" showTime hourFormat="12"
                                                    fluid />
                                            </div>

                                            <div>
                                                <label class="text-sm w-full">Start Time</label>
                                                <input type="time"
                                                    class="w-full text-sm border py-2 px-2 outline-none focus:border-green-200 rounded-md"
                                                    id="datepicker-12h" v-model="startTime" showTime hourFormat="12"
                                                    fluid />
                                            </div>

                                            <div>
                                                <label class="text-sm w-full">End Date</label>
                                                <input type="date"
                                                    class="text-sm w-full border py-2 px-2 outline-none focus:border-green-200 rounded-md"
                                                    id="datepicker-12h" v-model="endDate" showTime hourFormat="12"
                                                    fluid />
                                            </div>

                                            <div>
                                                <label class="text-sm w-full">End Time</label>
                                                <input type="time"
                                                    class="w-full text-sm border py-2 px-2 outline-none focus:border-green-200 rounded-md"
                                                    id="datepicker-12h" v-model="endTime" showTime hourFormat="12"
                                                    fluid />
                                            </div>


                                            <div>
                                                <label class="text-sm w-full">Special Discount %</label>
                                                <input v-model="specialDiscount"
                                                    class="w-full text-sm border py-2 px-2 outline-none focus:border-green-200 rounded-md"
                                                    type="number" placeholder="" />
                                            </div>

                                        </div>
                                    </div>
                                </TabPanel>
                            </TabPanels>
                        </Tabs>
                        <button v-if="!waiting"
                            class="bg-blue-600 p-2 rounded-md text-white font-semibold float-right mr-4 cursor-pointer"
                            type="submit">Add Product</button>
                        <span v-else
                            class="bg-blue-600 p-2 rounded-md text-white font-semibold float-right mr-4 cursor-pointer">Please
                            wait</span>


                    </form>

                </div>

                <!-- main content goes here -->

            </div>

        </div>
    </AppLayout>
</template>
