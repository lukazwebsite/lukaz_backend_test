<script setup lang="ts">

    import AppLayout from '@/layouts/AppLayout.vue';
    import { Head, Link, router, usePage } from '@inertiajs/vue3';
    import { ref, computed, onMounted } from 'vue';
    import { Icon } from '@iconify/vue';
    import { useToast } from "primevue/usetoast";

    const props = defineProps({
        coupon: Object,
    });


    const toast = useToast();

    const page = usePage()
    const user = page.props.auth.user
    const couponId = ref(props.coupon?.id);
    const name = ref(props.coupon?.name);
    const endDate = ref(props.coupon?.end_date);
    const startDate = ref(props.coupon?.start_date);
    const discount_type = ref(props.coupon?.discount_type);
    const discount = ref(props.coupon?.discount);
    const max_discount = ref(props.coupon?.max_discount);
    const limit = ref(props.coupon?.limit);
    const sequance = ref(props.coupon?.sequance);
    const thumbnail = ref('');
    const icon = ref('');
    const banner = ref('');
    const description = ref(props.coupon?.description);
    const status = ref(props.coupon?.status);
    const errors = ref({});
    const loading = ref(false)

    const breadcrumbs = ref([
        { title: 'Dashboard', href: '/' },
        { title: 'Coupons', href: '/coupons' },
        { title: 'Add New', href: '' },
    ]);


    const iconChange = (event) => {
        icon.value = event.target.files[0];
    };

    const thumbnailChange = (event) => {
        thumbnail.value = event.target.files[0];
    };

    const bannerChange = (event) => {
        banner.value = event.target.files[0];
    };


    const submit = () => {

        loading.value = true;

        const formData = new FormData();
        formData.append('name', name.value);
        formData.append('end_date', endDate.value);
        formData.append('start_date', startDate.value);
        formData.append('discount', discount.value);
        formData.append('discount_type', discount_type.value);
        formData.append('max_discount', max_discount.value);
        formData.append('sequance', sequance.value);
        formData.append('limit', limit.value);
        formData.append('icon', icon.value);
        formData.append('thumbnail', thumbnail.value);
        formData.append('banner', banner.value);
        formData.append('status', status.value);
        formData.append('description', description.value);


        router.post('/coupon/'+couponId?.value, formData, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            forceFormData: true,
            onSuccess: () => {
                toast.add({ severity: 'success', summary: 'Success Message', detail: 'Coupons store successfully!', life: 3000 });
                errors.value = {};
                loading.value = false;
            },
            onError: (e) => {
                toast.add({ severity: 'error', summary: 'Error Message', detail: 'Oops! Something wrongs', life: 3000 });
                errors.value = e;
                loading.value = false;
            },
        });
    };



</script>

