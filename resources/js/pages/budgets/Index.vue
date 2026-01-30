<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import SectionHeader from '@/components/SectionHeader.vue';
import EmptyState from '@/components/EmptyState.vue';
import StatCard from '@/components/StatCard.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { toast } from '@/lib/toast';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

type Project = { id: number; name: string; budget: number };

type BudgetItem = {
    id: number;
    project_id: number;
    name: string;
    category?: string | null;
    estimated_cost: number;
    committed_cost: number;
    actual_cost: number;
    variation_amount: number;
    alerted_at?: string | null;
    notes?: string | null;
};

type BudgetSummary = {
    estimated: number;
    committed: number;
    actual: number;
    variation: number;
};

type Pagination<T> = {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    meta?: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from?: number | null;
        to?: number | null;
    };
};

const props = defineProps<{
    filters: Record<string, string | null>;
    projectSearch?: string;
    summary: BudgetSummary;
    items: Pagination<BudgetItem>;
    projects: Pagination<Project>;
    selectedProject?: Project | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Budget', href: '/budgets' },
];

const filters = ref({
    project: props.filters.project ?? '',
    category: props.filters.category ?? '',
    search: props.filters.search ?? '',
});
const projectSearch = ref(props.projectSearch ?? '');
const projectOptions = ref<Project[]>([]);
const loadingProjects = ref(false);
const onFilterChange = () => {
    router.get('/budgets', filters.value, { preserveState: true, replace: true });
};

const syncProjectOptions = () => {
    const nextPage = props.projects.meta?.current_page ?? 1;
    if (nextPage <= 1) {
        projectOptions.value = [...props.projects.data];
    } else {
        const existingIds = new Set(projectOptions.value.map((item) => item.id));
        projectOptions.value = [
            ...projectOptions.value,
            ...props.projects.data.filter((item) => !existingIds.has(item.id)),
        ];
    }

    if (props.selectedProject && !projectOptions.value.some((item) => item.id === props.selectedProject?.id)) {
        projectOptions.value = [props.selectedProject, ...projectOptions.value];
    }
};

watch(
    () => props.projects.data,
    () => syncProjectOptions(),
    { immediate: true },
);

const fetchProjects = (page = 1) => {
    loadingProjects.value = true;
    router.get(
        '/budgets',
        {
            ...filters.value,
            project_search: projectSearch.value || undefined,
            project_page: page,
        },
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
            only: ['projects', 'projectSearch'],
            onFinish: () => {
                loadingProjects.value = false;
            },
        },
    );
};

const onProjectSearchChange = () => {
    fetchProjects(1);
};

const loadMoreProjects = () => {
    const current = props.projects.meta?.current_page ?? 1;
    fetchProjects(current + 1);
};

const hasMoreProjects = computed(() => {
    const meta = props.projects.meta;
    return meta ? meta.current_page < meta.last_page : false;
});

const projectNameById = computed(() => {
    const map = new Map<number, string>();
    projectOptions.value.forEach((project) => {
        map.set(project.id, project.name);
    });
    return map;
});

const itemsRangeLabel = computed(() => {
    const meta = props.items.meta;
    if (!meta || meta.total === 0) return 'Aucune ligne';
    const from = meta.from ?? 1;
    const to = meta.to ?? props.items.data.length;
    return `Affichage ${from}-${to} sur ${meta.total}`;
});

const createOpen = ref(false);
const editOpen = ref(false);
const deleteOpen = ref(false);
const selectedItem = ref<BudgetItem | null>(null);

const form = useForm({
    project_id: '',
    name: '',
    category: '',
    estimated_cost: '',
    committed_cost: '',
    actual_cost: '',
    variation_amount: '',
    notes: '',
});

const resetForm = () => {
    form.reset();
    form.clearErrors();
};

const openCreate = () => {
    resetForm();
    createOpen.value = true;
};

