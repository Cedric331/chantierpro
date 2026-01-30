<script setup lang="ts">
import SectionHeader from '@/components/SectionHeader.vue';
import StatCard from '@/components/StatCard.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

type Subscription = {
    stripe_status?: string;
    trial_ends_at?: string | null;
    ends_at?: string | null;
};

type UsageSummary = {
    feature_key: string;
    days_used: number;
    last_used_at?: string | null;
};

defineProps<{
    account?: {
        name: string;
        trial_ends_at?: string | null;
    } | null;
    subscription?: Subscription | null;
    usageSummary?: UsageSummary[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Abonnement', href: '/billing' },
];
</script>

<template>
    <Head title="Abonnement" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <SectionHeader title="Gestion de l'abonnement" />

            <div class="rounded-xl border bg-card p-6">
                <p class="text-base font-semibold">Plan Hub Chantier</p>
                <p class="mt-2 text-sm text-muted-foreground">
                    Abonnement mensuel · 12,99 € / mois
                </p>
                <div class="mt-4 text-sm text-muted-foreground">
                    <p v-if="subscription?.stripe_status">
                        Statut: {{ subscription.stripe_status }}
                    </p>
                    <p v-else>
                        Aucune souscription active.
                    </p>
                    <p v-if="account?.trial_ends_at">
                        Période d'essai jusqu'au {{ account.trial_ends_at }}
                    </p>
                </div>
                <button
                    type="button"
                    class="mt-6 rounded-lg bg-foreground px-4 py-2 text-sm font-semibold text-background"
                >
                    Gérer l'abonnement
                </button>
            </div>

            <div class="rounded-xl border bg-card p-6">
                <SectionHeader title="Offre Promoteur Pro" />
                <p class="mt-2 text-sm text-muted-foreground">
                    Centralisez le reporting, les budgets et le planning multi-projets pour vos opérations.
                </p>
                <div class="mt-4 grid gap-4 md:grid-cols-3">
                    <div class="rounded-lg border p-4 text-sm">
                        <p class="font-semibold">Portefeuille multi-projets</p>
                        <p class="text-xs text-muted-foreground">Vision globale de l'avancement et des alertes.</p>
                    </div>
                    <div class="rounded-lg border p-4 text-sm">
                        <p class="font-semibold">Reporting automatisé</p>
                        <p class="text-xs text-muted-foreground">Rapports PDF/CSV prêts à partager.</p>
                    </div>
                    <div class="rounded-lg border p-4 text-sm">
                        <p class="font-semibold">Budget & engagements</p>
                        <p class="text-xs text-muted-foreground">Suivi des écarts et alertes de dépassement.</p>
                    </div>
                </div>
                <button
                    type="button"
                    class="mt-6 rounded-lg bg-foreground px-4 py-2 text-sm font-semibold text-background"
                >
                    Passer au plan Promoteur
                </button>
            </div>

            <div class="rounded-xl border bg-card p-6">
                <SectionHeader title="Adoption des fonctions" />
                <div v-if="usageSummary?.length" class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <StatCard
                        v-for="usage in usageSummary"
                        :key="usage.feature_key"
                        :title="usage.feature_key"
                        :value="`${usage.days_used} j`"
                        description="Utilisé sur 30 jours"
                    />
                </div>
                <p v-else class="mt-2 text-sm text-muted-foreground">
                    Utilisez les nouvelles fonctions pour voir les indicateurs d'adoption.
                </p>
            </div>
        </div>
    </AppLayout>
</template>

