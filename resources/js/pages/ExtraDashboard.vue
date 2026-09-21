<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { Icon } from '@iconify/vue';
import Pagination from '@/components/Pagination.vue';

import Dialog from 'primevue/dialog';


import Chart from 'primevue/chart';
import { ref, onMounted } from 'vue';


const props = defineProps({
    orders: Object,
    check_stocks: Object,
    graphs: Array,
    total_order: Number,
    today_orders: Number,
    dilivered: Number,
    cancel: Number,
    today_offline_sale: Number,
    international: Number,
});

const offlineSale = props.today_offline_sale;
const onlineSale = props.today_orders;

const total = (offlineSale + onlineSale)
const percentageDiff = ((offlineSale  / total) * 100 )


const onlinePercentageDiff = ((onlineSale / total ) * 100)


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const loading = ref(false);
const pageNumber = ref(props?.orders?.current_page);

onMounted(() => {
    chartData.value = setChartData();
    chartOptions.value = setChartOptions();
});

const chartData = ref();
const chartOptions = ref();
const visible = ref(false);

const setChartData = () => {
    const documentStyle = getComputedStyle(document.documentElement);

    const apiColors = [
        '#4CAF50',
        '#9E9E9E',
        '#00BFFF',
        '#FF9800'
        ];


    const graphsArray = Object.values(props.graphs);

    const datasets = graphsArray.map((dataset, index) => ({
        ...dataset,
        backgroundColor: apiColors[index] || '#000000' // fallback color
    }));

    return {
        labels: [],
        datasets: datasets
    };


};
const setChartOptions = () => {
    const documentStyle = getComputedStyle(document.documentElement);
    const textColor = documentStyle.getPropertyValue('--p-text-color');
    const textColorSecondary = documentStyle.getPropertyValue('--p-text-muted-color');
    const surfaceBorder = documentStyle.getPropertyValue('--p-content-border-color');

    return {
        maintainAspectRatio: false,
        aspectRatio: 0.8,
        plugins: {
            legend: {
                labels: {
                    color: textColor
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    color: textColorSecondary,
                    font: {
                        weight: 500
                    }
                },
                grid: {
                    display: false,
                    drawBorder: false
                }
            },
            y: {
                ticks: {
                    color: textColorSecondary
                },
                grid: {
                    color: surfaceBorder,
                    drawBorder: false
                }
            }
        }
    };
}


// page change 0r pagination goes here

const goToPage = () => {
        if (pageNumber.value < 1) {
            pageNumber.value = 1;
        }

        loading.value = false;

        router.get(`/dashboard/extra?page=${pageNumber.value}`, {}, {
            only: ['orders'],       // must be in the 3rd argument
            preserveState: true,    // keep component state (optional)
            preserveScroll: true,   // keep scroll position (optional)
            onFinish: () => {
                loading.value = false
            },
        })
        // router.get('/orders?page=' + pageNumber.value, filter, {


    };

