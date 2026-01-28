<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue';
import SectionHeader from '@/components/SectionHeader.vue';
import StatCard from '@/components/StatCard.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import VueApexCharts from 'vue3-apexcharts';

type DashboardStats = {
    activeProjects: number;
    delayedProjects: number;
    totalBudget: number;
    pendingValidations: number;
    openIncidents: number;
};

type DashboardCharts = {
    averageProgress: number;
    budget: {
        consumed: number;
        remaining: number;
    };
    progressByProject: {
        labels: string[];
        series: number[];
    };
    statusBreakdown: Record<string, number>;
};

type Project = {
    id: number;
    name: string;
    client_name: string;
    address: string;
    city: string;
    status: string;
    budget: number;
    progress: number;
};

type Validation = {
    id: number;
    title: string;
    type: string;
    requested_by?: string | null;
};

type Decision = {
    id: number;
    title: string;
    actor_name?: string | null;
    decided_at?: string | null;
};

const props = defineProps<{
    stats: DashboardStats;
    charts: DashboardCharts;
    recentProjects: Project[];
    urgentValidations: Validation[];
    recentDecisions: Decision[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tableau de bord',
        href: '/dashboard',
    },
];

const formatCurrency = (value: number) =>
    new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
        maximumFractionDigits: 0,
    }).format(value ?? 0);

const progressChartOptions = {
    chart: {
        toolbar: { show: false },
    },
    dataLabels: { enabled: false },
    xaxis: {
        categories: props.charts.progressByProject.labels,
        labels: { rotate: -45 },
    },
    yaxis: {
        max: 100,
    },
};

const budgetChartOptions = {
    labels: ['Consommé', 'Restant'],
    legend: { position: 'bottom' },
};

const statusChartOptions = {
    chart: {
        toolbar: { show: false },
    },
    xaxis: {
        categories: Object.keys(props.charts.statusBreakdown).map((status) =>
            status === 'delayed'
                ? 'En retard'
                : status === 'completed'
                  ? 'Terminé'
                  : status === 'in_progress'
                    ? 'En cours'
                    : 'Préparation',
        ),
    },
    dataLabels: { enabled: false },
};
</script>

