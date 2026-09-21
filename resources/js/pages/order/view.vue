<script setup lang="ts">
    import AppLayout from '@/layouts/AppLayout.vue';
    import { Head, usePage, Link, router } from '@inertiajs/vue3';
    import { computed, onMounted, ref, watch } from 'vue'
    import { Icon } from '@iconify/vue';
    import { useDataDate } from '@/composables/useDataDate';
    import Currency from '@/components/Currency/index.vue';
    import { useToast } from "primevue/usetoast";
    import { useHistory } from '@/composables/useHistory'

    const breadcrumbs = ref([
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Orders', href: '/orders' },
    ]);

    const props = defineProps({
        orders: Object,
        status: Object,
        consignment: Object,
        steadfastEnabled: Boolean,
    });

    const { back, history } = useHistory()

    const toast = useToast();
    const branchData = ref({});
    const statusId = ref(props?.orders?.status?.id);
    const orderNo = ref(props?.orders?.order_no);


    const loading = ref(false);
    const sending = ref(false);

    const totalCreat = ref(0);
    const totalDebit = ref(0);
    const shipping_cost = ref(0);

    onMounted(() => {
        totalCreat.value = props?.orders?.transactions.reduce((acc, item) => acc + item.credit, 0);
        totalDebit.value = props?.orders?.transactions.reduce((acc, item) => acc + item.debit, 0);
        shipping_cost.value =  props?.orders?.shipping_info?.district?.courier_charge;

    })

    // Discount rows are credited in the ledger too, so they cannot count as cash.
    const paidAmount = computed(() => (props.orders?.transactions ?? [])
        .filter(txn => txn.payment_method !== 'discount')
        .reduce((acc, txn) => acc + Number(txn.credit || 0), 0));

    const netTotal = computed(() => (Number(props.orders?.total ?? 0)
        - Number(props.orders?.discount ?? 0)
        - Number(props.orders?.additional_discount ?? 0)));

    const dueAmount = computed(() => netTotal.value + Number(shipping_cost.value ?? 0) - paidAmount.value);


    const { dateFunction, dateMonthFunction } = useDataDate();

    const getBranch = (additionalKey, e) => {

        branchData.value = {
            ...branchData.value,
            [additionalKey] : parseInt(e.target.value) // product id $productId, $branchId
        }

    }


    watch( () => props?.orders, (newOrders) => {
            if (newOrders?.items) {
                newOrders.items.forEach(order => {
                    if (order.stock?.length > 0  && order?.branch_id == null) {
                        branchData.value[order.id] = order.stock[0].branchs.id // set first branch by default
                    }else{
                        branchData.value[order.id] = order.branch_id// set first branch by default
                    }
                })
            }
        },
        { immediate: true } // run on first load too
    )


    // update status function

    const orderUpdate = () => {

        loading.value = true;

        let formData = {
            'status_id' : statusId.value,
            'branch_data' : branchData.value,
        }


        router.post('/orders/'+orderNo.value, formData, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            forceFormData: true,
            onSuccess: () => {
                loading.value = false;
                toast.add({ severity: 'success', summary: 'Success Message', detail: 'Product store successfully!', life: 3000 });


            },
            onError: (e) => {
                loading.value = false;
                toast.add({ severity: 'error', summary: 'Error Message', detail: 'Oops! Something wrongs', life: 3000 });

            },
        });

    }


    /**
     * Hand this parcel to Steadfast. The server refuses a second booking, but
     * the button is disabled once a consignment exists so a double click does
     * not even reach it.
     */
    const sendToSteadfast = () => {

        if (sending.value || props.consignment) {
            return;
        }

        sending.value = true;

        router.post(`/steadfast/order/${orderNo.value}/send`, {}, {
            preserveScroll: true,
            onFinish: () => {
                sending.value = false;
            },
        });
    }


    // update status function

    const itemUpdate = (additional) => {

        loading.value = true;

        router.get(`/orders/${additional}/items_update`, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            onSuccess: () => {
                toast.add({
                    severity: 'success',
                    summary: 'Success Message',
                    detail: 'Product stored successfully!',
                    life: 3000,
                });

                loading.value = false;
            },
            onError: (e) => {
                toast.add({
                    severity: 'error',
                    summary: 'Error Message',
                    detail: 'Oops! Something went wrong',
                    life: 3000,
                });

                loading.value = false;
            },
        });

    }


</script>

