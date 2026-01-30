<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue';
import SectionHeader from '@/components/SectionHeader.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import StatusIcon from '@/components/StatusIcon.vue';
import DocumentPreviewDialog from '@/components/DocumentPreviewDialog.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
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

type Document = {
    id: number;
    title: string;
    category: string;
    version: string;
    status: string;
    project_id: number;
    media_url?: string | null;
    created_at?: string | null;
};

type Project = { id: number; name: string };
type Pagination<T> = {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

const props = defineProps<{
    filters: Record<string, string | null>;
    documents: Pagination<Document>;
    projects: Project[];
}>();

const filters = reactive({ ...props.filters });
const createOpen = ref(false);
const editOpen = ref(false);
const deleteOpen = ref(false);
const lightboxOpen = ref(false);
const lightboxDocument = ref<Document | null>(null);
const selectedDocument = ref<Document | null>(null);

const form = useForm({
    project_id: '',
    title: '',
    category: '',
    version: 'v1',
    status: 'pending',
    file: null as File | null,
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Documents', href: '/documents' },
];

const onFilterChange = () => {
    router.get('/documents', filters, { preserveState: true, replace: true });
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
    form.version = 'v1';
    form.status = 'pending';
    form.file = null;
};

const openCreate = () => {
    resetForm();
    createOpen.value = true;
};

const openEdit = (document: Document) => {
    selectedDocument.value = document;
    form.project_id = String(document.project_id);
    form.title = document.title;
    form.category = document.category;
    form.version = document.version;
    form.status = document.status;
    form.file = null;
    editOpen.value = true;
};

const openDelete = (document: Document) => {
    selectedDocument.value = document;
    deleteOpen.value = true;
};

const openLightbox = (document: Document) => {
    lightboxDocument.value = document;
    lightboxOpen.value = true;
};

const isImage = (url?: string | null) => {
    if (!url) return false;
    return ['.png', '.jpg', '.jpeg', '.webp', '.gif'].some((ext) => url.toLowerCase().includes(ext));
};

const onFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    form.file = target.files?.[0] ?? null;
};

const submitCreate = () => {
    form.post('/documents', {
        forceFormData: true,
        onSuccess: () => {
            createOpen.value = false;
            resetForm();
            toast.success('Document créé');
        },
        onError: () => toast.error('Impossible de créer le document'),
    });
};

const submitEdit = () => {
    if (!selectedDocument.value) return;
    form.put(`/documents/${selectedDocument.value.id}`, {
        forceFormData: true,
        onSuccess: () => {
            editOpen.value = false;
            selectedDocument.value = null;
            resetForm();
            toast.success('Document mis à jour');
        },
        onError: () => toast.error('Impossible de mettre à jour le document'),
    });
};

const submitDelete = () => {
    if (!selectedDocument.value) return;
    form.delete(`/documents/${selectedDocument.value.id}`, {
        onSuccess: () => {
            deleteOpen.value = false;
            selectedDocument.value = null;
            toast.success('Document supprimé');
        },
        onError: () => toast.error('Impossible de supprimer le document'),
    });
};
</script>

<template>
    <Head title="Documents" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <SectionHeader title="Documents" />
                <Button type="button" @click="openCreate">Nouveau document</Button>
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
                    <label class="text-xs font-semibold text-muted-foreground">Catégorie</label>
                    <input
                        v-model="filters.category"
                        type="text"
                        class="mt-2 w-full rounded-lg border px-3 py-2 text-sm"
                        placeholder="Plans, devis..."
                        @change="onFilterChange"
                    />
                </div>
                <div>
                    <label class="text-xs font-semibold text-muted-foreground">Statut</label>
                    <select v-model="filters.status" class="mt-2 w-full rounded-lg border px-3 py-2 text-sm" @change="onFilterChange">
                        <option value="">Tous les statuts</option>
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
                        placeholder="Titre du document"
                        @change="onFilterChange"
                    />
                </div>
            </div>

            <div v-if="documents.data.length" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div
                    v-for="document in documents.data"
                    :key="document.id"
                    class="rounded-xl border bg-card p-4"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <StatusIcon :status="document.status" />
                            <p class="font-semibold">{{ document.title }}</p>
                        </div>
                        <StatusBadge :label="formatStatus(document.status)" :tone="statusTone(document.status)" />
                    </div>
                    <p class="mt-2 text-sm text-muted-foreground">
                        {{ document.category }} · {{ document.version }}
                    </p>
                    <div class="mt-2 text-xs text-muted-foreground">
                        Ajouté le {{ formatDate(document.created_at) }}
                    </div>
                    <div v-if="isImage(document.media_url)" class="mt-3 overflow-hidden rounded-lg border">
                        <img
                            :src="document.media_url"
                            alt="Aperçu document"
                            class="h-32 w-full object-cover"
                        />
                    </div>
                    <div v-if="document.media_url" class="mt-3 flex items-center gap-3">
                        <button
                            type="button"
                            class="text-sm font-medium text-foreground underline decoration-neutral-300 underline-offset-4"
                            @click="openLightbox(document)"
                        >
                            Prévisualiser
                        </button>
                        <a
                            :href="document.media_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-sm text-muted-foreground underline decoration-neutral-300 underline-offset-4"
                        >
                            Télécharger
                        </a>
                    </div>
                    <div class="mt-4 flex items-center justify-end gap-2">
                        <Button type="button" variant="secondary" @click="openEdit(document)">Modifier</Button>
                        <Button type="button" variant="destructive" @click="openDelete(document)">Supprimer</Button>
                    </div>
                </div>
            </div>

            <PaginationLinks :links="documents.links" />

            <EmptyState
                v-if="documents.data.length === 0"
                title="Aucun document"
                description="Importez les documents pour démarrer la validation."
            />
        </div>

        <Dialog v-model:open="createOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Nouveau document</DialogTitle>
                    <DialogDescription>Ajoutez un document au chantier.</DialogDescription>
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
                        <Input id="title" v-model="form.title" placeholder="Plan cuisine" required />
                        <p v-if="form.errors.title" class="text-xs text-red-500">{{ form.errors.title }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="category">Catégorie</Label>
                        <Input id="category" v-model="form.category" placeholder="Plans" required />
                        <p v-if="form.errors.category" class="text-xs text-red-500">{{ form.errors.category }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="version">Version</Label>
                        <Input id="version" v-model="form.version" placeholder="v1" />
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
                        <Label for="file">Fichier</Label>
                        <Input
                            id="file"
                            type="file"
                            accept=".pdf,.png,.jpg,.jpeg,.webp,.gif,.dwg,.dxf,.ifc"
                            @change="onFileChange"
                            required
                        />
                        <p class="text-xs text-muted-foreground">
                            Formats supportés : PDF, images, DWG/DXF, IFC.
                        </p>
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
                    <DialogTitle>Modifier le document</DialogTitle>
                    <DialogDescription>Mettez à jour les informations.</DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submitEdit">
                    <div class="grid gap-2">
                        <Label for="edit-project">Chantier</Label>
                        <select id="edit-project" v-model="form.project_id" class="rounded-lg border px-3 py-2 text-sm">
                            <option value="">Sélectionner</option>
                            <option v-for="project in projects" :key="project.id" :value="project.id">
                                {{ project.name }}
                            </option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-title">Titre</Label>
                        <Input id="edit-title" v-model="form.title" required />
                        <p v-if="form.errors.title" class="text-xs text-red-500">{{ form.errors.title }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-category">Catégorie</Label>
                        <Input id="edit-category" v-model="form.category" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-version">Version</Label>
                        <Input id="edit-version" v-model="form.version" />
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
                        <Label for="edit-file">Nouveau fichier</Label>
                        <Input
                            id="edit-file"
                            type="file"
                            accept=".pdf,.png,.jpg,.jpeg,.webp,.gif,.dwg,.dxf,.ifc"
                            @change="onFileChange"
                        />
                        <p class="text-xs text-muted-foreground">
                            Formats supportés : PDF, images, DWG/DXF, IFC.
                        </p>
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
                    <DialogTitle>Supprimer le document</DialogTitle>
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
        <DocumentPreviewDialog v-model:open="lightboxOpen" :document="lightboxDocument" />
    </AppLayout>
</template>

