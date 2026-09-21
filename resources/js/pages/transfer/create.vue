<script setup lang="ts">

    import AppLayout from '@/layouts/AppLayout.vue';
    import { Head, Link, usePage, router } from '@inertiajs/vue3';
    import { computed, onMounted, ref, watch } from 'vue'
    import { Icon } from '@iconify/vue';
    import AutoComplete from 'primevue/autocomplete';
    import { useToast } from "primevue/usetoast";


    const props = defineProps({
        additionals: Object,
        branchs: Object,
        stocks: Object,
    });

    const toast = useToast();
    const add = ref(null);
    const edit = ref(null);
    const deleted = ref(null);

    const loading = ref(false)


    const items = ref();

    const filterItems = ref();
    const filterStatus = ref();
    const filterBranchs = ref();

    const branchs = ref(props?.branchs);
    const fromBranch = ref();
    const toBranch = ref();
    const product = ref(null);
    const stockList = ref({});
    const qty = ref(1);
    const indexCount = ref(1)
    const selectStatus = ref({
            id : 1,
            name : "Request",
        })
    const status = ref([
        {
            id : 1,
            name : "Request",
        },
        {
            id : 2,
            name : "Send",
        }

    ])

    watch(
        () => props.stocks,
        (newStocks) => {
            items.value = newStocks ?? []
        },
        { immediate: true }
    )

    watch(fromBranch, (branch) => {
        if (!branch) return;

        filterItems.value = [];

        router.get(
            `/stock_transfer/${branch.id}/getStock`,
            {},
            { preserveState: true, only: ['stocks'] }
        );

        items.value = props.stocks;

    });

    const breadcrumbs = ref([
        { title: 'Dashboard', href: '/' },
        { title: 'Stock Transfer', href: '/stock_transfer' },
        { title: 'Create', href: '' },
    ]);


    const search = (event) => {
        setTimeout(() => {
            const source = items.value ?? [];

            filterItems.value = source.filter(item => {
                const query = event.query?.trim()?.toLowerCase() || '';
                if (!query) return true; // If query is empty, include all items

                // Check multiple fields
                const nameMatch = item.product?.name?.toLowerCase().includes(query);
                const skuMatch = item?.sku?.toLowerCase().includes(query);

                // Return true if any field matches
                return nameMatch || skuMatch;
            })
            .map(item => ({
                ...item,
                label: item.product.name
            }));
        }, 250);
    };




    const branchSearch = (event) => {

        setTimeout(() => {
            if (!event.query.trim().length) {
                filterBranchs.value = [...branchs.value];
            } else {
                filterBranchs.value = items.value.filter((item) => {
                    return item.name.toLowerCase().startsWith(event.query.toLowerCase());
                });
            }
        }, 250);
    }


    const statusSearch = (event) => {

        setTimeout(() => {
            if (!event.query.trim().length) {
                filterStatus.value = [...status.value];
            } else {
                filterStatus.value = items.value.filter((item) => {
                    return item.name.toLowerCase().startsWith(event.query.toLowerCase());
                });
            }
        }, 250);
    }





    const addTransfer = () => {



        stockList.value = {
            ...stockList.value,
            [product?.value?.id] : {
                fromBranchName: fromBranch?.value.name,
                fromBranchId: fromBranch?.value.id,
                toBranchName: toBranch?.value.name,
                toBranchId: toBranch?.value.id,
                productName: product?.value?.product?.name,
                productId: product?.value?.product?.id,
                stockId: product?.value?.id,
                productColor: product?.value.color,
                productSize: product?.value.size,
                productSku: product?.value.sku,
                qty: parseInt(qty.value),
                avaiable: product?.value.stock,
            }

        }

    }


    const updateStockQty = (id, qty) => {

        if (stockList.value[id]) {
            stockList.value[id].qty = parseInt(qty)
        }

    }


    const transfer = () => {

        const dataSend = {
            items : stockList.value,
            status: selectStatus.value?.id,
        }


        loading.value = true;

        router.post(route('stock.transfer.store'), dataSend, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            forceFormData: true,
            onSuccess: () => {
                toast.add({ severity: 'success', summary: 'Success Message', detail: 'Update successfully!', life: 3000 });

                loading.value = false;

                // router.push('stock_transfer')   // relative route
            },
            onError: (e) => {
                toast.add({ severity: 'error', summary: 'Error Message', detail: 'Oops! Something wrongs', life: 3000 });
                loading.value = false

            },
        })
    }


    const removeQty = (index) => {

        const entries = Object.entries(stockList.value);
        // remove by index
        entries.splice(index, 1);
        // convert back to object map
        stockList.value = Object.fromEntries(entries);
    }




</script>

