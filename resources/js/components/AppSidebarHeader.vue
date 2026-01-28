<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Bell } from 'lucide-vue-next';
import { computed } from 'vue';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const page = usePage();
const unreadNotificationsCount = computed(
    () => (page.props.unreadNotificationsCount as number | undefined) ?? 0,
);
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>
        <div class="ml-auto flex items-center gap-2">
            <Link
                href="/notifications"
                class="relative inline-flex h-9 w-9 items-center justify-center rounded-full border bg-card text-foreground shadow-sm"
            >
                <Bell class="h-4 w-4" />
                <span
                    v-if="unreadNotificationsCount > 0"
                    class="absolute -right-1 -top-1 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-foreground px-1 text-[10px] text-background"
                >
                    {{ unreadNotificationsCount }}
                </span>
            </Link>
        </div>
    </header>
</template>
