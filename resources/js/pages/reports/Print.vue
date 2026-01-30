<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import SectionHeader from '@/components/SectionHeader.vue';
import StatCard from '@/components/StatCard.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { onMounted } from 'vue';
import { Button } from '@/components/ui/button';

type Project = { id: number; name: string };

type ReportItem = {
    id: number;
    title: string;
    status?: string;
    created_at?: string;
    decided_at?: string | null;
    due_date?: string | null;
    project_id: number;
};

type ReportSummary = {
    incidents: number;
    validations: number;
    decisions: number;
    photos: number;
    tasks: number;
    milestones: number;
};

const props = defineProps<{
    filters: { project?: string | null; from?: string | null; to?: string | null };
    summary: ReportSummary;
    incidents: ReportItem[];
    validations: ReportItem[];
    decisions: ReportItem[];
    photos: ReportItem[];
    tasks: ReportItem[];
    milestones: ReportItem[];
    projects: Project[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Reporting', href: '/reports' },
    { title: 'PDF', href: '/reports/print' },
];

const projectName = (projectId: number) =>
    props.projects.find((project) => project.id === projectId)?.name ?? 'Chantier';

const periodLabel = () => {
    if (props.filters.from && props.filters.to) return `${props.filters.from} au ${props.filters.to}`;
    if (props.filters.from) return `Depuis ${props.filters.from}`;
    if (props.filters.to) return `Jusqu'au ${props.filters.to}`;
    return 'Toutes dates';
};

onMounted(() => {
    window.setTimeout(() => window.print(), 400);
});
</script>

<template>
    <Head title="Reporting PDF" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <SectionHeader title="Rapport PDF" />
                <Button type="button" variant="secondary" @click="window.print()">Imprimer</Button>
            </div>

            <div class="rounded-xl border bg-card p-4 text-sm">
                <p class="font-semibold">Période</p>
                <p class="text-muted-foreground">{{ periodLabel() }}</p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <StatCard title="Incidents" :value="summary.incidents" description="Signalements" />
                <StatCard title="Validations" :value="summary.validations" description="Demandes" />
                <StatCard title="Décisions" :value="summary.decisions" description="Arbitrages" />
                <StatCard title="Photos" :value="summary.photos" description="Suivi visuel" />
                <StatCard title="Tâches" :value="summary.tasks" description="Planning" />
                <StatCard title="Jalons" :value="summary.milestones" description="Étapes clés" />
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-xl border bg-card p-4">
                    <SectionHeader title="Incidents" />
                    <div class="mt-4 space-y-3 text-sm">
                        <div v-for="item in incidents" :key="item.id" class="rounded-lg border p-3">
                            <p class="font-semibold">{{ item.title }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ projectName(item.project_id) }} · {{ item.status || '—' }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <SectionHeader title="Validations" />
                    <div class="mt-4 space-y-3 text-sm">
                        <div v-for="item in validations" :key="item.id" class="rounded-lg border p-3">
                            <p class="font-semibold">{{ item.title }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ projectName(item.project_id) }} · {{ item.status || '—' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-xl border bg-card p-4">
                    <SectionHeader title="Décisions" />
                    <div class="mt-4 space-y-3 text-sm">
                        <div v-for="item in decisions" :key="item.id" class="rounded-lg border p-3">
                            <p class="font-semibold">{{ item.title }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ projectName(item.project_id) }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <SectionHeader title="Photos" />
                    <div class="mt-4 space-y-3 text-sm">
                        <div v-for="item in photos" :key="item.id" class="rounded-lg border p-3">
                            <p class="font-semibold">{{ item.title }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ projectName(item.project_id) }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <SectionHeader title="Jalons & tâches" />
                    <div class="mt-4 space-y-3 text-sm">
                        <div v-for="item in milestones" :key="`m-${item.id}`" class="rounded-lg border p-3">
                            <p class="font-semibold">{{ item.title }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ projectName(item.project_id) }} · {{ item.status || '—' }}
                            </p>
                        </div>
                        <div v-for="item in tasks" :key="`t-${item.id}`" class="rounded-lg border p-3">
                            <p class="font-semibold">{{ item.title }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ projectName(item.project_id) }} · {{ item.status || '—' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>



