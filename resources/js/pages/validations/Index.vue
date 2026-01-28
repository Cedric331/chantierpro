<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue';
import SectionHeader from '@/components/SectionHeader.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import StatusIcon from '@/components/StatusIcon.vue';
import AppLayout from '@/layouts/AppLayout.vue';
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

type Validation = {
    id: number;
    title: string;
    type: string;
    status: string;
    requested_by?: string | null;
};

type Project = { id: number; name: string };
type Pagination<T> = { data: T[] };

const props = defineProps<{
    filters: Record<string, string | null>;
    validations: Pagination<Validation>;
    projects: Project[];
}>();

const filters = reactive({ ...props.filters });
const createOpen = ref(false);
const editOpen = ref(false);
const deleteOpen = ref(false);
const selectedValidation = ref<Validation | null>(null);

const form = useForm({
    project_id: '',
    title: '',
    type: '',
    status: 'pending',
    requested_by: '',
    decided_by: '',
    decided_at: '',
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Validations', href: '/validations' },
];

const onFilterChange = () => {
    router.get('/validations', filters, { preserveState: true, replace: true });
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
    form.status = 'pending';
};

const openCreate = () => {
    resetForm();
    createOpen.value = true;
};

const openEdit = (validation: Validation) => {
    selectedValidation.value = validation;
    form.project_id = '';
    form.title = validation.title;
    form.type = validation.type;
    form.status = validation.status;
    form.requested_by = validation.requested_by ?? '';
    form.decided_by = '';
    form.decided_at = '';
    editOpen.value = true;
};

const openDelete = (validation: Validation) => {
    selectedValidation.value = validation;
    deleteOpen.value = true;
};

const submitCreate = () => {
    form.post('/validations', {
        onSuccess: () => {
            createOpen.value = false;
            resetForm();
            toast.success('Validation créée');
        },
        onError: () => toast.error('Impossible de créer la validation'),
    });
};

const submitEdit = () => {
    if (!selectedValidation.value) return;
    form.put(`/validations/${selectedValidation.value.id}`, {
        onSuccess: () => {
            editOpen.value = false;
            selectedValidation.value = null;
            resetForm();
            toast.success('Validation mise à jour');
        },
        onError: () => toast.error('Impossible de mettre à jour la validation'),
    });
};

const submitDelete = () => {
    if (!selectedValidation.value) return;
    form.delete(`/validations/${selectedValidation.value.id}`, {
        onSuccess: () => {
            deleteOpen.value = false;
            selectedValidation.value = null;
            toast.success('Validation supprimée');
        },
        onError: () => toast.error('Impossible de supprimer la validation'),
    });
};
</script>

<template>
    <Head title="Validations" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <SectionHeader title="Validations" />
                <Button type="button" @click="openCreate">Nouvelle validation</Button>
            </div>

            <div class="grid gap-4 rounded-xl border bg-card p-4 md:grid-cols-4">
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
                    <label class="text-xs font-semibold text-muted-foreground">Type</label>
                    <input
                        v-model="filters.type"
                        type="text"
                        class="mt-2 w-full rounded-lg border px-3 py-2 text-sm"
                        placeholder="Plan, matériau..."
                        @change="onFilterChange"
                    />
                </div>
                <div>
                    <label class="text-xs font-semibold text-muted-foreground">Statut</label>
                    <select v-model="filters.status" class="mt-2 w-full rounded-lg border px-3 py-2 text-sm" @change="onFilterChange">
                        <option value="">Tous</option>
                        <option value="pending">En attente</option>
                        <option value="approved">Validé</option>
                        <option value="rejected">Refusé</option>
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

            <div v-if="validations.data.length" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div
                    v-for="validation in validations.data"
                    :key="validation.id"
                    class="rounded-xl border bg-card p-4"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <StatusIcon :status="validation.status" />
                            <p class="font-semibold">{{ validation.title }}</p>
                        </div>
                        <StatusBadge :label="formatStatus(validation.status)" :tone="statusTone(validation.status)" />
                    </div>
                    <p class="mt-2 text-sm text-muted-foreground">
                        {{ validation.type }} · {{ validation.requested_by || 'Équipe chantier' }}
                    </p>
                    <div class="mt-4 flex items-center justify-end gap-2">
                        <Button type="button" variant="secondary" @click="openEdit(validation)">Modifier</Button>
                        <Button type="button" variant="destructive" @click="openDelete(validation)">Supprimer</Button>
                    </div>
                </div>
            </div>

            <EmptyState
                v-else
                title="Aucune validation"
                description="Créez une demande de validation pour démarrer le workflow."
            />
        </div>

        <Dialog v-model:open="createOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Nouvelle validation</DialogTitle>
                    <DialogDescription>Créez une demande de validation.</DialogDescription>
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
                        <Input id="title" v-model="form.title" placeholder="Plan cuisine modifié" required />
                        <p v-if="form.errors.title" class="text-xs text-red-500">{{ form.errors.title }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="type">Type</Label>
                        <Input id="type" v-model="form.type" placeholder="Plan, matériau..." required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="status">Statut</Label>
                        <select id="status" v-model="form.status" class="rounded-lg border px-3 py-2 text-sm">
                            <option value="pending">En attente</option>
                            <option value="approved">Validé</option>
                            <option value="rejected">Refusé</option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="requested_by">Demandé par</Label>
                        <Input id="requested_by" v-model="form.requested_by" placeholder="Jean Moreau" />
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
                    <DialogTitle>Modifier la validation</DialogTitle>
                    <DialogDescription>Mettre à jour la décision.</DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submitEdit">
                    <div class="grid gap-2">
                        <Label for="edit-title">Titre</Label>
                        <Input id="edit-title" v-model="form.title" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-type">Type</Label>
                        <Input id="edit-type" v-model="form.type" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-status">Statut</Label>
                        <select id="edit-status" v-model="form.status" class="rounded-lg border px-3 py-2 text-sm">
                            <option value="pending">En attente</option>
                            <option value="approved">Validé</option>
                            <option value="rejected">Refusé</option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-requested-by">Demandé par</Label>
                        <Input id="edit-requested-by" v-model="form.requested_by" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-decided-by">Décidé par</Label>
                        <Input id="edit-decided-by" v-model="form.decided_by" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-decided-at">Date décision</Label>
                        <Input id="edit-decided-at" v-model="form.decided_at" type="date" />
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
                    <DialogTitle>Supprimer la validation</DialogTitle>
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

