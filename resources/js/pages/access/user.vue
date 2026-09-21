<script setup lang="ts">

    import AppLayout from '@/layouts/AppLayout.vue';
    import { Head, Link, router, usePage } from '@inertiajs/vue3';
    import { ref, computed, onMounted } from 'vue';
    import { Icon } from '@iconify/vue';
    import { useToast } from "primevue/usetoast";
    import AutoComplete from 'primevue/autocomplete';
    import axios from 'axios';

        const page = usePage()
    const user = page.props.auth.user


    const props = defineProps({
        staffs: Object,
        actions: Object,
        menus: Object
    });

    const toast = useToast();
    const loading = ref(false);
    const items = ref([]);
    const userId = ref('');
    const edit = ref();

    const addBox = ref(false);
    const editBox = ref(false);
    const viewBox = ref(false);
    const deleteBox = ref(false);

    const errors = ref('');
    const selectedPermssion = ref({})

    const breadcrumbs = ref([
        { title: 'Dashboard', href: '/' },
        { title: 'Permission', href: '/user_access' },
        { title: 'User Access', href: '' },
    ]);


    const permissions = ref ([]);

    const getUserPermission = (e) => {
        loading.value = true;

        userId.value  = e.value.id;
        console.log(e.value.id);



        axios.get(`/user_access/${e.value.id}/edit`)
        .then(res => {
            selectedPermssion.value = {};
            permissions.value = res.data;

            permissions.value.map((perm) => {
                if (!selectedPermssion.value[perm.menu_id]) {
                    selectedPermssion.value[perm.menu_id] = {};
                }
                selectedPermssion.value[perm.menu_id][perm.action_id] = perm.menu_id+"_"+perm.action_id;

            });
        })
        .catch(err => {
            toast.add({ severity: 'error', summary: 'Error', detail: err.response.data.message, life: 3000 });
        })
        .finally(() => loading.value = false);

    }


    // Explicitly type menuAccess as MenuAccessItem[]
    const menuAccess = computed(() => usePage().props.menuAccess);

    onMounted(() => {

        edit.value = menuAccess.value.find(access => access.action_id == 3) ?? null;

    });


    const getPermission = (e) => {
        let checkValue = (e.target.value).split('_');

        if(e.target.checked){
            selectedPermssion.value = {
                ...selectedPermssion.value,
                [checkValue[0]]: {
                    ...selectedPermssion.value[checkValue[0]],
                    [checkValue[1]]: e.target.value
                }
            };

        }else{

            delete selectedPermssion.value[checkValue[0]][checkValue[1]];
        }

    }

    // Get All checkbox
    const getAll = (e) => {

        props.menus.map((perm) => {

            props.actions.map((act) =>{
                if(e.target.checked){
                    viewBox.value = true;
                    addBox.value = true;
                    editBox.value = true;
                    deleteBox.value = true;
                    if (!selectedPermssion.value[perm.id]) {
                        selectedPermssion.value[perm.id] = {};
                    }
                    selectedPermssion.value[perm.id][act.id] = perm.id+"_"+act.id;
                }else{

                    delete selectedPermssion.value[perm.id][act.id];
                    viewBox.value = false;
                    addBox.value = false;
                    editBox.value = false;
                    deleteBox.value = false;
                }

            })

        });
    }

    // Get view checkbox
    const getView = (e) => {
        props.menus.map((perm) => {

            if(e.target.checked){
                if (!selectedPermssion.value[perm.id]) {
                    selectedPermssion.value[perm.id] = {};
                }
                selectedPermssion.value[perm.id][1] = perm.id+"_1";
            }else{

                delete selectedPermssion.value[perm.id][1];
            }
        });
    }

    // Get view checkbox
    const getAdd = (e) => {
        props.menus.map((perm) => {

            if(e.target.checked){
                if (!selectedPermssion.value[perm.id]) {
                    selectedPermssion.value[perm.id] = {};
                }
                selectedPermssion.value[perm.id][2] = perm.id+"_2";
            }else{

                delete selectedPermssion.value[perm.id][2];
            }
        });
    }


    // Get view checkbox
    const getEdit = (e) => {
        props.menus.map((perm) => {

            if(e.target.checked){
                if (!selectedPermssion.value[perm.id]) {
                    selectedPermssion.value[perm.id] = {};
                }
                selectedPermssion.value[perm.id][3] = perm.id+"_3";
            }else{

                delete selectedPermssion.value[perm.id][3];
            }
        });
    }


    // Get view checkbox
    const getDelete = (e) => {
        props.menus.map((perm) => {

            if(e.target.checked){
                if (!selectedPermssion.value[perm.id]) {
                    selectedPermssion.value[perm.id] = {};
                }
                selectedPermssion.value[perm.id][4] = perm.id+"_4";
            }else{

                delete selectedPermssion.value[perm.id][4];
            }
        });
    }

    // Filter Category Parent
    const search = (event) => {
        const query = event.query.toLowerCase();
        items.value = props.staffs.filter(p => p.name.toLowerCase().includes(query));

    }



    const submit = () => {

        if(userId.value == ''){
            errors.value = "Please User name or permissions checkbox"
            toast.add({ severity: 'error', summary: 'Error Message', detail: errors, life: 3000 });
        }



        loading.value = true;

        let formData = {
            user_id: userId.value,
            permission: selectedPermssion.value
        }


        router.post(`/user_access/${userId.value}/edit`, formData, {
            forceFormData: true,
            onSuccess: () => {
                toast.add({ severity: 'success', summary: 'Success Message', detail: 'Role store successfully!', life: 3000 });
                loading.value = false;
            },
            onError: (e) => {
                toast.add({ severity: 'error', summary: 'Error Message', detail: 'Oops! Something wrongs', life: 3000 });
                loading.value = false;

            },
        });
    };




    const goBack = () => {
        return window.history.back()
    }