<template>
    <Head title="Order" />

    <AppLayout :breadcrumbs="breadcrumbs" :loading="loading">
        <Toast position="top-right" />
        <div class="w-full flex flex-row place-content-center mb-4">
            <div class="w-5/6 mt-4 shadow-lg bg-white rounded-md">
                <div class="flex justify-between bg-gray-300 px-2 py-1 items-center rounded-t-md">
                    <div class="text-lg font-semibold">Order No# {{ orders?.order_no }}</div>
                    <div class="flex">
                        <div @click="back" class="bg-green-600 p-2 rounded-md text-lg px-3 flex text-black text-white cursor-pointer"> <Icon icon="line-md:arrow-left" class="mr-2" width="1.5rem" height="1.5rem" /> Back </div>
                    </div>
                </div>

                <div class="border py-1 min-h-screen bg-white rounded-md">
                    <div class="w-full mx-auto">
                        <div class="bg-white overflow-hidden">
                            <!-- Order Summary -->
                            <div class="p-6 border-b border-gray-200">
                                <div class="flex justify-between">
                                    <div>
                                        <h2 class="text-lg font-semibold text-gray-700 mb-2">Lukaz Shop</h2>
                                        <p class="text-gray-600"><span class="font-medium">E-mail:</span> lukazshop@gmail.com</p>
                                        <p class="text-gray-600"><span class="font-medium">Contact:</span> 01752-058475</p>
                                        <p class="text-gray-600"><span class="font-medium">Heade Office #:</span> Uttra, Dhaka, Bangladesh</p>
                                    </div>

                                    <div>
                                        <h2 class="text-lg font-semibold text-gray-700 mb-2">Order Information</h2>
                                        <p class="text-gray-600"><span class="font-medium">Order #:</span> {{ orders?.order_no }}</p>
                                        <p class="text-gray-600"><span class="font-medium">Date:</span> {{ dateMonthFunction(orders?.created_at) }}</p>
                                        <p class="text-gray-600"><span class="font-medium">Status:</span> <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-lg">{{ orders?.status?.name }}</span></p>
                                        <p class="text-gray-600 italic"><span class="font-medium font-bold">Note:</span> {{ orders?.shipping_info?.note }}</p>
                                    </div>

                                    <div>
                                        <h2 class="text-lg font-semibold text-gray-700 mb-2">Shipping Address</h2>
                                        <p class="text-gray-600"><span class="font-medium">Name:</span> {{ orders?.shipping_info?.full_name ?? orders?.user?.name }}</p>
                                        <p class="text-gray-600"><span class="font-medium">Phone:</span> {{ orders?.shipping_info?.phone ?? orders?.user?.mobile  }}</p>
                                        <template v-if="orders?.shipping_info">
                                            <p class="text-gray-600"><span class="font-medium">District:</span> {{ orders?.shipping_info?.district?.name }}, <span class="font-medium">Thana:</span> {{ orders?.shipping_info?.thana }}</p>
                                            <p class="text-gray-600"><span class="font-medium">Address:</span> {{ orders?.shipping_info?.address }}</p>
                                            <p class="text-gray-600" v-if="orders?.transactions[0]?.payment_method"><span class="font-medium">Payment:</span> {{ orders?.transactions[0].payment_method }}</p>
                                        </template>
                                        <template v-else>
                                            <p class="text-gray-600"> Payment: <b class="uppercase">{{ orders?.transactions[0].payment_method }}</b></p>
                                        </template>

                                        <!-- Courier: booking state, or the action to book -->
                                        <div class="mt-4 pt-3 border-t" v-if="steadfastEnabled && orders?.shipping_info">
                                            <template v-if="consignment">
                                                <p class="text-gray-600"><span class="font-medium">Steadfast:</span> consignment {{ consignment?.consignment_id ?? 'pending' }}</p>
                                                <p class="text-gray-600"><span class="font-medium">Tracking:</span> {{ consignment?.tracking_code ?? '-' }}</p>
                                                <p class="text-gray-600 capitalize"><span class="font-medium">Delivery:</span> {{ (consignment?.delivery_status ?? 'pending').replace(/_/g, ' ') }}</p>
                                            </template>
                                            <template v-else>
                                                <button
                                                    type="button"
                                                    class="bg-sky-600 hover:bg-sky-700 disabled:opacity-50 text-white rounded-md px-4 py-2 flex items-center gap-2"
                                                    :disabled="sending"
                                                    @click="sendToSteadfast"
                                                >
                                                    <Icon icon="mdi:truck-delivery" width="1.2rem" />
                                                    {{ sending ? 'Sending...' : 'Send to Steadfast' }}
                                                </button>
                                            </template>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- Order Items -->
                            <div class="p-6 border-b border-gray-200">
                                <div class="text-xl font-semibold text-black w-full place-items-center pb-2">
                                    <div class="border rounded-md w-2/12 text-center border-black">Order Items</div>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead>
                                            <tr class="bg-gray-300 text-left text-xs font-medium text-black uppercase tracking-wider">
                                                <th class="px-4 py-3">Product</th>
                                                <th class="px-4 py-3">Price</th>
                                                <th class="px-4 py-3">Qty</th>
                                                <th class="px-4 py-3">Total</th>
                                                <th class="px-4 py-3">Fulfilled From</th>
                                                <th class="px-4 py-3 text-right">...</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            <tr v-for="order in orders?.items">
                                                <td class="px-4 py-4">
                                                    <div class="flex items-center">
                                                        <div class="h-16 w-16 bg-gray-200 rounded-md overflow-hidden">
                                                            <img :src="`/products/${order.icon}`" :alt="`${order.item_name }`" class="h-full w-full object-cover">
                                                        </div>
                                                        <del v-if="order?.deleted_at != null">
                                                            <div class="ml-4 w-84">
                                                                <p class="font-medium text-gray-900 text-sm">{{ order.item_name }}</p>
                                                                <p class="text-sm text-gray-500">
                                                                    color: {{ order.color }} | Size: {{ order.size }}
                                                                </p>
                                                            </div>
                                                        </del>
                                                        <div v-else class="ml-4 w-84">
                                                            <p class="font-medium text-gray-900 text-sm">{{ order.item_name }}</p>
                                                            <p class="text-sm text-gray-500">
                                                                color: {{ order.color }} | Size: {{ order.size }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-4 text-gray-900 text-sm">
                                                    <del v-if="order.regular_price > 0">{{ order.regular_price }}</del>
                                                    {{ order.current_price }}
                                                </td>
                                                <td class="px-4 py-4 text-gray-900 text-sm">{{ order?.quantity }}</td>
                                                <td class="px-4 py-4 text-gray-900 text-sm">
                                                    <del v-if="order?.deleted_at != null">{{ ((order?.quantity * order?.current_price) - Number(order?.discount_amount || 0)) }}</del>
                                                    <span v-else>{{ ((order?.quantity * order?.current_price) - Number(order?.discount_amount || 0)) }}</span>
                                                    <span v-if="order?.discount_amount > 0" class="text-xs text-rose-500 ml-1">(-{{ order?.discount_amount }})</span>
                                                </td>
                                                <td class="px-4 py-4 text-gray-900">
                                                   <div v-if="order && !order.deleted_at && statusId != 8" class="ml-4">
                                                        <select @change="getBranch(order.id, $event)">
                                                            <option v-for="(stock, index) in order.stock || []" :key="index" :value="stock?.branchs?.id" :selected="stock?.branchs?.id == order?.branch_id">
                                                                {{ stock?.branchs?.name }}: <b>{{ stock?.stock }}</b>
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <div v-else-if="statusId == 8" class="ml-4">
                                                        <div v-for="(stock, index) in order.stock || []" :key="index">
                                                            <span v-if="branchData[order.additional_key] == stock?.id">
                                                                {{ stock?.branchs?.name }}
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <div v-else>---</div>
                                                </td>
                                                <td class="px-4 py-4 text-red-200 cursor-pointer">
                                                    <div v-for="(st, index) in status" :key="index" :value="st.id">
                                                        <span v-if="statusId == st.id"> {{ st.name  }} </span>
                                                    </div>
                                                    <span v-if="(order?.deleted_at != null)">Canceled</span>


                                                </td>
                                                <!-- <td v-else class="px-4 py-4 text-red-600 cursor-pointer" @click="itemUpdate(order.id)">Cancel</td> -->
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

                                        <div class="flex justify-between py-2" v-if="orders?.additional_discount > 0">
                                            <span class="text-gray-600">Additional Discount:</span>
                                            <span class="text-gray-900"><Currency :amount="orders?.additional_discount ?? 0"/></span>
                                        </div>



                                        <div class="flex justify-between py-2 border-t border-gray-200 mt-2 pt-2">
                                            <span class="text-lg font-semibold text-gray-800">Total:</span>
                                            <span class="text-lg font-semibold text-gray-800"><Currency :amount="netTotal"/></span>
                                        </div>


                                        <div class="flex justify-between py-2 border-t border-gray-200 mt-2 pt-2">
                                            <span class="text-lg font-semibold text-gray-800">Paid:</span>
                                            <span class="text-lg font-semibold text-gray-800"><Currency :amount="paidAmount"/></span>
                                        </div>


                                        <div v-if="dueAmount > 0" class="flex justify-between py-2 border-t border-gray-200 mt-2 pt-2">
                                            <span class="text-xl font-bold text-gray-800">Due:</span>
                                            <span class="text-xl font-bold text-gray-800"><Currency :amount="dueAmount"/></span>
                                        </div>

                                        <div v-else-if="dueAmount < 0" class="flex justify-between py-2 border-t border-gray-200 mt-2 pt-2">
                                            <span class="text-xl font-bold text-gray-800">Return:</span>
                                            <span class="text-xl font-bold text-gray-800"><Currency :amount="Math.abs(dueAmount)"/></span>
                                        </div>

                                        <div v-else class="flex justify-between py-2 border-t border-gray-200 mt-2 pt-2">
                                            <span class="text-xl font-bold text-gray-800">Fully Paid</span>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="py-4 border-t flex justify-between px-4">
                                <Link href="/orders" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
                                    <Icon icon="line-md:arrow-left" class="mr-2" width="1.5rem" height="1.5rem" /> Back
                                </Link>
                                <div class="space-x-3" v-if="statusId != 8">
                                    <select v-model="statusId" class="border p-2 rounded-md bg-gray-300 text-black focus:outline-none focus:ring-0 focus:border-transparent">
                                        <option v-for="(st, index) in status" :key="index" :value="st.id">{{ st.name  }} </option>
                                    </select>
                                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg cursor-pointer" @click="orderUpdate">
                                        Order Update
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

