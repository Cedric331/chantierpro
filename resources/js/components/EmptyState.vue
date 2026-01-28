<script setup lang="ts">
defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <div class="rounded-xl border border-dashed p-8 text-center">
        <p class="text-sm font-semibold text-foreground">{{ title }}</p>
        <p v-if="description" class="mt-2 text-sm text-muted-foreground">
            {{ description }}
        </p>
    </div>
</template>

