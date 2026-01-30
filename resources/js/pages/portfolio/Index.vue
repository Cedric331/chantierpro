<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import SectionHeader from '@/components/SectionHeader.vue';
import StatCard from '@/components/StatCard.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import EmptyState from '@/components/EmptyState.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';

type PortfolioStats = {
    projectCount: number;
    averageProgress: number;
    totalBudget: number;
    totalEstimated: number;
    totalCommitted: number;
    totalActual: number;
    totalVariation: number;
    delayedProjects: number;
};

type Project = {
    id: number;
    name: string;
    client_name: string;
    city: string;
    status: string;
    budget: number;
    progress: number;
    start_date?: string | null;
    end_date?: string | null;
    estimated_cost_sum?: number | string | null;
    committed_cost_sum?: number | string | null;
    actual_cost_sum?: number | string | null;
    variation_amount_sum?: number | string | null;
    open_incidents_count: number;
    pending_validations_count: number;
    upcoming_milestones_count: number;
};

const props = defineProps<{
    stats: PortfolioStats;
    projects: Project[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Portefeuille', href: '/portfolio' },
];

const formatCurrency = (value: number, decimals = 0) =>
    new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    })
    .format(value ?? 0)
    .replace(/\u202F/g, '\u00A0');

const normalizeAmount = (value?: number | string | null) =>
    Number(value ?? 0);

const statusLabel = (status: string) =>
    status === 'delayed'
        ? 'En retard'
        : status === 'completed'
            ? 'Terminé'
            : status === 'in_progress'
                ? 'En cours'
                : status === 'cancelled'
                    ? 'Annulé'
                    : 'Préparation';

const statusTone = (status: string) =>
    status === 'delayed'
        ? 'danger'
        : status === 'completed'
            ? 'success'
            : status === 'in_progress'
                ? 'info'
                : status === 'cancelled'
                    ? 'danger'
                    : 'warning';
</script>

<template>
    <Head title="Portefeuille" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <SectionHeader title="Portefeuille promoteur" />

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <StatCard
                    title="Chantiers suivis"
                    :value="stats.projectCount"
                    description="Portefeuille actif"
                />
                <StatCard
                    title="Progression moyenne"
                    :value="`${stats.averageProgress}%`"
                    description="Sur l'ensemble"
                />
                <StatCard
                    title="Chantiers en retard"
                    :value="stats.delayedProjects"
                    description="À sécuriser"
                    tone="danger"
                />
                <StatCard
                    title="Budget total"
                    :value="formatCurrency(stats.totalBudget)"
                    description="Budget initial"
                />
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <StatCard
                    title="Estimé cumulé"
                    :value="formatCurrency(stats.totalEstimated)"
                    description="Lots budgétés"
                />
                <StatCard
                    title="Engagé"
                    :value="formatCurrency(stats.totalCommitted)"
                    description="Commandes en cours"
                />
                <StatCard
                    title="Réalisé"
                    :value="formatCurrency(stats.totalActual)"
                    description="Dépenses facturées"
                />
                <StatCard
                    title="Variations"
                    :value="formatCurrency(stats.totalVariation)"
                    description="Avenants"
                    :tone="stats.totalVariation > 0 ? 'warning' : 'success'"
                />
            </div>

            <div class="rounded-xl border bg-card p-4">
                <SectionHeader title="Suivi multi-projets" />
                <div v-if="projects.length" class="mt-4 space-y-4">
                    <div
                        v-for="project in projects"
                        :key="project.id"
                        class="rounded-lg border p-4"
                    >
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-base font-semibold">{{ project.name }}</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ project.client_name }} · {{ project.city }}
                                </p>
                            </div>
                            <StatusBadge :label="statusLabel(project.status)" :tone="statusTone(project.status)" />
                        </div>
                        <div class="mt-3 grid gap-3 md:grid-cols-4">
                            <div class="text-sm">
                                <p class="text-xs text-muted-foreground">Avancement</p>
                                <p class="font-semibold">{{ project.progress }}%</p>
                                <div class="mt-2 h-2 w-full rounded-full bg-muted">
                                    <div
                                        class="h-2 rounded-full bg-foreground/80"
                                        :style="{ width: `${project.progress}%` }"
                                    />
                                </div>
                            </div>
                            <div class="text-sm">
                                <p class="text-xs text-muted-foreground">Budget initial</p>
                                <p class="font-semibold">{{ formatCurrency(project.budget) }}</p>
                                <p class="text-xs text-muted-foreground">
                                    Estimé: {{ formatCurrency(normalizeAmount(project.estimated_cost_sum)) }}
                                </p>
                            </div>
                            <div class="text-sm">
                                <p class="text-xs text-muted-foreground">Engagé / Réalisé</p>
                                <p class="font-semibold">
                                    {{ formatCurrency(normalizeAmount(project.committed_cost_sum)) }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Réalisé: {{ formatCurrency(normalizeAmount(project.actual_cost_sum)) }}
                                </p>
                            </div>
                            <div class="text-sm">
                                <p class="text-xs text-muted-foreground">Alertes</p>
                                <p class="font-semibold">
                                    {{ project.open_incidents_count }} incident(s)
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ project.pending_validations_count }} validation(s) ·
                                    {{ project.upcoming_milestones_count }} jalon(s) proche(s)
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <EmptyState
                    v-else
                    title="Aucun chantier"
                    description="Créez des chantiers pour alimenter le portefeuille."
                    class="mt-4"
                />
            </div>
        </div>
    </AppLayout>
</template>



