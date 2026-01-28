<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue';
import SectionHeader from '@/components/SectionHeader.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import frLocale from '@fullcalendar/core/locales/fr';
import { useMediaQuery } from '@vueuse/core';
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

type Task = {
    id: number;
    title: string;
    due_date?: string | null;
    status: string;
    assigned_to?: string | null;
    project_id: number;
};

type Project = { id: number; name: string };
type Contractor = { id: number; name: string; role?: string | null };

const props = defineProps<{
    filters: Record<string, string | null>;
    tasks: Task[];
    projects: Project[];
    contractors: Contractor[];
}>();

const projectNames = computed(() => {
    const map = new Map<number, string>();
    props.projects.forEach((project) => {
        map.set(project.id, project.name);
    });
    return map;
});

const filters = ref({ ...props.filters });
const onFilterChange = () => {
    router.get('/planning', filters.value, { preserveState: true, replace: true });
};

const isMobile = useMediaQuery('(max-width: 768px)');
const calendarRef = ref<InstanceType<typeof FullCalendar> | null>(null);

const normalizeKey = (value: string) =>
    value
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim();

const contractorRoles = computed(() => {
    const map = new Map<string, string>();
    props.contractors.forEach((contractor) => {
        if (contractor.name) {
            map.set(normalizeKey(contractor.name), contractor.role ?? '');
        }
    });
    return map;
});

const colorPalette = [
    '#f59e0b',
    '#3b82f6',
    '#a855f7',
    '#10b981',
    '#ef4444',
    '#f97316',
    '#6366f1',
    '#14b8a6',
    '#ec4899',
    '#22c55e',
    '#0ea5e9',
    '#8b5cf6',
];

const roleColorMap = computed(() => {
    const map = new Map<string, { label: string; color: string }>();
    const roles = props.contractors
        .map((contractor) => contractor.role)
        .filter((role): role is string => Boolean(role));

    const uniqueRoles = Array.from(new Set(roles));
    uniqueRoles.forEach((role, index) => {
        const key = normalizeKey(role);
        const color = colorPalette[index % colorPalette.length];
        map.set(key, { label: role, color });
    });
    return map;
});

const colorForRole = (role: string) => {
    const key = normalizeKey(role);
    return roleColorMap.value.get(key)?.color ?? '#0f172a';
};

const calendarEvents = computed(() =>
    props.tasks.map((task) => {
        const role = contractorRoles.value.get(normalizeKey(task.assigned_to ?? '')) ?? 'Autre';
        const color = colorForRole(role);
        const projectName = projectNames.value.get(task.project_id) ?? 'Chantier';
        return {
            id: String(task.id),
            title: `${task.title} · ${projectName}`,
            start: task.due_date ?? undefined,
            color,
            textColor: '#ffffff',
            backgroundColor: color,
            borderColor: color,
            display: 'block',
            extendedProps: {
                status: task.status,
                assigned_to: task.assigned_to,
                project_id: task.project_id,
                project_name: projectName,
                task_title: task.title,
                role,
            },
        };
    }),
);

const calendarOptions = computed(() => ({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    locale: frLocale,
    initialView: isMobile.value ? 'timeGridDay' : 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: isMobile.value ? '' : 'dayGridMonth,timeGridWeek,timeGridDay',
    },
    editable: !isMobile.value,
    eventDrop: (info: { event: { id: string; startStr: string } }) => {
        const task = props.tasks.find((item) => String(item.id) === info.event.id);
        if (!task) return;
        router.put(`/tasks/${task.id}`, {
            project_id: task.project_id,
            title: task.title,
            status: task.status,
            assigned_to: task.assigned_to,
            due_date: info.event.startStr,
        });
    },
    eventClick: (info: { event: { id: string } }) => {
        const task = props.tasks.find((item) => String(item.id) === info.event.id);
        if (task) {
            openEdit(task);
        }
    },
    eventContent: (arg: { event: { title: string } }) => {
        return { html: `<div class="truncate" title="${arg.event.title}">${arg.event.title}</div>` };
    },
    events: calendarEvents.value,
    height: 'auto',
}));

