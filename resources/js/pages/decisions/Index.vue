<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue';
import SectionHeader from '@/components/SectionHeader.vue';
import StatusIcon from '@/components/StatusIcon.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatDate } from '@/lib/formatters';
import { toast } from '@/lib/toast';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

type Decision = {
    id: number;
    title: string;
    description?: string | null;
    actor_name?: string | null;
    decided_at?: string | null;
};

type Project = { id: number; name: string };
type Pagination<T> = {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

const props = defineProps<{
    filters: Record<string, string | null>;
    decisions: Pagination<Decision>;
    projects: Project[];
}>();

const filters = reactive({ ...props.filters });
const createOpen = ref(false);
const editOpen = ref(false);
const deleteOpen = ref(false);
const selectedDecision = ref<Decision | null>(null);

const form = useForm({
    project_id: '',
    title: '',
    description: '',
    actor_name: '',
    decided_at: '',
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Décisions', href: '/decisions' },
];

const onFilterChange = () => {
    router.get('/decisions', filters, { preserveState: true, replace: true });
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
};

const openCreate = () => {
    resetForm();
    createOpen.value = true;
};

const openEdit = (decision: Decision) => {
    selectedDecision.value = decision;
    form.project_id = '';
    form.title = decision.title;
    form.description = decision.description ?? '';
    form.actor_name = decision.actor_name ?? '';
    form.decided_at = decision.decided_at ?? '';
    editOpen.value = true;
};

const openDelete = (decision: Decision) => {
    selectedDecision.value = decision;
    deleteOpen.value = true;
};

const submitCreate = () => {
    form.post('/decisions', {
        onSuccess: () => {
            createOpen.value = false;
            resetForm();
            toast.success('Décision créée');
        },
        onError: () => toast.error('Impossible de créer la décision'),
    });
};

const submitEdit = () => {
    if (!selectedDecision.value) return;
    form.put(`/decisions/${selectedDecision.value.id}`, {
        onSuccess: () => {
            editOpen.value = false;
            selectedDecision.value = null;
            resetForm();
            toast.success('Décision mise à jour');
        },
        onError: () => toast.error('Impossible de mettre à jour la décision'),
    });
};

const submitDelete = () => {
    if (!selectedDecision.value) return;
    form.delete(`/decisions/${selectedDecision.value.id}`, {
        onSuccess: () => {
            deleteOpen.value = false;
            selectedDecision.value = null;
            toast.success('Décision supprimée');
        },
        onError: () => toast.error('Impossible de supprimer la décision'),
    });
};
</script>

<template>
    <Head title="Décisions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <SectionHeader title="Journal des décisions" />
                <Button type="button" @click="openCreate">Nouvelle décision</Button>
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
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-muted-foreground">Recherche</label>
                    <input
                        v-model="filters.search"
                        type="text"
                        class="mt-2 w-full rounded-lg border px-3 py-2 text-sm"
                        placeholder="Sujet ou décision"
                        @change="onFilterChange"
                    />
                </div>
            </div>

            <div v-if="decisions.data.length" class="space-y-4">
                <div
                    v-for="decision in decisions.data"
                    :key="decision.id"
                    class="rounded-xl border bg-card p-4"
                >
                    <div class="flex items-center gap-2">
                        <StatusIcon status="approved" />
                        <p class="font-semibold">{{ decision.title }}</p>
                    </div>
                    <p class="mt-2 text-sm text-muted-foreground">
                        {{ decision.description || 'Aucun détail' }}
                    </p>
                    <p class="mt-2 text-xs text-muted-foreground">
                        {{ decision.actor_name || 'Équipe projet' }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        {{ decision.decided_at ? formatDate(decision.decided_at) : 'Date à définir' }}
                    </p>
                    <div class="mt-4 flex items-center justify-end gap-2">
                        <Button type="button" variant="secondary" @click="openEdit(decision)">Modifier</Button>
                        <Button type="button" variant="destructive" @click="openDelete(decision)">Supprimer</Button>
                    </div>
                </div>
            </div>

            <PaginationLinks :links="decisions.links" />

            <EmptyState
                v-if="decisions.data.length === 0"
                title="Aucune décision"
                description="Les décisions validées apparaîtront ici."
            />
        </div>

        <Dialog v-model:open="createOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Nouvelle décision</DialogTitle>
                    <DialogDescription>Ajoutez une décision au journal.</DialogDescription>
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
                        <Input id="title" v-model="form.title" placeholder="Plan cuisine validé" required />
                        <p v-if="form.errors.title" class="text-xs text-red-500">{{ form.errors.title }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="description">Description</Label>
                        <Input id="description" v-model="form.description" placeholder="Décision et contexte" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="actor">Décideur</Label>
                        <Input id="actor" v-model="form.actor_name" placeholder="Mme Dupont" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="decided_at">Date décision</Label>
                        <Input id="decided_at" v-model="form.decided_at" type="date" />
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
                    <DialogTitle>Modifier la décision</DialogTitle>
                    <DialogDescription>Mise à jour du journal.</DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submitEdit">
                    <div class="grid gap-2">
                        <Label for="edit-title">Titre</Label>
                        <Input id="edit-title" v-model="form.title" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-description">Description</Label>
                        <Input id="edit-description" v-model="form.description" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-actor">Décideur</Label>
                        <Input id="edit-actor" v-model="form.actor_name" />
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
                    <DialogTitle>Supprimer la décision</DialogTitle>
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

