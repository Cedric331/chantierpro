<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

const props = defineProps<{
    links: PaginationLink[];
}>();

const normalizeLabel = (label: string) => {
    if (label.includes('Previous')) return 'Précédent';
    if (label.includes('Next')) return 'Suivant';
    return label;
};
</script>

<template>
    <nav v-if="links.length > 3" class="mt-6 flex flex-wrap items-center justify-center gap-2">
        <component
            v-for="(link, index) in links"
            :key="`${link.label}-${index}`"
            :is="link.url ? Link : 'span'"
            :href="link.url || undefined"
            class="rounded-lg border px-3 py-1 text-sm transition"
            :class="link.active ? 'bg-foreground text-background' : 'text-foreground hover:bg-muted'"
            :aria-current="link.active ? 'page' : undefined"
            v-html="normalizeLabel(link.label)"
        />
    </nav>
</template>

