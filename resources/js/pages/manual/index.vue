<script setup lang="ts">
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { computed, onMounted, ref} from 'vue'
import InputNumber from 'primevue/inputnumber';
import { rand } from '@vueuse/core';
import  { useAddCart } from "@/stores/cart/cart";
import axios from 'axios'
import ManualLayout from '@/layouts/ManualLayout.vue';


    const props = defineProps({
        branch: Object,
        categories: Object,
        brands: Object,
        customers: Object,
        products: Object,
        branchs: Object,
        districts: Object,
        colors: Object,
        sizes: Object,

    });

    const page = usePage()
    const user = page.props.auth.user
    const productData = ref(props?.products?.data)
    const loading = ref(false);

    // Fitler function goes here
    const filterData = ref([]);

    // store data in pinia store
    const cartAdd = useAddCart();

    const posAddToCart = (id) => {

        let item = productData.value.filter(item =>  item.id === id);

        console.table(item);

        let addItemData = {
            'name': item[0]?.product?.name,
            'image': item[0]?.media?.color_thumbnails_small,
            'regular_price': item[0]?.regular_price,
            'current_price': item[0]?.current_price,
            'size': item[0].size,
            'brand_id': item[0].product?.brand_id,
            'branch_id': item[0]?.branch_id,
            'qty': 1,
            'color': item[0].color,
            'product_id': item[0].product?.id,
            'stocks': item[0]?.stock,
            'id': item[0].id,
            'total': (item[0].current_price),
        }

        cartAdd.cartStore(addItemData);

        var objDiv = document.getElementById("parentDiv");
        objDiv.scrollTop = objDiv.scrollHeight;


    }



    const pageNumber = ref(props.products?.current_page);

    const productWindow = ref(null);

    // Set blank timer
    let scrollTimer;

    const dataLoad = (e) => {

        clearTimeout(scrollTimer);

        scrollTimer = setTimeout(() => {
            // Check end of the page
            if(pageNumber.value < props.products?.last_page && (e.target.offsetHeight + (parseInt(e.target.scrollTop) + 100) >= productWindow.value.clientHeight)){

                loading.value = true;

                axios.get(route('manual.filter.index'),{
                    params: {
                        page: (parseInt(pageNumber.value) + 1),
                        ...filterData.value,

                    },
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                })
                .then(function (res) {

                    pageNumber.value =  (parseInt(res.data?.current_page) + 1);
                    const items = Object.values(res.data?.data); // converts numeric keys into an array
                    items.forEach(item => productData.value.push(item));

                    loading.value = false;


                })
                .catch(function (error) {
                    console.log(error);
                });

            } // check page


        }, 500);
    };


    const filtterFunction = (e) => {

        let name = e.target.name;
        let value = e.target.value;

        filterData.value = {
            ...filterData.value,
            [name] : value
        }

        dataLoadByfilter();
    }


    const filtterInputFunction = (e, dataName) => {

        let value = e?.value?.id ?? e.value?.size;

        filterData.value = {
            ...filterData.value,
            [dataName] : value
        }

        dataLoadByfilter();
    }


    const dataLoadByfilter = () => {


        clearTimeout(scrollTimer);

        scrollTimer = setTimeout(() => {


                loading.value = true;

                axios.get(route('manual.filter.index'),{
                    params: {
                        ...filterData.value,
                    },
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                })
                .then(function (res) {

                    productData.value = [];


                    productData.value = res.data?.data;

                    loading.value = false;

                })
                .catch(function (error) {
                    console.log(error);
                });


        }, 500);
    };




</script>
<template>
    <ManualLayout name="layout" :colors="colors" :sizes="sizes" :branch="branch" :districts="districts" :branchs="branchs" :brands="brands" :categories="categories" :customers="customers" @triggerParent="dataLoad" @triggerFilter="filtterFunction" @triggerInput="filtterInputFunction">

        <div class="grid grid-cols-4 gap-4 mx-2 product_window_inside relative" ref="productWindow">
            <div class="bg-black/25  w-full h-full z-2 absolute top-0 right-0" v-if="loading">
                <div class="fixed bottom-70 left-120 ">
                    <img src="/assets/loading.gif" style="width: 60px;"/>
                    <h1 class="text-xl">Loding...</h1>
                </div>
            </div>

            <div @click="posAddToCart(product.id)" v-for="(product, index) in productData" :key="index" class="shadow-md rounded-md bg-white flex flex-col aspect-square overflow-hidden relative cursor-pointer">
                <img alt="ladid finger" :src="product?.media?.color_thumbnails ? `/products/${product?.media?.color_thumbnails }` : `/assets/sites/sample.webp`" class="w-full h-auto" />

                <div class="text-black text-md px-2 pb-1 absolute bottom-0 left-0 bg-green-300 font-semibold w-full opacity-75">
                    <p>{{product?.product?.name}}</p>
                    <p class="text-xs">{{product?.color}}/{{product?.size}} | {{product?.branchs?.name}}</p>
                </div>
                <div class="text-white text-left absolute text-md px-2 bg-sky-500">
                    <p>৳{{product?.current_price}}/{{product?.stock}}</p>
                    <p v-if="(product?.regular_price > 0 && product?.regular_price > product?.current_price)">Save: ৳{{ (product?.regular_price - product?.current_price) }}</p>
                </div>
                <!-- <div class="text-white text-right absolute top-0 right-0 text-md px-2 bg-rose-500 flex-col opactity-25">
                    <div>{{product?.branchs?.name}}</div>
                    <div>{{product?.color}}/{{product?.size}}</div>
                </div> -->

            </div>


            <!-- <div @click="posAddToCart(product.id)" v-for="(product, index) in productData" :key="index" class="shadow-md rounded-md bg-white flex overflow-hidden relative cursor-pointer">
                <div class="border-r p-1">
                    <img alt="ladid finger" :src="product?.media?.color_thumbnails ? `/products/${product?.media?.color_thumbnails }` : `/assets/sites/sample.webp`" class="h-24 w-24" />

                </div>
                <div class="px-2 relative w-full">


                    <div class="text-black text-lg p-2 font-bold w-full">{{product?.product?.name}} </div>
                    <div class="text-black text-sm px-2">
                        <div class="flex gap-2">

                            <p class="font-semibold">Price: ৳{{product?.current_price}}</p> | <del class="font-semibold text-red-500">Price: ৳{{product?.regular_price}}</del>
                        </div>
                        <p class="font-semibold">Stock: {{product.stock}}</p>
                    </div>
                    <div class="text-white text-right absolute top-0 right-0 text-md p-2 bg-rose-500">{{product?.color}}/{{product?.size}}</div>
                    <div class="text-black font-bold text-center absolute bottom-0 right-0 text-md p-2 h-12 bg-green-400">Branch: {{product?.branchs?.name}}</div>

                </div>
            </div> -->
        </div>
    </ManualLayout>
</template>

