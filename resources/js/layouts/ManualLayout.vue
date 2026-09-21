<script setup lang="ts">
import { ref, onMounted, defineEmits, watch } from "vue";
import { useAddCart } from "@/stores/cart/cart";
import Dialog from 'primevue/dialog';

import InputGroup from 'primevue/inputgroup';

import Select from 'primevue/select';
import { Icon } from '@iconify/vue';
import { Link, usePage, router } from "@inertiajs/vue3";
import { useToast } from "primevue/usetoast";




const page = usePage()
const toast = useToast();
const user = page.props.auth.user
const emit = defineEmits(['triggerParent', 'triggerFilter', 'triggerInput']);

const props = defineProps(['branch', 'categories', 'brands', 'districts', 'branchs', 'customers', "colors", "sizes"]);

// store data in pinia store
const cartAdd = useAddCart();
const currentDateTime = ref();
const cardPay = ref(false);
const discount = ref('')



let qty = ref(1);
const brand = ref();
const branch_id = ref();
const confirm_branch_id = ref();
const size_id = ref();
const errors = ref({})
const pay = ref(0);

let dateTimeData = ref();
let todayTime = ref();
let todayDate = ref();
let category = ref();

const name = ref(null);
const phone_number = ref(null);
const district = ref(null);
const thana = ref(null);
const address = ref(null);
const note = ref(null)

// Police stations loaded dynamically
const policeStations = ref([]);
const loadingThana = ref(false);

watch(district, async (newDistrictId) => {
    thana.value = null;
    policeStations.value = [];
    if (!newDistrictId) return;

    // loadingThana.value = true;

    console.log(newDistrictId);

    try {
        const res = await fetch(`/api/police_stations/${newDistrictId?.id}`);
        const data = await res.json();
        policeStations.value = Array.isArray(data) ? data : (data.data ?? []);
    } catch (e) {
        policeStations.value = [];
    } finally {
        loadingThana.value = false;
    }
});


// Helper to get current date and time as a string
const setCurrentDateTime = () => {

    dateTimeData.value = new Date();
    todayDate.value = dateTimeData.value.getDate() + "/" + (dateTimeData.value.getMonth() + 1) + "/" + dateTimeData.value.getFullYear();
    todayTime.value = dateTimeData.value.getHours() + ":" + dateTimeData.value.getMinutes() + ":" + dateTimeData.value.getSeconds();

}



// Product decrease function goes here
const decrease = (id) => {
    cartAdd.porductDecrease(id);
}

// Product incress function goes here
const increase = (id) => {
    cartAdd.porductIncrease(id);
}

// Product remove function goes here
const remove = (index) => {
    cartAdd.productRemove(index);
}

onMounted(() => {
    currentDateTime.value = setCurrentDateTime();
});


watch(() => [cartAdd.cartPrice, cartAdd.totalDiscount],
    () => {
        discount.value = cartAdd.cartPrice - cartAdd.totalDiscount
    }
)

const handleScroll = (e) => {
    emit('triggerParent', e);
}


const filterChnage = (e) => {
    emit('triggerFilter', e);

    let name = e.target.name;
    let value = e.target.value;

    filterData.value = {
        ...filterData.value,
        [name]: value
    }
}

const optionChange = (e, nameData) => {
    emit('triggerInput', e, nameData);


}


const submit = () => {


    const formData = {
        pay: pay.value,
        discount: discount.value,
        total: cartAdd.totalPrice,
        items: cartAdd.cart,

        name: name.value,
        phone_number: phone_number.value,
        district: district.value,
        thana: thana.value,
        address: address.value,
        confirm_branch_id: confirm_branch_id.value,
        note: note.value
    }

    router.post('/manual', formData, {
        forceFormData: true,
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Success Message', detail: 'Order create successfully!', life: 3000 });

            name.value = null;
            phone_number.value = null;
            district.value = null;
            thana.value = null;
            address.value = null;
            note.value = null

            cartAdd.$reset();
            cardPay.value = false;
            errors.value = {};

        },
        onError: (e) => {
            toast.add({ severity: 'error', summary: 'Error Message', detail: 'Oops! Something wrongs', life: 3000 });
            errors.value = e;

        },
    });
};



</script>

