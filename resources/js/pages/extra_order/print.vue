<script setup lang="ts">
    import { Head, router } from '@inertiajs/vue3';
    import {  onMounted, ref, nextTick } from 'vue'
    import { Icon } from '@iconify/vue';
    import { useDataDate } from '@/composables/useDataDate';
    import Currency from '@/components/Currency/index.vue';

    import { useHistory } from '@/composables/useHistory'




    const props = defineProps({
        orders: Object,
        status: Object,
    });

    const { back } = useHistory()
    const totalCreat = ref(0);
    const totalDebit = ref(0);
    const shipping_cost = ref(0);

    onMounted(() => {

        totalCreat.value = props?.orders?.transactions.reduce((acc, item) => acc + item.credit, 0);
        totalDebit.value = props?.orders?.transactions.reduce((acc, item) => acc + item.debit, 0);
        shipping_cost.value =  props?.orders?.shipping_info?.district?.courier_charge;

        nextTick(() => {
            setTimeout(() => {
                window.print();

                window.onafterprint = () => {
                    if (document.referrer) {
                        router.visit(document.referrer)
                    } else {
                        router.visit('/orders') // fallback if no referrer
                    }

                };
            }, 50); // Small delay
        })
    })

    const { dateMonthFunction } = useDataDate();



</script>

