<script setup lang="ts">
import PosLayout from '@/layouts/PosLayout.vue';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { computed, onMounted, ref} from 'vue'
import InputNumber from 'primevue/inputnumber';
import { rand } from '@vueuse/core';
import  { useAddCart } from "@/stores/cart/cart";
import axios from 'axios'
import { useToast } from "primevue/usetoast";

    const posLayoutRef = ref(null);
    const toast = useToast();


    const props = defineProps({
       branch: Object,
       categories: Object,
       brands: Object,
       customers: Object,
       products: Object,
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


    const posAddToCart = (product) => {



        if(product.stock <= 0){
           toast.add({ severity: 'error', summary: 'Error Message', detail: 'Oops! This product is out of stock.', life: 3000 });
           return;
        }



        let addItemData = {
            'name': product?.product?.name,
            'image': product?.media?.color_thumbnails_small,
            'regular_price': product?.regular_price,
            'current_price': product?.current_price,
            'size': product?.size,
            'brand_id': product?.product?.brand_id,
            'qty': 1,
            'stocks': product?.stock,
            'color': product?.color,
            'product_id': product?.product_id,
            'id': product?.id,
            'total': (product?.current_price),
        }

        cartAdd.cartStore(addItemData);

        let tempStore = cartAdd?.cart.filter(tempStore =>  tempStore.id === product?.id);

        if(tempStore.length && tempStore?.[0].stocks < tempStore?.[0].qty){
           toast.add({ severity: 'error', summary: 'Error Message', detail: 'Oops! This product is out of stock.', life: 3000 });

           cartAdd.porductDecrease(product?.id);

           return;
        }

        posLayoutRef.value?.scrollToBottom();


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

                axios.get(route('extra.pos.filter.index'),{
                    params: {
                        ...filterData.value,
                        page: (parseInt(pageNumber.value)) == 1 ? (parseInt(pageNumber.value) + 1) : parseInt(pageNumber.value)
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
        let value = e.target.value ?? e.value?.size;

        filterData.value = {
            ...filterData.value,
            [name] : value
        }

        dataLoadByfilter();
    }


    const filtterInputFunction = (e, dataName) => {

        let value = e?.value?.id ?? e.value?.size;;

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

                axios.get(route('extra.pos.filter.index'),{
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
    <PosLayout name="layout" ref="posLayoutRef" :sizes="sizes" :colors="colors" :branch="branch" :brands="brands" :categories="categories" :customers="customers" @triggerParent="dataLoad" @triggerFilter="filtterFunction" @triggerInput="filtterInputFunction">

        <Toast position="top-right" />

        <div class="grid grid-cols-4 gap-4 mx-2 product_window_inside relative" ref="productWindow">
            <div class="bg-black/25  w-full h-full z-2 absolute top-0 right-0" v-if="loading">
                <div class="fixed bottom-70 left-120 ">
                    <img src="/assets/loading.gif" style="width: 60px;"/>
                    <h1 class="text-xl">Loding...</h1>
                </div>
            </div>
            <div @click="posAddToCart(product)" v-for="(product, index) in productData" :key="index" class="shadow-md rounded-md bg-white flex flex-col aspect-square overflow-hidden relative cursor-pointer">
                <img alt="ladid finger" :src="product?.media?.color_thumbnails ? `/products/${product?.media?.color_thumbnails }` : `/assets/sites/sample.webp`" class="w-full h-auto" />
                <div class="text-black text-md px-2 absolute bottom-0 left-0 bg-green-300 font-semibold w-full opacity-75">{{product?.product?.name}} </div>
                <div class="text-white text-center absolute text-md px-2 bg-sky-500">
                    <p>৳{{product?.current_price}}/{{product?.stock}}</p>
                    <p v-if="(product?.regular_price > 0 && product?.regular_price > product?.current_price)">Save: ৳{{ (product?.regular_price - product?.current_price) }}</p>
                </div>
                <div class="text-white text-center absolute top-0 right-0 text-md px-2 bg-rose-500">{{product?.color}}/{{product?.size}}</div>

            </div>
        </div>
    </PosLayout>
</template>

