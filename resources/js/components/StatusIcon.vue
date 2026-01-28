<script setup lang="ts">
import { AlertTriangle, CheckCircle2, Clock, Timer } from 'lucide-vue-next';
import { computed } from 'vue';

type Props = {
    status?: string | null;
    class?: string;
};

const props = defineProps<Props>();

const icon = computed(() => {
    switch (props.status) {
        case 'approved':
        case 'completed':
        case 'done':
        case 'resolved':
            return CheckCircle2;
        case 'in_progress':
            return Timer;
        case 'pending':
        case 'preparation':
            return Clock;
        case 'delayed':
        case 'rejected':
        case 'open':
            return AlertTriangle;
        default:
            return Clock;
    }
});

const tone = computed(() => {
    switch (props.status) {
        case 'approved':
        case 'completed':
        case 'done':
        case 'resolved':
            return 'text-emerald-600';
        case 'pending':
        case 'preparation':
            return 'text-amber-600';
        case 'in_progress':
            return 'text-blue-600';
        case 'delayed':
        case 'rejected':
        case 'open':
            return 'text-red-600';
        default:
            return 'text-muted-foreground';
    }
});
</script>

<template>
    <component :is="icon" class="size-4" :class="[tone, props.class]" />
</template>