watch(isMobile, () => {
    const api = calendarRef.value?.getApi();
    if (api) {
        api.changeView(isMobile.value ? 'timeGridDay' : 'dayGridMonth');
    }
});

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
    { title: 'Planning', href: '/planning' },
];

const resetForm = () => {
    form.reset();
    form.clearErrors();
    form.status = 'pending';
};

const openCreate = () => {
    resetForm();
    createOpen.value = true;
};

const normalizeDateInput = (value?: string | null) => (value ? value.slice(0, 10) : '');

const openEdit = (task: Task) => {
    selectedTask.value = task;
    form.project_id = String(task.project_id);
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
            editOpen.value = false;
            toast.success('Tâche supprimée');
        },
        onError: () => toast.error('Impossible de supprimer la tâche'),
    });
};
</script>

<template>
    <Head title="Planning" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <SectionHeader title="Planning des tâches" />
                <Button type="button" @click="openCreate">Nouvelle tâche</Button>
            </div>

            <div class="grid gap-4 rounded-xl border bg-card p-4 md:grid-cols-4">
                <div>
                    <label class="text-xs font-semibold text-muted-foreground">Chantier</label>
                    <select
                        v-model="filters.project"
                        class="mt-2 w-full rounded-lg border px-3 py-2 text-sm"
                        @change="onFilterChange"
                    >
                        <option value="">Tous les chantiers</option>
                        <option v-for="project in props.projects" :key="project.id" :value="project.id">
                            {{ project.name }}
                        </option>
                    </select>
                </div>
            </div>

            <div v-if="tasks.length" class="rounded-xl border bg-card p-4">
                <FullCalendar ref="calendarRef" :options="calendarOptions" />
                <div class="mt-4 flex flex-wrap gap-3 text-xs text-muted-foreground">
                    <div v-for="[key, entry] in roleColorMap" :key="key" class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full" :style="{ backgroundColor: entry.color }" />
                        <span>{{ entry.label }}</span>
                    </div>
                </div>
            </div>

            <EmptyState
                v-else
                title="Aucune tâche planifiée"
                description="Ajoutez des tâches pour remplir le calendrier."
            />
        </div>

        <Dialog v-model:open="createOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Nouvelle tâche</DialogTitle>
                    <DialogDescription>Ajoutez une tâche au planning.</DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submitCreate">
                    <div class="grid gap-2">
                        <Label for="project">Chantier</Label>
                        <select id="project" v-model="form.project_id" class="rounded-lg border px-3 py-2 text-sm" required>
                            <option value="">Sélectionner</option>
                            <option v-for="project in props.projects" :key="project.id" :value="project.id">
                                {{ project.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.project_id" class="text-xs text-red-500">{{ form.errors.project_id }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="title">Titre</Label>
                        <Input id="title" v-model="form.title" placeholder="Pose carrelage" required />
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
                        <Label for="assigned">Artisan</Label>
                        <Input id="assigned" v-model="form.assigned_to" list="planning-contractors" />
                        <datalist id="planning-contractors">
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
                    <DialogDescription>Mettre à jour le planning.</DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submitEdit">
                    <div class="grid gap-2">
                        <Label for="edit-project">Chantier</Label>
                        <select id="edit-project" v-model="form.project_id" class="rounded-lg border px-3 py-2 text-sm" required>
                            <option value="">Sélectionner</option>
                            <option v-for="project in props.projects" :key="project.id" :value="project.id">
                                {{ project.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.project_id" class="text-xs text-red-500">{{ form.errors.project_id }}</p>
                    </div>
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
                        <Label for="edit-assigned">Artisan</Label>
                        <Input id="edit-assigned" v-model="form.assigned_to" list="planning-contractors-edit" />
                        <datalist id="planning-contractors-edit">
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
                        <Button type="button" variant="destructive" @click="openDelete(selectedTask!)">
                            Supprimer
                        </Button>
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

