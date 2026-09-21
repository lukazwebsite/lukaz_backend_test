<script setup lang="ts">
    import {  onMounted} from 'vue'
    import { useDataDate } from '@/composables/useDataDate';
    import Currency from '@/components/Currency/index.vue';
    import { Icon } from '@iconify/vue';

    const props = defineProps({
       branch: Object,
       order: Object

    });


    const { dateMonthFunction } = useDataDate();

onMounted(() => {
    window.print();
    window.onafterprint = () => {
        if (window.opener) {
            window.opener.location.reload() // reload parent page
        }
        window.close();
    }

});



</script>

<template>
    <div class="w-[200px] text-[8px] font-semibold border border-black text-black semi-bold">
        <div class="w-full place-items-center">
            <!-- <img src="/assets/sites/logo.png" class="text-center w-full" alt="log"> -->
            <div  class="text-lg font-bold">Lukaz Shop</div>
            <div  class="">{{ order.branch?.address }}, {{ order.branch?.name }}</div>
            <div  class=""> {{ order.branch?.contact }}</div>
            <div  class="w-full border-b border-dashed border-double border-black"></div>
        </div>

        <div class="w-full justify-between mt-2">
            <div class="order w-full flex">
                <div class="w-3/7">Order No.</div>
                <div class="text-left w-4/7"> : {{ order.order_no }}</div>
            </div>

            <div class="order w-full flex">
                <div class="w-3/7">Date</div>
                <div class="text-left w-4/7"> : {{ dateMonthFunction(order?.created_at) }}</div>
            </div>
        </div>

        <div class="w-full place-items-center">
            <div class="text-[12px] font-bold roboto-400 mb-2 border border-black border-dashed rounded-md px-3 mt-2">Order Items</div>
        </div>
        <div class="w-full place-items-center">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-black uppercase border-b border-dashed border-black mb-2">
                        <th class="px-1">Product</th>
                        <th class="px-1">Price</th>
                        <th class="px-1">Qty</th>
                        <th class="px-1">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template v-for="item in order?.items" :key="item.id">

                        <tr>
                            <td class="px-1" colspan="4">
                                <div class="flex items-center">
                                    <!-- <div class="h-16 w-16 bg-gray-200 rounded-md overflow-hidden">
                                        <img :src="`/products/${item.icon}`" :alt="`${item.item_name }`" class="h-full w-full object-cover">
                                    </div> -->

                                    <div class="w-full">
                                        <p class="font-semibold text-black ">{{ item?.item_name }}</p>
                                        <p class="">
                                            color: {{ item.color }} | Size: {{ item.size }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td class="px-1 font-semibold !text-[8px] flex gap-2">
                                ৳ {{ item.regular_price }}
                            </td>
                            <td class="px-1 font-semibold ">{{ item?.quantity }}</td>
                            <td class="px-1 font-semibold  flex gap-2 text-[8px]">
                                <del v-if="item?.deleted_at != null" > ৳  {{ (item?.quantity * item?.regular_price) }}</del>
                                <span v-else>৳ {{ (item?.quantity * item?.regular_price) }}</span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="border-gray-200 border-t">
            <div class="flex justify-end">
                <div class="w-full max-w-xs mt-2">
                    <div class="px-2 text-xs flex border-b border-gray-200">
                        <span class="w-5/7 text-right">Subtotal : </span>
                        <span class="w-full text-right">৳ {{ order?.transactions_sum_debit }} </span>
                    </div>

                    <div v-if="order?.discount > 0" class="px-2 text-xs font-semibold flex justify-end border-b border-gray-200">
                        <span class="w-5/7 text-right">Discount : </span>
                        <span class="w-full text-right">৳ {{ order?.discount }} </span>
                    </div>
                    <div v-if="order?.discount > 0" class="px-2 text-xs font-semibold flex justify-end border-b border-gray-200">
                        <span class="w-5/7 text-right">Pay : </span>
                        <span class="w-full text-right">৳ {{ order?.grand_total }} </span>
                    </div>

                    <div class="flex justify-between px-2 border-gray-200 pt-2">
                        <span class="text-lg font-semibold text-gray-800">Total:</span>
                        <span class="text-lg font-semibold text-gray-800"><Currency :amount="order?.transactions_sum_credit"/></span>
                    </div>
                    <div v-if="order?.transactions_sum_debit > order?.transactions_sum_credit" class="flex px-2 border-gray-200 pt-2 text-center justify-center items-center">
                        <span class="text-lg font-semibold text-gray-800">Due:</span>
                        <span class="text-lg font-semibold text-gray-800 w-full"><Currency :amount="(order?.transactions_sum_debit - order?.transactions_sum_credit )"/></span>
                    </div>
                    <div v-else-if="order?.transactions_sum_debit < order?.transactions_sum_credit" class="flex px-2 border-gray-200 pt-2 text-center justify-center items-center">
                        <span class="text-lg font-semibold text-gray-800">Return:</span>
                        <span class="text-lg font-semibold text-gray-800 w-full"><Currency :amount="(order?.transactions_sum_debit - order?.transactions_sum_credit )"/></span>
                    </div>
                    <div v-else class="flex px-2 border-gray-200 pt-2 text-center justify-center items-center">
                        <span class="text-lg font-semibold text-gray-800">Paid</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full place-items-center mt-3 ">
            <p class="font-bold text-xl text-center">Thank you ! </p>
            <p class="font-bold text-[8px] text-justify">Please bring this bill back within the next 24 hours. If the exchange product is available in stock, No exchange or return on discounted products.</p>
            <p class="font-bold text-[10px]">https://lukazshop.com</p>
        </div>



    </div>
</template>


<style>
@import url('https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap');


.roboto-400 {
    font-family: "Roboto", sans-serif;
    font-optical-sizing: auto;
    font-weight: 400;
    font-style: normal;
    font-variation-settings:
        "wdth" 100;
}

@page {
    size: 58mm auto;
    margin: 0;
}

body {
    font-family: 'Courier New', monospace;
    margin: 0;
    padding: 0;
}


</style>