const openEdit = (item: BudgetItem) => {
    selectedItem.value = item;
    form.project_id = String(item.project_id);
    form.name = item.name;
    form.category = item.category ?? '';
    form.estimated_cost = item.estimated_cost ? String(item.estimated_cost) : '';
    form.committed_cost = item.committed_cost ? String(item.committed_cost) : '';
    form.actual_cost = item.actual_cost ? String(item.actual_cost) : '';
    form.variation_amount = item.variation_amount ? String(item.variation_amount) : '';
    form.notes = item.notes ?? '';
    editOpen.value = true;
};

const openDelete = (item: BudgetItem) => {
    selectedItem.value = item;
    deleteOpen.value = true;
};

const submitCreate = () => {
    form.post('/budgets', {
        onSuccess: () => {
            createOpen.value = false;
            resetForm();
            toast.success('Ligne budgétaire créée');
        },
        onError: () => toast.error('Impossible de créer la ligne'),
    });
};

const submitEdit = () => {
    if (!selectedItem.value) return;
    form.put(`/budgets/${selectedItem.value.id}`, {
        onSuccess: () => {
            editOpen.value = false;
            selectedItem.value = null;
            resetForm();
            toast.success('Ligne budgétaire mise à jour');
        },
        onError: () => toast.error('Impossible de mettre à jour la ligne'),
    });
};

const submitDelete = () => {
    if (!selectedItem.value) return;
    form.delete(`/budgets/${selectedItem.value.id}`, {
        onSuccess: () => {
            deleteOpen.value = false;
            selectedItem.value = null;
            toast.success('Ligne budgétaire supprimée');
        },
        onError: () => toast.error('Impossible de supprimer la ligne'),
    });
};

const formatCurrency = (value: number, decimals = 0) =>
    new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    })
    .format(value ?? 0)
    .replace(/\u202F/g, '\u00A0');

const asNumber = (value: unknown) => Number(value ?? 0);
</script>