</script>

<template>

    <Head title="User Access" />

    <AppLayout :breadcrumbs="breadcrumbs" :loading="loading">
        <Toast position="top-right" />
        <div class="w-full mx-auto p-2 flex flex-row place-content-center">
            <div className="w-5/6 mt-4 shadow-lg bg-white rounded-md pb-1">

                <div class="flex justify-between bg-gray-300 px-2 items-center rounded-t-md p-1 ">
                    <div class="text-lg font-semibold">User Permission</div>
                    <div class="flex">
                        <div @click="goBack" class="bg-rose-600 p-2 px-3 rounded-md text-lg flex items-center text-white cursor-pointer">  <Icon icon="line-md:arrow-left" class="mr-2" width="1.5rem" height="1.5rem" />
                            Back
                        </div>
                    </div>

                </div>

                <!-- main content goes here -->

                <div class="px-3 h-[calc(100vh-13rem)] justify-items-center mt-2 pb-4 overflow-auto">

                    <form @submit.prevent="submit" class="w-2/3">
                        <div class="w-full shadow-sm rounded-md border-t p-2 px-4 overflow-auto">
                            <div class="text-red-500">{{ errors }}</div>
                            <div class="w-full flex flex-col mb-4">
                                <label :class="{'text-red-500': errors?.name }" for="dd-city" class="text-md font-semibold w-full content-center"> All Staff:</label>
                                <AutoComplete @change="(e) => getUserPermission(e)" dropdown :suggestions="items" size="small" inputClass="w-full" optionLabel="name" @complete="search" />

                            </div>
                            <div class="w-full mb-1  ">
                                <table class="w-full">
                                    <thead>
                                        <tr class="bg-gray-400 text-white">
                                            <th class="text-left p-2">Menu <input @change="(e) => getAll(e)" type="checkbox" /></th>
                                            <th class="text-left p-2">View <input v-model="viewBox" @change="(e) => getView(e)" type="checkbox" /></th>
                                            <th class="text-left p-2">Add <input v-model="addBox" @change="(e) => getAdd(e)" type="checkbox" /></th>
                                            <th class="text-left p-2">Edit <input v-model="editBox" @change="(e) => getEdit(e)" type="checkbox" /></th>
                                            <th class="text-left p-2">Delete <input v-model="deleteBox" @change="(e) => getDelete(e)" type="checkbox" /></th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr class="even:bg-gray-100 text-black" v-for="menu in menus">
                                            <td class="text-left p-2">{{ menu.title }} </td>
                                            <td class="text-left p-2" v-for="action in actions">
                                                <input @change="(e) => getPermission(e)" v-if="selectedPermssion?.[menu.id]?.[action?.id] != undefined" checked :name="action.name" type="checkbox" :value="`${[menu?.id]+'_'+[action?.id]}`" />
                                                <input @change="(e) => getPermission(e)" v-else type="checkbox" :name="action.name" :value="`${[menu?.id]+'_'+[action?.id]}`" />


                                            </td>
                                        </tr>


                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="w-full mb-1 mt-2 px-4 flex justify-center">
                    <div class="w-2/3 justify-items-end grid grid-flow-col">

                        <div class="text-red-500">{{ errors }}</div>
                        <button @click="submit" v-if="edit?.action_id > 0 || user?.role_id == 1" class="w-32  bg-sky-500 hover:bg-sky-600 text-md border py-1 px-2 rounded-md font-semibold text-white">
                            Update
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

