<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BookOpen, CircleHelp, LayoutGrid, Road, BookOpenCheck, History } from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem, } from '@/components/ui/sidebar';
import { dashboard,} from '@/routes';
import { index as roadSigns } from '@/routes/road-signs';
import { index as practice, history as practiceHistory } from '@/routes/practice';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Study Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Road Signs',
        href: roadSigns(),
        icon: Road,
    },
    {
        title: 'Practice',
        href: practice(),
        icon: BookOpenCheck,
    },
    {
        title: 'Practice History',
        href: practiceHistory(),
        icon: History,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Official Guide Reference',
        href: 'https://www.mowt.go.ug/',
        icon: BookOpen,
    },
    {
        title: 'Road Safety Information',
        href: 'https://www.upf.go.ug/',
        icon: CircleHelp,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