<template>
    <Head title="Tableau de bord" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <StatCard
                    title="Chantiers actifs"
                    :value="stats.activeProjects"
                    description="En cours de réalisation"
                />
                <StatCard
                    title="Chantiers en retard"
                    :value="stats.delayedProjects"
                    description="À rattraper"
                    tone="danger"
                />
                <StatCard
                    title="Validations en attente"
                    :value="stats.pendingValidations"
                    description="Décisions à prendre"
                    tone="warning"
                />
                <StatCard
                    title="Incidents ouverts"
                    :value="stats.openIncidents"
                    description="Chantiers en risque"
                    tone="danger"
                />
                <!-- Prix affiché avec espaces pour la lisibilité -->
                <StatCard
                    title="Budget total"
                    :value="stats.totalBudget.toLocaleString('fr-FR', { maximumFractionDigits: 0 })"
                    description="Dépenses estimées"
                />
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-xl border bg-card p-4 shadow-sm">
                    <p class="text-base font-semibold">Avancement moyen</p>
                    <p class="mt-1 text-2xl font-semibold">{{ charts.averageProgress }}%</p>
                    <div class="mt-4 h-2 w-full rounded-full bg-muted">
                        <div
                            class="h-2 rounded-full bg-foreground/80"
                            :style="{ width: `${charts.averageProgress}%` }"
                        />
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-4 shadow-sm lg:col-span-2">
                    <div class="flex items-center justify-between">
                        <p class="text-base font-semibold">Progression par chantier</p>
                        <span class="text-xs text-muted-foreground">%</span>
                    </div>
                    <VueApexCharts
                        v-if="charts.progressByProject.labels.length"
                        type="bar"
                        height="240"
                        :options="progressChartOptions"
                        :series="[{ name: 'Progression', data: charts.progressByProject.series }]"
                    />
                    <EmptyState
                        v-else
                        title="Aucun chantier"
                        description="Créez un chantier pour visualiser la progression."
                    />
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-xl border bg-card p-4 shadow-sm">
                    <p class="text-base font-semibold">Budget consommé</p>
                    <VueApexCharts
                        type="donut"
                        height="240"
                        :options="budgetChartOptions"
                        :series="[charts.budget.consumed, charts.budget.remaining]"
                    />
                    <div class="mt-4 text-xs text-muted-foreground">
                        Consommé: {{ formatCurrency(charts.budget.consumed) }} · Restant:
                        {{ formatCurrency(charts.budget.remaining) }}
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-4 shadow-sm lg:col-span-2">
                    <p class="text-base font-semibold">Répartition des statuts</p>
                    <VueApexCharts
                        type="bar"
                        height="240"
                        :options="statusChartOptions"
                        :series="[{ name: 'Chantiers', data: Object.values(charts.statusBreakdown) }]"
                    />
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <SectionHeader title="Chantiers récents" action-label="Voir tout" action-href="/projects" />
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div
                            v-for="project in recentProjects"
                            :key="project.id"
                            class="rounded-xl border bg-card p-4 shadow-sm"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-base font-semibold">{{ project.name }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ project.client_name }}
                                    </p>
                                </div>
                                <StatusBadge
                                    :label="project.status === 'delayed' ? 'En retard' : project.status === 'completed' ? 'Terminé' : project.status === 'in_progress' ? 'En cours' : 'Préparation'"
                                    :tone="project.status === 'delayed' ? 'danger' : project.status === 'completed' ? 'success' : project.status === 'in_progress' ? 'info' : 'warning'"
                                />
                            </div>
                            <div class="mt-3 text-sm text-muted-foreground">
                                {{ project.address }}, {{ project.city }}
                            </div>
                            <div class="mt-3 flex items-center justify-between text-sm">
                                <span>{{ formatCurrency(project.budget) }}</span>
                                <span class="font-medium">{{ project.progress }}%</span>
                            </div>
                            <div class="mt-2 h-2 w-full rounded-full bg-muted">
                                <div
                                    class="h-2 rounded-full bg-foreground/80"
                                    :style="{ width: `${project.progress}%` }"
                                />
                            </div>
                        </div>
                        <EmptyState
                            v-if="recentProjects.length === 0"
                            title="Aucun chantier récent"
                            description="Créez un chantier pour suivre la progression."
                        />
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-xl border bg-card p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-base font-semibold">Validations urgentes</p>
                            <StatusBadge label="En attente" tone="danger" />
                        </div>
                        <div class="mt-4 space-y-3">
                            <div
                                v-for="validation in urgentValidations"
                                :key="validation.id"
                                class="rounded-lg border border-muted p-3 text-sm"
                            >
                                <p class="font-semibold text-foreground">
                                    {{ validation.title }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ validation.requested_by || 'Équipe chantier' }}
                                </p>
                            </div>
                            <EmptyState
                                v-if="urgentValidations.length === 0"
                                title="Aucune validation urgente"
                                description="Les demandes arriveront ici."
                            />
                        </div>
                        <a
                            href="/validations"
                            class="mt-4 inline-flex w-full items-center justify-center rounded-lg border px-4 py-2 text-sm font-medium"
                        >
                            Voir toutes les validations
                        </a>
                    </div>

                    <div class="rounded-xl border bg-card p-4 shadow-sm">
                        <p class="text-base font-semibold">Dernières décisions</p>
                        <div class="mt-4 space-y-4">
                            <div
                                v-for="decision in recentDecisions"
                                :key="decision.id"
                                class="flex gap-3"
                            >
                                <div class="mt-1 h-2 w-2 rounded-full bg-foreground/70" />
                                <div>
                                    <p class="text-sm font-medium">
                                        {{ decision.title }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ decision.actor_name || 'Équipe projet' }}
                                    </p>
                                </div>
                            </div>
                            <EmptyState
                                v-if="recentDecisions.length === 0"
                                title="Aucune décision récente"
                                description="Les décisions apparaîtront ici."
                            />
                        </div>
                        <a
                            href="/decisions"
                            class="mt-4 inline-flex w-full items-center justify-center rounded-lg border px-4 py-2 text-sm font-medium"
                        >
                            Voir tout l'historique
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
