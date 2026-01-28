<script setup lang="ts">
import { X } from 'lucide-vue-next';
import { toasts, removeToast } from '@/lib/toast';
</script>

<template>
    <Teleport to="body">
        <div class="fixed right-4 top-4 z-50 flex w-full max-w-sm flex-col gap-3">
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="flex items-start justify-between gap-3 rounded-xl border bg-card px-4 py-3 shadow-lg"
                :class="{
                    'border-emerald-200 bg-emerald-50 text-emerald-900': toast.level === 'success',
                    'border-red-200 bg-red-50 text-red-900': toast.level === 'error',
                    'border-blue-200 bg-blue-50 text-blue-900': toast.level === 'info',
                }"
            >
                <p class="text-sm font-medium">{{ toast.message }}</p>
                <button type="button" class="text-xs opacity-70 hover:opacity-100" @click="removeToast(toast.id)">
                    <X class="size-4" />
                </button>
            </div>
        </div>
    </Teleport>
</template>