<template>

    <Head title="Stock Transfer" />

    <AppLayout :breadcrumbs="breadcrumbs" :loading="loading">
         <Toast position="top-right" />
        <div class="w-full mx-auto p-2 flex flex-row place-content-center">
            <div class="w-5/6 mt-4 shadow-lg bg-white rounded-md pb-2">
                <div class="flex justify-between bg-gray-300 px-2 py-1 items-center rounded-t-md">
                    <div class="text-lg font-semibold">Stock Transfer</div>
                    <div class="flex">
                        <Link href="/stock_transfer" class="bg-rose-600 p-2 px-3 rounded-md text-lg flex items-center text-white cursor-pointer">  <Icon icon="line-md:arrow-left" class="mr-2" width="1.5rem" height="1.5rem" />
                            Back
                        </Link>
                    </div>
                </div>

                <!-- main content goes here -->

                <div class="border px-3 py-1 h-[calc(100vh-15rem)] overflow-auto pb-3">

                    <div class="grid grid-cols-4 gap-4 mt-4">
                        <div class="card w-full justify-center">
                            <label for="dd-city" class="text-md w-full content-center ">From</label>
                            <AutoComplete v-model="fromBranch" dropdown :suggestions="filterBranchs" size="small"  optionLabel="name" inputClass="w-full text-sm" @complete="branchSearch" class="w-full" />

                        </div>

                        <div class="card w-full justify-center">
                            <label for="dd-city" class="text-md w-full content-center ">Stock To</label>
                            <AutoComplete v-model="toBranch" dropdown :suggestions="filterBranchs" size="small"  optionLabel="name" inputClass="w-full text-sm" @complete="branchSearch" class="w-full" />

                        </div>

                        <div class="card w-full justify-center">
                            <label for="dd-city" class="text-md w-full content-center ">Product</label>
                            <div class="card flex justify-center w-full">
                                <AutoComplete v-model="product" size="small" optionLabel="label" class="w-full" inputClass="w-full text-sm"  dropdown :suggestions="filterItems" @complete="search">
                                    <template #option="slotProps" class="w-full">
                                        <div class="items-center w-full">
                                            <div class="text-md">{{ slotProps.option.product?.name }}</div>
                                            <div class="text-xs">{{ slotProps.option.stock }} | {{ slotProps.option.size }} | {{ slotProps.option.color }} | {{ slotProps.option.sku }}</div>
                                        </div>
                                    </template>
                                    <template #header>
                                        <div class="font-medium px-3 py-2 ">Available Products</div>
                                    </template>
                                </AutoComplete>
                            </div>
                        </div>

                        <div class="card w-full justify-center" v-if="fromBranch != toBranch">
                            <label for="dd-city" class="text-md w-full content-center text-white">.</label>
                            <div class="card flex justify-start">
                                <button class="bg-green-600 py-1 px-4 font-semibold text-white rounded-sm" @click="addTransfer">Add</button>
                            </div>
                        </div>
                    </div>


                    <div class="w-full border-t mt-4">
                        <table class="w-full">
                            <thead>

                                <tr class="bg-gray-600 text-white">
                                    <th>SL</th>
                                    <th>Product Name</th>
                                    <th>From Branch</th>
                                    <th>To Branch</th>
                                    <th>Available</th>
                                    <th>Transfer Qty Stock</th>
                                    <th>...</th>
                                </tr>
                            </thead>
                            <tbody>

                                <tr class="odd:bg-white even:bg-gray-200 text-gray-800" v-for="(stock, index) in Object.values(stockList)" :key="index">
                                    <td>{{ index +1 }}</td>
                                    <td>
                                        <div class="text-bold">{{ stock?.productName }}</div>
                                        <small>{{ stock?.productSize }} | {{ stock?.productColor }} | {{ stock?.productSku }}</small>

                                    </td>
                                    <td>{{ stock?.fromBranchName }}</td>
                                    <td>{{ stock?.toBranchName }}</td>
                                    <td>{{ stock?.avaiable }}</td>
                                    <td class="p-2">
                                        <input type="number" @keyup="updateStockQty(stock?.stockId, $event.target.value)" :value="stock?.qty" min="1" :max="stock?.avaiable" class=" pl-4 p-2 w-full outline-none focus:border-green-200 border rounded-sm" />
                                    </td>
                                    <td class="">
                                        <Icon icon="gravity-ui:trash-bin" @click="removeQty(index)" title="Remove" class="content-center cursor-pointer bg-red-500 w-full h-full p-1 text-white rounded-sm "/>
                                    </td>
                                </tr>
                            </tbody>

                        </table>
                    </div>


                </div>

                <div class="flex justify-end items-end gap-4 mr-3">
                    <div class="card">
                        <label for="dd-city" class="text-md block mb-1">Status</label>
                        <AutoComplete v-model="selectStatus" dropdown :suggestions="filterStatus" size="small" optionLabel="name" @complete="statusSearch" inputClass="w-full text-sm" class="w-48"/>
                    </div>

                    <button class="bg-blue-600 px-4 py-2 rounded text-white font-semibold" @click="transfer">
                        Product Transfer
                    </button>
                </div>



                <!-- main content goes here -->

            </div>

        </div>
    </AppLayout>
</template>
<style scoped>
    table tr td, th {
        padding:4px;
        text-align: left;

    }
</style>
