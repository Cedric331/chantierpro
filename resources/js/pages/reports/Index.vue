<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import SectionHeader from '@/components/SectionHeader.vue';
import EmptyState from '@/components/EmptyState.vue';
import StatCard from '@/components/StatCard.vue';
import { Head, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';

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
];

const filters = ref({
    project: props.filters.project ?? '',
    from: props.filters.from ?? '',
    to: props.filters.to ?? '',
});

const onFilterChange = () => {
    router.get('/reports', filters.value, { preserveState: true, replace: true });
};

const queryString = computed(() => {
    const params = new URLSearchParams();
    if (filters.value.project) params.set('project', filters.value.project);
    if (filters.value.from) params.set('from', filters.value.from);
    if (filters.value.to) params.set('to', filters.value.to);
    const serialized = params.toString();
    return serialized ? `?${serialized}` : '';
});

const projectName = (projectId: number) =>
    props.projects.find((project) => project.id === projectId)?.name ?? 'Chantier';
</script>

<template>
    <Head title="Reporting" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <SectionHeader title="Reporting chantier" />
                <div class="flex flex-wrap gap-2">
                    <Button type="button" variant="secondary" :href="`/reports/print${queryString}`" as="a">
                        Vue PDF
                    </Button>
                    <Button type="button" :href="`/reports/export${queryString}`" as="a">
                        Export CSV
                    </Button>
                </div>
            </div>

            <div class="grid gap-4 rounded-xl border bg-card p-4 md:grid-cols-3">
                <div>
                    <Label for="project" class="text-xs font-semibold text-muted-foreground">Chantier</Label>
                    <select
                        id="project"
                        v-model="filters.project"
                        class="mt-2 w-full rounded-lg border px-3 py-2 text-sm"
                        @change="onFilterChange"
                    >
                        <option value="">Tous les chantiers</option>
                        <option v-for="project in projects" :key="project.id" :value="project.id">
                            {{ project.name }}
                        </option>
                    </select>
                </div>
                <div>
                    <Label for="from" class="text-xs font-semibold text-muted-foreground">Du</Label>
                    <input
                        id="from"
                        v-model="filters.from"
                        type="date"
                        class="mt-2 w-full rounded-lg border px-3 py-2 text-sm"
                        @change="onFilterChange"
                    />
                </div>
                <div>
                    <Label for="to" class="text-xs font-semibold text-muted-foreground">Au</Label>
                    <input
                        id="to"
                        v-model="filters.to"
                        type="date"
                        class="mt-2 w-full rounded-lg border px-3 py-2 text-sm"
                        @change="onFilterChange"
                    />
                </div>
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
                    <SectionHeader title="Incidents récents" />
                    <div v-if="incidents.length" class="mt-4 space-y-3">
                        <div v-for="item in incidents" :key="item.id" class="rounded-lg border p-3 text-sm">
                            <p class="font-semibold">{{ item.title }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ projectName(item.project_id) }} · {{ item.status || '—' }}
                            </p>
                        </div>
                    </div>
                    <EmptyState
                        v-else
                        title="Aucun incident"
                        description="Les incidents apparaîtront ici."
                        class="mt-4"
                    />
                </div>

                <div class="rounded-xl border bg-card p-4">
                    <SectionHeader title="Validations récentes" />
                    <div v-if="validations.length" class="mt-4 space-y-3">
                        <div v-for="item in validations" :key="item.id" class="rounded-lg border p-3 text-sm">
                            <p class="font-semibold">{{ item.title }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ projectName(item.project_id) }} · {{ item.status || '—' }}
                            </p>
                        </div>
                    </div>
                    <EmptyState
                        v-else
                        title="Aucune validation"
                        description="Les demandes apparaîtront ici."
                        class="mt-4"
                    />
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-xl border bg-card p-4">
                    <SectionHeader title="Décisions" />
                    <div v-if="decisions.length" class="mt-4 space-y-3">
                        <div v-for="item in decisions" :key="item.id" class="rounded-lg border p-3 text-sm">
                            <p class="font-semibold">{{ item.title }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ projectName(item.project_id) }}
                            </p>
                        </div>
                    </div>
                    <EmptyState
                        v-else
                        title="Aucune décision"
                        description="Les décisions apparaîtront ici."
                        class="mt-4"
                    />
                </div>

                <div class="rounded-xl border bg-card p-4">
                    <SectionHeader title="Photos" />
                    <div v-if="photos.length" class="mt-4 space-y-3">
                        <div v-for="item in photos" :key="item.id" class="rounded-lg border p-3 text-sm">
                            <p class="font-semibold">{{ item.title }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ projectName(item.project_id) }}
                            </p>
                        </div>
                    </div>
                    <EmptyState
                        v-else
                        title="Aucune photo"
                        description="Les photos apparaîtront ici."
                        class="mt-4"
                    />
                </div>

                <div class="rounded-xl border bg-card p-4">
                    <SectionHeader title="Jalons & tâches" />
                    <div v-if="tasks.length || milestones.length" class="mt-4 space-y-3">
                        <div v-for="item in milestones" :key="`m-${item.id}`" class="rounded-lg border p-3 text-sm">
                            <p class="font-semibold">{{ item.title }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ projectName(item.project_id) }} · {{ item.status || '—' }}
                            </p>
                        </div>
                        <div v-for="item in tasks" :key="`t-${item.id}`" class="rounded-lg border p-3 text-sm">
                            <p class="font-semibold">{{ item.title }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ projectName(item.project_id) }} · {{ item.status || '—' }}
                            </p>
                        </div>
                    </div>
                    <EmptyState
                        v-else
                        title="Aucun jalon"
                        description="Les jalons et tâches apparaîtront ici."
                        class="mt-4"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>



