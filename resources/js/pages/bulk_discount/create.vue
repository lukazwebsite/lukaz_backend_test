<script setup lang="ts">

    import AppLayout from '@/layouts/AppLayout.vue';
    import { Head, Link, router } from '@inertiajs/vue3';
    import { ref, computed, watch } from 'vue'
    import { Icon } from '@iconify/vue';
    import Dialog from 'primevue/dialog';

    const props = defineProps({
        categories: Array,
        brands: Array,
        campaign: Object,          // edit mode only
        selectedCategoryIds: Array,
        selectedProducts: Array,
    });

    const isEdit = computed(() => !!props.campaign);

    const breadcrumbs = ref([
        { title: 'Dashboard', href: '/' },
        { title: 'Bulk Discount', href: '/bulk_discount' },
        { title: isEdit.value ? 'Edit Campaign' : 'New Campaign', href: '#' }
    ]);

    // --- step 1: target type -------------------------------------------------
    // Locked after create, because priority is derived from it and products are
    // already priced against that priority.
    const targetType = ref(props.campaign?.target_type ?? 'product');

    // --- step 2: targets -----------------------------------------------------
    const selectedCategories = ref([...(props.selectedCategoryIds ?? [])]);

    const categorySearch = ref('');

    // Searching a tree must keep the ancestors of a hit, otherwise a matching
    // child appears detached from its parent and the indentation lies.
    const visibleCategories = computed(() => {
        const term = categorySearch.value.trim().toLowerCase();
        if (!term) return props.categories ?? [];

        const all = props.categories ?? [];
        const byId = new Map(all.map(c => [c.id, c]));
        const keep = new Set();

        all.forEach(category => {
            if (!category.name?.toLowerCase().includes(term)) return;

            keep.add(category.id);

            // walk up to the root so the branch stays readable
            let parentId = category.parent_id;
            while (parentId && byId.has(parentId)) {
                keep.add(parentId);
                parentId = byId.get(parentId).parent_id;
            }
        });

        return all.filter(c => keep.has(c.id));
    });

    // Ticking a parent means the whole branch, because products hang off leaf
    // categories and a root on its own would discount nothing.
    const toggleCategory = (category) => {
        const branch = [category.id, ...(category.descendant_ids ?? [])];
        const current = new Set(selectedCategories.value);

        const isOn = current.has(category.id);
        branch.forEach(id => isOn ? current.delete(id) : current.add(id));

        selectedCategories.value = Array.from(current);
    };

    const categoryChecked = (category) => selectedCategories.value.includes(category.id);

    // Some of the branch is selected, but not the category itself.
    const categoryPartial = (category) => {
        if (categoryChecked(category)) return false;
        return (category.descendant_ids ?? []).some(id => selectedCategories.value.includes(id));
    };

    const clearCategories = () => { selectedCategories.value = []; };

    // Selection is held as a Map keyed by product id, independent of whatever
    // page or filter is on screen. Filtering must never silently drop ticks.
    const selected = ref(new Map(
        (props.selectedProducts ?? []).map(p => [p.id, p])
    ));

    const selectedCount = computed(() => selected.value.size);
    const selectedIds = computed(() => Array.from(selected.value.keys()));

    const toggleProduct = (product) => {
        const next = new Map(selected.value);
        next.has(product.id) ? next.delete(product.id) : next.set(product.id, product);
        selected.value = next;
    };

    const isSelected = (id) => selected.value.has(id);

    const clearSelection = () => { selected.value = new Map(); };

    // --- product picker ------------------------------------------------------
    const pickerOpen = ref(false);
    const picker = ref({ data: [], current_page: 1, last_page: 1, total: 0 });
    const pickerLoading = ref(false);

    const filters = ref({ name: '', category_id: '', brand_id: '', per_page: 20 });

    const loadProducts = async (page = 1) => {
        pickerLoading.value = true;

        const params = new URLSearchParams({ page, per_page: filters.value.per_page });
        if (filters.value.name) params.append('name', filters.value.name);
        if (filters.value.category_id) params.append('category_id', filters.value.category_id);
        if (filters.value.brand_id) params.append('brand_id', filters.value.brand_id);

        try {
            const res = await fetch(`/bulk_discount/products?${params}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            picker.value = await res.json();
        } finally {
            pickerLoading.value = false;
        }
    };

    const openPicker = () => {
        pickerOpen.value = true;
        loadProducts(1);
    };

    // Scope must be explicit. An ambiguous "select all" is the classic bulk-UI bug.
    const selectAllOnPage = () => {
        const next = new Map(selected.value);
        picker.value.data.forEach(p => next.set(p.id, p));
        selected.value = next;
    };

    const deselectAllOnPage = () => {
        const next = new Map(selected.value);
        picker.value.data.forEach(p => next.delete(p.id));
        selected.value = next;
    };

    const allOnPageSelected = computed(() =>
        picker.value.data.length > 0 && picker.value.data.every(p => selected.value.has(p.id))
    );

    const selectAllOpen = ref(false);

    const selectAllMatching = async () => {
        selectAllOpen.value = false;
        pickerLoading.value = true;
        const next = new Map(selected.value);

        try {
            let page = 1;
            let lastPage = 1;

            do {
                const params = new URLSearchParams({ page, per_page: 200 });
                if (filters.value.name) params.append('name', filters.value.name);
                if (filters.value.category_id) params.append('category_id', filters.value.category_id);
                if (filters.value.brand_id) params.append('brand_id', filters.value.brand_id);

                const res = await fetch(`/bulk_discount/products?${params}`, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const json = await res.json();

                json.data.forEach(p => next.set(p.id, p));
                lastPage = json.last_page;
                page++;
            } while (page <= lastPage);

            selected.value = next;
        } finally {
            pickerLoading.value = false;
        }
    };

    // --- step 3: discount + window ------------------------------------------
    const toLocalInput = (value) => {
        if (!value) return '';
        const d = new Date(value);
        const pad = (n) => String(n).padStart(2, '0');
        return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    };

    const form = ref({
        name: props.campaign?.name ?? '',
        discount_type: String(props.campaign?.discount_type ?? '0'),
        discount: props.campaign?.discount ?? '',
        starts_at: toLocalInput(props.campaign?.starts_at),
        ends_at: toLocalInput(props.campaign?.ends_at),
        status: props.campaign ? !!props.campaign.status : true,
        description: props.campaign?.description ?? '',
    });

    // --- step 4: preview -----------------------------------------------------
    const preview = ref(null);
    const previewLoading = ref(false);
    const errors = ref({});

    const canPreview = computed(() => {
        if (form.value.discount === '' || Number(form.value.discount) < 0) return false;
        return targetType.value === 'category'
            ? selectedCategories.value.length > 0
            : selectedCount.value > 0;
    });

    const previewError = ref('');

    // Read the XSRF cookie Laravel sets, since this app renders no csrf-token
    // meta tag. Without a valid token the POST is rejected with a 419 and the
    // preview silently renders empty.
    const xsrfToken = () => {
        const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]*)/);
        return match ? decodeURIComponent(match[1]) : '';
    };

    const runPreview = async () => {
        if (!canPreview.value) return;

        previewLoading.value = true;
        previewError.value = '';

        const payload = {
            target_type: targetType.value,
            discount_type: form.value.discount_type,
            discount: form.value.discount,
            category_ids: selectedCategories.value,
            product_ids: selectedIds.value,
            ignore_id: props.campaign?.id ?? null,
        };

        try {
            const res = await fetch('/bulk_discount/preview', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-XSRF-TOKEN': xsrfToken(),
                },
                body: JSON.stringify(payload),
            });

            if (!res.ok) {
                previewError.value = res.status === 419
                    ? 'Session expired. Reload the page and try again.'
                    : `Preview failed (${res.status}).`;
                preview.value = null;
                return;
            }

            preview.value = await res.json();
        } catch (e) {
            previewError.value = 'Preview request failed.';
            preview.value = null;
        } finally {
            previewLoading.value = false;
        }
    };

    // Preview must follow the inputs, otherwise it shows stale prices.
    watch([() => form.value.discount, () => form.value.discount_type, selectedCategories, selected], () => {
        preview.value = null;
    }, { deep: true });

    const unchangedCount = computed(() =>
        (preview.value?.rows ?? []).filter(r => !r.applied || r.current_price === r.new_price).length
    );

    const losingConflicts = computed(() =>
        (preview.value?.conflicts ?? []).filter(c => !c.wins)
    );

    const winningConflicts = computed(() =>
        (preview.value?.conflicts ?? []).filter(c => c.wins)
    );

    const submitting = ref(false);

    const submit = () => {
        errors.value = {};

        const payload = {
            name: form.value.name,
            discount_type: form.value.discount_type,
            discount: form.value.discount,
            starts_at: form.value.starts_at,
            ends_at: form.value.ends_at,
            status: form.value.status,
            description: form.value.description,
            category_ids: targetType.value === 'category' ? selectedCategories.value : [],
            product_ids: targetType.value === 'product' ? selectedIds.value : [],
        };

        if (!isEdit.value) {
            payload.target_type = targetType.value;
        }

        // Newest wins, so a takeover is a warning and not a block. Ask once,
        // then send. Confirming sets skipConfirm so this does not loop.
        if (winningConflicts.value.length && !skipConfirm.value) {
            takeoverOpen.value = true;
            return;
        }

        submitting.value = true;

        const url = isEdit.value ? `/bulk_discount/${props.campaign.id}` : '/bulk_discount';

        router.post(url, payload, {
            onError: (e) => { errors.value = e; },
            onFinish: () => { submitting.value = false; skipConfirm.value = false; },
        });
    };

    const takeoverOpen = ref(false);
    const skipConfirm = ref(false);

    const confirmTakeover = () => {
        takeoverOpen.value = false;
        skipConfirm.value = true;
        submit();
    };

    const money = (n) => `৳${Number(n ?? 0).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 })}`;

    // Green only when the shopper actually pays less than they do today.
    const changeClass = (row) => {
        if (!row.applied || row.current_price === row.new_price) return 'text-gray-400';
        return row.new_price < row.current_price ? 'text-green-700' : 'text-red-600';
    };

</script>

<template>

    <Head :title="isEdit ? 'Edit Bulk Discount' : 'New Bulk Discount'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full mx-auto p-2 flex flex-row place-content-center">
            <div class="w-5/6 mt-4 shadow-lg bg-white rounded-md pb-4">

                <div class="flex justify-between bg-gray-300 px-2 py-1 items-center rounded-t-md">
                    <div class="text-lg font-semibold">{{ isEdit ? 'Edit Campaign' : 'New Campaign' }}</div>
                    <Link href="/bulk_discount" class="bg-gray-600 p-2 rounded-md px-3 flex text-white text-sm">
                        <Icon icon="mdi:arrow-left" class="mr-1" width="1.2rem"/> Back
                    </Link>
                </div>

                <form @submit.prevent="submit" class="px-6 py-4">

                    <!-- step 1 -->
                    <div class="mb-6">
                        <div class="font-semibold text-gray-700 mb-2">1. Discount applies to</div>

                        <div class="flex gap-3">
                            <label class="flex-1 border rounded-md p-3 cursor-pointer"
                                :class="targetType === 'category' ? 'border-green-600 bg-green-50' : 'border-gray-300'"
                                :style="isEdit && targetType !== 'category' ? 'opacity:.4;pointer-events:none' : ''">
                                <input type="radio" value="category" v-model="targetType" :disabled="isEdit" class="mr-2"/>
                                <span class="font-semibold">Category</span>
                                <div class="text-xs text-gray-600 mt-1">Every product in the selected categories.</div>
                            </label>

                            <label class="flex-1 border rounded-md p-3 cursor-pointer"
                                :class="targetType === 'product' ? 'border-green-600 bg-green-50' : 'border-gray-300'"
                                :style="isEdit && targetType !== 'product' ? 'opacity:.4;pointer-events:none' : ''">
                                <input type="radio" value="product" v-model="targetType" :disabled="isEdit" class="mr-2"/>
                                <span class="font-semibold">Selected Products</span>
                                <div class="text-xs text-gray-600 mt-1">Hand-picked. Overrides category discounts while running.</div>
                            </label>
                        </div>

                        <div v-if="isEdit" class="text-xs text-gray-500 mt-1">Target type cannot be changed after creation.</div>
                    </div>

                    <!-- step 2 -->
                    <div class="mb-6">
                        <div class="font-semibold text-gray-700 mb-2">2. Targets</div>

                        <div v-if="targetType === 'category'">
                            <div class="flex gap-2 mb-2">
                                <input v-model="categorySearch" placeholder="Search category"
                                    class="text-sm border py-1 px-2 rounded-md outline-none focus:border-green-200 flex-1"/>
                                <button v-if="categorySearch" type="button" @click="categorySearch = ''"
                                    class="border px-3 py-1 rounded-md text-sm cursor-pointer">Clear search</button>
                                <button v-if="selectedCategories.length" type="button" @click="clearCategories"
                                    class="border px-3 py-1 rounded-md text-sm text-red-600 cursor-pointer">Clear all</button>
                            </div>

                            <div class="border rounded-md p-3 max-h-72 overflow-auto">
                                <div v-if="!visibleCategories.length" class="text-sm text-gray-500 text-center py-4">
                                    No category matches "{{ categorySearch }}".
                                </div>

                                <label v-for="category in visibleCategories" :key="category.id"
                                    class="flex items-center gap-2 text-sm py-1 hover:bg-gray-50 cursor-pointer"
                                    :style="{ paddingLeft: (category.depth * 20) + 'px' }">
                                    <input type="checkbox"
                                        :checked="categoryChecked(category)"
                                        :indeterminate="categoryPartial(category)"
                                        @change="toggleCategory(category)"/>

                                    <span :class="category.depth === 0 ? 'font-semibold' : ''">{{ category.name }}</span>

                                    <span v-if="category.descendant_ids?.length" class="text-xs text-gray-400">
                                        ({{ category.descendant_ids.length }} sub)
                                    </span>
                                    <span v-else-if="category.own_products" class="text-xs text-gray-400">
                                        ({{ category.own_products }} products)
                                    </span>
                                </label>
                            </div>

                            <div class="text-sm text-gray-600 mt-1">
                                {{ selectedCategories.length }} categories selected
                                <span class="text-gray-500">— selecting a parent includes all its subcategories.</span>
                            </div>
                        </div>

                        <div v-else>
                            <div class="flex items-center justify-between border rounded-md p-3 bg-gray-50">
                                <div class="font-semibold">{{ selectedCount }} products selected</div>
                                <div class="flex gap-2">
                                    <button type="button" @click="openPicker"
                                        class="bg-sky-600 text-white px-3 py-1 rounded-md text-sm cursor-pointer">
                                        {{ selectedCount ? 'Edit selection' : 'Select products' }}
                                    </button>
                                    <button v-if="selectedCount" type="button" @click="clearSelection"
                                        class="border px-3 py-1 rounded-md text-sm cursor-pointer">Clear all</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- step 3 -->
                    <div class="mb-6">
                        <div class="font-semibold text-gray-700 mb-2">3. Discount and schedule</div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-semibold">Campaign Name *</label>
                                <input v-model="form.name" class="w-full text-sm border py-1 px-2 rounded-md outline-none focus:border-green-200"/>
                                <div v-if="errors.name" class="text-red-600 text-xs">{{ errors.name }}</div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="text-sm font-semibold">Type *</label>
                                    <select v-model="form.discount_type" class="w-full text-sm border py-1 px-2 rounded-md outline-none">
                                        <option value="0">Percentage (%)</option>
                                        <option value="1">Fixed (৳)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-sm font-semibold">Value *</label>
                                    <input v-model="form.discount" type="number" step="0.01" min="0"
                                        class="w-full text-sm border py-1 px-2 rounded-md outline-none focus:border-green-200"/>
                                    <div v-if="errors.discount" class="text-red-600 text-xs">{{ errors.discount }}</div>
                                </div>
                            </div>

                            <div>
                                <label class="text-sm font-semibold">Starts at *</label>
                                <input v-model="form.starts_at" type="datetime-local"
                                    class="w-full text-sm border py-1 px-2 rounded-md outline-none focus:border-green-200"/>
                                <div v-if="errors.starts_at" class="text-red-600 text-xs">{{ errors.starts_at }}</div>
                            </div>

                            <div>
                                <label class="text-sm font-semibold">Ends at *</label>
                                <input v-model="form.ends_at" type="datetime-local"
                                    class="w-full text-sm border py-1 px-2 rounded-md outline-none focus:border-green-200"/>
                                <div v-if="errors.ends_at" class="text-red-600 text-xs">{{ errors.ends_at }}</div>
                            </div>

                            <div>
                                <label class="text-sm font-semibold">Status</label>
                                <select v-model="form.status" class="w-full text-sm border py-1 px-2 rounded-md outline-none">
                                    <option :value="true">Enabled</option>
                                    <option :value="false">Disabled</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-sm font-semibold">Note</label>
                                <input v-model="form.description" class="w-full text-sm border py-1 px-2 rounded-md outline-none focus:border-green-200"/>
                            </div>
                        </div>
                    </div>

                    <!-- step 4 -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-2">
                            <div class="font-semibold text-gray-700">4. Preview</div>
                            <button type="button" @click="runPreview" :disabled="!canPreview || previewLoading"
                                class="bg-gray-700 text-white px-3 py-1 rounded-md text-sm cursor-pointer disabled:opacity-40">
                                {{ previewLoading ? 'Calculating...' : 'Preview prices' }}
                            </button>
                        </div>

                        <div v-if="previewError" class="text-sm text-red-700 border border-red-300 bg-red-50 rounded-md p-3 mb-2">
                            {{ previewError }}
                        </div>

                        <div v-if="!preview" class="text-sm text-gray-500 border rounded-md p-4 text-center">
                            <span v-if="!canPreview">Pick targets and enter a discount value, then run a preview.</span>
                            <span v-else>Run a preview to check prices before saving.</span>
                        </div>

                        <div v-else-if="!preview.rows?.length" class="text-sm text-gray-600 border rounded-md p-4 text-center">
                            No products matched. A category with no products of its own still counts its subcategories, so check the selection.
                        </div>

                        <div v-else>
                            <div v-if="winningConflicts.length"
                                class="border border-orange-300 bg-orange-50 text-orange-800 rounded-md p-3 mb-2 text-sm">
                                <Icon icon="mdi:alert" class="inline mr-1"/>
                                This campaign takes over products already in:
                                <strong>{{ winningConflicts.map(c => c.name).join(', ') }}</strong>
                            </div>

                            <div v-if="losingConflicts.length"
                                class="border border-blue-300 bg-blue-50 text-blue-800 rounded-md p-3 mb-2 text-sm">
                                <Icon icon="mdi:information" class="inline mr-1"/>
                                Some products stay with a higher priority campaign:
                                <strong>{{ losingConflicts.map(c => c.name).join(', ') }}</strong>
                            </div>

                            <div class="text-sm text-gray-600 mb-1">
                                {{ preview.total }} product(s) affected<span v-if="preview.shown < preview.total">, showing first {{ preview.shown }}</span>
                            </div>

                            <!-- A campaign matching an existing discount changes no price.
                                 Worth saying outright rather than leaving the admin to
                                 compare two identical columns row by row. -->
                            <div v-if="unchangedCount === preview.rows.length"
                                class="border border-amber-300 bg-amber-50 text-amber-800 rounded-md p-3 mb-2 text-sm">
                                <Icon icon="mdi:information" class="inline mr-1"/>
                                No price changes. These products already sell at this price, so this
                                campaign leaves them where they are.
                            </div>
                            <div v-else-if="unchangedCount" class="text-sm text-amber-700 mb-1">
                                {{ unchangedCount }} of these already sell at the campaign price and will not move.
                            </div>

                            <div class="border rounded-md max-h-72 overflow-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-600 text-white sticky top-0">
                                        <tr>
                                            <th>Product</th>
                                            <th title="Undiscounted base price. Every campaign is calculated from this.">Regular</th>
                                            <th title="What a shopper pays today, before this campaign.">Now</th>
                                            <th title="What a shopper pays once this campaign runs.">After</th>
                                            <th title="Difference between Now and After.">Change</th>
                                            <th>Off regular</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="row in preview.rows" :key="row.id" class="odd:bg-white even:bg-gray-100">
                                            <td>{{ row.name }}<span v-if="row.sku" class="text-xs text-gray-500"> ({{ row.sku }})</span></td>
                                            <td>{{ money(row.regular_price) }}</td>
                                            <td>{{ money(row.current_price) }}</td>
                                            <td class="font-semibold" :class="row.applied ? 'text-green-700' : 'text-gray-500'">{{ money(row.new_price) }}</td>
                                            <!-- What actually changes for the shopper: Now vs After.
                                                 Measuring against regular instead would report a
                                                 discount even when the price does not move. -->
                                            <td :class="changeClass(row)">
                                                <span v-if="!row.applied">no change</span>
                                                <span v-else-if="row.current_price === row.new_price">no change</span>
                                                <span v-else>
                                                    {{ row.new_price < row.current_price ? '↓' : '↑' }}
                                                    {{ money(Math.abs(row.current_price - row.new_price)) }}
                                                    <span v-if="row.current_price > 0" class="text-xs">
                                                        ({{ Math.round(((row.new_price - row.current_price) / row.current_price) * 100) }}%)
                                                    </span>
                                                </span>
                                            </td>

                                            <!-- Total discount off the base price, which is what the
                                                 storefront badge will show. -->
                                            <td class="text-gray-600">
                                                <span v-if="row.regular_price > 0">
                                                    -{{ Math.round(((row.regular_price - row.new_price) / row.regular_price) * 100) }}%
                                                </span>
                                            </td>
                                            <td class="text-xs">
                                                <span v-if="row.overrides && row.applied" class="text-orange-600">
                                                    overrides "{{ row.overrides }}"
                                                </span>
                                                <span v-else-if="row.overrides" class="text-gray-500">
                                                    kept by "{{ row.overrides }}"
                                                </span>
                                                <!-- a manual discount is not lost, only covered up
                                                     while the campaign runs -->
                                                <span v-if="row.original_price !== row.current_price" class="text-gray-500 block">
                                                    reverts to {{ money(row.original_price) }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" :disabled="submitting"
                            class="bg-green-600 py-2 px-6 font-semibold text-white rounded-md cursor-pointer disabled:opacity-40">
                            {{ submitting ? 'Saving...' : (isEdit ? 'Update Campaign' : 'Create Campaign') }}
                        </button>
                        <Link href="/bulk_discount" class="border py-2 px-6 rounded-md">Cancel</Link>
                    </div>

                </form>

                <!-- product picker -->
                <Dialog v-model:visible="pickerOpen" modal header="Select Products" :style="{ width: '60rem' }">

                    <div class="flex gap-2 mb-3">
                        <input v-model="filters.name" @keyup.enter="loadProducts(1)" placeholder="Name or SKU"
                            class="text-sm border py-1 px-2 rounded-md outline-none flex-1"/>

                        <select v-model="filters.category_id" @change="loadProducts(1)" class="text-sm border py-1 px-2 rounded-md outline-none">
                            <option value="">All categories</option>
                            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>

                        <select v-model="filters.brand_id" @change="loadProducts(1)" class="text-sm border py-1 px-2 rounded-md outline-none">
                            <option value="">All brands</option>
                            <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>

                        <select v-model="filters.per_page" @change="loadProducts(1)" class="text-sm border py-1 px-2 rounded-md outline-none">
                            <option :value="20">20 / page</option>
                            <option :value="50">50 / page</option>
                            <option :value="100">100 / page</option>
                        </select>

                        <button type="button" @click="loadProducts(1)" class="bg-sky-600 text-white px-3 rounded-md text-sm cursor-pointer">
                            <Icon icon="fa7-solid:magnifying-glass"/>
                        </button>
                    </div>

                    <!-- selection survives filtering and pagination -->
                    <div class="flex items-center justify-between bg-gray-100 border rounded-md px-3 py-2 mb-2 text-sm">
                        <div class="font-semibold">{{ selectedCount }} products selected</div>
                        <div class="flex gap-3">
                            <button type="button" @click="allOnPageSelected ? deselectAllOnPage() : selectAllOnPage()" class="text-sky-700 cursor-pointer">
                                {{ allOnPageSelected ? 'Deselect' : 'Select all' }} {{ picker.data?.length ?? 0 }} on this page
                            </button>
                            <button type="button" @click="selectAllOpen = true" class="text-sky-700 cursor-pointer">
                                Select all {{ picker.total ?? 0 }} matching filter
                            </button>
                            <button type="button" @click="clearSelection" class="text-red-600 cursor-pointer">Clear all</button>
                        </div>
                    </div>

                    <div class="border rounded-md max-h-96 overflow-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-600 text-white sticky top-0">
                                <tr>
                                    <th style="width:2rem"></th>
                                    <th>Name</th>
                                    <th>SKU</th>
                                    <th>Brand</th>
                                    <th>Regular</th>
                                    <th>Current</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="pickerLoading"><td colspan="6" class="text-center py-6 text-gray-500">Loading...</td></tr>
                                <tr v-else-if="!picker.data?.length"><td colspan="6" class="text-center py-6 text-gray-500">No products found.</td></tr>

                                <tr v-for="product in picker.data" :key="product.id"
                                    class="odd:bg-white even:bg-gray-100 cursor-pointer"
                                    :class="{ 'bg-green-50': isSelected(product.id) }"
                                    @click="toggleProduct(product)">
                                    <td class="text-center">
                                        <input type="checkbox" :checked="isSelected(product.id)" @click.stop="toggleProduct(product)"/>
                                    </td>
                                    <td>{{ product.name }}</td>
                                    <td>{{ product.sku }}</td>
                                    <td>{{ product.brand?.name }}</td>
                                    <td>{{ money(product.regular_price) }}</td>
                                    <td>{{ money(product.current_price) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-between items-center mt-3">
                        <div class="text-sm text-gray-600">Page {{ picker.current_page }} of {{ picker.last_page }} ({{ picker.total }} products)</div>
                        <div class="flex gap-2">
                            <button type="button" :disabled="picker.current_page <= 1" @click="loadProducts(picker.current_page - 1)"
                                class="border px-3 py-1 rounded-md text-sm disabled:opacity-40 cursor-pointer">Prev</button>
                            <button type="button" :disabled="picker.current_page >= picker.last_page" @click="loadProducts(picker.current_page + 1)"
                                class="border px-3 py-1 rounded-md text-sm disabled:opacity-40 cursor-pointer">Next</button>
                            <button type="button" @click="pickerOpen = false"
                                class="bg-green-600 text-white px-4 py-1 rounded-md text-sm cursor-pointer">Done</button>
                        </div>
                    </div>

                </Dialog>

                <!-- Takeover warning. Replaces window.confirm, which shows the
                     host address and cannot say which products are affected. -->
                <Dialog v-model:visible="takeoverOpen" modal header="Take over these products?" :style="{ width: '32rem' }">
                    <div class="flex gap-3">
                        <Icon icon="mdi:alert" class="text-orange-500 shrink-0" width="2rem" height="2rem"/>
                        <div class="text-sm">
                            <p class="mb-2">
                                Some of these products already belong to another running campaign:
                            </p>
                            <ul class="list-disc pl-5 mb-2 font-semibold">
                                <li v-for="c in winningConflicts" :key="c.id">{{ c.name }}</li>
                            </ul>
                            <p class="text-gray-600">
                                This campaign takes priority while it runs. When it ends, those products
                                return to the campaign above if it is still active.
                            </p>
                        </div>
                    </div>

                    <template #footer>
                        <button type="button" @click="takeoverOpen = false"
                            class="border px-4 py-2 rounded-md text-sm cursor-pointer">Cancel</button>
                        <button type="button" @click="confirmTakeover"
                            class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-md text-sm cursor-pointer ml-2">
                            Continue
                        </button>
                    </template>
                </Dialog>

                <Dialog v-model:visible="selectAllOpen" modal header="Select all matching products?" :style="{ width: '30rem' }">
                    <div class="flex gap-3">
                        <Icon icon="mdi:information" class="text-sky-500 shrink-0" width="2rem" height="2rem"/>
                        <div class="text-sm">
                            <p>
                                This adds all <strong>{{ picker.total ?? 0 }}</strong> products matching the
                                current filter to your selection, not only the ones on this page.
                            </p>
                            <p class="text-gray-600 mt-2">Products already selected stay selected.</p>
                        </div>
                    </div>

                    <template #footer>
                        <button type="button" @click="selectAllOpen = false"
                            class="border px-4 py-2 rounded-md text-sm cursor-pointer">Cancel</button>
                        <button type="button" @click="selectAllMatching"
                            class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-md text-sm cursor-pointer ml-2">
                            Select all {{ picker.total ?? 0 }}
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