<template>

    <Head title="Create Copons" />

    <AppLayout :breadcrumbs="breadcrumbs" :loading="loading">
        <Toast position="top-right" />
        <div class="w-full mx-auto p-2 flex flex-row place-content-center">
            <div className="w-5/6 mt-4 shadow-lg bg-white rounded-md pb-1">

                <div class="flex justify-between bg-gray-300 px-2 items-center rounded-t-md p-1 ">
                    <div class="text-lg font-semibold">Edit Coupons</div>
                    <div class="flex">
                        <Link href="/coupon" class="bg-rose-600 p-2 px-3 rounded-md text-lg flex items-center text-white cursor-pointer">  <Icon icon="line-md:arrow-left" class="mr-2" width="1.5rem" height="1.5rem" />
                            Back
                        </Link>
                    </div>
                </div>

                <!-- main content goes here -->
                <form @submit.prevent="submit" class="px-3 min-h-[calc(100vh-12rem)] justify-items-center mt-2">

                    <div class="w-3/4 rounded-md  p-2 px-4 grid grid-cols-2 gap-2">

                        <div class="w-full mb-1">
                            <label :class="{'text-red-500': errors?.name }" for="dd-city" class="text-md font-semibold w-full content-center"> Name:</label>
                            <input :class="{'border': true, 'border-red-500 text-red-500': errors?.name }" type="text" v-model="name" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" placeholder="Coupons Name"/>
                            <p v-if="errors?.name" class="text-red-500 text-sm mt-1">{{ errors?.name }}</p>
                        </div>

                        <div class="w-full mb-1">
                            <label :class="{'text-red-500': errors?.discount }" for="dd-city" class="text-md font-semibold w-full content-center"> Discount:</label>
                            <input :class="{'border': true, 'border-red-500 text-red-500': errors?.discount }" type="number" v-model="discount" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" placeholder="Discount"/>
                            <p v-if="errors?.discount" class="text-red-500 text-sm mt-1">{{ errors?.discount }}</p>
                        </div>



                        <div class="w-full mb-1">
                            <label for="dd-city" :class="{'text-red-500': errors?.discount_type }" class="text-md font-semibold w-full content-center">Discount Type:</label>
                            <select :class="{'border': true, 'border-red-500 text-red-500': errors?.discount_type }" v-model="discount_type" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md">
                                <option value="1">Fixed</option>
                                <option value="0">Parcentage</option>
                            </select>
                            <p v-if="errors?.discount_type" class="text-red-500 text-sm mt-1">{{ errors?.discount_type }}</p>
                        </div>

                        <div class="w-full mb-1">
                            <label :class="{'text-red-500': errors?.max_discount }" for="dd-city" class="text-md font-semibold w-full content-center">Max Discount:</label>
                            <input :class="{'border': true, 'border-red-500 text-red-500': errors?.max_discount }" type="number" v-model="max_discount" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" placeholder="Max Discount"/>
                            <p v-if="errors?.max_discount" class="text-red-500 text-sm mt-1">{{ errors?.max_discount }}</p>
                        </div>


                        <div class="w-full mb-1">
                            <label :class="{'text-red-500': errors?.limit }" for="dd-city" class="text-md font-semibold w-full content-center">Limit Number:</label>
                            <input :class="{'border': true, 'border-red-500 text-red-500': errors?.limit }" type="number" v-model="limit" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" placeholder="Limit Number"/>
                            <p v-if="errors?.limit" class="text-red-500 text-sm mt-1">{{ errors?.limit }}</p>
                        </div>

                        <div class="w-full mb-1 ">
                            <label :class="{'text-green-500': props.coupon?.icon }" for="dd-city" class="text-md font-semibold w-full content-center">Icon:</label>
                            <input :class="{'border': true, 'border-red-500 text-red-500': errors?.icon }" type="file" @change="iconChange" accept="image/*" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" />
                            <p v-if="errors?.icon" class="text-red-500 text-sm mt-1">{{ errors?.icon }}</p>
                        </div>




                        <div class="w-full mb-1">
                            <label for="dd-city" :class="{'text-green-500': props.coupon?.thumbnail }"  class="text-md font-semibold w-full content-center">Thumbnail:</label>
                            <input :class="{'border': true, 'border-red-500 text-red-500': errors?.thumbnail }"  type="file" @change="thumbnailChange" accept="image/*" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" />
                            <p v-if="errors?.thumbnail" class="text-red-500 text-sm mt-1">{{ errors?.thumbnail }}</p>
                        </div>

                        <div class="w-full mb-1">
                            <label for="dd-city" :class="{'text-green-500': props.coupon?.banner }" class="text-md font-semibold w-full content-center">Banner:</label>
                            <input type="file" @change="bannerChange" accept="image/*" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" />
                        </div>


                        <div class="w-full mb-1">
                            <label for="dd-city" class="text-md font-semibold w-full content-center">Start Date & Time:</label>
                            <input type="datetime-local" v-model="startDate" :class="{'border': true, 'border-red-500 text-red-500': errors?.start_date }" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" />
                            <p v-if="errors?.start_date" class="text-red-500 text-sm mt-1">{{ errors?.start_date }}</p>

                        </div>


                        <div class="w-full mb-1">
                            <label for="dd-city" :class="{'text-red-500': errors?.end_date }" class="text-md font-semibold w-full content-center">End Date & Time:</label>
                            <input type="datetime-local" v-model="endDate" :class="{'border': true, 'border-red-500 text-red-500': errors?.end_date }" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" />
                            <p v-if="errors?.end_date" class="text-red-500 text-sm mt-1">{{ errors?.end_date }}</p>

                        </div>

                        <div class="w-full mb-1">
                            <label for="dd-city" class="text-md font-semibold w-full content-center">Sequance:</label>
                            <input type="number" v-model="sequance" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md"></input>
                        </div>

                        <div class="w-full mb-1">
                            <label for="dd-city" :class="{'text-red-500': errors?.status }" class="text-md font-semibold w-full content-center">Status:</label>
                            <select :class="{'border': true, 'border-red-500 text-red-500': errors?.status }" v-model="status" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <p v-if="errors?.status" class="text-red-500 text-sm mt-1">{{ errors?.status }}</p>
                        </div>

                        <div class="grid mb-1 col-span-2">
                            <label for="dd-city" class="text-md font-semibold w-full content-center">Description:</label>
                            <textarea v-model="description" class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md"></textarea>
                        </div>

                    </div>

                    <div class="mb-1 w-3/4 flex justify-end mt-2">
                        <button class="justify-items-end cursor-pointer bg-orange-500 hover:bg-orange-600 text-md border py-1 px-2 rounded-md font-semibold text-white">Update</button>
                    </div>

                </form>

            </div>

        </div>
    </AppLayout>
</template>
