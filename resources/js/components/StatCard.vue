<script setup lang="ts">
type Props = {
    title: string;
    value: string | number;
    description?: string;
    tone?: 'default' | 'warning' | 'danger';
};

const props = withDefaults(defineProps<Props>(), {
    description: '',
    tone: 'default',
});

const toneClasses = {
    default: 'border-border bg-card text-foreground',
    warning: 'border-yellow-200 bg-yellow-50 text-yellow-900',
    danger: 'border-red-200 bg-red-50 text-red-900',
};
</script>

<template>
    <div class="rounded-xl border p-4 shadow-sm" :class="toneClasses[props.tone]">
        <div class="text-sm font-medium text-muted-foreground">{{ title }}</div>
        <div class="mt-2 text-3xl font-semibold">{{ value }}</div>
        <div v-if="description" class="mt-1 text-sm text-muted-foreground">
            {{ description }}
        </div>
    </div>
</template>

