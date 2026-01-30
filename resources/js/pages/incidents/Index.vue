<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue';
import SectionHeader from '@/components/SectionHeader.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import StatusIcon from '@/components/StatusIcon.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatStatus, statusTone } from '@/lib/formatters';
import { toast } from '@/lib/toast';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

type Incident = {
    id: number;
    title: string;
    description?: string | null;
    status: string;
    impact_days: number;
    impact_cost: number;
};

type Project = { id: number; name: string };
type Pagination<T> = {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

const props = defineProps<{
    filters: Record<string, string | null>;
    incidents: Pagination<Incident>;
    projects: Project[];
}>();

const filters = reactive({ ...props.filters });
const createOpen = ref(false);
const editOpen = ref(false);
const deleteOpen = ref(false);
const selectedIncident = ref<Incident | null>(null);

const form = useForm({
    project_id: '',
    title: '',
    description: '',
    status: 'open',
    impact_days: 0,
    impact_cost: 0,
    reported_by: '',
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Incidents', href: '/incidents' },
];

const onFilterChange = () => {
    router.get('/incidents', filters, { preserveState: true, replace: true });
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
    form.status = 'open';
    form.impact_days = 0;
    form.impact_cost = 0;
};

const openCreate = () => {
    resetForm();
    createOpen.value = true;
};

const openEdit = (incident: Incident) => {
    selectedIncident.value = incident;
    form.project_id = '';
    form.title = incident.title;
    form.description = incident.description ?? '';
    form.status = incident.status;
    form.impact_days = incident.impact_days ?? 0;
    form.impact_cost = incident.impact_cost ?? 0;
    form.reported_by = '';
    editOpen.value = true;
};

const openDelete = (incident: Incident) => {
    selectedIncident.value = incident;
    deleteOpen.value = true;
};

const submitCreate = () => {
    form.post('/incidents', {
        onSuccess: () => {
            createOpen.value = false;
            resetForm();
            toast.success('Incident créé');
        },
        onError: () => toast.error('Impossible de créer l’incident'),
    });
};

const submitEdit = () => {
    if (!selectedIncident.value) return;
    form.put(`/incidents/${selectedIncident.value.id}`, {
        onSuccess: () => {
            editOpen.value = false;
            selectedIncident.value = null;
            resetForm();
            toast.success('Incident mis à jour');
        },
        onError: () => toast.error('Impossible de mettre à jour l’incident'),
    });
};

const submitDelete = () => {
    if (!selectedIncident.value) return;
    form.delete(`/incidents/${selectedIncident.value.id}`, {
        onSuccess: () => {
            deleteOpen.value = false;
            selectedIncident.value = null;
            toast.success('Incident supprimé');
        },
        onError: () => toast.error('Impossible de supprimer l’incident'),
    });
};
</script>

<template>
    <Head title="Incidents" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <SectionHeader title="Incidents" />
                <Button type="button" @click="openCreate">Nouvel incident</Button>
            </div>

            <div class="grid gap-4 rounded-xl border bg-card p-4 md:grid-cols-3">
                <div>
                    <label class="text-xs font-semibold text-muted-foreground">Chantier</label>
                    <select v-model="filters.project" class="mt-2 w-full rounded-lg border px-3 py-2 text-sm" @change="onFilterChange">
                        <option value="">Tous les chantiers</option>
                        <option v-for="project in projects" :key="project.id" :value="project.id">
                            {{ project.name }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-muted-foreground">Statut</label>
                    <select v-model="filters.status" class="mt-2 w-full rounded-lg border px-3 py-2 text-sm" @change="onFilterChange">
                        <option value="">Tous</option>
                        <option value="open">Ouvert</option>
                        <option value="resolved">Résolu</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-muted-foreground">Recherche</label>
                    <input
                        v-model="filters.search"
                        type="text"
                        class="mt-2 w-full rounded-lg border px-3 py-2 text-sm"
                        placeholder="Titre"
                        @change="onFilterChange"
                    />
                </div>
            </div>

            <div v-if="incidents.data.length" class="grid gap-4 md:grid-cols-2">
                <div
                    v-for="incident in incidents.data"
                    :key="incident.id"
                    class="rounded-xl border bg-card p-4"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <StatusIcon :status="incident.status" />
                            <p class="font-semibold">{{ incident.title }}</p>
                        </div>
                        <StatusBadge :label="formatStatus(incident.status)" :tone="statusTone(incident.status)" />
                    </div>
                    <p class="mt-2 text-sm text-muted-foreground">
                        {{ incident.description || 'Aucun détail' }}
                    </p>
                    <div class="mt-2 text-xs text-muted-foreground">
                        Impact: {{ incident.impact_days }} jour(s) ·
                        {{ incident.impact_cost.toLocaleString('fr-FR') }} €
                    </div>
                    <div class="mt-4 flex items-center justify-end gap-2">
                        <Button type="button" variant="secondary" @click="openEdit(incident)">Modifier</Button>
                        <Button type="button" variant="destructive" @click="openDelete(incident)">Supprimer</Button>
                    </div>
                </div>
            </div>

            <PaginationLinks :links="incidents.links" />

            <EmptyState
                v-if="incidents.data.length === 0"
                title="Aucun incident"
                description="Signalez un incident pour le suivi."
            />
        </div>

        <Dialog v-model:open="createOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Nouvel incident</DialogTitle>
                    <DialogDescription>Signalez un incident sur un chantier.</DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submitCreate">
                    <div class="grid gap-2">
                        <Label for="project">Chantier</Label>
                        <select id="project" v-model="form.project_id" class="rounded-lg border px-3 py-2 text-sm" required>
                            <option value="">Sélectionner</option>
                            <option v-for="project in projects" :key="project.id" :value="project.id">
                                {{ project.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.project_id" class="text-xs text-red-500">{{ form.errors.project_id }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="title">Titre</Label>
                        <Input id="title" v-model="form.title" placeholder="Retard livraison" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="description">Description</Label>
                        <Input id="description" v-model="form.description" placeholder="Détail de l'incident" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="status">Statut</Label>
                        <select id="status" v-model="form.status" class="rounded-lg border px-3 py-2 text-sm">
                            <option value="open">Ouvert</option>
                            <option value="resolved">Résolu</option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="impact-days">Impact (jours)</Label>
                        <Input id="impact-days" v-model="form.impact_days" type="number" min="0" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="impact-cost">Impact (€)</Label>
                        <Input id="impact-cost" v-model="form.impact_cost" type="number" min="0" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="reported-by">Signalé par</Label>
                        <Input id="reported-by" v-model="form.reported_by" placeholder="Jean Moreau" />
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
                    <DialogTitle>Modifier l'incident</DialogTitle>
                    <DialogDescription>Mettez à jour les informations.</DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submitEdit">
                    <div class="grid gap-2">
                        <Label for="edit-title">Titre</Label>
                        <Input id="edit-title" v-model="form.title" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-description">Description</Label>
                        <Input id="edit-description" v-model="form.description" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-status">Statut</Label>
                        <select id="edit-status" v-model="form.status" class="rounded-lg border px-3 py-2 text-sm">
                            <option value="open">Ouvert</option>
                            <option value="resolved">Résolu</option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-impact-days">Impact (jours)</Label>
                        <Input id="edit-impact-days" v-model="form.impact_days" type="number" min="0" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-impact-cost">Impact (€)</Label>
                        <Input id="edit-impact-cost" v-model="form.impact_cost" type="number" min="0" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-reported-by">Signalé par</Label>
                        <Input id="edit-reported-by" v-model="form.reported_by" />
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
                    <DialogTitle>Supprimer l'incident</DialogTitle>
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

