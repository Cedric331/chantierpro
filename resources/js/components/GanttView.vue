<script setup lang="ts">
import { computed } from 'vue';

type GanttTask = {
    id: string;
    name: string;
    start: string;
    end: string;
    progress: number;
    status?: string;
    dependencies?: string;
    custom_class?: string;
};

const props = defineProps<{
    tasks: GanttTask[];
    viewMode?: 'Day' | 'Week' | 'Month';
}>();

const emit = defineEmits<{
    (e: 'date-change', task: GanttTask, start: string, end: string): void;
    (e: 'progress-change', task: GanttTask, progress: number): void;
    (e: 'task-click', task: GanttTask): void;
}>();

const parseDate = (value: string) => {
    if (!value) return null;
    const match = value.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (match) {
        const year = Number(match[1]);
        const month = Number(match[2]) - 1;
        const day = Number(match[3]);
        return new Date(year, month, day);
    }
    const date = new Date(value);
    return Number.isNaN(date.getTime()) ? null : date;
};

const parsedTasks = computed(() =>
    props.tasks
        .map((task) => {
            const start = parseDate(task.start);
            const end = parseDate(task.end);
            if (!start || !end) {
                return null;
            }
            const safeEnd = end < start ? start : end;
            return { ...task, startDate: start, endDate: safeEnd };
        })
        .filter((task): task is GanttTask & { startDate: Date; endDate: Date } => Boolean(task)),
);

const range = computed(() => {
    if (!parsedTasks.value.length) return null;
    const min = Math.min(...parsedTasks.value.map((task) => task.startDate.getTime()));
    const max = Math.max(...parsedTasks.value.map((task) => task.endDate.getTime()));
    return { start: new Date(min), end: new Date(max) };
});

const totalDays = computed(() => {
    if (!range.value) return 1;
    const diff = Math.round((range.value.end.getTime() - range.value.start.getTime()) / 86400000);
    return Math.max(diff + 1, 1);
});

const pixelsPerDay = computed(() => {
    switch (props.viewMode) {
        case 'Day':
            return 48;
        case 'Month':
            return 12;
        default:
            return 24;
    }
});

const timelineWidth = computed(() => totalDays.value * pixelsPerDay.value);

const statusColor = (status?: string) => {
    if (status === 'done' || status === 'completed') return '#16a34a';
    if (status === 'in_progress') return '#2563eb';
    if (status === 'blocked' || status === 'delayed') return '#dc2626';
    if (status === 'pending') return '#f59e0b';
    return '#64748b';
};

const barStyle = (task: { startDate: Date; endDate: Date; status?: string }) => {
    if (!range.value) return {};
    const startOffset = Math.round((task.startDate.getTime() - range.value.start.getTime()) / 86400000);
    const duration = Math.round((task.endDate.getTime() - task.startDate.getTime()) / 86400000) + 1;
    const left = startOffset * pixelsPerDay.value;
    const width = duration * pixelsPerDay.value;
    return {
        left: `${left}px`,
        width: `${width}px`,
        backgroundColor: statusColor(task.status),
    };
};

const formatDate = (value: Date) =>
    new Intl.DateTimeFormat('fr-FR', { day: '2-digit', month: '2-digit', year: '2-digit' }).format(value);

const rangeLabels = computed(() => {
    if (!range.value) return null;
    const start = range.value.start;
    const end = range.value.end;
    const middle = new Date((start.getTime() + end.getTime()) / 2);
    return {
        start: formatDate(start),
        middle: formatDate(middle),
        end: formatDate(end),
    };
});
</script>

<template>
    <div class="space-y-3 rounded-xl border bg-card p-4">
        <div v-if="rangeLabels" class="grid items-center gap-4 text-xs text-muted-foreground" :style="{ gridTemplateColumns: '180px 1fr' }">
            <div>Dates</div>
            <div class="relative h-6">
                <span class="absolute left-0">{{ rangeLabels.start }}</span>
                <span class="absolute left-1/2 -translate-x-1/2">{{ rangeLabels.middle }}</span>
                <span class="absolute right-0">{{ rangeLabels.end }}</span>
            </div>
        </div>
        <div
            v-for="task in parsedTasks"
            :key="task.id"
            class="grid items-center gap-4"
            :style="{ gridTemplateColumns: '180px 1fr' }"
        >
            <div>
                <div class="text-sm font-semibold text-foreground truncate" :title="task.name">{{ task.name }}</div>
                <div class="text-xs text-muted-foreground">
                    {{ formatDate(task.startDate) }} → {{ formatDate(task.endDate) }}
                </div>
            </div>
            <div class="overflow-x-auto">
                <div class="relative h-8 rounded bg-muted" :style="{ width: `${timelineWidth}px` }">
                    <button
                        type="button"
                        class="absolute top-1/2 h-4 -translate-y-1/2 rounded text-xs text-white"
                        :style="barStyle(task)"
                        @click="emit('task-click', task)"
                    >
                        <span class="px-2">{{ task.progress }}%</span>
                    </button>
                </div>
            </div>
        </div>
        <p v-if="!parsedTasks.length" class="text-sm text-muted-foreground">Aucune tâche planifiée.</p>
    </div>
</template>

