<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue';
import SectionHeader from '@/components/SectionHeader.vue';
import { Camera } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
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

type Photo = {
    id: number;
    caption?: string | null;
    taken_at?: string | null;
    media_url?: string | null;
    project_id?: number | null;
    project_task_id?: number | null;
};

type Project = { id: number; name: string };
type Task = { id: number; title: string; project_id: number };
type Pagination<T> = { data: T[] };

const props = defineProps<{
    filters: Record<string, string | null>;
    photos: Pagination<Photo>;
    projects: Project[];
    tasks: Task[];
}>();

const filters = reactive({ ...props.filters });
const createOpen = ref(false);
const editOpen = ref(false);
const deleteOpen = ref(false);
const lightboxOpen = ref(false);
const lightboxPhoto = ref<Photo | null>(null);
const selectedPhoto = ref<Photo | null>(null);

const form = useForm({
    project_id: '',
    project_task_id: '',
    caption: '',
    taken_at: '',
    image: null as File | null,
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Photos', href: '/photos' },
];

const onFilterChange = () => {
    router.get('/photos', filters, { preserveState: true, replace: true });
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
    form.image = null;
};

const openCreate = () => {
    resetForm();
    createOpen.value = true;
};

const openEdit = (photo: Photo) => {
    selectedPhoto.value = photo;
    form.project_id = photo.project_id ? String(photo.project_id) : '';
    form.project_task_id = photo.project_task_id ? String(photo.project_task_id) : '';
    form.caption = photo.caption ?? '';
    form.taken_at = photo.taken_at ?? '';
    form.image = null;
    editOpen.value = true;
};

const filteredTasks = (projectId: string) =>
    props.tasks.filter((task) => String(task.project_id) === String(projectId));

const getProjectName = (projectId?: number | null) =>
    props.projects.find((project) => project.id === projectId)?.name ?? 'Chantier non défini';

const getTaskTitle = (taskId?: number | null) =>
    props.tasks.find((task) => task.id === taskId)?.title ?? null;

const openDelete = (photo: Photo) => {
    selectedPhoto.value = photo;
    deleteOpen.value = true;
};

const openLightbox = (photo: Photo) => {
    lightboxPhoto.value = photo;
    lightboxOpen.value = true;
};

const onFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    form.image = target.files?.[0] ?? null;
};

const submitCreate = () => {
    form.post('/photos', {
        forceFormData: true,
        onSuccess: () => {
            createOpen.value = false;
            resetForm();
            toast.success('Photo ajoutée');
        },
        onError: () => toast.error('Impossible d’ajouter la photo'),
    });
};

const submitEdit = () => {
    if (!selectedPhoto.value) return;
    form.put(`/photos/${selectedPhoto.value.id}`, {
        forceFormData: true,
        onSuccess: () => {
            editOpen.value = false;
            selectedPhoto.value = null;
            resetForm();
            toast.success('Photo mise à jour');
        },
        onError: () => toast.error('Impossible de mettre à jour la photo'),
    });
};

const submitDelete = () => {
    if (!selectedPhoto.value) return;
    form.delete(`/photos/${selectedPhoto.value.id}`, {
        onSuccess: () => {
            deleteOpen.value = false;
            selectedPhoto.value = null;
            toast.success('Photo supprimée');
        },
        onError: () => toast.error('Impossible de supprimer la photo'),
    });
};
</script>

