<script setup>
    import Card from 'primevue/card';
    import { reactive, ref } from 'vue';
    const props = defineProps(['addionalData', 'permission', 'errors']);
    const emit = defineEmits(['update']);
    const edit = ref(props.permission)

    const product = reactive(
        {
            id              : props.addionalData.id,
            sku             : props.addionalData.sku,
            additional_key  : props.addionalData.additional_key,
            size            : props.addionalData.size,
            color           : props.addionalData.color,
            regular_price   : props.addionalData.regular_price,
            current_price   : props.addionalData.current_price,
            discount        : props.addionalData.discount,
            discount_type   : props.addionalData.discount_type,
            status          : props.addionalData.status,
            stock           : props.addionalData.stock,
            stock_status    : props.addionalData.stock_status

        }

    )


    const submitAdditionalData  = async () => {
        emit('update', product)
    }


</script>

<template>

        <div className="w-full mt-2 shadow-lg bg-white rounded-md pb-2 relative">

            <div class="bg-gray-300 px-2 py-1 rounded-t-md">
                <div class="text-md font-semibold text-center">Stock: {{ product.stock }} | {{ product.color }} | {{ product.size }}</div>
            </div>

            <form @submit.prevent="submitAdditionalData" class="p-2">

                <div class="w-full flex mb-1">
                    <label for="dd-city" :class="{'text-red-500': errors?.sku }" class="text-xs w-2/5 content-center">SKU:</label>
                    <input type="text" :class="{'text-red-500 border-red-500': errors?.sku }" v-model="product.sku" class="w-3/5 text-xs border py-1 px-2 outline-none focus:border-green-200 rounded-md" placeholder="SKU"/>
                </div>

                <div class="w-full flex mb-1">
                    <label for="dd-city" :class="{'text-red-500': errors?.regular_price }" class="text-xs w-2/5 content-center">Regular Price:</label>
                    <input type="number" :class="{'text-red-500 border-red-500': errors?.regular_price }" v-model="product.regular_price" class="w-3/5 text-xs border py-1 px-2 outline-none focus:border-green-200 rounded-md" placeholder="Regular Price"/>
                </div>

                <div class="w-full flex mb-1">
                    <label for="dd-city" :class="{'text-red-500': errors?.current_price }" class="text-xs w-2/5 content-center">Current Price:</label>
                    <input type="number" :class="{'text-red-500 border-red-500': errors?.current_price }" v-model="product.current_price" class="w-3/5 text-xs border py-1 px-2 outline-none focus:border-green-200 rounded-md" placeholder="Current Price"/>
                </div>

                <div class="w-full flex mb-1">
                    <label for="dd-city" :class="{'text-red-500': errors?.discount }" class="text-xs w-2/5 content-center">Discount:</label>
                    <input type="number" :class="{'text-red-500 border-red-500': errors?.discount }" v-model="product.discount" class="w-3/5 text-xs border py-1 px-2 outline-none focus:border-green-200 rounded-md" placeholder="Discount"/>
                </div>

                <div class="w-full flex mb-1">
                    <label for="dd-city" :class="{'text-red-500': errors?.discount_type }" class="text-xs w-2/5 content-center">Discount Type: </label>
                    <select id="discount_type" :class="{'text-red-500 border-red-500': errors?.discount_type }" v-model="product.discount_type" class="w-3/5 text-sm border py-1 px-2 outline-none focus:border-green-200 rounded-md">
                        <option value="1"> Fixed</option>
                        <option value="0"> Parcentage</option>
                    </select>
                </div>

                <div class="w-full flex mb-1">
                    <label for="dd-city" :class="{'text-red-500': errors?.status }" class="text-xs w-2/5 content-center">Status:</label>
                    <select id="status" :class="{'text-red-500 border-red-500': errors?.status }" v-model="product.status" class="w-3/5 text-sm border py-1 px-2 outline-none focus:border-green-200 rounded-md">
                        <option value="1"> Active</option>
                        <option value="0"> Inactive</option>
                    </select>
                </div>

                <div class="w-full flex mb-1">
                    <label for="dd-city" :class="{'text-red-500': errors?.stock }" class="text-xs w-2/5 content-center">Stock:</label>
                    <input type="number" :class="{'text-red-500 border-red-500': errors?.stock }" v-model="product.stock" class="w-3/5 text-xs border py-1 px-2 outline-none focus:border-green-200 rounded-md" placeholder="Stock"/>
                </div>

                <div class="w-full flex mb-1">
                    <label for="dd-city" :class="{'text-red-500': errors?.stock_status }" class="text-xs w-2/5 content-center">Stock Status:</label>
                    <select id="stock" :class="{'text-red-500 border-red-500': errors?.stock_status }" v-model="product.stock_status" class="w-3/5 text-sm border py-1 px-2 outline-none focus:border-green-200 rounded-md">
                        <option value="1"> Stock</option>
                        <option value="0"> Out of Stock</option>
                    </select>
                </div>

                <button v-if="edit" type="submit" class="bg-yellow-400 cursor-pointer hover:bg-yellow-900 hover:text-white transition-all py-1 px-2 float-end mt-1 rounded-md"> Update </button>
            </form>

        </div>

</template>
