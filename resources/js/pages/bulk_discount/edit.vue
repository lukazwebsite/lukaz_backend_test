<script setup lang="ts">

    // Edit reuses the create form rather than duplicating it, so the picker,
    // preview and conflict rules can never drift between the two screens.
    import CampaignForm from './create.vue';

    const props = defineProps({
        campaign: Object,
        categories: Array,
        brands: Array,
        selectedCategoryIds: Array,
        selectedProducts: Array,
    });

</script>

<template>
    <CampaignForm
        :campaign="props.campaign"
        :categories="props.categories"
        :brands="props.brands"
        :selected-category-ids="props.selectedCategoryIds"
        :selected-products="props.selectedProducts"
    />
</template>
