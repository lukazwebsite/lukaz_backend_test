<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: [String, Number, File, null],
    label: String,
    name: String,
    className: String,
    type: {
        type: String,
        default: 'text'
    },
    errors: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['update:modelValue']);

const inputErrors = computed(() => props.errors[props.name]);
</script>

<template>

        <div class="w-full mb-1">
            <label :for="name" class="text-md font-semibold w-full content-center">{{ label }}</label>

            <input
                v-if="type !== 'textarea'"
                :id="name"
                :name="name"
                :type="type"
                :value="modelValue"
                :placeholder="label"
                @input="$emit('update:modelValue', $event.target.value)"
                :class="[className, inputErrors ? 'border-red-500' : '']"
            />
            <p v-if="inputErrors" class="text-red-500 text-sm mt-1">{{ inputErrors }}</p>
        </div>
</template>
