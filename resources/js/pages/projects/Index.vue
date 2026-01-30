<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue';
import SectionHeader from '@/components/SectionHeader.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import StatusIcon from '@/components/StatusIcon.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import { provideDataContext } from '@/composables/DataContext';
import { useChantierData } from '@/composables/useChantierData';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
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

type Project = {
    id: number;
    name: string;
    client_name: string;
    address: string;
    city: string;
    status: string;
    budget: number;
    progress: number;
    start_date?: string | null;
    end_date?: string | null;
};

type Pagination<T> = {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

const props = defineProps<{
    filters: Record<string, string | null>;
    projects: Pagination<Project>;
}>();

const dataContext = provideDataContext(
    { ...props.filters },
    (props.filters.view as 'grid' | 'list') || 'grid',
);

const { filters, setView, view, isGrid } = useChantierData(dataContext);

const createOpen = ref(false);
const editOpen = ref(false);
const deleteOpen = ref(false);
const selectedProject = ref<Project | null>(null);
const toDateInput = (value?: string | null) => (value ? value.split('T')[0] : '');

const form = useForm({
    name: '',
    client_name: '',
    address: '',
    city: '',
    status: 'preparation',
    budget: '',
    start_date: '',
    end_date: '',
    progress: 0,
});

const statusOptions = [
    { value: '', label: 'Tous les statuts' },
    { value: 'preparation', label: 'Préparation' },
    { value: 'in_progress', label: 'En cours' },
    { value: 'delayed', label: 'En retard' },
    { value: 'completed', label: 'Terminé' },
    { value: 'cancelled', label: 'Annulé' },
];

const onFilterChange = () => {
    router.get('/projects', { ...filters, view }, { preserveState: true, replace: true });
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
    form.status = 'preparation';
};

const openCreate = () => {
    resetForm();
    createOpen.value = true;
};

const openEdit = (project: Project) => {
    selectedProject.value = project;
    form.name = project.name;
    form.client_name = project.client_name;
    form.address = project.address;
    form.city = project.city;
    form.status = project.status;
    form.budget = project.budget ? String(project.budget) : '';
    form.progress = project.progress ?? 0;
    form.start_date = toDateInput(project.start_date);
    form.end_date = toDateInput(project.end_date);
    editOpen.value = true;
};

const openDelete = (project: Project) => {
    selectedProject.value = project;
    deleteOpen.value = true;
};

const submitCreate = () => {
    form.post('/projects', {
        onSuccess: () => {
            createOpen.value = false;
            resetForm();
            toast.success('Chantier créé');
        },
        onError: () => toast.error('Impossible de créer le chantier'),
    });
};

const submitEdit = () => {
    if (!selectedProject.value) return;
    form.put(`/projects/${selectedProject.value.id}`, {
        onSuccess: () => {
            editOpen.value = false;
            selectedProject.value = null;
            resetForm();
            toast.success('Chantier mis à jour');
        },
        onError: () => toast.error('Impossible de mettre à jour le chantier'),
    });
};

const submitDelete = () => {
    if (!selectedProject.value) return;
    form.delete(`/projects/${selectedProject.value.id}`, {
        onSuccess: () => {
            deleteOpen.value = false;
            selectedProject.value = null;
            toast.success('Chantier supprimé');
        },
        onError: () => toast.error('Impossible de supprimer le chantier'),
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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Chantiers',
        href: '/projects',
    },
];

const gridItems = computed(() => props.projects.data);
</script>

<template>
    <Head title="Chantiers" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <SectionHeader title="Gestion des chantiers" />
                <Button type="button" @click="openCreate">Nouveau chantier</Button>
            </div>

            <div class="grid gap-4 rounded-xl border bg-card p-4 md:grid-cols-5">
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-muted-foreground">Recherche</label>
                    <input
                        v-model="filters.search"
                        type="text"
                        class="mt-2 w-full rounded-lg border px-3 py-2 text-sm"
                        placeholder="Nom, client ou adresse"
                        @change="onFilterChange"
                    />
                </div>
                <div>
                    <label class="text-xs font-semibold text-muted-foreground">Ville</label>
                    <input
                        v-model="filters.city"
                        type="text"
                        class="mt-2 w-full rounded-lg border px-3 py-2 text-sm"
                        placeholder="Marseille, Lyon..."
                        @change="onFilterChange"
                    />
                </div>
                <div>
                    <label class="text-xs font-semibold text-muted-foreground">Client</label>
                    <input
                        v-model="filters.client"
                        type="text"
                        class="mt-2 w-full rounded-lg border px-3 py-2 text-sm"
                        placeholder="Nom du client"
                        @change="onFilterChange"
                    />
                </div>
                <div>
                    <label class="text-xs font-semibold text-muted-foreground">Statut</label>
                    <select
                        v-model="filters.status"
                        class="mt-2 w-full rounded-lg border px-3 py-2 text-sm"
                        @change="onFilterChange"
                    >
                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </div>
                <div class="md:col-span-5 flex flex-wrap items-center justify-between gap-2">
                    <div class="text-sm text-muted-foreground">
                        {{ projects.data.length }} chantier(s) affiché(s)
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="rounded-lg border px-3 py-1 text-sm"
                            :class="isGrid ? 'bg-foreground text-background' : ''"
                            @click="
                                setView('grid');
                                onFilterChange();
                            "
                        >
                            Vue grille
                        </button>
                        <button
                            type="button"
                            class="rounded-lg border px-3 py-1 text-sm"
                            :class="!isGrid ? 'bg-foreground text-background' : ''"
                            @click="
                                setView('list');
                                onFilterChange();
                            "
                        >
                            Vue liste
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="gridItems.length && isGrid" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <a
                    v-for="project in gridItems"
                    :key="project.id"
                    :href="`/projects/${project.id}`"
                    class="rounded-xl border bg-card p-4 shadow-sm transition hover:border-foreground/40"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-start gap-2">
                            <StatusIcon :status="project.status" class="mt-1" />
                            <div>
                                <p class="text-base font-semibold">{{ project.name }}</p>
                                <p class="text-sm text-muted-foreground">{{ project.client_name }}</p>
                            </div>
                        </div>
                        <StatusBadge
                            :label="project.status === 'delayed' ? 'En retard' : project.status === 'completed' ? 'Terminé' : project.status === 'cancelled' ? 'Annulé' : project.status === 'in_progress' ? 'En cours' : 'Préparation'"
                            :tone="project.status === 'delayed' ? 'danger' : project.status === 'completed' ? 'success' : project.status === 'cancelled' ? 'danger' : project.status === 'in_progress' ? 'info' : 'warning'"
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
                    <div class="mt-4 flex items-center justify-end gap-2">
                        <Button type="button" variant="secondary" @click.prevent="openEdit(project)">Modifier</Button>
                        <Button type="button" variant="destructive" @click.prevent="openDelete(project)">Supprimer</Button>
                    </div>
                </a>
            </div>

            <div v-else-if="gridItems.length" class="rounded-xl border bg-card">
                <div class="grid grid-cols-[2fr,1fr,1fr,1fr] gap-4 border-b px-4 py-3 text-xs font-semibold text-muted-foreground">
                    <span>Chantier</span>
                    <span>Client</span>
                    <span>Statut</span>
                    <span>Budget</span>
                </div>
                <a
                    v-for="project in gridItems"
                    :key="project.id"
                    :href="`/projects/${project.id}`"
                    class="grid grid-cols-[2fr,1fr,1fr,1fr] items-center gap-4 border-b px-4 py-3 text-sm last:border-b-0 hover:bg-muted/40"
                >
                    <div class="flex items-center gap-2">
                        <StatusIcon :status="project.status" />
                        <div>
                            <p class="font-semibold">{{ project.name }}</p>
                            <p class="text-xs text-muted-foreground">{{ project.city }}</p>
                        </div>
                    </div>
                    <span>{{ project.client_name }}</span>
                    <StatusBadge
                        :label="project.status === 'delayed' ? 'En retard' : project.status === 'completed' ? 'Terminé' : project.status === 'cancelled' ? 'Annulé' : project.status === 'in_progress' ? 'En cours' : 'Préparation'"
                        :tone="project.status === 'delayed' ? 'danger' : project.status === 'completed' ? 'success' : project.status === 'cancelled' ? 'danger' : project.status === 'in_progress' ? 'info' : 'warning'"
                    />
                    <div class="flex items-center justify-between gap-2">
                        <span>{{ formatCurrency(project.budget) }}</span>
                        <div class="flex items-center gap-2">
                            <Button type="button" size="sm" variant="secondary" @click.prevent="openEdit(project)">Modifier</Button>
                            <Button type="button" size="sm" variant="destructive" @click.prevent="openDelete(project)">Supprimer</Button>
                        </div>
                    </div>
                </a>
            </div>

            <PaginationLinks :links="projects.links" />

            <EmptyState
                v-if="gridItems.length === 0"
                title="Aucun chantier trouvé"
                description="Ajustez les filtres ou créez un nouveau chantier."
            />
        </div>

        <Dialog v-model:open="createOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Nouveau chantier</DialogTitle>
                    <DialogDescription>Créez un chantier et suivez sa progression.</DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submitCreate">
                    <div class="grid gap-2">
                        <Label for="name">Nom</Label>
                        <Input id="name" v-model="form.name" placeholder="Villa Méditerranée" required />
                        <p v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="client">Client</Label>
                        <Input id="client" v-model="form.client_name" placeholder="M. Dupont" required />
                        <p v-if="form.errors.client_name" class="text-xs text-red-500">{{ form.errors.client_name }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="address">Adresse</Label>
                        <Input id="address" v-model="form.address" placeholder="45 Avenue du Port" required />
                        <p v-if="form.errors.address" class="text-xs text-red-500">{{ form.errors.address }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="city">Ville</Label>
                        <Input id="city" v-model="form.city" placeholder="Marseille" required />
                        <p v-if="form.errors.city" class="text-xs text-red-500">{{ form.errors.city }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="status">Statut</Label>
                        <select id="status" v-model="form.status" class="rounded-lg border px-3 py-2 text-sm">
                            <option value="preparation">Préparation</option>
                            <option value="in_progress">En cours</option>
                            <option value="delayed">En retard</option>
                            <option value="completed">Terminé</option>
                            <option value="cancelled">Annulé</option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="budget">Budget</Label>
                        <Input id="budget" v-model="form.budget" type="number" min="0" placeholder="350000" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="start_date">Date de début</Label>
                        <Input id="start_date" v-model="form.start_date" type="date" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="end_date">Date de fin</Label>
                        <Input id="end_date" v-model="form.end_date" type="date" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="progress">Progression (%)</Label>
                        <Input id="progress" v-model="form.progress" type="number" min="0" max="100" />
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
                    <DialogTitle>Modifier le chantier</DialogTitle>
                    <DialogDescription>Mettez à jour les informations du chantier.</DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submitEdit">
                    <div class="grid gap-2">
                        <Label for="edit-name">Nom</Label>
                        <Input id="edit-name" v-model="form.name" />
                        <p v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-client">Client</Label>
                        <Input id="edit-client" v-model="form.client_name" />
                        <p v-if="form.errors.client_name" class="text-xs text-red-500">{{ form.errors.client_name }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-address">Adresse</Label>
                        <Input id="edit-address" v-model="form.address" />
                        <p v-if="form.errors.address" class="text-xs text-red-500">{{ form.errors.address }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-city">Ville</Label>
                        <Input id="edit-city" v-model="form.city" />
                        <p v-if="form.errors.city" class="text-xs text-red-500">{{ form.errors.city }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-status">Statut</Label>
                        <select id="edit-status" v-model="form.status" class="rounded-lg border px-3 py-2 text-sm">
                            <option value="preparation">Préparation</option>
                            <option value="in_progress">En cours</option>
                            <option value="delayed">En retard</option>
                            <option value="completed">Terminé</option>
                            <option value="cancelled">Annulé</option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-budget">Budget</Label>
                        <Input id="edit-budget" v-model="form.budget" type="number" min="0" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-start-date">Date de début</Label>
                        <Input id="edit-start-date" v-model="form.start_date" type="date" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-end-date">Date de fin</Label>
                        <Input id="edit-end-date" v-model="form.end_date" type="date" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-progress">Progression (%)</Label>
                        <Input id="edit-progress" v-model="form.progress" type="number" min="0" max="100" />
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
                    <DialogTitle>Supprimer le chantier</DialogTitle>
                    <DialogDescription>
                        Cette action est définitive. Voulez-vous supprimer ce chantier ?
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button type="button" variant="secondary" @click="deleteOpen = false">Annuler</Button>
                    <Button type="button" variant="destructive" @click="submitDelete" :disabled="form.processing">
                        Supprimer
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

