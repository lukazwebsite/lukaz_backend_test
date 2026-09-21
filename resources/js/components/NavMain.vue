<script setup lang="ts">
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import {
    SidebarGroup,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Icon } from '@iconify/vue';

const page = usePage();

const { items } = defineProps<{ items: NavItem[] }>();

/* page.url carries query string and hash, strip both before comparing */
const currentPath = computed(() => page.url.split(/[?#]/)[0].replace(/\/+$/, '') || '/');

function normalize(href?: string | null) {
    if (!href) return '';
    return href.split(/[?#]/)[0].replace(/\/+$/, '') || '/';
}

/* single source of truth for "is this link the page we are on" */
function isActive(href?: string | null) {
    const path = normalize(href);
    if (!path) return false;
    return currentPath.value === path || currentPath.value.startsWith(path + '/');
}

function hasActiveChild(item: NavItem) {
    return (item.children || []).some((child) => isActive(child.href));
}

/* stable key: menu id when present, title otherwise */
function keyOf(item: NavItem) {
    return String(item.id ?? item.title);
}

/* user overrides: menu key -> open/closed. unset means "follow the url" */
const overrides = ref<Record<string, boolean>>({});

function isOpen(item: NavItem) {
    const override = overrides.value[keyOf(item)];
    return override ?? hasActiveChild(item);
}

function setOpen(item: NavItem, open: boolean) {
    overrides.value = { ...overrides.value, [keyOf(item)]: open };
}
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarMenu>
            <template v-for="item in items" :key="item.id ?? item.title">
                <!-- parent with children: collapsible group -->
                <Collapsible
                    v-if="item.children && item.children.length > 0"
                    as-child
                    :open="isOpen(item)"
                    @update:open="setOpen(item, $event)"
                    class="group/collapsible"
                >
                    <SidebarMenuItem>
                        <CollapsibleTrigger as-child>
                            <SidebarMenuButton :is-active="hasActiveChild(item)" :tooltip="item.title">
                                <Icon :icon="item.icon ?? ''" class="text-lg text-gray-600" />
                                <span>{{ item.title }}</span>
                                <Icon
                                    icon="iconamoon:arrow-down-2-bold"
                                    class="ml-auto transition-transform duration-300 group-data-[state=open]/collapsible:rotate-180"
                                />
                            </SidebarMenuButton>
                        </CollapsibleTrigger>

                        <CollapsibleContent>
                            <SidebarMenuSub>
                                <SidebarMenuSubItem v-for="child in item.children" :key="child.id ?? child.title">
                                    <SidebarMenuSubButton as-child :is-active="isActive(child.href)">
                                        <Link :href="child.href">
                                            <Icon
                                                :icon="child.icon ?? ''"
                                                class="text-lg"
                                                :class="isActive(child.href) ? 'text-green-700' : 'text-gray-600'"
                                            />
                                            <span>{{ child.title }}</span>
                                        </Link>
                                    </SidebarMenuSubButton>
                                </SidebarMenuSubItem>
                            </SidebarMenuSub>
                        </CollapsibleContent>
                    </SidebarMenuItem>
                </Collapsible>

                <!-- leaf: plain link -->
                <SidebarMenuItem v-else>
                    <SidebarMenuButton as-child :is-active="isActive(item.href)" :tooltip="item.title">
                        <Link :href="item.href">
                            <Icon :icon="item.icon ?? ''" class="text-lg text-gray-600" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </template>
        </SidebarMenu>
    </SidebarGroup>
</template>