</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs" :loading="loading">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="grid auto-rows-min gap-4 md:grid-cols-6">
                <div class="relative grid items-center p-2 aspect-video overflow-hidden rounded-md border border-sidebar-border/70 dark:border-sidebar-border bg-white">
                    <PlaceholderPattern />
                    <div class="flex justify-between">
                        <div class="font-semibold">
                            Total Orders
                        </div>
                        <div class="rounded-full bg-green-100 p-2 text-green-600">
                            <Icon icon="fluent:cart-16-filled" width="1.5rem" height="1.5rem" class=""  />
                        </div>
                    </div>
                    <div class="flex justify-between gap-2 relative">
                        <div class="font-bold text-3xl">{{ total_order }}</div>
                        <div class="text-rose-300 pr-3 absolute bottom-0 right-0"><Icon icon="entypo:line-graph" width="1.5rem" height="1.5rem" /></div>
                    </div>

                </div>
                <div class="relative grid items-center p-2 aspect-video overflow-hidden rounded-md border border-sidebar-border/70 dark:border-sidebar-border bg-white">
                    <PlaceholderPattern />
                    <div class="flex justify-between">
                        <div class="font-semibold">
                            Today Orders
                        </div>
                        <div class="rounded-full bg-green-100 p-2 text-green-600">
                            <Icon icon="mingcute:refresh-3-fill" width="1.5rem" height="1.5rem" class=""  />
                        </div>
                    </div>
                    <div class="flex justify-between gap-2 relative">
                        <div class="font-bold text-3xl">{{ today_orders }}</div>
                        <div :class="onlinePercentageDiff >= 0 ? 'text-green-800' : 'text-red-800'" class=" pr-3 text-xs flex gap-2 absolute bottom-0 right-0">
                            <Icon icon="wpf:online" />
                            <div>
                               {{ onlinePercentageDiff > 0 ? (onlinePercentageDiff).toFixed(2) : '0' }} %
                            </div>
                            <Icon v-if="onlinePercentageDiff >= 0" icon="streamline:graph-arrow-increase" class="" />
                            <Icon v-else icon="streamline:graph-arrow-decrease" class="" />

                        </div>
                    </div>

                </div>

                <div class="relative grid items-center p-2 aspect-video overflow-hidden rounded-md border border-sidebar-border/70 dark:border-sidebar-border bg-white">
                    <PlaceholderPattern />
                    <div class="flex justify-between">
                        <div class="font-semibold">
                           Todays Offline Sale
                        </div>
                        <div class="rounded-full bg-gray-100 p-2 text-gray-800">
                            <Icon icon="bxs:store" width="1.5rem" height="1.5rem" class=""  />
                        </div>
                    </div>
                    <div class="flex justify-between gap-2 relative">
                        <div class="font-bold text-3xl">{{ today_offline_sale }}</div>
                        <div :class="(percentageDiff) >= 0 ? 'text-green-800' : 'text-red-800'" class=" pr-3 text-xs flex gap-2 absolute bottom-0 right-0">
                            <Icon icon="nrk:offline" class="" />
                            <div>
                               {{ percentageDiff > 0 ? (percentageDiff).toFixed(2) : '0' }} %
                            </div>
                            <Icon v-if="percentageDiff >= 0" icon="streamline:graph-arrow-increase" class="" />
                            <Icon v-else icon="streamline:graph-arrow-decrease" class="" />

                        </div>
                    </div>

                </div>

                <div class="relative grid items-center p-2 aspect-video overflow-hidden rounded-md border border-sidebar-border/70 dark:border-sidebar-border bg-white">
                    <PlaceholderPattern />
                    <div class="flex justify-between">
                        <div class="font-semibold">
                            International Order
                        </div>
                        <div class="rounded-full bg-green-100 p-2 text-black">
                            <Icon icon="fa:globe" width="1.5rem" height="1.5rem" class=""  />
                        </div>
                    </div>
                    <div class="flex justify-between gap-2 relative">
                        <div class="font-bold text-3xl">{{ international }}</div>
                        <div class="text-green-800 pr-3 absolute bottom-0 right-0 flex text-xs gap-2">
                            <Icon icon="bxs:plane-take-off" />
                            <div>{{ international > 0 ? (( international / total_order ) * 100).toFixed(2) : 0 }}%</div>
                            <Icon icon="streamline:graph-arrow-increase" class="" />
                        </div>
                    </div>

                </div>

                <div class="relative grid items-center p-2 aspect-video overflow-hidden rounded-md border border-sidebar-border/70 dark:border-sidebar-border bg-white">
                    <PlaceholderPattern />
                    <div class="flex justify-between">
                        <div class="font-semibold">
                            Delivered
                        </div>
                        <div class="rounded-full bg-green-100 p-2 text-black">
                            <Icon icon="solar:cart-check-bold" width="1.5rem" height="1.5rem" class=""  />
                        </div>
                    </div>
                    <div class="flex justify-between gap-2 relative">
                        <div class="font-bold text-3xl ">{{ dilivered }}</div>
                        <div class="text-green-800 pr-3 absolute bottom-0 right-0 flex text-xs gap-2">
                            <Icon icon="material-symbols:delivery-truck-speed-outline-rounded" />
                            <div>{{ dilivered > 0 ? ((dilivered  / total_order) * 100).toFixed(2) : 0 }}%</div>
                            <Icon icon="streamline:graph-arrow-increase" class="" />
                        </div>
                    </div>

                </div>

                <div class="relative grid items-center p-2 aspect-video overflow-hidden rounded-md border border-sidebar-border/70 dark:border-sidebar-border bg-white">
                    <PlaceholderPattern />
                    <div class="flex justify-between">
                        <div class="font-semibold">
                            Total Cancel
                        </div>
                        <div class="rounded-full bg-red-100 p-2 text-red-800">
                            <Icon icon="solar:cart-check-bold" width="1.5rem" height="1.5rem" class=""  />
                        </div>
                    </div>
                    <div class="flex justify-between gap-2 relative">
                        <div class="font-bold text-3xl">{{ cancel }}</div>
                        <div class="text-green-800 pr-3 text-xs flex gap-2 absolute bottom-0 right-0">
                            <Icon icon="streamline:graph-arrow-increase" class="" />{{ cancel > 0 ? ((cancel / total_order) * 100).toFixed(2) : 0 }} %
                        </div>
                    </div>

                </div>




            </div>
            <div class="flex gap-4">
                <div class="relative min-h-[100vh] flex-1 md:min-h-min dark:border-sidebar-border w-2/3">
                    <div class="w-full bg-white rounded-md border border-sidebar-border/70 relative">
                        <Chart type="bar" :data="chartData" :options="chartOptions" class="h-[30rem]"  />
                        <Icon icon="picon:fullscreen" label="Full Screen" @click="visible = true"  class="absolute top-2 right-3 cursor-pointer"/>

                    </div>

                    <div class="grid auto-rows-min gap-4 flex-1 flex-row md:grid-cols-3 pt-4">
                        <div v-for="stock in check_stocks" class="relative grid p-2 aspect-video overflow-hidden rounded-md border border-sidebar-border/70 dark:border-sidebar-border bg-white">
                            <PlaceholderPattern />
                            <div class="flex justify-between">
                                <div class="font-semibold">
                                    {{ stock.name }}
                                </div>

                            </div>
                            <div class="flex justify-between gap-2 relative">
                                <div class="font-bold text-3xl">{{ stock.stocks_sum_stock >= 1000 ? (stock.stocks_sum_stock/1000)+'k' : stock.stocks_sum_stock }}
                                <small class="text-xs text-gray-400">Stocks Avaiable </small>

                                </div>
                                <div class="text-gray-600 pr-3 absolute bottom-0 right-0"><Icon icon="fluent:box-multiple-20-filled" width="2.5rem" height="2.5rem" class=""  /></div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="relative min-h-[100vh] flex-1 md:min-h-min dark:border-sidebar-border w-1/3 bg-white rounded-md border border-sidebar-border/70">
                    <div class="w-full py-1 pl-3 border-b">Recent Orders</div>
                    <div class="table w-full p-2">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-400">
                                    <th>SL</th>
                                    <th class="text-left">Order No</th>
                                    <th class="text-left">Items</th>
                                    <th class="text-left">Qty</th>
                                    <th class="text-right">Total Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(order, index) in Object.values(orders?.data)" class="bg-gray-100 even:bg-white cursor-pointer hover:bg-gray-300">
                                    <td class="text-center">{{ ((orders?.current_page - 1) * orders?.per_page) + (index + 1) }}</td>
                                    <td><Link :href="`/orders/${order.order_no}/view`" class="hover:text-blue-500 text-pink-600">{{ order?.order_no }}</Link> </td>
                                    <td>{{ order?.items_count }}</td>
                                    <td>{{ order?.quantity }}</td>
                                    <td class="text-right">
                                        {{ order?.grand_total }}
                                    </td>
                                    <td class="text-center">{{ order?.status?.name }}</td>
                                </tr>
                            </tbody>

                        </table>

                        <div class="flex justify-between w-full px-3 py-2 border-t absolute bottom-0 right-0">
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
                </div>

            </div>
        </div>

        <Dialog v-model:visible="visible" modal header="Sale Graph" :style="{ width: '100vw', height: '100vw' }">
            <Chart type="bar" :data="chartData" :options="chartOptions" class="min-h-[calc(100vh-13rem)]"  />
        </Dialog>


    </AppLayout>




</template>
