<script setup lang="ts">
type Props = {
    label: string;
    tone?: 'default' | 'success' | 'warning' | 'danger' | 'info';
};

const props = withDefaults(defineProps<Props>(), {
    tone: 'default',
});

const toneClasses = {
    default: 'bg-muted text-muted-foreground',
    success: 'bg-emerald-100 text-emerald-700',
    warning: 'bg-amber-100 text-amber-700',
    danger: 'bg-red-100 text-red-700',
    info: 'bg-blue-100 text-blue-700',
};
</script>

<template>
    <span
        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
        :class="toneClasses[props.tone]"
    >
        {{ label }}
    </span>
</template>

