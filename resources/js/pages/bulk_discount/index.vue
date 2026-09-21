<script setup lang="ts">

    import AppLayout from '@/layouts/AppLayout.vue';
    import { Head, Link, usePage, router } from '@inertiajs/vue3';
    import { computed, onMounted, ref } from 'vue'
    import { Icon } from '@iconify/vue';
    import Dialog from 'primevue/dialog';
    import Pagination from '@/components/Pagination.vue';

    const props = defineProps({
        campaigns: Object,
        coverage: Object,
        append: Object,
        checkPermission: Boolean
    });

    const menuAccess = computed(() => usePage().props.menuAccess);

    const add = ref(null);
    const edit = ref(null);
    const deleted = ref(null);

    onMounted(() => {
        add.value = menuAccess.value.find(access => access.action_id == 2) ?? null;
        edit.value = menuAccess.value.find(access => access.action_id == 3) ?? null;
        deleted.value = menuAccess.value.find(access => access.action_id == 4) ?? null;
    });

    const loading = ref(false);
    const name = ref(props.append?.name ?? '');
    const state = ref(props.append?.state ?? '');

    const breadcrumbs = ref([
        { title: 'Dashboard', href: '/' },
        { title: 'Bulk Discount', href: '/bulk_discount' }
    ]);

    const tabs = [
        { key: '', label: 'All' },
        { key: 'active', label: 'Active' },
        { key: 'scheduled', label: 'Scheduled' },
        { key: 'expired', label: 'Expired' },
        { key: 'disabled', label: 'Disabled' },
    ];

    // Status comes from the datetime window, so filtering just re-queries.
    const selectTab = (key) => {
        state.value = key;
        applyFilters();
    };

    const applyFilters = () => {
        router.get('/bulk_discount', {
            name: name.value || undefined,
            state: state.value || undefined,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const stateOf = (campaign) => {
        if (!campaign.status) return 'disabled';

        const now = new Date();
        const startsAt = new Date(campaign.starts_at);
        const endsAt = new Date(campaign.ends_at);

        if (startsAt > now) return 'scheduled';
        if (endsAt < now) return 'expired';
        return 'active';
    };

    const badgeClass = (campaign) => {
        const map = {
            active: 'bg-green-100 text-green-700 border-green-300',
            scheduled: 'bg-blue-100 text-blue-700 border-blue-300',
            expired: 'bg-gray-100 text-gray-600 border-gray-300',
            disabled: 'bg-red-100 text-red-700 border-red-300',
        };
        return map[stateOf(campaign)];
    };

    const formatDateTime = (value) => {
        if (!value) return '-';
        const d = new Date(value);
        return d.toLocaleString('en-GB', {
            day: '2-digit', month: 'short', year: 'numeric',
            hour: '2-digit', minute: '2-digit', hour12: true
        });
    };

    const discountLabel = (campaign) => {
        return Number(campaign.discount_type) === 1
            ? `৳${campaign.discount}`
            : `${campaign.discount}%`;
    };

    // One dialog drives both actions. `pending` holds which campaign and which
    // action is awaiting confirmation.
    const pending = ref(null);
    const busy = ref(false);

    const askEndNow = (campaign) => { pending.value = { campaign, action: 'end' }; };
    const askDestroy = (campaign) => { pending.value = { campaign, action: 'delete' }; };

    const cancelPending = () => { pending.value = null; };

    const confirmPending = () => {
        if (!pending.value) return;

        const { campaign, action } = pending.value;
        busy.value = true;

        const done = {
            preserveScroll: true,
            onFinish: () => { busy.value = false; pending.value = null; },
        };

        action === 'end'
            ? router.post(`/bulk_discount/${campaign.id}/end`, {}, done)
            : router.delete(`/bulk_discount/${campaign.id}`, done);
    };

</script>

<template>

    <Head title="Bulk Discount" />

    <AppLayout :breadcrumbs="breadcrumbs" :loading="loading">
        <div class="w-full mx-auto p-2 flex flex-row place-content-center">
            <div class="w-5/6 mt-4 shadow-lg bg-white rounded-md pb-1">

                <div class="flex justify-between bg-gray-300 px-2 py-1 items-center rounded-t-md">
                    <div class="text-lg font-semibold">Bulk Discount Campaigns</div>
                    <div class="flex">
                        <Link v-if="add?.action_id || checkPermission" href="/bulk_discount/create"
                            class="bg-green-600 p-2 rounded-md text-lg px-3 flex text-white">
                            <Icon icon="fluent:add-12-filled" class="mr-2" width="1.5rem" height="1.5rem"/> New Campaign
                        </Link>
                    </div>
                </div>

                <!-- status tabs + search -->
                <div class="flex justify-between items-center px-3 pt-3 border-b">
                    <div class="flex gap-1">
                        <button v-for="tab in tabs" :key="tab.key"
                            @click="selectTab(tab.key)"
                            class="px-4 py-2 text-sm font-semibold border-b-2 cursor-pointer"
                            :class="state === tab.key
                                ? 'border-green-700 text-green-700'
                                : 'border-transparent text-gray-500 hover:text-gray-800'">
                            {{ tab.label }}
                        </button>
                    </div>

                    <div class="flex pb-2">
                        <input v-model="name" @keyup.enter="applyFilters" placeholder="Search campaign"
                            class="text-sm border py-1 px-2 rounded-l-md outline-none focus:border-green-200" />
                        <div class="bg-gray-100 border border-l-0 px-2 flex items-center rounded-r-md cursor-pointer" @click="applyFilters">
                            <Icon icon="fa7-solid:magnifying-glass" />
                        </div>
                    </div>
                </div>

                <div class="px-3 h-[calc(100vh-16rem)] overflow-auto pb-3">
                    <div class="w-full border-t mt-4">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-600 text-white">
                                    <th>SL</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Discount</th>
                                    <th>Starts</th>
                                    <th>Ends</th>
                                    <th>Products</th>
                                    <th>Status</th>
                                    <th>...</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!campaigns?.data?.length">
                                    <td colspan="9" class="text-center py-6 text-gray-500">No campaigns yet.</td>
                                </tr>

                                <tr v-for="(campaign, index) in campaigns?.data" :key="campaign.id"
                                    class="odd:bg-white even:bg-gray-200 text-gray-800">
                                    <td class="text-center">{{ ((campaigns?.current_page - 1) * campaigns?.per_page) + (index + 1) }}</td>
                                    <td>{{ campaign?.name }}</td>
                                    <td>
                                        <span class="text-xs px-2 py-1 rounded border"
                                            :class="campaign.target_type === 'product'
                                                ? 'bg-purple-100 text-purple-700 border-purple-300'
                                                : 'bg-amber-100 text-amber-700 border-amber-300'">
                                            {{ campaign.target_type === 'product' ? 'Products' : 'Category' }}
                                        </span>
                                    </td>
                                    <td class="font-semibold">{{ discountLabel(campaign) }}</td>
                                    <td class="text-sm">{{ formatDateTime(campaign?.starts_at) }}</td>
                                    <td class="text-sm">{{ formatDateTime(campaign?.ends_at) }}</td>
                                    <td>
                                        <div>{{ coverage?.[campaign.id]?.products ?? 0 }}</div>
                                        <!-- makes the priority rule visible: without this an admin
                                             sets a category discount, sees no change, and files a bug -->
                                        <div v-if="coverage?.[campaign.id]?.overridden"
                                            class="text-xs text-orange-600 font-semibold flex items-center gap-1">
                                            <Icon icon="mdi:alert" width="0.9rem"/>
                                            {{ coverage[campaign.id].overridden }} overridden
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-xs px-2 py-1 rounded border capitalize" :class="badgeClass(campaign)">
                                            {{ stateOf(campaign) }}
                                        </span>
                                    </td>
                                    <td class="flex gap-1 place-content-center">
                                        <Link v-if="edit?.action_id || checkPermission" :href="`/bulk_discount/${campaign?.id}/edit`" title="Edit">
                                            <Icon icon="icon-park-outline:pencil"
                                                class="bg-sky-600 hover:bg-sky-700 p-1 w-auto h-8 cursor-pointer text-white rounded-sm" width="1.3rem"/>
                                        </Link>

                                        <Icon v-if="(edit?.action_id || checkPermission) && stateOf(campaign) === 'active'"
                                            @click="askEndNow(campaign)" icon="mdi:stop-circle-outline" title="End now"
                                            class="bg-orange-500 hover:bg-orange-600 p-1 w-auto h-8 cursor-pointer text-white rounded-sm" width="1.3rem"/>

                                        <Icon v-if="(deleted?.action_id || checkPermission) && stateOf(campaign) !== 'active'"
                                            @click="askDestroy(campaign)" icon="mdi:trash-can-outline" title="Delete"
                                            class="bg-red-500 hover:bg-red-600 p-1 w-auto h-8 cursor-pointer text-white rounded-sm" width="1.3rem"/>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end w-full px-3 py-2 border-t">
                    <Pagination :links="campaigns?.links" />
                </div>

                <!-- Replaces window.confirm, which shows the host address and
                     gives no room to explain what each action does to prices. -->
                <Dialog :visible="!!pending" @update:visible="cancelPending" modal
                    :header="pending?.action === 'end' ? 'End this campaign now?' : 'Delete this campaign?'"
                    :style="{ width: '32rem' }">

                    <div v-if="pending" class="flex gap-3">
                        <Icon :icon="pending.action === 'end' ? 'mdi:stop-circle-outline' : 'mdi:trash-can-outline'"
                            :class="pending.action === 'end' ? 'text-orange-500' : 'text-red-500'"
                            class="shrink-0" width="2rem" height="2rem"/>

                        <div class="text-sm">
                            <p class="mb-2">
                                <strong>{{ pending.campaign.name }}</strong>
                                <span v-if="coverage?.[pending.campaign.id]?.products">
                                    — {{ coverage[pending.campaign.id].products }} product(s)
                                </span>
                            </p>

                            <template v-if="pending.action === 'end'">
                                <p class="text-gray-700">
                                    Prices revert straight away. Products fall back to any category
                                    campaign still running, otherwise to the price they had before
                                    this campaign started.
                                </p>
                                <p class="text-gray-600 mt-2">
                                    The campaign record is kept, so the history stays intact.
                                </p>
                            </template>

                            <template v-else>
                                <p class="text-gray-700">
                                    This removes the record permanently and cannot be undone.
                                </p>
                                <p class="text-gray-600 mt-2">
                                    Prices are recalculated, so nothing is left discounted by a
                                    campaign that no longer exists.
                                </p>
                            </template>
                        </div>
                    </div>

                    <template #footer>
                        <button type="button" @click="cancelPending" :disabled="busy"
                            class="border px-4 py-2 rounded-md text-sm cursor-pointer disabled:opacity-40">Cancel</button>

                        <button type="button" @click="confirmPending" :disabled="busy"
                            class="text-white px-4 py-2 rounded-md text-sm cursor-pointer ml-2 disabled:opacity-40"
                            :class="pending?.action === 'end'
                                ? 'bg-orange-500 hover:bg-orange-600'
                                : 'bg-red-600 hover:bg-red-700'">
                            {{ busy ? 'Working...' : (pending?.action === 'end' ? 'End now' : 'Delete') }}
                        </button>
                    </template>
                </Dialog>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
    table tr td, th {
        padding: 6px 4px;
        text-align: left;
    }
</style>
