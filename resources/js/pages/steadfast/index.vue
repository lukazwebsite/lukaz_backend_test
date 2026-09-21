<script setup lang="ts">

    import AppLayout from '@/layouts/AppLayout.vue';
    import { Head, usePage, router } from '@inertiajs/vue3';
    import { computed, onMounted, ref } from 'vue'
    import { Icon } from '@iconify/vue';
    import Drawer from 'primevue/drawer';
    import Currency from '@/components/Currency/index.vue';
    import Pagination from '@/components/Pagination.vue';
    import { useToast } from "primevue/usetoast";
    import { useDataDate } from '@/composables/useDataDate';

    const { dateFunction } = useDataDate();

    const page = usePage()
    const toast = useToast();
    const user = page.props.auth.user
    const add = ref(null);
    const loading = ref(false)

    const props = defineProps({
        consignments: Object,
        append: Array,
        statuses: Array,
        enabled: Boolean
    });

    const menuAccess = computed(() => usePage().props.menuAccess);

    onMounted(() => {
        add.value = menuAccess.value.find(access => access.action_id == 2) ?? null;

        if (props.enabled) {
            loadBalance();
        }
    });

    const name = ref(props.append?.name);
    const status = ref(props.append?.status);
    const pageNumber = ref(props.consignments?.current_page);
    const visibleRight = ref(false);
    const balance = ref(null);

    const breadcrumbs = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Steadfast Courier', href: '/steadfast' },
    ];

    const filters = () => ({
        name: name.value,
        status: status.value
    });

    const submit = () => {
        router.get('/steadfast/paginate/filters', filters(), {
            preserveState: true,
            replace: true
        });
    };

    const goToPage = () => {
        if (pageNumber.value < 1) {
            pageNumber.value = 1;
        }

        router.get('/steadfast?page=' + pageNumber.value, filters(), {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
    };

    /**
     * Pull the live status for one parcel. The scheduled job does this every
     * twenty minutes anyway, so this is only for when someone is chasing a
     * specific order.
     */
    const refreshStatus = (orderNo) => {
        loading.value = true;

        router.get(`/steadfast/order/${orderNo}/status`, {}, {
            preserveScroll: true,
            onFinish: () => { loading.value = false; }
        });
    };

    const loadBalance = async () => {
        try {
            const response = await fetch('/steadfast/balance', {
                headers: { Accept: 'application/json' }
            });
            const data = await response.json();

            balance.value = data.error ? data.error : data.balance;
        } catch (e) {
            balance.value = 'unavailable';
        }
    };

    /**
     * Steadfast statuses are snake_case; show them the way an operator reads
     * them, and colour only the two that need attention.
     */
    const statusLabel = (value) => {
        if (!value) {
            return 'not sent';
        }

        return value.replace(/_/g, ' ');
    };

    const statusClass = (value) => {
        if (value === 'delivered' || value === 'partial_delivered') {
            return 'bg-green-100 text-green-700';
        }

        if (value === 'cancelled' || value === 'unknown') {
            return 'bg-red-100 text-red-700';
        }

        return 'bg-gray-100 text-gray-700';
    };

</script>

<template>

    <Head title="Steadfast Courier" />

    <AppLayout :breadcrumbs="breadcrumbs" :loading="loading">
        <Toast position="top-right" />
        <div class="w-full mx-auto p-2 flex flex-row place-content-center">
            <div className="w-5/6 mt-4 shadow-lg bg-white rounded-md pb-1">
                <div class="flex justify-between bg-gray-300 px-2 py-1 items-center rounded-t-md">
                    <div class="text-lg font-semibold">Steadfast Consignments</div>
                    <div class="flex items-center gap-3">
                        <div v-if="enabled && balance !== null" class="text-sm text-gray-700">
                            Balance: <span class="font-semibold">{{ balance }}</span>
                        </div>
                        <div class="bg-sky-600 items-center p-2 flex px-4 rounded-md cursor-pointer" @click="visibleRight = true">
                            <Icon icon="mdi:filter-outline" width="1.5rem" height="1.5rem" class="text-white" />
                            <div class="px-2 text-white text-lg">Filter</div>
                        </div>
                    </div>
                </div>

                <div v-if="!enabled" class="mx-3 mt-3 rounded-md bg-amber-100 text-amber-800 px-3 py-2 text-sm">
                    The Steadfast integration is turned off. Set STEADFAST_ENABLED=true and add the API keys to start sending parcels.
                </div>

                <!-- main content goes here -->
                <div class="px-3 h-[calc(100vh-13rem)] overflow-auto pb-3">
                    <div class="w-full border-t mt-4">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-600 text-white">
                                    <th>SL</th>
                                    <th>Order</th>
                                    <th>Consignment</th>
                                    <th>Tracking</th>
                                    <th>COD</th>
                                    <th>Status</th>
                                    <th>Sent</th>
                                    <th>Last sync</th>
                                    <th class="w-16">...</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr class="even:bg-gray-200 text-gray-600" v-for="(row, index) in Object.values(consignments?.data ?? {})" :key="row.id">
                                    <td>{{ ((consignments?.current_page - 1) * consignments?.per_page) + (index + 1) }}</td>
                                    <td>{{ row?.order_no }}</td>
                                    <td>{{ row?.consignment_id ?? '-' }}</td>
                                    <td>{{ row?.tracking_code ?? '-' }}</td>
                                    <td><Currency :amount="row?.cod_amount" /></td>
                                    <td>
                                        <span class="px-2 py-1 rounded-md text-xs capitalize" :class="statusClass(row?.delivery_status)">
                                            {{ statusLabel(row?.delivery_status) }}
                                        </span>
                                    </td>
                                    <td>{{ dateFunction(row?.created_at) }}</td>
                                    <td>{{ row?.last_synced_at ? dateFunction(row?.last_synced_at) : '-' }}</td>
                                    <td>
                                        <div
                                            class="bg-sky-600 hover:bg-sky-700 px-2 text-white rounded-sm flex items-center py-1 cursor-pointer"
                                            title="Refresh status"
                                            @click="refreshStatus(row.order_no)"
                                        >
                                            <Icon icon="mdi:refresh" width="1.3rem" />
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="!Object.values(consignments?.data ?? {}).length">
                                    <td colspan="9" class="text-center text-gray-500 py-6">
                                        No parcels have been sent to Steadfast yet.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-between w-full px-3 py-2 border-t">
                    <div class="flex">
                        <input class="ring-0 border border-r-0 rounded-l-md p-1 px-3 focus:outline-none focus:ring-0" v-model="pageNumber" type="number" />
                        <div class="bg-gray-100 p-2 rounded-r-md">
                            <Icon @click="goToPage" icon="nonicons:go-16" />
                        </div>
                    </div>
                    <div>
                        <Pagination :links="consignments?.links" />
                    </div>
                </div>

                <Drawer v-model:visible="visibleRight" header="Filter Options" position="right">
                    <form @submit.prevent="submit">
                        <div class="grid grid-col-1 mt-4">
                            <div class="card w-full justify-center mt-2">
                                <label class="text-sm text-gray-600">Order, consignment or tracking code</label>
                                <input v-model="name" type="text" class="w-full border rounded-md p-2 mt-1" placeholder="Search" />
                            </div>

                            <div class="card w-full justify-center mt-4">
                                <label class="text-sm text-gray-600">Delivery status</label>
                                <select v-model="status" class="w-full border rounded-md p-2 mt-1">
                                    <option value="">All</option>
                                    <option v-for="option in statuses" :key="option" :value="option">
                                        {{ statusLabel(option) }}
                                    </option>
                                </select>
                            </div>

                            <button type="submit" class="bg-sky-600 text-white rounded-md py-2 mt-6">Apply</button>
                        </div>
                    </form>
                </Drawer>

            </div>
        </div>
    </AppLayout>
</template>