<template>
    <Head title="Budget" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <SectionHeader title="Budget & engagements" />
                <Button type="button" @click="openCreate">Nouvelle ligne</Button>
            </div>
            <p class="text-sm text-muted-foreground">
                Suivez les coûts estimés, engagés et réalisés par lot pour détecter rapidement les écarts.
            </p>

            <div class="rounded-xl border bg-card p-4">
                <div class="flex flex-col gap-2">
                    <p class="text-sm font-semibold">Vue d'ensemble</p>
                    <p class="text-xs text-muted-foreground">
                        Synthèse des montants cumulés pour l'ensemble des chantiers visibles.
                    </p>
                </div>
                <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <StatCard title="Estimé" :value="formatCurrency(asNumber(summary.estimated))" description="Lots budgétés" />
                    <StatCard title="Engagé" :value="formatCurrency(asNumber(summary.committed))" description="Commandes" />
                    <StatCard title="Réalisé" :value="formatCurrency(asNumber(summary.actual))" description="Factures" />
                    <StatCard
                        title="Variations"
                        :value="formatCurrency(asNumber(summary.variation))"
                        description="Avenants"
                        :tone="asNumber(summary.variation) > 0 ? 'warning' : 'default'"
                    />
                </div>
            </div>

            <div class="rounded-xl border bg-card p-4">
                <div class="flex flex-col gap-2">
                    <p class="text-sm font-semibold">Filtres</p>
                    <p class="text-xs text-muted-foreground">
                        Affinez l'affichage par chantier, catégorie ou mot-clé.
                    </p>
                </div>
                <div class="mt-4 grid gap-4 md:grid-cols-3">
                    <div>
                        <label class="text-xs font-semibold text-muted-foreground">Chantier</label>
                    <div class="mt-2 grid gap-2">
                        <input
                            v-model="projectSearch"
                            type="text"
                            class="w-full rounded-lg border px-3 py-2 text-sm"
                            placeholder="Rechercher un chantier"
                            @change="onProjectSearchChange"
                        />
                        <select
                            v-model="filters.project"
                            class="w-full rounded-lg border px-3 py-2 text-sm"
                            @change="onFilterChange"
                        >
                            <option value="">Tous les chantiers</option>
                            <option v-for="project in projectOptions" :key="project.id" :value="project.id">
                                {{ project.name }}
                            </option>
                        </select>
                        <Button
                            v-if="hasMoreProjects"
                            type="button"
                            size="sm"
                            variant="outline"
                            :disabled="loadingProjects"
                            @click="loadMoreProjects"
                        >
                            Charger plus
                        </Button>
                    </div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-muted-foreground">Catégorie</label>
                        <input
                            v-model="filters.category"
                            type="text"
                            class="mt-2 w-full rounded-lg border px-3 py-2 text-sm"
                            placeholder="Gros œuvre, second œuvre..."
                            @change="onFilterChange"
                        />
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-muted-foreground">Recherche</label>
                        <input
                            v-model="filters.search"
                            type="text"
                            class="mt-2 w-full rounded-lg border px-3 py-2 text-sm"
                            placeholder="Lot, entreprise..."
                            @change="onFilterChange"
                        />
                    </div>
                </div>
            </div>

            <div v-if="items.data.length" class="rounded-xl border bg-card">
                <div class="border-b px-4 py-3">
                    <p class="text-sm font-semibold">Lignes budgétaires</p>
                    <p class="text-xs text-muted-foreground">
                        Détail par lot avec alertes de dépassement.
                    </p>

                </div>
                <div class="hidden md:grid grid-cols-[2fr_1fr_1fr_1fr_1fr_1fr] gap-4 border-b px-4 py-3 text-xs font-semibold text-muted-foreground">
                    <span>Lot</span>
                    <span>Catégorie</span>
                    <span>Estimé</span>
                    <span>Engagé</span>
                    <span>Réalisé</span>
                    <span>Actions</span>
                </div>
                <div
                    v-for="item in items.data"
                    :key="item.id"
                    class="hidden md:grid grid-cols-[2fr_1fr_1fr_1fr_1fr_1fr] items-center gap-4 border-b px-4 py-3 text-sm last:border-b-0"
                >
                    <div>
                        <p class="font-semibold">{{ item.name }}</p>
                        <p class="text-xs text-muted-foreground">
                            {{ projectNameById.get(item.project_id) || 'Chantier' }}
                        </p>
                        <p v-if="item.alerted_at" class="mt-1 text-xs font-semibold text-red-600">
                            Dépassement détecté
                        </p>
                    </div>
                    <span>{{ item.category || '—' }}</span>
                    <span>{{ formatCurrency(item.estimated_cost) }}</span>
                    <span>{{ formatCurrency(item.committed_cost) }}</span>
                    <span>
                        {{ formatCurrency(item.actual_cost) }}
                        <span v-if="item.variation_amount" class="block text-xs text-muted-foreground">
                            Var: {{ formatCurrency(item.variation_amount) }}
                        </span>
                    </span>
                    <div class="flex flex-wrap items-center gap-2">
                        <Button type="button" size="sm" variant="secondary" @click="openEdit(item)">Modifier</Button>
                        <Button type="button" size="sm" variant="destructive" @click="openDelete(item)">Supprimer</Button>
                    </div>
                </div>

                <div class="md:hidden divide-y">
                    <div v-for="item in items.data" :key="`card-${item.id}`" class="p-4 text-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold">{{ item.name }}</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ projectNameById.get(item.project_id) || 'Chantier' }}
                                </p>
                                <p v-if="item.alerted_at" class="mt-1 text-xs font-semibold text-red-600">
                                    Dépassement détecté
                                </p>
                            </div>
                            <span class="rounded-full bg-muted px-2 py-1 text-xs">
                                {{ item.category || 'Sans catégorie' }}
                            </span>
                        </div>
                        <div class="mt-3 grid gap-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-muted-foreground">Estimé</span>
                                <span class="font-medium">{{ formatCurrency(item.estimated_cost) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-muted-foreground">Engagé</span>
                                <span class="font-medium">{{ formatCurrency(item.committed_cost) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-muted-foreground">Réalisé</span>
                                <span class="font-medium">{{ formatCurrency(item.actual_cost) }}</span>
                            </div>
                            <div v-if="item.variation_amount" class="flex items-center justify-between">
                                <span class="text-xs text-muted-foreground">Variation</span>
                                <span class="font-medium">{{ formatCurrency(item.variation_amount) }}</span>
                            </div>
                        </div>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <Button type="button" size="sm" variant="secondary" @click="openEdit(item)">Modifier</Button>
                            <Button type="button" size="sm" variant="destructive" @click="openDelete(item)">Supprimer</Button>
                        </div>
                    </div>
                </div>
            </div>

            <PaginationLinks :links="items.links" />

            <EmptyState
                v-if="items.data.length === 0"
                title="Aucune ligne budgétaire"
                description="Ajoutez des lots pour suivre les engagements."
            />
        </div>

        <Dialog v-model:open="createOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Nouvelle ligne budgétaire</DialogTitle>
                    <DialogDescription>Suivez les coûts par lot.</DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submitCreate">
                    <div class="grid gap-2">
                        <Label for="project">Chantier</Label>
                        <select id="project" v-model="form.project_id" class="rounded-lg border px-3 py-2 text-sm" required>
                            <option value="">Sélectionner</option>
                            <option v-for="project in projectOptions" :key="project.id" :value="project.id">
                                {{ project.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.project_id" class="text-xs text-red-500">{{ form.errors.project_id }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="name">Nom du lot</Label>
                        <Input id="name" v-model="form.name" placeholder="Gros œuvre" required />
                        <p v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="category">Catégorie</Label>
                        <Input id="category" v-model="form.category" placeholder="Structure" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="estimated">Estimé</Label>
                        <Input id="estimated" v-model="form.estimated_cost" type="number" min="0" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="committed">Engagé</Label>
                        <Input id="committed" v-model="form.committed_cost" type="number" min="0" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="actual">Réalisé</Label>
                        <Input id="actual" v-model="form.actual_cost" type="number" min="0" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="variation">Variation</Label>
                        <Input id="variation" v-model="form.variation_amount" type="number" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="notes">Notes</Label>
                        <Input id="notes" v-model="form.notes" placeholder="Avenant signé le..." />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="secondary" @click="createOpen = false">Annuler</Button>
                        <Button type="submit" :disabled="form.processing">Créer</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="editOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Modifier la ligne</DialogTitle>
                    <DialogDescription>Mettez à jour les engagements.</DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submitEdit">
                    <div class="grid gap-2">
                        <Label for="edit-project">Chantier</Label>
                        <select id="edit-project" v-model="form.project_id" class="rounded-lg border px-3 py-2 text-sm" required>
                            <option value="">Sélectionner</option>
                            <option v-for="project in projectOptions" :key="project.id" :value="project.id">
                                {{ project.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.project_id" class="text-xs text-red-500">{{ form.errors.project_id }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-name">Nom du lot</Label>
                        <Input id="edit-name" v-model="form.name" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-category">Catégorie</Label>
                        <Input id="edit-category" v-model="form.category" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-estimated">Estimé</Label>
                        <Input id="edit-estimated" v-model="form.estimated_cost" type="number" min="0" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-committed">Engagé</Label>
                        <Input id="edit-committed" v-model="form.committed_cost" type="number" min="0" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-actual">Réalisé</Label>
                        <Input id="edit-actual" v-model="form.actual_cost" type="number" min="0" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-variation">Variation</Label>
                        <Input id="edit-variation" v-model="form.variation_amount" type="number" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-notes">Notes</Label>
                        <Input id="edit-notes" v-model="form.notes" />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="secondary" @click="editOpen = false">Annuler</Button>
                        <Button type="submit" :disabled="form.processing">Enregistrer</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="deleteOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Supprimer la ligne</DialogTitle>
                    <DialogDescription>Cette action est définitive.</DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button type="button" variant="secondary" @click="deleteOpen = false">Annuler</Button>
                    <Button type="button" variant="destructive" :disabled="form.processing" @click="submitDelete">
                        Supprimer
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>



