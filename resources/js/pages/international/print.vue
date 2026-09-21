<script setup lang="ts">
    import AppLayout from '@/layouts/AppLayout.vue';
    import { Head, usePage, Link, router } from '@inertiajs/vue3';
    import { computed, onMounted, ref, watch, nextTick } from 'vue'
    import { Icon } from '@iconify/vue';
    import { useDataDate } from '@/composables/useDataDate';
    import Currency from '@/components/Currency/index.vue';
    import { useToast } from "primevue/usetoast";
    import { useHistory } from '@/composables/useHistory'

    const breadcrumbs = ref([
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Orders', href: '/International' },
    ]);

    const page = usePage();

    const props = defineProps({
        single: Object,
        status: Object,
    });

    const { back, history } = useHistory()
    const toast = useToast();


    onMounted(() => {

        nextTick(() => {
            setTimeout(() => {
                window.print();

                window.onafterprint = () => {
                    if (document.referrer) {
                        router.visit(document.referrer)
                    } else {
                        router.visit('/international_order') // fallback if no referrer
                    }

                };
            }, 50); // Small delay
        })
    })

    const { dateFunction, dateMonthFunction } = useDataDate();



</script>

<template>
    <Head title="Order" />

        <div class="w-full flex flex-row place-content-center mb-4">

            <div class="w-full mt-4 bg-white rounded-md">
                <div class="flex justify-between px-2 py-1 items-center rounded-t-md">
                    <div class="w-1/3">
                        <img src="/assets/sites/logo.png" class="w-[150px]"/>
                        <!-- <h2 class="text-lg font-semibold text-gray-700 mb-2">Lukaz Shop</h2> -->
                        <p class="text-gray-600"><span class="font-medium">E-mail:</span> lukazshop@gmail.com</p>
                        <p class="text-gray-600"><span class="font-medium">Contact:</span> 01752-058475</p>
                        <p class="text-gray-600"><span class="font-medium">Heade Office #:</span> Uttra, Dhaka, Bangladesh</p>
                    </div>
                    <div class="flex print:hidden">
                        <div @click="back" class="bg-green-600 p-2 rounded-md text-lg px-3 flex text-black text-white cursor-pointer"> <Icon icon="line-md:arrow-left" class="mr-2" width="1.5rem" height="1.5rem" /> Back </div>
                    </div>
                </div>

                <div class="border py-1 bg-white">
                    <div class="w-full mx-auto">
                        <div class="bg-white overflow-hidden">
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

                    </div>
                </div>
            </div>
        </div>
</template>

