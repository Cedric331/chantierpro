<script setup lang="ts">
import SectionHeader from '@/components/SectionHeader.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

type Subscription = {
    stripe_status?: string;
    trial_ends_at?: string | null;
    ends_at?: string | null;
};

defineProps<{
    account?: {
        name: string;
        trial_ends_at?: string | null;
    } | null;
    subscription?: Subscription | null;
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
        </div>
    </AppLayout>
</template>

