<script setup lang="ts">
import { dashboard, login, register } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref } from 'vue';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);

const currentYear = new Date().getFullYear();
const cookieBannerVisible = ref(false);
const cookieConsentKey = 'chantierpro_cookie_consent';
const canonicalUrl = ref('');
const jsonLdId = 'chantierpro-jsonld';
let jsonLdScript: HTMLScriptElement | null = null;

const setCookieConsent = (value: 'accepted' | 'declined') => {
    localStorage.setItem(cookieConsentKey, value);
    cookieBannerVisible.value = false;
};

const openCookiePreferences = () => {
    cookieBannerVisible.value = true;
};

onMounted(() => {
    const existing = localStorage.getItem(cookieConsentKey);
    cookieBannerVisible.value = !existing;
    canonicalUrl.value = `${window.location.origin}${window.location.pathname}`;

    const jsonLdData = {
        '@context': 'https://schema.org',
        '@type': 'SoftwareApplication',
        name: 'ChantierPro',
        applicationCategory: 'BusinessApplication',
        operatingSystem: 'Web',
        description:
            'ChantierPro centralise la gestion des chantiers : budgets, tâches, documents, incidents, validations et décisions.',
        offers: {
            '@type': 'Offer',
            price: '12.99',
            priceCurrency: 'EUR',
        },
    };

    const existingScript = document.getElementById(jsonLdId);
    if (existingScript) {
        existingScript.textContent = JSON.stringify(jsonLdData);
        jsonLdScript = existingScript as HTMLScriptElement;
        return;
    }

    jsonLdScript = document.createElement('script');
    jsonLdScript.id = jsonLdId;
    jsonLdScript.type = 'application/ld+json';
    jsonLdScript.text = JSON.stringify(jsonLdData);
    document.head.appendChild(jsonLdScript);
});

onBeforeUnmount(() => {
    jsonLdScript?.remove();
});
</script>

