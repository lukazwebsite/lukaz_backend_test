<script setup>
    import { Icon } from '@iconify/vue';
    import { Head, Link, usePage, router } from '@inertiajs/vue3';
    import Category from '@/components/Category.vue';
    import ToggleSwitch from 'primevue/toggleswitch';
    import { ref } from 'vue'
    import { useToast } from "primevue/usetoast";


    const props = defineProps({
        categories: Array,
        checkPermission: Boolean,
        edit: Object,
        level: {
            type: Number,
            default: 0
        },

    });

    const serial = ref(1);
    const toast = useToast();
    const toggling = ref({});

    const toggleFeatured = (item) => {

        toggling.value[item.id] = true;

        const value = item.featured ? 1 : 0;

        router.post(`/categories/${item.id}/featured/toggle`, { featured: value }, {
            preserveScroll: true,
            preserveState: true,
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            onSuccess: () => {
                toast.add({ severity: 'success', summary: 'Success Message', detail: value ? 'Marked as featured!' : 'Removed from featured!', life: 3000 });
                toggling.value[item.id] = false;
            },
            onError: () => {
                item.featured = value ? 0 : 1;
                toast.add({ severity: 'error', summary: 'Error Message', detail: 'Oops! Something wrongs', life: 3000 });
                toggling.value[item.id] = false;
            },
        });
    };

</script>

<template>
    <template v-for="(item, index) in categories" :key="item.id">
        <tr class="odd:bg-white even:bg-gray-200 text-gray-800" >
            <!-- <td class="text-center" >{{ item.id }}</td> -->
            <td><img :src="`/category/${item.icon}`" width="40px" height="auto" class="text-center"/></td>
            <td> <span v-for="n in level" :key="n" class="mr-1">→</span>{{ item.name }} </td>
            <td class="p-2">{{ item.description }}</td>
            <td class="p-2">{{ item.status ? 'Active' : 'Inactive' }}</td>
            <td class="p-2">{{ item.isActive ? 'Active' : 'Inactive' }}</td>
            <td class="p-2">
                <ToggleSwitch
                    :modelValue="!!item.featured"
                    :disabled="toggling[item.id] || !(edit?.action_id == 3 || checkPermission)"
                    @update:modelValue="(val) => { item.featured = val ? 1 : 0; toggleFeatured(item); }"
                />
            </td>
            <td class="flex gap-1 place-content-center">
                <Link v-if="edit?.action_id == 3 || checkPermission" :href="`/categories/${item?.id}/edit`">
                    <Icon icon="icon-park-outline:pencil" class="bg-red-500 hover:bg-red-600 p-1 w-auto h-8 cursor-pointer text-white rounded-sm" width="1.3rem"/>
                </Link>
            </td>
        </tr>
        <Category v-if="item.children_recursive?.length" :categories="item.children_recursive" :edit="edit" :checkPermission="checkPermission" :level="level + 1" />
    </template>
</template>