<template>
    <Head title="Order" />

        <div class="w-full flex flex-row place-content-center mb-4">

            <div class="w-full mt-4 bg-white rounded-md">
                <div class="flex justify-between bg-gray-300 px-2 py-1 items-center rounded-t-md">
                    <div class="text-lg font-semibold">Order No# {{ orders?.order_no }}</div>
                    <div class="flex print:hidden">
                        <div @click="back" class="bg-green-600 p-2 rounded-md text-lg px-3 flex text-black text-white cursor-pointer"> <Icon icon="line-md:arrow-left" class="mr-2" width="1.5rem" height="1.5rem" /> Back </div>
                    </div>
                </div>

                <div class="border py-1 bg-white">
                    <div class="w-full mx-auto">
                        <div class="bg-white overflow-hidden">
                            <!-- Order Summary -->
                            <div class="p-6 border-b border-gray-200">
                                <div class="flex justify-between">
                                    <div class="w-1/3">
                                        <img src="/assets/sites/logo.png" class="w-[150px]"/>
                                        <!-- <h2 class="text-lg font-semibold text-gray-700 mb-2">Lukaz Shop</h2> -->
                                        <p class="text-gray-600"><span class="font-medium">E-mail:</span> lukazshop@gmail.com</p>
                                        <p class="text-gray-600"><span class="font-medium">Contact:</span> {{ orders?.branch?.contact }}</p>
                                        <p class="text-gray-600"><span class="font-medium">Branch #:</span> {{ orders?.branch?.address }}, {{ orders?.branch?.name }}</p>
                                    </div>

                                    <div class="w-1/3">
                                        <h2 class="text-lg font-semibold text-gray-700 mb-2">Order Information</h2>
                                        <p class="text-gray-600"><span class="font-medium">Order #:</span> {{ orders?.order_no }}</p>
                                        <p class="text-gray-600"><span class="font-medium">Date:</span> {{ dateMonthFunction(orders?.created_at) }}</p>
                                        <p class="text-gray-600"><span class="font-medium">Status:</span> <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-lg">{{ orders?.status?.name }}</span></p>
                                        <p class="text-gray-600 italic"><span class="font-medium font-bold">Note:</span> {{ orders?.shipping_info?.note }}</p>
                                    </div>

                                    <div class="w-1/3">
                                        <h2 class="text-lg font-semibold text-gray-700 mb-2">Shipping Address</h2>
                                        <p class="text-gray-600"><span class="font-medium">Name:</span> {{ orders?.shipping_info?.full_name ?? orders?.user?.name }}</p>
                                        <p class="text-gray-600"><span class="font-medium">Phone:</span> {{ orders?.shipping_info?.phone ?? orders?.user?.mobile  }}</p>
                                        <template v-if="orders?.shipping_info">
                                            <p class="text-gray-600"><span class="font-medium">District:</span> {{ orders?.shipping_info?.district?.name }}, <span class="font-medium">Thana:</span> {{ orders?.shipping_info?.thana }}</p>
                                            <p class="text-gray-600"><span class="font-medium">Address:</span> {{ orders?.shipping_info?.address }}</p>
                                        </template>
                                        <template v-else>
                                            <p class="text-gray-600"> Payment: <b class="uppercase">{{ orders?.transactions[0].payment_method }}</b></p>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Items -->
                            <div class="p-6 border-b border-gray-200">
                                <div class="text-xl font-semibold text-black w-full place-items-center pb-2">
                                    <div class="border rounded-md w-1/3 text-center border-black">Order Items</div>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead>
                                            <tr class="bg-gray-300 text-left text-xs font-medium text-black uppercase tracking-wider">
                                                <th class="px-4 py-3">Product</th>
                                                <th class="px-4 py-3">Price</th>
                                                <th class="px-4 py-3">Qty</th>
                                                <th class="px-4 py-3">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            <tr v-for="order in orders?.items">
                                                <td class="px-4 py-4">
                                                    <div class="flex items-center">
                                                        <div class="h-16 w-16 bg-gray-200 rounded-md overflow-hidden">
                                                            <img :src="`/products/${order.icon}`" :alt="`${order.item_name }`" class="h-full w-full object-cover">
                                                            <!-- <img :src="`/products/${order.icon}`" :alt="`${order.item_name }`" class="h-full w-full object-cover"> -->
                                                        </div>
                                                        <del v-if="order?.deleted_at != null">
                                                            <div class="ml-4">
                                                                <p class="font-medium text-gray-900 text-sm">{{ order.item_name }}</p>
                                                                <p class="text-sm text-gray-500">
                                                                    color: {{ order.color }} | Size: {{ order.size }}
                                                                </p>
                                                            </div>
                                                        </del>
                                                        <div v-else class="ml-4">
                                                            <p class="font-medium text-gray-900 text-sm">{{ order.item_name }}</p>
                                                            <p class="text-sm text-gray-500">
                                                                color: {{ order.color }} | Size: {{ order.size }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-4 text-gray-900 text-sm flex gap-2">
                                                    <del v-if="order.regular_price > 0"><Currency :amount="order.regular_price"/></del>
                                                    <Currency :amount="order.current_price"/>
                                                </td>
                                                <td class="px-4 py-4 text-gray-900 text-sm">{{ order?.quantity }}</td>
                                                <td class="px-4 py-4 text-gray-900 text-sm flex gap-2">
                                                    <del v-if="order?.deleted_at != null"><Currency :amount="(order?.quantity * order?.current_price)"/></del>
                                                    <span v-else><Currency :amount="(order?.quantity * order?.current_price)"/></span>
                                                </td>


                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Order Totals -->
                            <div class="p-6 border-gray-200">
                                <div class="flex justify-end">
                                    <div class="w-full max-w-xs">
                                        <div class="flex justify-between py-2 border-b border-gray-200">
                                            <span class="text-gray-600">Subtotal:</span>
                                            <span class="text-gray-900"><Currency :amount="orders?.total ?? 0"/></span>
                                        </div>
                                        <div class="flex justify-between py-2">
                                            <span class="text-gray-600">Shipping:</span>
                                            <span class="text-gray-900"><Currency :amount="shipping_cost ?? 0"/></span>
                                        </div>

                                        <div class="flex justify-between py-2" v-if="orders?.discount > 0">
                                            <span class="text-gray-600">Discount:</span>
                                            <span class="text-gray-900"><Currency :amount="orders?.discount ?? 0"/></span>
                                        </div>



                                        <div class="flex justify-between py-2 border-t border-gray-200 mt-2 pt-2">
                                            <span class="text-lg font-semibold text-gray-800">Total:</span>
                                            <span class="text-lg font-semibold text-gray-800"><Currency :amount="((orders?.total ?? 0) - (orders?.discount ?? 0))"/></span>
                                        </div>



                                        <div v-if="((orders?.total ?? 0) - (orders?.discount ?? 0)) > totalCreat" class="flex justify-between py-2 border-t border-gray-200 mt-2 pt-2">
                                            <span class="text-lg font-semibold text-gray-800">Pay:</span>
                                            <span class="text-lg font-semibold text-gray-800"><Currency :amount="totalCreat"/></span>
                                        </div>


                                        <!-- <div v-if="totalCreat > 0" class="flex justify-between py-2 border-t border-gray-200 mt-2 pt-2">
                                            <span class="text-lg font-semibold text-gray-800">Return:</span>
                                            <span class="text-lg font-semibold text-gray-800"><Currency :amount="(totalCreat - totalDebit)"/></span>
                                        </div> -->


                                        <div v-if="((orders?.total ?? 0) - (orders?.discount ?? 0)) > totalCreat" class="flex justify-between py-2 border-t border-gray-200 mt-2 pt-2">
                                            <span class="text-xl font-bold text-gray-800">Due:</span>
                                            <span class="text-xl font-bold text-gray-800"><Currency :amount="(((orders?.total ?? 0) - (orders?.discount ?? 0)) +(shipping_cost ?? 0) - totalCreat)"/></span>
                                        </div>

                                        <div v-else class="flex justify-between py-2 border-t border-gray-200 mt-2 pt-2">
                                            <span class="text-xl font-bold text-gray-800">Paid:</span>
                                            <span class="text-xl font-bold text-gray-800"><Currency :amount="((orders?.total ?? 0) - (orders?.discount ?? 0))"/></span>
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </div>
</template>

