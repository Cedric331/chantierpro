<script setup lang="ts">
type Props = {
    title: string;
    actionLabel?: string;
    actionHref?: string;
};

withDefaults(defineProps<Props>(), {
    actionLabel: '',
    actionHref: '',
});
</script>

<template>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-semibold">{{ title }}</h2>
        <a
            v-if="actionLabel && actionHref"
            :href="actionHref"
            class="text-sm font-medium text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors hover:decoration-current"
        >
            {{ actionLabel }}
        </a>
    </div>
</template>

