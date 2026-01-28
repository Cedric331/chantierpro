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
import { formatDate, formatStatus, statusTone } from '@/lib/formatters';
import { toast } from '@/lib/toast';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

type Task = {
    id: number;
    title: string;
    status: string;
    assigned_to?: string | null;
    due_date?: string | null;
};

type Project = { id: number; name: string };
type Contractor = { id: number; name: string; role?: string | null };
type Pagination<T> = { data: T[] };

const props = defineProps<{
    filters: Record<string, string | null>;
    tasks: Pagination<Task>;
    projects: Project[];
    contractors: Contractor[];
}>();

const filters = reactive({ ...props.filters });
const createOpen = ref(false);
const editOpen = ref(false);
const deleteOpen = ref(false);
const selectedTask = ref<Task | null>(null);

const form = useForm({
    project_id: '',
    title: '',
    status: 'pending',
    assigned_to: '',
    due_date: '',
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Tâches', href: '/tasks' },
];

const onFilterChange = () => {
    router.get('/tasks', filters, { preserveState: true, replace: true });
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
    form.status = 'pending';
};

const normalizeDateInput = (value?: string | null) => (value ? value.slice(0, 10) : '');

const openCreate = () => {
    resetForm();
    createOpen.value = true;
};

const openEdit = (task: Task) => {
    selectedTask.value = task;
    form.project_id = '';
    form.title = task.title;
    form.status = task.status;
    form.assigned_to = task.assigned_to ?? '';
    form.due_date = normalizeDateInput(task.due_date);
    editOpen.value = true;
};

const openDelete = (task: Task) => {
    selectedTask.value = task;
    deleteOpen.value = true;
};

const submitCreate = () => {
    form.post('/tasks', {
        onSuccess: () => {
            createOpen.value = false;
            resetForm();
            toast.success('Tâche créée');
        },
        onError: () => toast.error('Impossible de créer la tâche'),
    });
};

const submitEdit = () => {
    if (!selectedTask.value) return;
    form.put(`/tasks/${selectedTask.value.id}`, {
        onSuccess: () => {
            editOpen.value = false;
            selectedTask.value = null;
            resetForm();
            toast.success('Tâche mise à jour');
        },
        onError: () => toast.error('Impossible de mettre à jour la tâche'),
    });
};

const submitDelete = () => {
    if (!selectedTask.value) return;
    form.delete(`/tasks/${selectedTask.value.id}`, {
        onSuccess: () => {
            deleteOpen.value = false;
            selectedTask.value = null;
            toast.success('Tâche supprimée');
        },
        onError: () => toast.error('Impossible de supprimer la tâche'),
    });
};
</script>

<template>
    <Head title="Tâches" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <SectionHeader title="Checklist terrain" />
                <Button type="button" @click="openCreate">Nouvelle tâche</Button>
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
                    <label class="text-xs font-semibold text-muted-foreground">Statut</label>
                    <select v-model="filters.status" class="mt-2 w-full rounded-lg border px-3 py-2 text-sm" @change="onFilterChange">
                        <option value="">Tous</option>
                        <option value="pending">À faire</option>
                        <option value="in_progress">En cours</option>
                        <option value="done">Terminé</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-muted-foreground">Recherche</label>
                    <input
                        v-model="filters.search"
                        type="text"
                        class="mt-2 w-full rounded-lg border px-3 py-2 text-sm"
                        placeholder="Titre de la tâche"
                        @change="onFilterChange"
                    />
                </div>
            </div>

            <div v-if="tasks.data.length" class="space-y-3">
                <div
                    v-for="task in tasks.data"
                    :key="task.id"
                    class="flex flex-wrap items-center justify-between gap-4 rounded-xl border bg-card p-4"
                >
                    <div>
                        <div class="flex items-center gap-2">
                            <StatusIcon :status="task.status" />
                            <p class="font-semibold">{{ task.title }}</p>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            {{ task.assigned_to || 'Non assigné' }} · {{ formatDate(task.due_date) }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <StatusBadge
                            :label="formatStatus(task.status)"
                            :tone="statusTone(task.status)"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <Button type="button" variant="secondary" @click="openEdit(task)">Modifier</Button>
                        <Button type="button" variant="destructive" @click="openDelete(task)">Supprimer</Button>
                    </div>
                </div>
            </div>

            <EmptyState
                v-else
                title="Aucune tâche"
                description="Créez des tâches pour organiser le terrain."
            />
        </div>

        <Dialog v-model:open="createOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Nouvelle tâche</DialogTitle>
                    <DialogDescription>Ajoutez une tâche terrain.</DialogDescription>
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
                        <Input id="title" v-model="form.title" placeholder="Pose carrelage" required />
                        <p v-if="form.errors.title" class="text-xs text-red-500">{{ form.errors.title }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="status">Statut</Label>
                        <select id="status" v-model="form.status" class="rounded-lg border px-3 py-2 text-sm">
                            <option value="pending">À faire</option>
                            <option value="in_progress">En cours</option>
                            <option value="done">Terminé</option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="assigned">Responsable</Label>
                        <Input id="assigned" v-model="form.assigned_to" list="contractor-options" placeholder="Mme Dupont" />
                        <datalist id="contractor-options">
                            <option v-for="contractor in props.contractors" :key="contractor.id" :value="contractor.name">
                                {{ contractor.role ? `${contractor.name} · ${contractor.role}` : contractor.name }}
                            </option>
                        </datalist>
                    </div>
                    <div class="grid gap-2">
                        <Label for="due">Date</Label>
                        <Input id="due" v-model="form.due_date" type="date" />
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
                    <DialogTitle>Modifier la tâche</DialogTitle>
                    <DialogDescription>Mettez à jour la tâche.</DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submitEdit">
                    <div class="grid gap-2">
                        <Label for="edit-title">Titre</Label>
                        <Input id="edit-title" v-model="form.title" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-status">Statut</Label>
                        <select id="edit-status" v-model="form.status" class="rounded-lg border px-3 py-2 text-sm">
                            <option value="pending">À faire</option>
                            <option value="in_progress">En cours</option>
                            <option value="done">Terminé</option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-assigned">Responsable</Label>
                        <Input id="edit-assigned" v-model="form.assigned_to" list="contractor-options-edit" />
                        <datalist id="contractor-options-edit">
                            <option v-for="contractor in props.contractors" :key="contractor.id" :value="contractor.name">
                                {{ contractor.role ? `${contractor.name} · ${contractor.role}` : contractor.name }}
                            </option>
                        </datalist>
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-due">Date</Label>
                        <Input id="edit-due" v-model="form.due_date" type="date" />
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
                    <DialogTitle>Supprimer la tâche</DialogTitle>
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

