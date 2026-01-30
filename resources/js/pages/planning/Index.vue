<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue';
import GanttView from '@/components/GanttView.vue';
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
    start_date?: string | null;
    end_date?: string | null;
    duration_days?: number | null;
    progress?: number | null;
    due_date?: string | null;
    status: string;
    assigned_to?: string | null;
    project_id: number;
    phase_id?: number | null;
};

type Milestone = {
    id: number;
    title: string;
    status: string;
    due_date?: string | null;
    owner_name?: string | null;
    description?: string | null;
    project_id: number;
};

type Phase = {
    id: number;
    title: string;
    description?: string | null;
    start_date?: string | null;
    end_date?: string | null;
    position: number;
    project_id: number;
};

type Dependency = {
    id: number;
    task_id: number;
    depends_on_task_id: number;
    dependency_type: string;
    project_id: number;
};

type Project = { id: number; name: string; status: string };
type Contractor = { id: number; name: string; role?: string | null };

const props = defineProps<{
    filters: Record<string, string | null>;
    tasks: Task[];
    milestones: Milestone[];
    phases: Phase[];
    dependencies: Dependency[];
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
const activeView = ref<'calendar' | 'gantt'>('calendar');
const ganttViewMode = ref<'Day' | 'Week' | 'Month'>('Week');

const defaultProjectId = computed(() => {
    const activeProject = props.projects.find(
        (project) => !['completed', 'cancelled'].includes(project.status),
    );
    return activeProject?.id ?? props.projects[0]?.id ?? null;
});

const ensureProjectForGantt = () => {
    if (activeView.value !== 'gantt') return;
    if (!filters.value.project && defaultProjectId.value) {
        filters.value.project = String(defaultProjectId.value);
        onFilterChange();
    }
};

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

const phaseNames = computed(() => {
    const map = new Map<number, string>();
    props.phases.forEach((phase) => {
        map.set(phase.id, phase.title);
    });
    return map;
});

const ganttDependencies = computed(() => {
    const map = new Map<number, number[]>();
    props.dependencies.forEach((dep) => {
        if (!map.has(dep.task_id)) {
            map.set(dep.task_id, []);
        }
        map.get(dep.task_id)?.push(dep.depends_on_task_id);
    });
    return map;
});

const ganttTasks = computed(() =>
    props.tasks
        .filter((task) => task.start_date || task.end_date || task.due_date)
        .map((task) => {
            const start = task.start_date ?? task.due_date ?? '';
            const end = task.end_date ?? task.due_date ?? start;
            const phaseLabel = task.phase_id ? phaseNames.value.get(task.phase_id) : null;
            const projectName = projectNames.value.get(task.project_id) ?? 'Chantier';
            const dependencies = ganttDependencies.value.get(task.id)?.join(',') ?? '';
            return {
                id: String(task.id),
                name: [phaseLabel, task.title, projectName].filter(Boolean).join(' · '),
                start,
                end,
                progress: task.progress ?? 0,
                dependencies,
                status: task.status,
            };
        }),
);

const ganttLegend = [
    { label: 'À venir', color: '#f59e0b' },
    { label: 'En cours', color: '#2563eb' },
    { label: 'Terminé', color: '#16a34a' },
    { label: 'Bloqué / en retard', color: '#dc2626' },
];

const onGanttDateChange = (task: { id: string }, start: string, end: string) => {
    const item = props.tasks.find((candidate) => String(candidate.id) === task.id);
    if (!item) return;
    router.put(`/tasks/${item.id}`, {
        project_id: item.project_id,
        title: item.title,
        status: item.status,
        assigned_to: item.assigned_to,
        start_date: start,
        end_date: end,
        due_date: end,
        progress: item.progress ?? 0,
        phase_id: item.phase_id,
    });
};

const onGanttProgressChange = (task: { id: string }, progress: number) => {
    const item = props.tasks.find((candidate) => String(candidate.id) === task.id);
    if (!item) return;
    router.put(`/tasks/${item.id}`, {
        project_id: item.project_id,
        title: item.title,
        status: item.status,
        assigned_to: item.assigned_to,
        start_date: item.start_date,
        end_date: item.end_date,
        due_date: item.due_date,
        progress,
        phase_id: item.phase_id,
    });
};

const onGanttTaskClick = (task: { id: string }) => {
    const item = props.tasks.find((candidate) => String(candidate.id) === task.id);
    if (item) {
        openEdit(item);
    }
};

watch(isMobile, () => {
    const api = calendarRef.value?.getApi();
    if (api) {
        api.changeView(isMobile.value ? 'timeGridDay' : 'dayGridMonth');
    }
});

watch(activeView, () => {
    ensureProjectForGantt();
});

const createOpen = ref(false);
const editOpen = ref(false);
const deleteOpen = ref(false);
const selectedTask = ref<Task | null>(null);

const milestoneCreateOpen = ref(false);
const milestoneEditOpen = ref(false);
const milestoneDeleteOpen = ref(false);
const selectedMilestone = ref<Milestone | null>(null);

const phaseCreateOpen = ref(false);
const phaseEditOpen = ref(false);
const phaseDeleteOpen = ref(false);
const selectedPhase = ref<Phase | null>(null);

const dependencyCreateOpen = ref(false);
const dependencyDeleteOpen = ref(false);
const selectedDependency = ref<Dependency | null>(null);

const form = useForm({
    project_id: '',
    title: '',
    status: 'pending',
    assigned_to: '',
    phase_id: '',
    start_date: '',
    end_date: '',
    duration_days: '',
    progress: 0,
    due_date: '',
});

const milestoneForm = useForm({
    project_id: '',
    title: '',
    status: 'pending',
    due_date: '',
    owner_name: '',
    description: '',
});

const phaseForm = useForm({
    project_id: '',
    title: '',
    description: '',
    start_date: '',
    end_date: '',
    position: 0,
});

const dependencyForm = useForm({
    project_id: '',
    task_id: '',
    depends_on_task_id: '',
    dependency_type: 'finish_to_start',
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Planning', href: '/planning' },
];

const resetForm = () => {
    form.reset();
    form.clearErrors();
    form.status = 'pending';
    form.progress = 0;
};

const resetMilestoneForm = () => {
    milestoneForm.reset();
    milestoneForm.clearErrors();
    milestoneForm.status = 'pending';
};

const resetPhaseForm = () => {
    phaseForm.reset();
    phaseForm.clearErrors();
};

const resetDependencyForm = () => {
    dependencyForm.reset();
    dependencyForm.clearErrors();
    dependencyForm.dependency_type = 'finish_to_start';
};

const openCreate = () => {
    resetForm();
    createOpen.value = true;
};

const normalizeDateInput = (value?: string | null) => (value ? value.slice(0, 10) : '');

const openCreateMilestone = () => {
    resetMilestoneForm();
    milestoneCreateOpen.value = true;
};

const openCreatePhase = () => {
    resetPhaseForm();
    phaseCreateOpen.value = true;
};

const openEditPhase = (phase: Phase) => {
    selectedPhase.value = phase;
    phaseForm.project_id = String(phase.project_id);
    phaseForm.title = phase.title;
    phaseForm.description = phase.description ?? '';
    phaseForm.start_date = normalizeDateInput(phase.start_date);
    phaseForm.end_date = normalizeDateInput(phase.end_date);
    phaseForm.position = phase.position ?? 0;
    phaseEditOpen.value = true;
};

const openDeletePhase = (phase: Phase) => {
    selectedPhase.value = phase;
    phaseDeleteOpen.value = true;
};

const openCreateDependency = () => {
    resetDependencyForm();
    dependencyCreateOpen.value = true;
};

const openDeleteDependency = (dependency: Dependency) => {
    selectedDependency.value = dependency;
    dependencyDeleteOpen.value = true;
};

const openEdit = (task: Task) => {
    selectedTask.value = task;
    form.project_id = String(task.project_id);
    form.title = task.title;
    form.status = task.status;
    form.assigned_to = task.assigned_to ?? '';
    form.phase_id = task.phase_id ? String(task.phase_id) : '';
    form.start_date = normalizeDateInput(task.start_date);
    form.end_date = normalizeDateInput(task.end_date);
    form.duration_days = task.duration_days ? String(task.duration_days) : '';
    form.progress = task.progress ?? 0;
    form.due_date = normalizeDateInput(task.due_date);
    editOpen.value = true;
};

const openEditMilestone = (milestone: Milestone) => {
    selectedMilestone.value = milestone;
    milestoneForm.project_id = String(milestone.project_id);
    milestoneForm.title = milestone.title;
    milestoneForm.status = milestone.status;
    milestoneForm.due_date = normalizeDateInput(milestone.due_date);
    milestoneForm.owner_name = milestone.owner_name ?? '';
    milestoneForm.description = milestone.description ?? '';
    milestoneEditOpen.value = true;
};

const openDelete = (task: Task) => {
    selectedTask.value = task;
    deleteOpen.value = true;
};

const openDeleteMilestone = (milestone: Milestone) => {
    selectedMilestone.value = milestone;
    milestoneDeleteOpen.value = true;
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

const submitCreateMilestone = () => {
    milestoneForm.post('/milestones', {
        onSuccess: () => {
            milestoneCreateOpen.value = false;
            resetMilestoneForm();
            toast.success('Jalon créé');
        },
        onError: () => toast.error('Impossible de créer le jalon'),
    });
};

const submitCreatePhase = () => {
    phaseForm.post('/phases', {
        onSuccess: () => {
            phaseCreateOpen.value = false;
            resetPhaseForm();
            toast.success('Phase créée');
        },
        onError: () => toast.error('Impossible de créer la phase'),
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

const submitEditMilestone = () => {
    if (!selectedMilestone.value) return;
    milestoneForm.put(`/milestones/${selectedMilestone.value.id}`, {
        onSuccess: () => {
            milestoneEditOpen.value = false;
            selectedMilestone.value = null;
            resetMilestoneForm();
            toast.success('Jalon mis à jour');
        },
        onError: () => toast.error('Impossible de mettre à jour le jalon'),
    });
};

const submitEditPhase = () => {
    if (!selectedPhase.value) return;
    phaseForm.put(`/phases/${selectedPhase.value.id}`, {
        onSuccess: () => {
            phaseEditOpen.value = false;
            selectedPhase.value = null;
            resetPhaseForm();
            toast.success('Phase mise à jour');
        },
        onError: () => toast.error('Impossible de mettre à jour la phase'),
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

const submitDeleteMilestone = () => {
    if (!selectedMilestone.value) return;
    milestoneForm.delete(`/milestones/${selectedMilestone.value.id}`, {
        onSuccess: () => {
            milestoneDeleteOpen.value = false;
            selectedMilestone.value = null;
            milestoneEditOpen.value = false;
            toast.success('Jalon supprimé');
        },
        onError: () => toast.error('Impossible de supprimer le jalon'),
    });
};

const submitDeletePhase = () => {
    if (!selectedPhase.value) return;
    phaseForm.delete(`/phases/${selectedPhase.value.id}`, {
        onSuccess: () => {
            phaseDeleteOpen.value = false;
            selectedPhase.value = null;
            phaseEditOpen.value = false;
            toast.success('Phase supprimée');
        },
        onError: () => toast.error('Impossible de supprimer la phase'),
    });
};

const submitCreateDependency = () => {
    dependencyForm.post('/task-dependencies', {
        onSuccess: () => {
            dependencyCreateOpen.value = false;
            resetDependencyForm();
            toast.success('Dépendance ajoutée');
        },
        onError: () => toast.error('Impossible d’ajouter la dépendance'),
    });
};

const submitDeleteDependency = () => {
    if (!selectedDependency.value) return;
    dependencyForm.delete(`/task-dependencies/${selectedDependency.value.id}`, {
        onSuccess: () => {
            dependencyDeleteOpen.value = false;
            selectedDependency.value = null;
            toast.success('Dépendance supprimée');
        },
        onError: () => toast.error('Impossible de supprimer la dépendance'),
    });
};
</script>

<template>
    <Head title="Planning" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <SectionHeader title="Planning des tâches" />
                <div class="flex flex-wrap items-center gap-2">
                    <Button
                        type="button"
                        :variant="activeView === 'calendar' ? 'default' : 'outline'"
                        @click="activeView = 'calendar'"
                    >
                        Calendrier
                    </Button>
                    <Button
                        type="button"
                        :variant="activeView === 'gantt' ? 'default' : 'outline'"
                        @click="activeView = 'gantt'"
                    >
                        Gantt
                    </Button>
                    <Button type="button" @click="openCreate">Nouvelle tâche</Button>
                </div>
            </div>
            <p class="text-sm text-muted-foreground">
                Choisissez une vue (calendrier ou Gantt) puis filtrez par chantier pour concentrer l’affichage.
            </p>

            <div class="grid gap-4 rounded-xl border bg-card p-4 md:grid-cols-4">
                <div>
                    <label class="text-xs font-semibold text-muted-foreground">Chantier</label>
                    <select
                        v-model="filters.project"
                        class="mt-2 w-full rounded-lg border px-3 py-2 text-sm"
                        @change="onFilterChange"
                    >
                        <option v-if="activeView === 'calendar'" value="">Tous les chantiers</option>
                        <option v-for="project in props.projects" :key="project.id" :value="project.id">
                            {{ project.name }}
                        </option>
                    </select>
                </div>
            </div>

            <div v-if="activeView === 'calendar'">
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

            <div v-else>
                <div class="rounded-xl border bg-card p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <SectionHeader title="Gantt des tâches" />
                        <div class="flex flex-wrap items-center gap-2">
                            <select v-model="ganttViewMode" class="rounded-lg border px-3 py-2 text-sm">
                                <option value="Day">Jour</option>
                                <option value="Week">Semaine</option>
                                <option value="Month">Mois</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-4 text-xs text-muted-foreground">
                        <div v-for="entry in ganttLegend" :key="entry.label" class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full" :style="{ backgroundColor: entry.color }" />
                            <span>{{ entry.label }}</span>
                        </div>
                    </div>
                    <div v-if="ganttTasks.length" class="mt-4">
                        <GanttView
                            :tasks="ganttTasks"
                            :view-mode="ganttViewMode"
                            @date-change="onGanttDateChange"
                            @progress-change="onGanttProgressChange"
                            @task-click="onGanttTaskClick"
                        />
                    </div>
                    <EmptyState
                        v-else
                        title="Aucune tâche planifiée"
                        description="Ajoutez des tâches avec des dates de début/fin."
                        class="mt-4"
                    />
                </div>
            </div>

            <div v-if="activeView === 'gantt'" class="rounded-xl border bg-card p-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <SectionHeader title="Jalons clés" />
                    <Button type="button" @click="openCreateMilestone">Nouveau jalon</Button>
                </div>
                <div v-if="props.milestones.length" class="mt-4 space-y-3">
                    <div
                        v-for="milestone in props.milestones"
                        :key="milestone.id"
                        class="flex flex-wrap items-center justify-between gap-3 rounded-lg border p-3 text-sm"
                    >
                        <div>
                            <p class="font-semibold">{{ milestone.title }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ projectNames.get(milestone.project_id) || 'Chantier' }}
                                · {{ milestone.status }}
                                <span v-if="milestone.due_date"> · {{ milestone.due_date }}</span>
                            </p>
                            <p v-if="milestone.owner_name" class="text-xs text-muted-foreground">
                                Responsable: {{ milestone.owner_name }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button type="button" size="sm" variant="secondary" @click="openEditMilestone(milestone)">
                                Modifier
                            </Button>
                            <Button type="button" size="sm" variant="destructive" @click="openDeleteMilestone(milestone)">
                                Supprimer
                            </Button>
                        </div>
                    </div>
                </div>
                <EmptyState
                    v-else
                    title="Aucun jalon"
                    description="Ajoutez des jalons pour suivre les étapes clés."
                    class="mt-4"
                />
            </div>

            <details v-if="activeView === 'gantt'" class="rounded-xl border bg-card p-4">
                <summary class="cursor-pointer text-sm font-semibold">
                    Gestion avancée (phases & dépendances)
                </summary>
                <p class="mt-2 text-xs text-muted-foreground">
                    Ces réglages sont optionnels. Utilisez-les si vous avez besoin de structurer le Gantt.
                </p>
                <div class="mt-4 grid gap-6 lg:grid-cols-2">
                    <div class="rounded-lg border p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <SectionHeader title="Phases" />
                            <Button type="button" size="sm" @click="openCreatePhase">Nouvelle phase</Button>
                        </div>
                        <div v-if="props.phases.length" class="mt-4 space-y-3">
                            <div
                                v-for="phase in props.phases"
                                :key="phase.id"
                                class="flex flex-wrap items-center justify-between gap-3 rounded-lg border p-3 text-sm"
                            >
                                <div>
                                    <p class="font-semibold">{{ phase.title }}</p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ projectNames.get(phase.project_id) || 'Chantier' }}
                                        <span v-if="phase.start_date || phase.end_date">
                                            · {{ phase.start_date || '---' }} → {{ phase.end_date || '---' }}
                                        </span>
                                    </p>
                                    <p v-if="phase.description" class="text-xs text-muted-foreground">
                                        {{ phase.description }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Button type="button" size="sm" variant="secondary" @click="openEditPhase(phase)">Modifier</Button>
                                    <Button type="button" size="sm" variant="destructive" @click="openDeletePhase(phase)">
                                        Supprimer
                                    </Button>
                                </div>
                            </div>
                        </div>
                        <EmptyState v-else title="Aucune phase" description="Ajoutez des phases pour structurer le planning." class="mt-4" />
                    </div>

                    <div class="rounded-lg border p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <SectionHeader title="Dépendances" />
                            <Button type="button" size="sm" @click="openCreateDependency">Nouvelle dépendance</Button>
                        </div>
                        <div v-if="props.dependencies.length" class="mt-4 space-y-3 text-sm">
                            <div
                                v-for="dependency in props.dependencies"
                                :key="dependency.id"
                                class="flex flex-wrap items-center justify-between gap-3 rounded-lg border p-3"
                            >
                                <div>
                                    <p class="font-semibold">
                                        {{ props.tasks.find((task) => task.id === dependency.task_id)?.title || 'Tâche' }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        dépend de
                                        {{
                                            props.tasks.find((task) => task.id === dependency.depends_on_task_id)?.title ||
                                                'Tâche'
                                        }}
                                    </p>
                                </div>
                                <Button type="button" size="sm" variant="destructive" @click="openDeleteDependency(dependency)">
                                    Supprimer
                                </Button>
                            </div>
                        </div>
                        <EmptyState
                            v-else
                            title="Aucune dépendance"
                            description="Reliez les tâches pour gérer les contraintes."
                            class="mt-4"
                        />
                    </div>
                </div>
            </details>
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
                        <Label for="phase">Phase</Label>
                        <select id="phase" v-model="form.phase_id" class="rounded-lg border px-3 py-2 text-sm">
                            <option value="">Aucune</option>
                            <option v-for="phase in props.phases" :key="phase.id" :value="phase.id">
                                {{ phase.title }}
                            </option>
                        </select>
                    </div>
                    <div class="grid gap-2 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="start-date">Début</Label>
                            <Input id="start-date" v-model="form.start_date" type="date" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="end-date">Fin</Label>
                            <Input id="end-date" v-model="form.end_date" type="date" />
                        </div>
                    </div>
                    <div class="grid gap-2 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="duration">Durée (jours)</Label>
                            <Input id="duration" v-model="form.duration_days" type="number" min="1" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="progress">Avancement (%)</Label>
                            <Input id="progress" v-model="form.progress" type="number" min="0" max="100" />
                        </div>
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
                        <Label for="edit-phase">Phase</Label>
                        <select id="edit-phase" v-model="form.phase_id" class="rounded-lg border px-3 py-2 text-sm">
                            <option value="">Aucune</option>
                            <option v-for="phase in props.phases" :key="phase.id" :value="phase.id">
                                {{ phase.title }}
                            </option>
                        </select>
                    </div>
                    <div class="grid gap-2 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="edit-start-date">Début</Label>
                            <Input id="edit-start-date" v-model="form.start_date" type="date" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="edit-end-date">Fin</Label>
                            <Input id="edit-end-date" v-model="form.end_date" type="date" />
                        </div>
                    </div>
                    <div class="grid gap-2 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="edit-duration">Durée (jours)</Label>
                            <Input id="edit-duration" v-model="form.duration_days" type="number" min="1" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="edit-progress">Avancement (%)</Label>
                            <Input id="edit-progress" v-model="form.progress" type="number" min="0" max="100" />
                        </div>
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

        <Dialog v-model:open="milestoneCreateOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Nouveau jalon</DialogTitle>
                    <DialogDescription>Ajoutez une étape clé au planning.</DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submitCreateMilestone">
                    <div class="grid gap-2">
                        <Label for="milestone-project">Chantier</Label>
                        <select
                            id="milestone-project"
                            v-model="milestoneForm.project_id"
                            class="rounded-lg border px-3 py-2 text-sm"
                            required
                        >
                            <option value="">Sélectionner</option>
                            <option v-for="project in props.projects" :key="project.id" :value="project.id">
                                {{ project.name }}
                            </option>
                        </select>
                        <p v-if="milestoneForm.errors.project_id" class="text-xs text-red-500">
                            {{ milestoneForm.errors.project_id }}
                        </p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="milestone-title">Titre</Label>
                        <Input id="milestone-title" v-model="milestoneForm.title" placeholder="Livraison lot 1" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="milestone-status">Statut</Label>
                        <select id="milestone-status" v-model="milestoneForm.status" class="rounded-lg border px-3 py-2 text-sm">
                            <option value="pending">À venir</option>
                            <option value="in_progress">En cours</option>
                            <option value="done">Terminé</option>
                            <option value="blocked">Bloqué</option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="milestone-due">Date cible</Label>
                        <Input id="milestone-due" v-model="milestoneForm.due_date" type="date" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="milestone-owner">Responsable</Label>
                        <Input id="milestone-owner" v-model="milestoneForm.owner_name" placeholder="Chef de projet" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="milestone-description">Description</Label>
                        <Input id="milestone-description" v-model="milestoneForm.description" placeholder="Conditions de réception" />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="secondary" @click="milestoneCreateOpen = false">Annuler</Button>
                        <Button type="submit" :disabled="milestoneForm.processing">Créer</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="milestoneEditOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Modifier le jalon</DialogTitle>
                    <DialogDescription>Mettez à jour l'étape clé.</DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submitEditMilestone">
                    <div class="grid gap-2">
                        <Label for="milestone-edit-project">Chantier</Label>
                        <select
                            id="milestone-edit-project"
                            v-model="milestoneForm.project_id"
                            class="rounded-lg border px-3 py-2 text-sm"
                            required
                        >
                            <option value="">Sélectionner</option>
                            <option v-for="project in props.projects" :key="project.id" :value="project.id">
                                {{ project.name }}
                            </option>
                        </select>
                        <p v-if="milestoneForm.errors.project_id" class="text-xs text-red-500">
                            {{ milestoneForm.errors.project_id }}
                        </p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="milestone-edit-title">Titre</Label>
                        <Input id="milestone-edit-title" v-model="milestoneForm.title" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="milestone-edit-status">Statut</Label>
                        <select
                            id="milestone-edit-status"
                            v-model="milestoneForm.status"
                            class="rounded-lg border px-3 py-2 text-sm"
                        >
                            <option value="pending">À venir</option>
                            <option value="in_progress">En cours</option>
                            <option value="done">Terminé</option>
                            <option value="blocked">Bloqué</option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="milestone-edit-due">Date cible</Label>
                        <Input id="milestone-edit-due" v-model="milestoneForm.due_date" type="date" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="milestone-edit-owner">Responsable</Label>
                        <Input id="milestone-edit-owner" v-model="milestoneForm.owner_name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="milestone-edit-description">Description</Label>
                        <Input id="milestone-edit-description" v-model="milestoneForm.description" />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="destructive" @click="openDeleteMilestone(selectedMilestone!)">
                            Supprimer
                        </Button>
                        <Button type="button" variant="secondary" @click="milestoneEditOpen = false">Annuler</Button>
                        <Button type="submit" :disabled="milestoneForm.processing">Enregistrer</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="milestoneDeleteOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Supprimer le jalon</DialogTitle>
                    <DialogDescription>Cette action est définitive.</DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button type="button" variant="secondary" @click="milestoneDeleteOpen = false">Annuler</Button>
                    <Button type="button" variant="destructive" :disabled="milestoneForm.processing" @click="submitDeleteMilestone">
                        Supprimer
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="phaseCreateOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Nouvelle phase</DialogTitle>
                    <DialogDescription>Structurez le planning par phase.</DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submitCreatePhase">
                    <div class="grid gap-2">
                        <Label for="phase-project">Chantier</Label>
                        <select id="phase-project" v-model="phaseForm.project_id" class="rounded-lg border px-3 py-2 text-sm" required>
                            <option value="">Sélectionner</option>
                            <option v-for="project in props.projects" :key="project.id" :value="project.id">
                                {{ project.name }}
                            </option>
                        </select>
                        <p v-if="phaseForm.errors.project_id" class="text-xs text-red-500">{{ phaseForm.errors.project_id }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="phase-title">Titre</Label>
                        <Input id="phase-title" v-model="phaseForm.title" placeholder="Gros œuvre" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="phase-description">Description</Label>
                        <Input id="phase-description" v-model="phaseForm.description" placeholder="Travaux principaux" />
                    </div>
                    <div class="grid gap-2 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="phase-start">Début</Label>
                            <Input id="phase-start" v-model="phaseForm.start_date" type="date" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="phase-end">Fin</Label>
                            <Input id="phase-end" v-model="phaseForm.end_date" type="date" />
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label for="phase-position">Ordre</Label>
                        <Input id="phase-position" v-model="phaseForm.position" type="number" min="0" />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="secondary" @click="phaseCreateOpen = false">Annuler</Button>
                        <Button type="submit" :disabled="phaseForm.processing">Créer</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="phaseEditOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Modifier la phase</DialogTitle>
                    <DialogDescription>Mettre à jour la phase.</DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submitEditPhase">
                    <div class="grid gap-2">
                        <Label for="phase-edit-project">Chantier</Label>
                        <select
                            id="phase-edit-project"
                            v-model="phaseForm.project_id"
                            class="rounded-lg border px-3 py-2 text-sm"
                            required
                        >
                            <option value="">Sélectionner</option>
                            <option v-for="project in props.projects" :key="project.id" :value="project.id">
                                {{ project.name }}
                            </option>
                        </select>
                        <p v-if="phaseForm.errors.project_id" class="text-xs text-red-500">{{ phaseForm.errors.project_id }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="phase-edit-title">Titre</Label>
                        <Input id="phase-edit-title" v-model="phaseForm.title" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="phase-edit-description">Description</Label>
                        <Input id="phase-edit-description" v-model="phaseForm.description" />
                    </div>
                    <div class="grid gap-2 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="phase-edit-start">Début</Label>
                            <Input id="phase-edit-start" v-model="phaseForm.start_date" type="date" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="phase-edit-end">Fin</Label>
                            <Input id="phase-edit-end" v-model="phaseForm.end_date" type="date" />
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label for="phase-edit-position">Ordre</Label>
                        <Input id="phase-edit-position" v-model="phaseForm.position" type="number" min="0" />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="destructive" @click="openDeletePhase(selectedPhase!)">
                            Supprimer
                        </Button>
                        <Button type="button" variant="secondary" @click="phaseEditOpen = false">Annuler</Button>
                        <Button type="submit" :disabled="phaseForm.processing">Enregistrer</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="phaseDeleteOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Supprimer la phase</DialogTitle>
                    <DialogDescription>Cette action est définitive.</DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button type="button" variant="secondary" @click="phaseDeleteOpen = false">Annuler</Button>
                    <Button type="button" variant="destructive" :disabled="phaseForm.processing" @click="submitDeletePhase">
                        Supprimer
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="dependencyCreateOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Nouvelle dépendance</DialogTitle>
                    <DialogDescription>Reliez deux tâches.</DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submitCreateDependency">
                    <div class="grid gap-2">
                        <Label for="dependency-project">Chantier</Label>
                        <select
                            id="dependency-project"
                            v-model="dependencyForm.project_id"
                            class="rounded-lg border px-3 py-2 text-sm"
                            required
                        >
                            <option value="">Sélectionner</option>
                            <option v-for="project in props.projects" :key="project.id" :value="project.id">
                                {{ project.name }}
                            </option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="dependency-task">Tâche</Label>
                        <select id="dependency-task" v-model="dependencyForm.task_id" class="rounded-lg border px-3 py-2 text-sm" required>
                            <option value="">Sélectionner</option>
                            <option
                                v-for="task in props.tasks.filter(
                                    (item) => !dependencyForm.project_id || String(item.project_id) === dependencyForm.project_id,
                                )"
                                :key="task.id"
                                :value="task.id"
                            >
                                {{ task.title }}
                            </option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="dependency-on">Dépend de</Label>
                        <select
                            id="dependency-on"
                            v-model="dependencyForm.depends_on_task_id"
                            class="rounded-lg border px-3 py-2 text-sm"
                            required
                        >
                            <option value="">Sélectionner</option>
                            <option
                                v-for="task in props.tasks.filter(
                                    (item) => !dependencyForm.project_id || String(item.project_id) === dependencyForm.project_id,
                                )"
                                :key="task.id"
                                :value="task.id"
                            >
                                {{ task.title }}
                            </option>
                        </select>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="secondary" @click="dependencyCreateOpen = false">Annuler</Button>
                        <Button type="submit" :disabled="dependencyForm.processing">Créer</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="dependencyDeleteOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Supprimer la dépendance</DialogTitle>
                    <DialogDescription>Cette action est définitive.</DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button type="button" variant="secondary" @click="dependencyDeleteOpen = false">Annuler</Button>
                    <Button type="button" variant="destructive" :disabled="dependencyForm.processing" @click="submitDeleteDependency">
                        Supprimer
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