<template>
    <Head title="Photos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <SectionHeader title="Galerie photos" />
                <Button type="button" @click="openCreate">Ajouter une photo</Button>
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
                    <label class="text-xs font-semibold text-muted-foreground">Intervention</label>
                    <select v-model="filters.task" class="mt-2 w-full rounded-lg border px-3 py-2 text-sm" @change="onFilterChange">
                        <option value="">Toutes les interventions</option>
                        <option
                            v-for="task in filteredTasks(filters.project ?? '')"
                            :key="task.id"
                            :value="task.id"
                        >
                            {{ task.title }}
                        </option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-muted-foreground">Recherche</label>
                    <input
                        v-model="filters.search"
                        type="text"
                        class="mt-2 w-full rounded-lg border px-3 py-2 text-sm"
                        placeholder="Légende"
                        @change="onFilterChange"
                    />
                </div>
            </div>

            <div v-if="photos.data.length" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div
                    v-for="photo in photos.data"
                    :key="photo.id"
                    class="rounded-xl border bg-card p-4"
                >
                    <div
                        class="flex h-40 cursor-zoom-in items-center justify-center overflow-hidden rounded-lg bg-muted"
                        @click="photo.media_url && openLightbox(photo)"
                    >
                        <img
                            v-if="photo.media_url"
                            :src="photo.media_url"
                            alt="Photo chantier"
                            class="h-full w-full object-cover"
                        />
                        <span v-else class="text-sm text-muted-foreground">Aperçu indisponible</span>
                    </div>
                    <div class="mt-3 flex items-center gap-2">
                        <Camera class="size-4 text-muted-foreground" />
                        <p class="text-sm font-semibold">{{ photo.caption || 'Photo chantier' }}</p>
                    </div>
                    <p class="text-xs text-muted-foreground">
                        {{ getProjectName(photo.project_id ?? null) }}
                        <span v-if="getTaskTitle(photo.project_task_id ?? null)">
                            · {{ getTaskTitle(photo.project_task_id ?? null) }}
                        </span>
                    </p>
                    <p class="text-xs text-muted-foreground">{{ formatDate(photo.taken_at) }}</p>
                    <div class="mt-4 flex items-center justify-end gap-2">
                        <Button type="button" variant="secondary" @click="openEdit(photo)">Modifier</Button>
                        <Button type="button" variant="destructive" @click="openDelete(photo)">Supprimer</Button>
                    </div>
                </div>
            </div>

            <EmptyState
                v-else
                title="Aucune photo"
                description="Ajoutez des photos pour conserver les preuves."
            />
        </div>

        <Dialog v-model:open="createOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Ajouter une photo</DialogTitle>
                    <DialogDescription>Importez une photo de chantier.</DialogDescription>
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
                        <Label for="task">Intervention (optionnel)</Label>
                        <select id="task" v-model="form.project_task_id" class="rounded-lg border px-3 py-2 text-sm">
                            <option value="">Aucune</option>
                            <option v-for="task in filteredTasks(form.project_id)" :key="task.id" :value="task.id">
                                {{ task.title }}
                            </option>
                        </select>
                        <p v-if="form.errors.project_task_id" class="text-xs text-red-500">
                            {{ form.errors.project_task_id }}
                        </p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="caption">Légende</Label>
                        <Input id="caption" v-model="form.caption" placeholder="Mur porteur" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="taken_at">Date</Label>
                        <Input id="taken_at" v-model="form.taken_at" type="date" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="image">Image</Label>
                        <Input id="image" type="file" accept="image/*" @change="onFileChange" required />
                        <p v-if="form.errors.image" class="text-xs text-red-500">{{ form.errors.image }}</p>
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
                    <DialogTitle>Modifier la photo</DialogTitle>
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
                        <Label for="edit-task">Intervention (optionnel)</Label>
                        <select id="edit-task" v-model="form.project_task_id" class="rounded-lg border px-3 py-2 text-sm">
                            <option value="">Aucune</option>
                            <option v-for="task in filteredTasks(form.project_id)" :key="task.id" :value="task.id">
                                {{ task.title }}
                            </option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-caption">Légende</Label>
                        <Input id="edit-caption" v-model="form.caption" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-taken-at">Date</Label>
                        <Input id="edit-taken-at" v-model="form.taken_at" type="date" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-image">Nouvelle image</Label>
                        <Input id="edit-image" type="file" accept="image/*" @change="onFileChange" />
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
                    <DialogTitle>Supprimer la photo</DialogTitle>
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

        <Dialog v-model:open="lightboxOpen">
            <DialogContent class="sm:max-w-3xl">
                <DialogHeader>
                    <DialogTitle>Photo chantier</DialogTitle>
                    <DialogDescription>{{ lightboxPhoto?.caption || 'Aucune légende' }}</DialogDescription>
                </DialogHeader>
                <div class="overflow-hidden rounded-lg bg-muted">
                    <img
                        v-if="lightboxPhoto?.media_url"
                        :src="lightboxPhoto.media_url"
                        alt="Photo chantier"
                        class="h-full w-full object-contain"
                    />
                </div>
                <DialogFooter>
                    <Button type="button" variant="secondary" @click="lightboxOpen = false">Fermer</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

