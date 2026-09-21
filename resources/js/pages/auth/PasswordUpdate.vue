<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { ref } from "vue";
import axios from 'axios'

defineProps<{
    status?: string;
}>();

const otp = ref('')

const form = useForm({
    otp: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {

    axios.post(route('opt.update'), form, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
    .then(function (response) {

        if("success" == response.data){
            window.location.href = route('dashboard');
        }

        otp.value = response.data

    })
    .catch(function (error) {
        console.log(error);
    });

};
</script>

<template>
    <AuthLayout title="Forgot password" description="Enter your email or mobile number to receive a OTP">
        <Head title="Forgot password" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <div v-if="otp" class="mb-4 text-center text-sm font-medium text-red-600">
            {{ otp }}
        </div>



        <div class="space-y-6">
            <form @submit.prevent="submit">
                <div class="grid gap-2 mb-3">
                    <Label for="otp" class="font-semibold">OTP</Label>
                    <Input id="otp" type="text" name="otp" autocomplete="off" v-model="form.otp" autofocus placeholder="OTP" />
                    <InputError :message="form.errors.otp" />
                </div>

                 <div class="grid gap-2 mb-3">
                    <Label for="email" class="font-semibold">Password</Label>
                    <Input id="email" type="password" name="password" autocomplete="off" v-model="form.password" autofocus placeholder="Password" />
                    <InputError :message="form.errors.password" />
                </div>

                 <div class="grid gap-2 mb-3">
                    <Label for="email" class="font-semibold">Confirm Password</Label>
                    <Input id="email" type="password" name="password_confirmation" autocomplete="off" v-model="form.password_confirmation" autofocus placeholder="Password Confirmation" />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <div class="my-6 flex items-center justify-start">
                    <Button class="w-full" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        Update Password
                    </Button>
                </div>
            </form>

            <div class="space-x-1 text-center text-sm text-muted-foreground">
                <span>Or, return to</span>
                <TextLink :href="route('login')">log in</TextLink>
            </div>
        </div>
    </AuthLayout>
</template>