<template>
    <Head title="ChantierPro — Pilotez vos chantiers en temps réel">
        <meta
            name="description"
            content="ChantierPro centralise le suivi des chantiers, tâches, documents, incidents, validations et décisions. Un cockpit moderne pour tenir délais, budget et qualité sur le terrain."
        />
        <meta name="robots" content="index, follow" />
        <meta name="author" content="ChantierPro" />
        <meta property="og:type" content="website" />
        <meta property="og:site_name" content="ChantierPro" />
        <meta property="og:title" content="ChantierPro — Pilotez vos chantiers en temps réel" />
        <meta
            property="og:description"
            content="Suivi complet des chantiers : budgets, tâches, documents, incidents, validations et décisions. Une vue claire pour agir vite et bien."
        />
        <meta property="og:url" :content="canonicalUrl" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="ChantierPro — Pilotez vos chantiers en temps réel" />
        <meta
            name="twitter:description"
            content="Centralisez vos chantiers, tâches et validations. Décidez plus vite avec un tableau de bord clair et un suivi terrain structuré."
        />
        <link rel="canonical" :href="canonicalUrl" />
    </Head>

    <div class="min-h-screen bg-[#0a0f1f] text-white">
        <div class="relative overflow-hidden">
            <div
                class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(59,130,246,0.25),_transparent_55%)]"
            ></div>
            <div class="mx-auto flex min-h-[720px] max-w-6xl flex-col px-6 pb-20 pt-8 lg:px-10">
                <header class="flex items-center justify-between gap-6">
                    <div class="flex items-center gap-3 text-lg font-semibold tracking-wide">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-base font-bold"
                        >
                            CP
                        </div>
                        ChantierPro
                    </div>
                    <nav class="hidden items-center gap-6 text-sm text-white/70 lg:flex">
                        <a href="#features" class="transition hover:text-white">Fonctionnalités</a>
                        <a href="#workflows" class="transition hover:text-white">Workflow</a>
                        <a href="#rgpd" class="transition hover:text-white">RGPD</a>
                        <a href="#pricing" class="transition hover:text-white">Offre</a>
                    </nav>
                    <div class="flex items-center gap-2">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="dashboard()"
                            class="rounded-full border border-white/20 px-4 py-2 text-sm font-semibold text-white/90 transition hover:border-white/60"
                        >
                            Accéder au tableau de bord
                        </Link>
                        <template v-else>
                            <Link
                                :href="login()"
                                class="rounded-full border border-white/10 px-4 py-2 text-sm text-white/80 transition hover:border-white/40"
                            >
                                Se connecter
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-[#0a0f1f] transition hover:bg-white/90"
                            >
                                Démarrer
                            </Link>
                        </template>
                    </div>
                </header>

                <div class="mt-16 grid gap-12 lg:grid-cols-[1.2fr_0.8fr] lg:items-center">
                    <div>
                        <p class="text-sm uppercase tracking-[0.3em] text-white/60">Gestion des chantiers</p>
                        <h1 class="mt-4 text-4xl font-semibold leading-tight text-white lg:text-5xl">
                            La plateforme qui relie planning, terrain et décisions pour vos chantiers.
                        </h1>
                        <p class="mt-6 text-base text-white/70 lg:text-lg">
                            ChantierPro centralise le suivi des chantiers, tâches, incidents, documents,
                            validations et décisions. Un cockpit clair pour agir vite, sécuriser le budget et tenir les délais.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="rounded-full bg-blue-500 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-400"
                            >
                                Créer un compte
                            </Link>
                            <a
                                href="#features"
                                class="rounded-full border border-white/20 px-5 py-2.5 text-sm font-semibold text-white/90 transition hover:border-white/50"
                            >
                                Explorer les modules
                            </a>
                        </div>
                        <div class="mt-10 flex flex-wrap gap-6 text-sm text-white/60">
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                Suivi budget et avancement
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-blue-400"></span>
                                Workflow validations & décisions
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-purple-400"></span>
                                Pilotage incidents terrain
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl shadow-blue-500/10">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-white">Tableau de bord</p>
                            <span class="rounded-full bg-emerald-500/20 px-3 py-1 text-xs text-emerald-200">
                                En temps réel
                            </span>
                        </div>
                        <div class="mt-6 grid gap-4">
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                <p class="text-xs uppercase tracking-wide text-white/60">Chantiers actifs</p>
                                <p class="mt-2 text-2xl font-semibold">Vue consolidée</p>
                                <p class="mt-1 text-xs text-white/50">Budget, retard, validations, incidents</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                <p class="text-xs uppercase tracking-wide text-white/60">Avancement moyen</p>
                                <div class="mt-3 h-2 w-full rounded-full bg-white/10">
                                    <div class="h-2 w-2/3 rounded-full bg-blue-400"></div>
                                </div>
                                <p class="mt-2 text-xs text-white/50">Visualisez la progression par chantier</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                <p class="text-xs uppercase tracking-wide text-white/60">Décisions & validations</p>
                                <p class="mt-2 text-sm text-white/80">
                                    Centralisez les arbitrages pour sécuriser la suite du chantier.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <section id="features" class="bg-white text-[#0a0f1f]">
            <div class="mx-auto max-w-6xl px-6 py-20 lg:px-10">
                <div class="grid gap-10 lg:grid-cols-[1fr_1.2fr]">
                    <div>
                        <p class="text-sm uppercase tracking-[0.3em] text-blue-600">Fonctionnalités</p>
                        <h2 class="mt-4 text-3xl font-semibold">Tout le chantier dans un seul espace.</h2>
                        <p class="mt-4 text-base text-[#3f4555]">
                            Chaque module est pensé pour les équipes terrain, la maîtrise d'œuvre et
                            les décideurs. Rien de superflu, juste les informations qui font avancer le chantier.
                        </p>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
                            <p class="text-sm font-semibold">Chantiers & avancement</p>
                            <p class="mt-2 text-sm text-[#5b6273]">
                                Suivez le statut, le budget et la progression chantier par chantier.
                            </p>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
                            <p class="text-sm font-semibold">Checklist terrain</p>
                            <p class="mt-2 text-sm text-[#5b6273]">
                                Planifiez, assignez et suivez les tâches opérationnelles.
                            </p>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
                            <p class="text-sm font-semibold">Incidents & risques</p>
                            <p class="mt-2 text-sm text-[#5b6273]">
                                Centralisez les incidents et mesurez leur impact délai/budget.
                            </p>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
                            <p class="text-sm font-semibold">Documents & versions</p>
                            <p class="mt-2 text-sm text-[#5b6273]">
                                Classez les pièces du chantier, suivez leur statut et les versions.
                            </p>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
                            <p class="text-sm font-semibold">Validations</p>
                            <p class="mt-2 text-sm text-[#5b6273]">
                                Structurez les demandes d'accord et suivez les décisions attendues.
                            </p>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
                            <p class="text-sm font-semibold">Décisions</p>
                            <p class="mt-2 text-sm text-[#5b6273]">
                                Gardez un journal clair des arbitrages pour chaque projet.
                            </p>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
                            <p class="text-sm font-semibold">Intervenants</p>
                            <p class="mt-2 text-sm text-[#5b6273]">
                                Centralisez les acteurs et leurs coordonnées sur chaque chantier.
                            </p>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
                            <p class="text-sm font-semibold">Tableau de bord</p>
                            <p class="mt-2 text-sm text-[#5b6273]">
                                Vue synthétique : budgets, retards, incidents et validations.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="workflows" class="bg-[#f4f6fb] text-[#0a0f1f]">
            <div class="mx-auto max-w-6xl px-6 py-20 lg:px-10">
                <div class="grid gap-12 lg:grid-cols-[1.1fr_0.9fr]">
                    <div class="space-y-6">
                        <p class="text-sm uppercase tracking-[0.3em] text-blue-600">Workflow</p>
                        <h2 class="text-3xl font-semibold">Une méthode fluide, du brief à la réception.</h2>
                        <p class="text-base text-[#3f4555]">
                            ChantierPro propose un fil conducteur simple : créer, suivre, arbitrer.
                            Chaque étape garde l'équipe alignée et les décisions documentées.
                        </p>
                        <div class="space-y-4">
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <p class="text-sm font-semibold">1. Démarrez votre chantier</p>
                                <p class="mt-2 text-sm text-[#5b6273]">
                                    Ajoutez les informations clés, le budget et les intervenants.
                                </p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <p class="text-sm font-semibold">2. Organisez le terrain</p>
                                <p class="mt-2 text-sm text-[#5b6273]">
                                    Tâches, documents et validations structurent l'exécution quotidienne.
                                </p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <p class="text-sm font-semibold">3. Décidez, sécurisez</p>
                                <p class="mt-2 text-sm text-[#5b6273]">
                                    Incidents et décisions sont tracés pour protéger qualité et délais.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl">
                        <p class="text-sm font-semibold">Exemple de pilotage</p>
                        <div class="mt-5 grid gap-4">
                            <div class="rounded-xl border border-slate-200 p-4">
                                <p class="text-xs uppercase tracking-wide text-slate-500">Statut chantier</p>
                                <p class="mt-2 text-lg font-semibold">En cours · 68% d'avancement</p>
                                <p class="mt-2 text-xs text-slate-500">2 validations en attente</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 p-4">
                                <p class="text-xs uppercase tracking-wide text-slate-500">Budget estimé</p>
                                <p class="mt-2 text-lg font-semibold">Suivi en direct</p>
                                <p class="mt-2 text-xs text-slate-500">Consommé vs restant</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 p-4">
                                <p class="text-xs uppercase tracking-wide text-slate-500">Incidents</p>
                                <p class="mt-2 text-lg font-semibold">0 critique · 1 ouvert</p>
                                <p class="mt-2 text-xs text-slate-500">Impact estimé documenté</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="rgpd" class="bg-white text-[#0a0f1f]">
            <div class="mx-auto max-w-6xl px-6 py-20 lg:px-10">
                <div class="grid gap-10 lg:grid-cols-[1fr_1fr]">
                    <div>
                        <p class="text-sm uppercase tracking-[0.3em] text-blue-600">RGPD</p>
                        <h2 class="mt-4 text-3xl font-semibold">Transparence et conformité dès le départ.</h2>
                        <p class="mt-4 text-base text-[#3f4555]">
                            ChantierPro applique les principes RGPD essentiels : finalités claires,
                            données minimisées, sécurité et gestion des consentements. Vos équipes restent informées,
                            vos données restent maîtrisées.
                        </p>
                    </div>
                    <div class="grid gap-4">
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
                            <p class="text-sm font-semibold">Consentement cookies</p>
                            <p class="mt-2 text-sm text-[#5b6273]">
                                Gestion explicite des cookies dès l'arrivée sur la plateforme.
                            </p>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
                            <p class="text-sm font-semibold">Transparence</p>
                            <p class="mt-2 text-sm text-[#5b6273]">
                                Informations lisibles sur la collecte et l'usage des données.
                            </p>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
                            <p class="text-sm font-semibold">Protection</p>
                            <p class="mt-2 text-sm text-[#5b6273]">
                                Accès maîtrisés et suivi des actions pour limiter les risques.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="pricing" class="bg-[#0a0f1f] text-white">
            <div class="mx-auto max-w-6xl px-6 py-20 lg:px-10">
                <div class="grid gap-10 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
                    <div>
                        <p class="text-sm uppercase tracking-[0.3em] text-white/60">Offre</p>
                        <h2 class="mt-4 text-3xl font-semibold">Un plan clair pour démarrer vite.</h2>
                        <p class="mt-4 text-base text-white/70">
                            L'essentiel du pilotage chantier dans une formule simple. Activez votre compte,
                            connectez vos équipes et commencez à suivre vos chantiers immédiatement.
                        </p>
                        <ul class="mt-6 space-y-3 text-sm text-white/70">
                            <li>Tableau de bord synthétique</li>
                            <li>Gestion chantiers, tâches, documents, incidents</li>
                            <li>Validations et journal des décisions</li>
                            <li>Suivi des intervenants</li>
                        </ul>
                    </div>
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-8">
                        <p class="text-sm text-white/60">Plan Hub Chantier</p>
                        <div class="mt-4 flex items-end gap-2">
                            <span class="text-4xl font-semibold">12,99 €</span>
                            <span class="text-sm text-white/60">/ mois</span>
                        </div>
                        <p class="mt-3 text-sm text-white/60">Abonnement mensuel sans engagement.</p>
                        <div class="mt-6">
                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="block rounded-full bg-white px-5 py-3 text-center text-sm font-semibold text-[#0a0f1f] transition hover:bg-white/90"
                            >
                                Activer le plan
                            </Link>
                            <Link
                                v-else
                                :href="login()"
                                class="block rounded-full border border-white/20 px-5 py-3 text-center text-sm font-semibold text-white"
                            >
                                Accéder à mon compte
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer class="border-t border-white/10 bg-[#05070f]">
            <div class="mx-auto max-w-6xl px-6 py-14 text-sm text-white/60 lg:px-10">
                <div class="grid gap-8 md:grid-cols-4">
                    <div>
                        <p class="text-base font-semibold text-white">ChantierPro</p>
                        <p class="mt-3 text-sm text-white/50">
                            Le cockpit moderne pour piloter vos chantiers avec clarté.
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white">Produit</p>
                        <ul class="mt-3 space-y-2">
                            <li><a href="#features" class="hover:text-white">Fonctionnalités</a></li>
                            <li><a href="#workflows" class="hover:text-white">Workflow</a></li>
                            <li><a href="#pricing" class="hover:text-white">Offre</a></li>
                        </ul>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white">Ressources</p>
                        <ul class="mt-3 space-y-2">
                            <li><a href="#rgpd" class="hover:text-white">RGPD</a></li>
                            <li>
                                <button
                                    type="button"
                                    class="text-left hover:text-white"
                                    @click="openCookiePreferences"
                                >
                                    Gestion des cookies
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white">Contact</p>
                        <ul class="mt-3 space-y-2">
                            <li><a href="mailto:contact@chantierpro.fr" class="hover:text-white">contact@chantierpro.fr</a></li>
                            <li class="text-white/40">France · Équipe chantier</li>
                        </ul>
                    </div>
                </div>
                <div class="mt-10 flex flex-wrap items-center justify-between gap-4 border-t border-white/10 pt-6 text-xs text-white/40">
                    <p>© {{ currentYear }} ChantierPro. Tous droits réservés.</p>
                    <div class="flex items-center gap-4">
                        <a href="#rgpd" class="hover:text-white">Politique de confidentialité</a>
                        <a href="#rgpd" class="hover:text-white">Mentions RGPD</a>
                    </div>
                </div>
            </div>
        </footer>

        <div
            v-if="cookieBannerVisible"
            class="fixed bottom-6 left-1/2 z-50 w-[min(1500px,92%)] -translate-x-1/2 rounded-2xl border border-white/10 bg-[#101624] p-5 text-sm text-white shadow-2xl"
        >
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="font-semibold">Respect de votre vie privée</p>
                    <p class="mt-1 text-white/70">
                        Nous utilisons uniquement les cookies nécessaires au bon fonctionnement et à
                        l'amélioration de l'expérience. Vous pouvez accepter ou refuser.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        class="rounded-full border border-white/20 px-4 py-2 text-sm text-white/80 hover:border-white/50"
                        @click="setCookieConsent('declined')"
                    >
                        Refuser
                    </button>
                    <button
                        type="button"
                        class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-[#0a0f1f]"
                        @click="setCookieConsent('accepted')"
                    >
                        Accepter
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

