<script setup lang="ts">

    import { Head, Link, usePage, router } from '@inertiajs/vue3';
    import { computed, onMounted, ref, nextTick } from 'vue'

    const props = defineProps({
        result: Array,
    });

    onMounted(() => {

        nextTick(() => {
            setTimeout(() => {
                window.print();

                window.onafterprint = () => {
                    if (document.referrer) {
                        router.visit(document.referrer)
                    } else {
                        router.visit('/orders') // fallback if no referrer
                    }

                };
            }, 50); // Small delay
        })
    })

</script>

<template>

    <div class="w-full">

        <div class="mb-4">
            <h2 class="text-lg font-semibold text-gray-700 mb-2">Lukaz Shop</h2>
            <p class="text-gray-600"><span class="font-medium">E-mail:</span> lukazshop@gmail.com</p>
            <p class="text-gray-600"><span class="font-medium">Contact:</span> 01752-058475</p>
            <p class="text-gray-600"><span class="font-medium">Heade Office #:</span> Uttra, Dhaka, Bangladesh</p>
        </div>

        <div class="border-b border-gray-200">
            <div class="text-xl font-semibold text-black w-full place-items-center pb-2">
                <div class="border rounded-md w-64 text-center border-black">Items</div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-300 text-left text-xs font-medium text-black uppercase tracking-wider">
                            <th class="px-4 py-3 w-16">SL</th>
                            <th class="px-4 py-3">Product</th>
                            <th class="px-4 py-3">Color</th>
                            <th class="px-4 py-3">Size</th>
                            <th class="px-4 py-3">Warehouse</th>
                            <th class="px-4 py-3">To</th>
                            <th class="px-4 py-3">Quantity</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="(order, index) in result">
                            <td class="px-4 py-4">{{ (index+1) }}</td>
                            <td class="px-4 py-4">{{ order?.product?.name }}</td>
                            <td class="px-4 py-4">{{ order?.color }}</td>
                            <td class="px-4 py-4">{{ order?.size }}</td>
                            <td class="px-4 py-4">-</td>
                            <td class="px-4 py-4">{{ order?.to_branch?.name }}</td>
                            <td class="px-4 py-4">{{ order?.transfer_request }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


</template>
