<script setup lang="ts">
import { ref, computed, onMounted, defineEmits, defineExpose, watch } from "vue";
import { useAddCart } from "@/stores/cart/cart";

import InputGroup from 'primevue/inputgroup';
import InputGroupAddon from 'primevue/inputgroupaddon';
import Select from 'primevue/select';
import Dialog from 'primevue/dialog';
import { Icon } from '@iconify/vue';
import { Link, Head, usePage, router } from "@inertiajs/vue3";
import { useToast } from "primevue/usetoast";
import axios from 'axios';

    const printSubmit = ref(true);

    const cartScroll = ref(null);

    defineExpose({
        scrollToBottom() {
            if (cartScroll.value) {
            cartScroll.value.scrollTop = cartScroll.value.scrollHeight;
            }
        }
    });

    const page = usePage()
    const toast = useToast();
    const user = page.props.auth.user
    const emit = defineEmits(['triggerParent', 'triggerFilter', 'triggerInput']);

    const props = defineProps(['branch', 'categories', 'brands', 'customers', 'sizes', 'colors']);

    // store data in pinia store
    const cartAdd = useAddCart();
    const currentDateTime = ref();
    const discount = ref('');
    const additional_discount = ref('');
    const cardPay = ref(false);
    const color_id = ref();
    const size_id = ref();
    const payment_method = ref('cash');
    const vat_tax = ref(0);

	let qty = ref(1);
	const brand = ref(null);
	const transaction = ref(null);


    const pay = ref(null);
    const total_amount = ref(0);

    const errors = ref({})

    let dateTimeData = ref();
    let todayTime = ref();
    let todayDate = ref();
    let category = ref();

	let customer = ref({id : 1, mobile : "0167", name : "Guest"});

    // Local copy so a quick add from the POS can extend the list without
    // mutating the prop or reloading the page.
    const customerList = ref([...(props.customers ?? [])]);

    watch(() => props.customers, (list) => {
        customerList.value = [...(list ?? [])];
    });

    // Quick "add customer" modal, opened by the + next to the customer select.
    // Same fields as the full customer form, but only the mobile is required.
    const showCustomerModal = ref(false);
    const customerErrors = ref({});
    const customerSaving = ref(false);

    const blankCustomerForm = () => ({
        name: '',
        email: '',
        mobile: '',
        password: '',
        password_confirmation: '',
        status: 1,
        description: '',
    });

    const newCustomer = ref(blankCustomerForm());

    const openCustomerModal = () => {
        newCustomer.value = blankCustomerForm();
        customerErrors.value = {};
        showCustomerModal.value = true;
    };

    const storeQuickCustomer = () => {

        if (customerSaving.value) {
            return;
        }

        customerSaving.value = true;
        customerErrors.value = {};

        axios.post(route('customer.pos.store'), newCustomer.value)
        .then((res) => {
            const created = res.data?.customer;

            customerList.value = [created, ...customerList.value];
            customer.value = created;

            showCustomerModal.value = false;
            customerSaving.value = false;

            toast.add({ severity: 'success', summary: 'Success Message', detail: 'Customer added successfully!', life: 3000 });
        })
        .catch((error) => {
            customerSaving.value = false;

            if (error.response?.status === 422) {
                customerErrors.value = error.response.data?.errors ?? {};
                return;
            }

            toast.add({ severity: 'error', summary: 'Error Message', detail: error.response?.data?.message ?? 'Oops! Something went wrong.', life: 3000 });
        });
    };

    // Helper to get current date and time as a string
    const setCurrentDateTime = () => {

        dateTimeData.value = new Date();
        todayDate.value = dateTimeData.value.getDate()+"/"+(dateTimeData.value.getMonth()+1)+"/"+dateTimeData.value.getFullYear();
        todayTime.value = dateTimeData.value.getHours() + ":" + dateTimeData.value.getMinutes() + ":" + dateTimeData.value.getSeconds();

    }



    // Product decrease function goes here
    const decrease = (id) => {
        cartAdd.porductDecrease(id);
    }

    // Product incress function goes here
    const increase = (id) => {
        let checkExits = cartAdd.cart.filter(exitItem =>  exitItem.id === id);

        if(checkExits[0]?.stocks <= checkExits[0].qty){
           toast.add({ severity: 'error', summary: 'Error Message', detail: 'Oops! This product is out of stock.', life: 3000 });
           return;
        }

        // checkExits[0].qty +=1;

        cartAdd.porductIncrease(id);
    }

    // Product remove function goes here
    const remove = (index) => {
        cartAdd.productRemove(index);
    }

    // Flat taka off one bill line, whatever its quantity is.
    const lineDiscountChange = (id, value) => {
        cartAdd.productDiscount(id, value);
    }

    // What one line is worth after its own discount.
    const lineTotal = (cart) => {
        return (cart.current_price * cart.qty) - cartAdd.lineDiscount(cart);
    }

    const toAmount = (value) => {
        let amount = Number(value);

        return (isNaN(amount) || amount < 0) ? 0 : amount;
    }

    // Sub Total already carries the per item discounts.
    const subTotal = computed(() => cartAdd.cartPrice - cartAdd.itemDiscount);

    const grandTotal = computed(() => Math.max(0, subTotal.value - toAmount(discount.value) - toAmount(additional_discount.value)));

    onMounted(() => {
		currentDateTime.value = setCurrentDateTime();
	});

    watch(() => [cartAdd.cartPrice, cartAdd.totalDiscount],
        () => {
            discount.value = cartAdd.cartPrice - cartAdd.totalDiscount
        }
    )

    // The quantity box writes straight into the cart, so refresh the totals here.
    watch(() => cartAdd.cart.map(item => item.qty).join(','),
        () => {
            cartAdd.recalculate();
        }
    )


    const handleScroll = (e) => {
        emit('triggerParent', e);
    }


    const filterChnage = (e) => {
        emit('triggerFilter', e);

    }

    const optionChange = (e, nameData) => {
        emit('triggerInput', e, nameData);
    }


    const submit = () => {


        if(cartAdd.cart.length <= 0){
            toast.add({ severity: 'warn', summary: 'Cart is empty', detail: 'Please add some products to the cart before submitting.', life: 3000 });
            printSubmit.value = true;
            return;
        }

        printSubmit.value = false;


        if (pay.value == null && pay.value < grandTotal.value ) {
            toast.add({ severity: 'warn', summary: 'Check payment amount', detail: 'The sale total does not match the amount to pay', life: 3000 });
            printSubmit.value = true;
            return;
        }
        const formData = {
            customer: customer?.value?.id,
            payment_method: payment_method.value,
            pay: pay.value,
            discount: toAmount(discount.value),
            additional_discount: toAmount(additional_discount.value),
            total: subTotal.value,
            items: cartAdd.cart.map(item => ({ ...item, item_discount: cartAdd.lineDiscount(item) }))
        }

        router.post('/pos', formData, {
            forceFormData: true,
            onSuccess: () => {
                toast.add({ severity: 'success', summary: 'Success Message', detail: 'Product store successfully!', life: 3000 });
                errors.value = {};
                printSubmit.value = true;
            },
            onError: (e) => {
                toast.add({ severity: 'error', summary: 'Error Message', detail: 'Oops! Something wrongs', life: 3000 });
                errors.value = e;
                printSubmit.value = true;

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
                <Link class="bg-rose-400 p-1 px-2 mx-1 text-white rounded-md" href="/logout">Logout</Link>
            </div>
        </div>

        <div class="w-full flex">
            <div class="w-5/7  rounded-tr-md m-2  min-h-[calc(100vh-6rem)] print:hidden">


                <div class="w-full mb-2 flex items-center justify-between print:hidden">
                    <div class="w-full">
                        <InputGroup>
                            <Select v-model="customer" :options="customerList" filter showClear optionLabel="name" placeholder="Customer" :filterFields="['name', 'mobile']" class="w-full md:w-56">
                                <template #value="slotProps">
                                    <div v-if="slotProps.value">{{ slotProps.value.name }} | {{ slotProps.value.mobile }}</div>
                                    <span v-else>
                                        {{ slotProps.placeholder }}
                                    </span>
                                </template>

                                <template #option="slotProps">
                                    <div>{{ slotProps.option.name }} | {{ slotProps.option.mobile }}</div>
                                </template>

                            </Select>

                            <InputGroupAddon>
                                <Icon @click="openCustomerModal" icon="twemoji:plus" width="1.5em" height="1.5em" class="cursor-pointer" />
                            </InputGroupAddon>
                        </InputGroup>
                    </div>

                    <div class="mx-1 w-full">
                        <input autofocus="true" placeholder="Barcode/Code/Name" name="barcode" value=""  class="w-full focus:outline-none rounded-md text-sm  bg-white p-3 border border-gray-300" @keyup="filterChnage"/>
                    </div>


                    <div class="mx-1 w-full">
                        <InputGroup>
                            <Select v-model="category" :options="categories" optionLabel="name" filter showClear placeholder="Categories" name="category" @change="(e) => optionChange(e, 'category')"/>
                        </InputGroup>
                    </div>

                    <div class="mx-1 w-full">
                        <InputGroup>
                            <Select v-model="size_id" :options="sizes" optionLabel="size" filter showClear placeholder="Size" name="size_id" @change="(e) => optionChange(e, 'size_id')"/>
                        </InputGroup>
                    </div>

                    <!-- <div class="mx-1 w-full">
                        <InputGroup>
                            <Select v-model="color_id" :options="colors" optionLabel="color" filter showClear placeholder="Color" name="color_id" @change="(e) => optionChange(e, 'color_id')"/>
                        </InputGroup>
                    </div> -->

                    <div class="mx-1 w-full">
                        <InputGroup>
                            <Select v-model="brand" :options="brands" optionLabel="name" filter showClear placeholder="Brand" name="brand" @change="(e) => optionChange(e, 'brand')"/>
                        </InputGroup>
                    </div>
                </div> <!-- Category Filter -->
                <div class="w-full max-h-[calc(100vh-7rem)] overflow-auto product_window print:hidden" @scroll="handleScroll">
                    <slot />
                </div>
            </div>
            <div class="w-2/7 border rounded-tl-md mt-2 bg-white max-h-[calc(100vh-3rem)] print:hidden relative">
                <div class="w-full flex flex-row px-2 py-1 text-sm font-bold mt-1 border-b">New Order Bill</div>
                <div class="flex justify-between flex-col ">
                    <div ref="cartScroll" class="w-full max-h-[calc(100vh-26rem)] overflow-auto product_window print:hidden parentDiv">

                        <div v-for="eror in errors" class="text-red-500">
                            {{ eror }}
                        </div>

                        <div v-for="(cart, index) in cartAdd.cart" :key="index" class="w-full px-2 text-sm mt-1 ">
                            <div to="javascript:" class="relative group overflow-hidden flex items-center text-center mr-[3px] bg-sky-50 rounded-md border-green-400 border">
                                <img class="w-14 h-12" :alt="cart.name" :src="cart.image ? `/products/${cart.image }` : `/assets/sites/sample.webp`" />
                                <div class="grid w-full">
                                    <span class="ml-1 w-full text-left">{{ cart.name }}</span>
                                    <span class="ml-1 w-full text-left">৳{{ cart.regular_price }} | {{ cart.size }} | {{ cart.color }}</span>
                                </div>
                                <div class="grid content-center">
                                    <div class="flex items-center justify-end w-full mr-1">
                                        <span class="text-[10px] bg-red-600 text-white rounded-sm px-1 py-0.5 mr-1">Discount</span>
                                        <input :value="cart.item_discount" @input="lineDiscountChange(cart.id, $event.target.value)" type="text" placeholder="0" class="w-16 text-right border bg-gray-200 rounded-sm px-1 py-1 text-sm"/>
                                    </div>
                                </div>
                                <div class="grid content-center">
                                    <div class="text-right text-sm font-semibold w-16 mr-1">৳{{ lineTotal(cart) }}</div>
                                </div>
                                <div class="grid content-center">
                                    <div class="flex justify-between w-full border overflow-hidden mr-1">
                                        <Icon @click="decrease(cart.id)" icon="twemoji:minus" class="fa-solid fa-minus bg-green-400 text-sm h-fit w-8 text-white text-center py-2 cursor-pointer"/>
                                        <input v-model="cart.qty" :max="cart?.stocks" inputId="integeronly" fluid :useGrouping="false" class="w-2/3 text-center text-lg p-0 m-0 rounded-none"/>
                                        <Icon @click="increase(cart.id)" icon="twemoji:plus" class="fa-regular bg-green-400 h-fit text-sm w-8 text-white text-center py-2 cursor-pointer"/>
                                    </div>
                                </div>
                                <span class="hidden group-hover:block absolute cursor-pointer left-0 bottom-0 p-1 rounded-tr-lg  rounded-bl-mg text-white bg-red-500">
                                    <Icon @click="remove(index)" icon="bxs:trash" class="text-md w-8 h-8"></Icon>
                                </span>
                            </div>
                        </div><!-- add card end -->
                    </div>
                </div>
                <div class="print:hidden absolute bottom-2 right-0 w-full bg-white">
                    <div class="flex justify-between px-4">
                        <div class="text-lg w-full">Sub Total :</div>
                        <div class="text-md font-bold text-right text-lg border-b w-full pr-4">{{ subTotal }}</div>
                    </div>
                    <div class="flex justify-between px-4">
                        <div class="text-lg w-full">Discount</div>
                        <input class="text-right border-b w-full bg-gray-200 pr-3 rounded-sm p-1" w-full type="text" v-model="discount">
                    </div>

                    <div class="flex justify-between px-4">
                        <div class="text-lg w-full whitespace-nowrap">Additional Discount</div>
                        <input class="text-right border-b w-full bg-gray-200 pr-3 rounded-sm p-1" w-full type="text" v-model="additional_discount" placeholder="0">
                    </div>

                    <div class="flex justify-between px-4">
                        <div class="text-lg w-full font-bold">Grand Total</div>
                        <div class="text-md w-full font-bold text-right text-lg pr-4 border-b">{{ grandTotal }}</div>
                    </div>

                    <div class="flex justify-between px-4">
                        <div class="text-lg w-full">Payment Method</div>
                        <select v-model="payment_method" class=" p-2 mb-1 rounded-sm bg-gray-200 w-full">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="mobile_banking">Mobile Banking</option>
                        </select>
                    </div>

                    <div class="flex justify-between px-4" :class="{'text-red-500': errors?.pay }">
                        <div class="text-lg w-full">Pay</div>
                        <input :class="{'border': true, 'border-red-500 bg-red-500 text-white': errors?.pay }" class="text-right bg-green-500 w-full inset-shadow-sm inset-shadow-gray-400 rounded-sm p-2 pr-3" type="text" v-model="pay" placeholder="0">
                    </div>

                    <div class="flex justify-between px-4" v-if="pay > grandTotal">
                        <div class="text-lg w-full">Return</div>
                        <div class="text-md w-full font-bold text-right text-lg pr-4">{{ (grandTotal - pay) }}</div>
                    </div>

                    <div class="px-2 mt-3 grid grid-cols-1">

                        <div class="px-2 mt-2 grid grid-cols-2 gap-2">
                            <div v-if="printSubmit" class="flex items-center justify-center border text-white text-center rounded-md bg-green-600 cursor-pointer hover:bg-green-700 py-2" @click="submit">
                                <Icon icon="teenyicons:print-solid" height="2rem" width="2rem" />
                                <div class="text-lg font-bold mt-1 ml-2">Print</div>
                            </div>
                             <div v-else class="flex items-center justify-center border text-white text-center rounded-md bg-green-600 cursor-pointer hover:bg-green-700 py-2">
                                <Icon icon="teenyicons:print-solid" height="2rem" width="2rem" />
                                <div class="text-lg font-bold mt-1 ml-2">Please wait...</div>
                            </div>

                            <!-- <div class="flex flex-col items-center justify-center border text-white text-center rounded-md bg-sky-600 cursor-pointer hover:bg-sky-700 py-2"  @click="cardPay = true">
                                <Icon icon="mingcute:hand-card-fill" height="2rem" width="2rem" />
                                <div class="text-lg font-bold mt-1">Card</div>
                            </div> -->

                            <div class="flex items-center justify-center border text-white text-center rounded-md bg-red-600 cursor-pointer hover:bg-red-700 py-2"  @click="cardPay = true">
                                <Icon icon="fa:refresh" height="2rem" width="2rem" />
                                <div class="text-lg font-bold mt-1 ml-2">Cancel</div>
                            </div>



                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>

    <Dialog v-model:visible="showCustomerModal" modal header="Add Customer" :style="{ width: '44rem' }" class="print:hidden">

        <!-- autocomplete="off" everywhere: the browser otherwise offers the
             logged in admin's own email and password into the customer form. -->
        <form @submit.prevent="storeQuickCustomer" autocomplete="off">

            <div class="grid grid-cols-2 gap-3">

                <div class="w-full">
                    <label :class="{ 'text-red-500': customerErrors?.mobile }" class="text-md font-semibold w-full content-center">Mobile:</label>
                    <input v-model="newCustomer.mobile" type="text" autofocus placeholder="Mobile" autocomplete="off"
                        :class="{ 'border-red-500 text-red-500': customerErrors?.mobile }"
                        class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" />
                    <p v-if="customerErrors?.mobile" class="text-red-500 text-sm mt-1">{{ customerErrors.mobile[0] }}</p>
                </div>

                <div class="w-full">
                    <label :class="{ 'text-red-500': customerErrors?.name }" class="text-md font-semibold w-full content-center">Name:</label>
                    <input v-model="newCustomer.name" type="text" placeholder="Name" autocomplete="off"
                        :class="{ 'border-red-500 text-red-500': customerErrors?.name }"
                        class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" />
                    <p v-if="customerErrors?.name" class="text-red-500 text-sm mt-1">{{ customerErrors.name[0] }}</p>
                </div>

                <div class="w-full">
                    <label :class="{ 'text-red-500': customerErrors?.email }" class="text-md font-semibold w-full content-center">Email:</label>
                    <input v-model="newCustomer.email" type="email" placeholder="Email" autocomplete="off"
                        :class="{ 'border-red-500 text-red-500': customerErrors?.email }"
                        class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" />
                    <p v-if="customerErrors?.email" class="text-red-500 text-sm mt-1">{{ customerErrors.email[0] }}</p>
                </div>

                <div class="w-full">
                    <label :class="{ 'text-red-500': customerErrors?.status }" class="text-md font-semibold w-full content-center">Status:</label>
                    <select v-model="newCustomer.status"
                        :class="{ 'border-red-500 text-red-500': customerErrors?.status }"
                        class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md">
                        <option :value="1">Active</option>
                        <option :value="0">Inactive</option>
                    </select>
                    <p v-if="customerErrors?.status" class="text-red-500 text-sm mt-1">{{ customerErrors.status[0] }}</p>
                </div>

                <div class="w-full">
                    <label :class="{ 'text-red-500': customerErrors?.password }" class="text-md font-semibold w-full content-center">Password:</label>
                    <input v-model="newCustomer.password" type="password" autocomplete="new-password"
                        :class="{ 'border-red-500 text-red-500': customerErrors?.password }"
                        class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" />
                    <p v-if="customerErrors?.password" class="text-red-500 text-sm mt-1">{{ customerErrors.password[0] }}</p>
                </div>

                <div class="w-full">
                    <label class="text-md font-semibold w-full content-center">Confirm Password:</label>
                    <input v-model="newCustomer.password_confirmation" type="password" autocomplete="new-password"
                        class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" />
                </div>

                <div class="w-full col-span-2">
                    <label class="text-md font-semibold w-full content-center">Description:</label>
                    <textarea v-model="newCustomer.description"
                        class="w-full text-md border py-1 px-2 outline-none focus:border-green-200 rounded-md" />
                </div>

            </div>

            <div class="flex justify-end mt-4">
                <button type="button" @click="showCustomerModal = false"
                    class="cursor-pointer bg-gray-300 hover:bg-gray-400 text-md border py-1 px-3 rounded-md font-semibold mr-2">Cancel</button>
                <button type="submit" :disabled="customerSaving"
                    class="cursor-pointer bg-sky-500 hover:bg-sky-600 disabled:opacity-50 text-md border py-1 px-3 rounded-md font-semibold text-white">
                    {{ customerSaving ? 'Saving...' : 'Add Customer' }}
                </button>
            </div>

        </form>

    </Dialog>

</template>