<template>
    <div class="flex flex-col bg-gradient-to-r from-sky-100 to-sky-50 h-screen">
        <Toast position="top-right" />
        <div class="w-full border-b bg-gray-50 h-10 flex justify-between items-center print:hidden">
            <div class="flex">
                <img src="/assets/sites/lukaz_icon-192x192.png" alt="Logo" class="h-8 w-8 rounded-full bg-white p-1" />
                <h1 class="text-2xl ml-1 mt-0 font-bold">Lukaz Shop</h1>
            </div>

            <div class="font-bold p-2">Location: <b> <i>{{ props.branch?.address }}</i> </b> | Window: Sale</div>


            <div> {{ todayDate }} : {{ todayTime }}

            </div>
            <div class="flex ">
                <div class="text-xl">Welcome back <span class="italic text-sky-500">{{ user.name }}</span></div>
                <Link class="bg-rose-400 p-1 px-2 mx-1 text-white rounded-md" href="/dashboard">Dashboard</Link>
                <Link class="bg-rose-400 p-1 px-2 mx-1 text-white rounded-md" href="/dashboard">Logout</Link>
            </div>
        </div>

        <div class="w-full flex">
            <div class="w-5/7  rounded-tr-md m-2  min-h-[calc(100vh-6rem)] print:hidden">


                <div class="w-full mb-2 flex items-center justify-between print:hidden">

                    <div class="mx-1 w-full">
                        <input autofocus="true" placeholder="Barcode/Code/Name" name="barcode" value=""
                            class="w-full focus:outline-none rounded-md text-sm  bg-white p-3 border border-gray-300"
                            @keyup="filterChnage" />
                    </div>


                    <div class="mx-1 w-full">
                        <InputGroup>
                            <Select v-model="category" :options="categories" optionLabel="name" filter showClear
                                placeholder="Categories" name="category" @change="(e) => optionChange(e, 'category')" />
                        </InputGroup>
                    </div>

                    <div class="mx-1 w-full">
                        <InputGroup>
                            <Select v-model="size_id" :options="sizes" optionLabel="size" filter showClear
                                placeholder="Size" name="size_id" @change="(e) => optionChange(e, 'size_id')" />
                        </InputGroup>
                    </div>

                    <div class="mx-1 w-full">
                        <InputGroup>
                            <Select v-model="brand" :options="brands" optionLabel="name" filter showClear
                                placeholder="Brand" name="brand" @change="(e) => optionChange(e, 'brand')" />
                        </InputGroup>
                    </div>

                    <div class="mx-1 w-full">
                        <InputGroup>
                            <Select v-model="branch_id" :options="branchs" optionLabel="name" filter showClear
                                placeholder="Branch" name="branch" @change="(e) => optionChange(e, 'branchs')" />
                        </InputGroup>
                    </div>


                </div> <!-- Category Filter -->
                <div class="w-full max-h-[calc(100vh-7rem)] overflow-auto product_window print:hidden"
                    @scroll="handleScroll">
                    <slot />
                </div>
            </div>
            <div class="w-2/7 border rounded-tl-md mt-2 bg-white max-h-[calc(100vh-3rem)] print:hidden relative">
                <div class="w-full flex flex-row px-2 py-1 text-sm font-bold mt-1 border-b">New Order Bill</div>
                <div class="flex justify-between flex-col ">
                    <div class="w-full max-h-[calc(100vh-23rem)] overflow-auto product_window print:hidden parentDiv">

                        <div v-for="(cart, index) in cartAdd.cart" :key="index" class="w-full px-2 text-sm mt-1 ">
                            <div to="javascript:"
                                class="relative group overflow-hidden flex items-center text-center mr-[3px] bg-sky-50 rounded-md border-green-400 border">
                                <img class="w-14 h-12" :alt="cart.name"
                                    :src="cart.image ? `/products/${cart.image}` : `/assets/sites/sample.webp`" />
                                <div class="grid w-full">
                                    <span class="ml-1 w-full text-left">{{ cart.name }}</span>
                                    <span class="ml-1 w-full text-left">৳{{ cart.current_price }} | {{ cart.size }} | {{
                                        cart.color }}</span>
                                </div>
                                <div class="grid content-center">
                                    <div class="flex justify-between w-full border overflow-hidden mr-1">
                                        <Icon @click="decrease(cart.id)" icon="twemoji:minus"
                                            class="fa-solid fa-minus bg-green-400 text-sm h-fit w-8 text-white text-center py-2 cursor-pointer" />
                                        <input v-model="cart.qty" inputId="integeronly" fluid :useGrouping="false"
                                            class="w-2/3 text-center text-lg p-0 m-0 rounded-none" />
                                        <Icon @click="increase(cart.id)" icon="twemoji:plus"
                                            class="fa-regular bg-green-400 h-fit text-sm w-8 text-white text-center py-2 cursor-pointer" />
                                    </div>
                                </div>
                                <span
                                    class="hidden group-hover:block absolute cursor-pointer left-0 bottom-0 p-1 rounded-tr-lg  rounded-bl-mg text-white bg-red-500">
                                    <Icon @click="remove(index)" icon="bxs:trash" class="text-md w-8 h-8"></Icon>
                                </span>
                            </div>
                        </div><!-- add card end -->
                    </div>
                </div>
                <div class="print:hidden absolute bottom-2 right-0 w-full bg-white">
                    <div class="flex justify-between px-4">
                        <div class="text-lg w-full">Sub Total :</div>
                        <div class="text-md font-bold text-right text-lg border-b w-full pr-4">{{ cartAdd.totalPrice }}
                        </div>
                    </div>
                    <div class="flex justify-between px-4">
                        <div class="text-lg w-full">Discount</div>
                        <input class="text-right border-b w-full bg-gray-200" w-full type="number" v-model="discount">
                    </div>

                    <div class="flex justify-between px-4">
                        <div class="text-lg w-full font-bold">Grand Total</div>
                        <div class="text-md w-full font-bold text-right text-lg pr-4 border-b">{{
                            cartAdd.totalPrice - discount }}</div>
                    </div>

                    <div class="flex justify-between px-4">
                        <div class="text-lg w-full">Pay</div>
                        <input
                            class="text-right bg-green-500 w-full inset-shadow-sm inset-shadow-gray-400 rounded-sm p-2 pr-3"
                            type="text" v-model="pay" placeholder="0">
                    </div>

                    <div class="flex justify-between px-4">
                        <div class="text-lg w-full">Return</div>
                        <div class="text-md w-full font-bold text-right text-lg pr-4">{{
                            ((cartAdd.totalPrice - discount) - (pay)) }}</div>
                    </div>

                    <div class="px-2 mt-3 grid grid-cols-1">

                        <div class="px-2 mt-2 grid grid-cols-1 gap-2">
                            <div class="flex items-center justify-center border text-white text-center rounded-md bg-green-600 cursor-pointer hover:bg-green-700 py-2"
                                @click="cardPay = true">
                                <Icon icon="line-md:confirm" height="2rem" width="2rem" />
                                <div class="text-lg font-bold mt-1 ml-2">Confirm Order</div>
                            </div>

                            <!-- <div class="flex flex-col items-center justify-center border text-white text-center rounded-md bg-sky-600 cursor-pointer hover:bg-sky-700 py-2"  @click="cardPay = true">
                                <Icon icon="mingcute:hand-card-fill" height="2rem" width="2rem" />
                                <div class="text-lg font-bold mt-1">Card</div>
                            </div> -->

                            <!-- <div class="flex items-center justify-center border text-white text-center rounded-md bg-red-600 cursor-pointer hover:bg-red-700 py-2"  @click="cardPay = true">
                                <Icon icon="fa:refresh" height="2rem" width="2rem" />
                                <div class="text-lg font-bold mt-1 ml-2">Cancel</div>
                            </div> -->



                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>


    <Dialog v-model:visible="cardPay" modal header="Edit Profile" pt:mask:class="backdrop-blur-sm">
        <template #container="{ closeCallback }">
            <div class="px-8 py-8 gap-6 rounded-md w-full"
                style="background-image: radial-gradient(circle at left top, var(--p-primary-400), var(--p-primary-700))">

                <div class="w-full flex flex-col mb-2">
                    <label for="username" :class="{ 'text-red-500': errors?.name }"
                        class="text-primary-50 font-semibold">Full Name <span class="text-red-600">*</span></label>
                    <input id="username" :class="{ 'border': true, 'border-red-500 text-red-500': errors?.name }"
                        v-model="name"
                        class="!bg-white/20 !border-0 !p-2 w-full !text-primary-50 rounded-md focus:outline-none focus:ring-0 readonly"
                        placeholder="Full Name" />
                    <p v-if="errors?.name" class="text-red-500 text-sm mt-1">{{ errors?.name }}</p>
                </div>

                <div class="w-full flex flex-col mb-2">
                    <label for="phone_number" :class="{ 'text-red-500': errors?.phone_number }"
                        class="text-primary-50 font-semibold">Phone Number <span class="text-red-600">*</span></label>
                    <input id="phone_number"
                        :class="{ 'border': true, 'border-red-500 text-red-500': errors?.phone_number }"
                        v-model="phone_number"
                        class="!bg-white/20 !border-0 !p-2 w-full !text-primary-50 rounded-md focus:outline-none focus:ring-0 readonly"
                        placeholder="Phone Number" />
                    <p v-if="errors?.phone_number" class="text-red-500 text-sm mt-1">{{ errors?.phone_number }}</p>
                </div>

                <div class="w-full flex flex-col mb-2">
                    <label for="district" :class="{ 'text-red-500': errors?.district }"
                        class="text-primary-50 font-semibold">District <span class="text-red-600">*</span></label>
                    <Select v-model="district" :options="districts" optionLabel="name" filter showClear
                        placeholder="District" name="district" @change="(e) => optionChange(e, 'district')"
                        :class="{ 'border': true, 'border-red-500 text-red-500': errors?.district }"
                        class="!bg-white/20 !border-0 w-full !text-primary-50 rounded-md focus:outline-none focus:ring-0" />

                    <p v-if="errors?.district" class="text-red-500 text-sm mt-1">{{ errors?.district }}</p>
                </div>

                <div class="w-full flex flex-col mb-2">
                    <label for="thana" :class="{ 'text-red-500': errors?.thana }"
                        class="text-primary-50 font-semibold">Thana <span class="text-red-600">*</span></label>
                    <Select v-model="thana" :options="policeStations" optionLabel="name"
                        :disabled="loadingThana || policeStations.length === 0" filter showClear
                        placeholder="police Stations" name="police Stations"
                        :class="{ 'border': true, 'border-red-500 text-red-500': errors?.thana }"
                        class="!bg-white/20 !border-0 w-full !text-primary-50 rounded-md focus:outline-none focus:ring-0" />
                    <p v-if="errors?.thana" class="text-red-500 text-sm mt-1">{{ errors?.thana }}</p>
                </div>

                <div class="w-full flex flex-col mb-2">
                    <label for="thana" :class="{ 'text-red-500': errors?.confirm_branch_id }"
                        class="text-primary-50 font-semibold">Confirm Branch <span class="text-red-600">*</span></label>
                    <select :class="{ 'border': true, 'border-red-500 text-red-500': errors?.confirm_branch_id }"
                        class="!bg-white/20 !border-0 !p-2 !text-primary-50 rounded-md focus:outline-none focus:ring-0 readonly"
                        id="confirm_branch_id" v-model="confirm_branch_id" placeholder="Select Branch">
                        <option :value="branch?.id" v-for="branch in branchs">{{ branch?.name }}</option>
                    </select>
                    <p v-if="errors?.confirm_branch_id" class="text-red-500 text-sm mt-1">{{ errors?.confirm_branch_id
                    }}</p>
                </div>

                <div class="w-full flex flex-col mb-2">
                    <label for="thana" :class="{ 'text-red-500': errors?.address }"
                        class="text-primary-50 font-semibold">Address <span class="text-red-600">*</span></label>
                    <textarea id="thana" :class="{ 'border': true, 'border-red-500 text-red-500': errors?.address }"
                        v-model="address"
                        class="!bg-white/20 !border-0 !p-2 w-full !text-primary-50 rounded-md focus:outline-none focus:ring-0 readonly"
                        placeholder="Address" />
                    <p v-if="errors?.address" class="text-red-500 text-sm mt-1">{{ errors?.address }}</p>
                </div>

                <div class="w-full flex flex-col mb-2">
                    <label for="note" class="text-primary-50 font-semibold">Note </label>
                    <input id="note" v-model="note"
                        class="!bg-white/20 !border-0 !p-2 w-full !text-primary-50 rounded-md focus:outline-none focus:ring-0 readonly"
                        placeholder="Note" />
                </div>

                <div class="w-full grid grid-cols-2 mb-2 gap-4">
                    <div class="">
                        <label for="amount" class="text-primary-50 font-semibold">Total Amount <span
                                class="text-red-600">*</span></label>
                        <input id="amount" :value="((cartAdd.totalPrice - discount) - (pay))"
                            class="!bg-white/20 !border-0 !p-2 w-full !text-primary-50 rounded-md focus:outline-none focus:ring-0 readonly"
                            :readonly="true" placeholder="Total Amount" />
                    </div>

                    <div class="">
                        <label for="amount" :class="{ 'text-red-500': errors?.pay }"
                            class="text-primary-50 font-semibold">Advance Amount <span
                                class="text-red-600">*</span></label>
                        <input id="amount" :class="{ 'border': true, 'border-red-500 text-red-500': errors?.pay }"
                            v-model="pay"
                            class="!bg-white/20 !border-0 !p-2 w-full !text-primary-50 rounded-md focus:outline-none focus:ring-0 readonly"
                            placeholder="Advance Amount" />
                        <p v-if="errors?.pay" class="text-red-500 text-sm mt-1">{{ errors?.pay }}</p>
                    </div>

                </div>


                <div class="w-full grid grid-cols-2 mb-2 gap-4 mt-6">
                    <div class="flex-col">

                        <button @click="closeCallback"
                            class="w-full p-2 bg-red-500 rounded-md !border !border-white/30 text-white font-semibold hover:!bg-white/10">Cancel</button>
                    </div>
                    <div>

                        <button label="Sign-In" @click="submit"
                            class="w-full p-2 bg-white rounded-md !text-primary-50 text-black font-semibold !border !border-white/30 hover:!bg-white/10">Order
                            Confirm</button>
                    </div>
                </div>

            </div>
        </template>
    </Dialog>



</template>
