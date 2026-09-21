<script setup>
const props = defineProps(['amount']);
</script>
<template>
    <div class="flex place-items-center">
        <div class="text-xl mr-1">৳</div> <div>{{ (parseFloat(amount)).toLocaleString('en-US') }}</div>
    </div>

</template>
