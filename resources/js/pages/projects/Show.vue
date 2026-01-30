<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue';
import SectionHeader from '@/components/SectionHeader.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import StatusIcon from '@/components/StatusIcon.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import DocumentPreviewDialog from '@/components/DocumentPreviewDialog.vue';
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatDate, formatStatus, statusTone } from '@/lib/formatters';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { toast } from '@/lib/toast';

type Contractor = {
    id: number;
    name: string;
    role?: string | null;
    email?: string | null;
    phone?: string | null;
    company?: string | null;
};

type Document = {
    id: number;
    title: string;
    category: string;
    version: string;
    status: string;
    media_url?: string | null;
    created_at?: string | null;
};

type Validation = {
    id: number;
    title: string;
    type: string;
    status: string;
    requested_by?: string | null;
    decided_at?: string | null;
    created_at?: string | null;
};

type Incident = {
    id: number;
    title: string;
    description?: string | null;
    status: string;
    impact_days: number;
    impact_cost: number;
    created_at?: string | null;
};

type ProjectTask = {
    id: number;
    title: string;
    status: string;
    assigned_to?: string | null;
    due_date?: string | null;
    created_at?: string | null;
    comments?: Comment[];
};

type Decision = {
    id: number;
    title: string;
    actor_name?: string | null;
    decided_at?: string | null;
    description?: string | null;
};

type Comment = {
    id: number;
    body: string;
    created_at?: string | null;
    author?: { id: number; name: string };
};

type ProjectMessage = {
    id: number;
    body: string;
    created_at?: string | null;
    author?: { id: number; name: string };
};

type ProjectActivity = {
    id: number;
    type: string;
    created_at?: string | null;
    actor?: { id: number; name: string };
    payload?: Record<string, unknown> | null;
};

type Project = {
    id: number;
    name: string;
    client_name: string;
    address: string;
    city: string;
    status: string;
    budget: number;
    budget_alert_enabled: boolean;
    budget_alert_threshold: number;
    progress: number;
    start_date?: string | null;
    end_date?: string | null;
    contractors: Contractor[];
    documents: Document[];
    validations: Validation[];
    incidents: Incident[];
    tasks: ProjectTask[];
    decisions: Decision[];
    messages: ProjectMessage[];
    activities: ProjectActivity[];
};

const props = defineProps<{
    project: Project;
    contractorsCatalog: Contractor[];
}>();

const tabs = [
    { id: 'contractors', label: 'Intervenants' },
    { id: 'documents', label: 'Documents' },
    { id: 'validations', label: 'Validations' },
    { id: 'incidents', label: 'Incidents' },
    { id: 'tasks', label: 'Tâches' },
    { id: 'communication', label: 'Communication' },
];

const activeTab = ref('contractors');
const statusOptions = [
    { value: 'preparation', label: 'Préparation' },
    { value: 'in_progress', label: 'En cours' },
    { value: 'delayed', label: 'En retard' },
    { value: 'completed', label: 'Terminé' },
    { value: 'cancelled', label: 'Annulé' },
];

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Chantiers', href: '/projects' },
    { title: props.project.name, href: `/projects/${props.project.id}` },
];

const projectId = props.project.id;
const toDateInput = (value?: string | null) => (value ? value.split('T')[0] : '');
const projectQuickForm = useForm({
    name: props.project.name,
    client_name: props.project.client_name,
    address: props.project.address,
    city: props.project.city,
    status: props.project.status,
    budget: props.project.budget,
    budget_alert_enabled: props.project.budget_alert_enabled,
    budget_alert_threshold: props.project.budget_alert_threshold,
    progress_value: props.project.progress,
    start_date: toDateInput(props.project.start_date),
    end_date: toDateInput(props.project.end_date),
});
const editProjectOpen = ref(false);
const cancelProjectOpen = ref(false);
const cancelForm = useForm({
    reason: '',
});
const messageForm = useForm({
    project_id: props.project.id,
    body: '',
});
const commentForm = useForm({
    commentable_type: 'task',
    commentable_id: '',
    body: '',
});
const commentOpen = ref(false);
const selectedCommentTarget = ref<{ type: 'task' | 'milestone' | 'decision' | 'budget_item'; id: number } | null>(null);
const projectForm = useForm({
    name: props.project.name,
    client_name: props.project.client_name,
    address: props.project.address,
    city: props.project.city,
    status: props.project.status,
    budget: props.project.budget,
    budget_alert_enabled: props.project.budget_alert_enabled,
    budget_alert_threshold: props.project.budget_alert_threshold,
    progress_value: props.project.progress,
    start_date: toDateInput(props.project.start_date),
    end_date: toDateInput(props.project.end_date),
});

const kpiData = computed(() => {
    const pendingValidations = props.project.validations.filter((v) => v.status === 'pending').length;
    const openIncidents = props.project.incidents.filter((i) => i.status === 'open').length;
    const completedTasks = props.project.tasks.filter((t) => t.status === 'done').length;
    const totalTasks = props.project.tasks.length;

    return {
        pendingValidations,
        openIncidents,
        completedTasks,
        totalTasks,
        documents: props.project.documents.length,
    };
});

const submitProjectQuickUpdate = () => {
    projectQuickForm.transform((data) => {
        const { progress_value, ...rest } = data;
        return { ...rest, progress: progress_value };
    });
    projectQuickForm.put(`/projects/${projectId}`, {
        onSuccess: () => {
            toast.success('Chantier mis à jour');
        },
        onError: () => toast.error('Impossible de mettre à jour le chantier'),
    });
};

const quickFormChanged = computed(() => {
    return (
        projectQuickForm.status !== props.project.status ||
        Number(projectQuickForm.budget) !== Number(props.project.budget) ||
        Number(projectQuickForm.progress_value) !== Number(props.project.progress)
    );
});

const openEditProject = () => {
    projectForm.name = props.project.name;
    projectForm.client_name = props.project.client_name;
    projectForm.address = props.project.address;
    projectForm.city = props.project.city;
    projectForm.status = props.project.status;
    projectForm.budget = props.project.budget;
    projectForm.budget_alert_enabled = props.project.budget_alert_enabled;
    projectForm.budget_alert_threshold = props.project.budget_alert_threshold;
    projectForm.progress_value = props.project.progress;
    projectForm.start_date = toDateInput(props.project.start_date);
    projectForm.end_date = toDateInput(props.project.end_date);
    editProjectOpen.value = true;
};

const submitEditProject = () => {
    projectForm.transform((data) => {
        const { progress_value, ...rest } = data;
        return { ...rest, progress: progress_value };
    });
    projectForm.put(`/projects/${projectId}`, {
        onSuccess: () => {
            editProjectOpen.value = false;
            toast.success('Chantier mis à jour');
        },
        onError: () => toast.error('Impossible de mettre à jour le chantier'),
    });
};

const submitMessage = () => {
    messageForm.post('/project-messages', {
        onSuccess: () => {
            messageForm.body = '';
            toast.success('Message envoyé');
        },
        onError: () => toast.error('Impossible d’envoyer le message'),
    });
};

const openComments = (type: 'task' | 'milestone' | 'decision' | 'budget_item', id: number) => {
    selectedCommentTarget.value = { type, id };
    commentForm.commentable_type = type;
    commentForm.commentable_id = String(id);
    commentForm.body = '';
    commentOpen.value = true;
};

const submitComment = () => {
    commentForm.post('/comments', {
        onSuccess: () => {
            commentForm.body = '';
            toast.success('Commentaire ajouté');
        },
        onError: () => toast.error('Impossible d’ajouter le commentaire'),
    });
};

const currentComments = computed(() => {
    if (!selectedCommentTarget.value) return [];
    const { type, id } = selectedCommentTarget.value;
    if (type === 'task') {
        return props.project.tasks.find((task) => task.id === id)?.comments ?? [];
    }
    return [];
});

const activityLabel = (activity: ProjectActivity) => {
    switch (activity.type) {
        case 'message_posted':
            return 'Message publié';
        case 'comment_added':
            return 'Commentaire ajouté';
        case 'budget_overrun':
            return 'Dépassement budgétaire';
        default:
            return 'Mise à jour';
    }
};

const openCancelProject = () => {
    cancelForm.reset();
    cancelForm.clearErrors();
    cancelProjectOpen.value = true;
};

const submitCancelProject = () => {
    if (!cancelForm.reason.trim()) {
        cancelForm.setError('reason', 'La raison est obligatoire.');
        return;
    }
    projectQuickForm.status = 'cancelled';
    projectQuickForm.put(`/projects/${projectId}`, {
        onSuccess: () => {
            cancelProjectOpen.value = false;
            toast.success('Chantier annulé');
        },
        onError: () => toast.error("Impossible d'annuler le chantier"),
    });
};

const timelineItems = computed(() => {
    const now = new Date();
    const isDelayed =
        props.project.end_date &&
        new Date(props.project.end_date).getTime() < now.getTime() &&
        props.project.status !== 'completed';

    const phaseItems = [
        {
            id: 'phase-prep',
            title: 'Phase préparation',
            date: props.project.start_date ?? null,
            status: 'preparation',
            type: 'Phase',
            meta: 'Avant démarrage',
        },
        {
            id: 'phase-work',
            title: 'Phase exécution',
            date: props.project.start_date ?? null,
            status: 'in_progress',
            type: 'Phase',
            meta: 'Travaux en cours',
        },
        {
            id: 'phase-delivery',
            title: 'Phase livraison',
            date: props.project.end_date ?? null,
            status: props.project.status === 'completed' ? 'completed' : isDelayed ? 'delayed' : 'pending',
            type: 'Phase',
            meta: 'Livraison prévue',
        },
    ];

    const delayItem = isDelayed
        ? [
              {
                  id: 'phase-delay',
                  title: 'Retard identifié',
                  date: props.project.end_date,
                  status: 'delayed',
                  type: 'Alerte',
                  meta: 'Date de livraison dépassée',
              },
          ]
        : [];

    const taskItems = props.project.tasks
        .filter((task) => task.due_date && task.status !== 'done')
        .slice(0, 4)
        .map((task) => ({
            id: `task-${task.id}`,
            title: task.title,
            date: task.due_date,
            status: task.status,
            type: 'Jalon',
            meta: task.assigned_to ?? 'Intervention planifiée',
        }));

    const items = [
        ...phaseItems,
        ...delayItem,
        ...taskItems,
        ...props.project.decisions.map((decision) => ({
            id: `decision-${decision.id}`,
            title: decision.title,
            date: decision.decided_at,
            status: 'approved',
            type: 'Décision',
            meta: decision.actor_name,
        })),
        ...props.project.validations.map((validation) => ({
            id: `validation-${validation.id}`,
            title: validation.title,
            date: validation.decided_at ?? validation.created_at,
            status: validation.status,
            type: validation.status === 'approved' ? 'Jalon' : 'Validation',
            meta: validation.requested_by,
        })),
        ...props.project.incidents.map((incident) => ({
            id: `incident-${incident.id}`,
            title: incident.title,
            date: incident.created_at,
            status: incident.status,
            type: 'Incident',
            meta: `${incident.impact_days} j · ${incident.impact_cost.toLocaleString('fr-FR')} €`,
        })),
        ...props.project.documents.map((document) => ({
            id: `document-${document.id}`,
            title: document.title,
            date: document.created_at,
            status: document.status,
            type: 'Document',
            meta: document.category,
        })),
    ];

    return items
        .filter((item) => item.date)
        .sort((a, b) => new Date(b.date ?? '').getTime() - new Date(a.date ?? '').getTime())
        .slice(0, 8);
});

const contractorAssignOpen = ref(false);
const contractorRemoveOpen = ref(false);
const selectedContractor = ref<Contractor | null>(null);
const contractorForm = useForm({
    contractor_id: '',
    role: '',
});

const documentCreateOpen = ref(false);
const documentEditOpen = ref(false);
const documentDeleteOpen = ref(false);
const documentLightboxOpen = ref(false);
const documentLightboxDocument = ref<Document | null>(null);
const selectedDocument = ref<Document | null>(null);
const documentForm = useForm({
    project_id: projectId,
    title: '',
    category: '',
    version: 'v1',
    status: 'pending',
    file: null as File | null,
});

const validationCreateOpen = ref(false);
const validationEditOpen = ref(false);
const validationDeleteOpen = ref(false);
const selectedValidation = ref<Validation | null>(null);
const validationForm = useForm({
    project_id: projectId,
    title: '',
    type: '',
    status: 'pending',
    requested_by: '',
    decided_by: '',
    decided_at: '',
});

const incidentCreateOpen = ref(false);
const incidentEditOpen = ref(false);
const incidentDeleteOpen = ref(false);
const selectedIncident = ref<Incident | null>(null);
const incidentForm = useForm({
    project_id: projectId,
    title: '',
    description: '',
    status: 'open',
    impact_days: 0,
    impact_cost: 0,
    reported_by: '',
});

const taskCreateOpen = ref(false);
const taskEditOpen = ref(false);
const taskDeleteOpen = ref(false);
const selectedTask = ref<ProjectTask | null>(null);
const normalizeDateInput = (value?: string | null) => (value ? value.slice(0, 10) : '');
const taskForm = useForm({
    project_id: projectId,
    title: '',
    status: 'pending',
    assigned_to: '',
    due_date: '',
});

const resetContractorForm = () => {
    contractorForm.reset();
    contractorForm.clearErrors();
};

const openAssignContractor = () => {
    resetContractorForm();
    contractorAssignOpen.value = true;
};

const openRemoveContractor = (contractor: Contractor) => {
    selectedContractor.value = contractor;
    contractorRemoveOpen.value = true;
};

const submitAssignContractor = () => {
    contractorForm.post(`/projects/${projectId}/contractors`, {
        onSuccess: () => {
            contractorAssignOpen.value = false;
            resetContractorForm();
            toast.success('Intervenant affecté');
        },
        onError: () => toast.error('Impossible d’affecter l’intervenant'),
    });
};

const submitRemoveContractor = () => {
    if (!selectedContractor.value) return;
    contractorForm.delete(`/projects/${projectId}/contractors/${selectedContractor.value.id}`, {
        onSuccess: () => {
            contractorRemoveOpen.value = false;
            selectedContractor.value = null;
            toast.success('Intervenant retiré');
        },
        onError: () => toast.error('Impossible de retirer l’intervenant'),
    });
};

const resetDocumentForm = () => {
    documentForm.reset();
    documentForm.clearErrors();
    documentForm.project_id = projectId;
    documentForm.version = 'v1';
    documentForm.status = 'pending';
    documentForm.file = null;
};

const openCreateDocument = () => {
    resetDocumentForm();
    documentCreateOpen.value = true;
};

const openEditDocument = (document: Document) => {
    selectedDocument.value = document;
    documentForm.project_id = projectId;
    documentForm.title = document.title;
    documentForm.category = document.category;
    documentForm.version = document.version;
    documentForm.status = document.status;
    documentForm.file = null;
    documentEditOpen.value = true;
};

const openDeleteDocument = (document: Document) => {
    selectedDocument.value = document;
    documentDeleteOpen.value = true;
};

const openDocumentLightbox = (document: Document) => {
    documentLightboxDocument.value = document;
    documentLightboxOpen.value = true;
};

const isImageDocument = (url?: string | null) => {
    if (!url) return false;
    return ['.png', '.jpg', '.jpeg', '.webp', '.gif'].some((ext) => url.toLowerCase().includes(ext));
};

const onDocumentFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    documentForm.file = target.files?.[0] ?? null;
};

const submitCreateDocument = () => {
    documentForm.post('/documents', {
        forceFormData: true,
        onSuccess: () => {
            documentCreateOpen.value = false;
            resetDocumentForm();
            toast.success('Document créé');
        },
        onError: () => toast.error('Impossible de créer le document'),
    });
};

const submitEditDocument = () => {
    if (!selectedDocument.value) return;
    documentForm.put(`/documents/${selectedDocument.value.id}`, {
        forceFormData: true,
        onSuccess: () => {
            documentEditOpen.value = false;
            selectedDocument.value = null;
            resetDocumentForm();
            toast.success('Document mis à jour');
        },
        onError: () => toast.error('Impossible de mettre à jour le document'),
    });
};

const submitDeleteDocument = () => {
    if (!selectedDocument.value) return;
    documentForm.delete(`/documents/${selectedDocument.value.id}`, {
        onSuccess: () => {
            documentDeleteOpen.value = false;
            selectedDocument.value = null;
            toast.success('Document supprimé');
        },
        onError: () => toast.error('Impossible de supprimer le document'),
    });
};

const resetValidationForm = () => {
    validationForm.reset();
    validationForm.clearErrors();
    validationForm.project_id = projectId;
    validationForm.status = 'pending';
};

const openCreateValidation = () => {
    resetValidationForm();
    validationCreateOpen.value = true;
};

const openEditValidation = (validation: Validation) => {
    selectedValidation.value = validation;
    validationForm.project_id = projectId;
    validationForm.title = validation.title;
    validationForm.type = validation.type;
    validationForm.status = validation.status;
    validationForm.requested_by = validation.requested_by ?? '';
    validationForm.decided_by = '';
    validationForm.decided_at = '';
    validationEditOpen.value = true;
};

const openDeleteValidation = (validation: Validation) => {
    selectedValidation.value = validation;
    validationDeleteOpen.value = true;
};

const submitCreateValidation = () => {
    validationForm.post('/validations', {
        onSuccess: () => {
            validationCreateOpen.value = false;
            resetValidationForm();
            toast.success('Validation créée');
        },
        onError: () => toast.error('Impossible de créer la validation'),
    });
};

const submitEditValidation = () => {
    if (!selectedValidation.value) return;
    validationForm.put(`/validations/${selectedValidation.value.id}`, {
        onSuccess: () => {
            validationEditOpen.value = false;
            selectedValidation.value = null;
            resetValidationForm();
            toast.success('Validation mise à jour');
        },
        onError: () => toast.error('Impossible de mettre à jour la validation'),
    });
};

const submitDeleteValidation = () => {
    if (!selectedValidation.value) return;
    validationForm.delete(`/validations/${selectedValidation.value.id}`, {
        onSuccess: () => {
            validationDeleteOpen.value = false;
            selectedValidation.value = null;
            toast.success('Validation supprimée');
        },
        onError: () => toast.error('Impossible de supprimer la validation'),
    });
};

const resetIncidentForm = () => {
    incidentForm.reset();
    incidentForm.clearErrors();
    incidentForm.project_id = projectId;
    incidentForm.status = 'open';
    incidentForm.impact_days = 0;
    incidentForm.impact_cost = 0;
};

const openCreateIncident = () => {
    resetIncidentForm();
    incidentCreateOpen.value = true;
};

const openEditIncident = (incident: Incident) => {
    selectedIncident.value = incident;
    incidentForm.project_id = projectId;
    incidentForm.title = incident.title;
    incidentForm.description = incident.description ?? '';
    incidentForm.status = incident.status;
    incidentForm.impact_days = incident.impact_days ?? 0;
    incidentForm.impact_cost = incident.impact_cost ?? 0;
    incidentForm.reported_by = '';
    incidentEditOpen.value = true;
};

const openDeleteIncident = (incident: Incident) => {
    selectedIncident.value = incident;
    incidentDeleteOpen.value = true;
};

const submitCreateIncident = () => {
    incidentForm.post('/incidents', {
        onSuccess: () => {
            incidentCreateOpen.value = false;
            resetIncidentForm();
            toast.success('Incident créé');
        },
        onError: () => toast.error('Impossible de créer l’incident'),
    });
};

const submitEditIncident = () => {
    if (!selectedIncident.value) return;
    incidentForm.put(`/incidents/${selectedIncident.value.id}`, {
        onSuccess: () => {
            incidentEditOpen.value = false;
            selectedIncident.value = null;
            resetIncidentForm();
            toast.success('Incident mis à jour');
        },
        onError: () => toast.error('Impossible de mettre à jour l’incident'),
    });
};

const submitDeleteIncident = () => {
    if (!selectedIncident.value) return;
    incidentForm.delete(`/incidents/${selectedIncident.value.id}`, {
        onSuccess: () => {
            incidentDeleteOpen.value = false;
            selectedIncident.value = null;
            toast.success('Incident supprimé');
        },
        onError: () => toast.error('Impossible de supprimer l’incident'),
    });
};

const resetTaskForm = () => {
    taskForm.reset();
    taskForm.clearErrors();
    taskForm.project_id = projectId;
    taskForm.status = 'pending';
};

const openCreateTask = () => {
    resetTaskForm();
    taskCreateOpen.value = true;
};

const openEditTask = (task: ProjectTask) => {
    selectedTask.value = task;
    taskForm.project_id = projectId;
    taskForm.title = task.title;
    taskForm.status = task.status;
    taskForm.assigned_to = task.assigned_to ?? '';
    taskForm.due_date = normalizeDateInput(task.due_date);
    taskEditOpen.value = true;
};

const openDeleteTask = (task: ProjectTask) => {
    selectedTask.value = task;
    taskDeleteOpen.value = true;
};

const submitCreateTask = () => {
    taskForm.post('/tasks', {
        onSuccess: () => {
            taskCreateOpen.value = false;
            resetTaskForm();
            toast.success('Tâche créée');
        },
        onError: () => toast.error('Impossible de créer la tâche'),
    });
};

const submitEditTask = () => {
    if (!selectedTask.value) return;
    taskForm.put(`/tasks/${selectedTask.value.id}`, {
        onSuccess: () => {
            taskEditOpen.value = false;
            selectedTask.value = null;
            resetTaskForm();
            toast.success('Tâche mise à jour');
        },
        onError: () => toast.error('Impossible de mettre à jour la tâche'),
    });
};

const submitDeleteTask = () => {
    if (!selectedTask.value) return;
    taskForm.delete(`/tasks/${selectedTask.value.id}`, {
        onSuccess: () => {
            taskDeleteOpen.value = false;
            selectedTask.value = null;
            toast.success('Tâche supprimée');
        },
        onError: () => toast.error('Impossible de supprimer la tâche'),
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

</script>

<template>
    <Head :title="project.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="grid gap-6 p-6 lg:grid-cols-[600px_1fr]">
            <div class="space-y-4 lg:sticky lg:top-4 lg:self-start">
                <div class="rounded-xl border bg-card p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-lg font-semibold">{{ project.name }}</p>
                            <p class="text-sm text-muted-foreground">
                                {{ project.client_name }} · {{ project.address }}, {{ project.city }}
                            </p>
                        </div>
                        <StatusBadge :label="formatStatus(project.status)" :tone="statusTone(project.status)" />
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-muted-foreground">
                        <span>Budget: {{ formatCurrency(projectQuickForm.budget) }}</span>
                        <span>Avancement: {{ project.progress }}%</span>
                        <span v-if="project.end_date">Livraison: {{ formatDate(project.end_date) }}</span>
                    </div>
                    <div class="mt-3 flex items-center gap-2">
                        <Button type="button" size="sm" variant="secondary" @click="openEditProject">
                            Éditer le chantier
                        </Button>
                    </div>
                </div>

                <div class="rounded-xl border bg-card p-4">
                    <SectionHeader title="Actions rapides" />
                    <form class="mt-3 grid gap-3" @submit.prevent="submitProjectQuickUpdate">
                        <div class="grid gap-2">
                            <Label for="quick-status">Statut</Label>
                            <select
                                id="quick-status"
                                v-model="projectQuickForm.status"
                                class="rounded-lg border px-3 py-2 text-sm"
                            >
                                <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                        <div class="grid gap-2">
                            <Label for="quick-budget">Budget ({{ formatCurrency(projectQuickForm.budget) }})</Label>
                            <Input id="quick-budget" v-model="projectQuickForm.budget" type="number" min="0" step="100" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="quick-progress">Avancement (%)</Label>
                        <Input id="quick-progress" v-model="projectQuickForm.progress_value" type="number" min="0" max="100" />
                        </div>
                        <div class="flex justify-end gap-2">
                            <Button type="button" size="sm" variant="destructive" @click="openCancelProject">
                                Annuler le chantier
                            </Button>
                            <Button type="submit" size="sm" :disabled="projectQuickForm.processing || !quickFormChanged">
                                Enregistrer
                            </Button>
                        </div>
                    </form>
                </div>

                <div class="rounded-xl border bg-card p-4">
                    <SectionHeader title="KPI chantier" />
                    <div class="mt-3 grid gap-2">
                        <div class="flex items-center justify-between rounded-lg border px-3 py-2 text-xs">
                            <span class="text-muted-foreground">Validations en attente</span>
                            <span class="font-semibold">{{ kpiData.pendingValidations }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg border px-3 py-2 text-xs">
                            <span class="text-muted-foreground">Incidents ouverts</span>
                            <span class="font-semibold">{{ kpiData.openIncidents }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg border px-3 py-2 text-xs">
                            <span class="text-muted-foreground">Tâches terminées</span>
                            <span class="font-semibold">{{ kpiData.completedTasks }}/{{ kpiData.totalTasks }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg border px-3 py-2 text-xs">
                            <span class="text-muted-foreground">Documents actifs</span>
                            <span class="font-semibold">{{ kpiData.documents }}</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border bg-card p-4">
                    <SectionHeader title="Résumé chantier" />
                    <div class="mt-3 grid gap-2 text-xs text-muted-foreground">
                        <div class="flex items-center justify-between">
                            <span>Phase</span>
                            <span class="font-semibold text-foreground">
                                {{
                                    project.status === 'completed'
                                        ? 'Livraison'
                                        : project.status === 'cancelled'
                                          ? 'Annulé'
                                          : project.status === 'delayed'
                                            ? 'Retard'
                                            : project.status === 'in_progress'
                                              ? 'Exécution'
                                              : 'Préparation'
                                }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Début</span>
                            <span class="font-semibold text-foreground">{{ formatDate(project.start_date) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Fin prévue</span>
                            <span class="font-semibold text-foreground">{{ formatDate(project.end_date) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Équipe</span>
                            <span class="font-semibold text-foreground">{{ project.contractors.length }} intervenant(s)</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border bg-card p-4">
                    <SectionHeader title="Timeline récente" />
                    <div v-if="timelineItems.length" class="mt-4 max-h-96 space-y-4 overflow-y-auto pr-2">
                        <div v-for="item in timelineItems" :key="item.id" class="flex gap-3">
                            <StatusIcon :status="item.status" class="mt-1" />
                            <div>
                                <p class="text-sm font-semibold">{{ item.title }}</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ item.type }} · {{ formatDate(item.date) }} {{ item.meta ? `· ${item.meta}` : '' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <EmptyState v-else title="Aucune activité récente" description="Les actions s’affichent ici." />
                </div>
            </div>

            <div class="space-y-6">
                <div class="grid gap-4 lg:grid-cols-2">
                    <Alert v-if="project.status === 'delayed'" class="border-red-200 bg-red-50 text-red-900">
                        <AlertTitle>Chantier en retard</AlertTitle>
                        <AlertDescription>
                            Des actions prioritaires sont recommandées pour rattraper le planning.
                        </AlertDescription>
                    </Alert>
                    <Alert v-else-if="kpiData.openIncidents > 0" class="border-amber-200 bg-amber-50 text-amber-900">
                        <AlertTitle>Incidents à traiter</AlertTitle>
                        <AlertDescription>
                            {{ kpiData.openIncidents }} incident(s) ouvert(s) sur ce chantier.
                        </AlertDescription>
                    </Alert>
                    <Alert v-else class="border-emerald-200 bg-emerald-50 text-emerald-900">
                        <AlertTitle>Chantier sous contrôle</AlertTitle>
                        <AlertDescription>
                            Aucun incident critique ou retard signalé.
                        </AlertDescription>
                    </Alert>
                </div>

                <div class="flex flex-wrap gap-2 border-b pb-2">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    type="button"
                    class="rounded-full px-3 py-1.5 text-xs font-medium"
                    :class="activeTab === tab.id ? 'bg-foreground text-background' : 'bg-muted text-muted-foreground'"
                    @click="activeTab = tab.id"
                >
                    {{ tab.label }}
                </button>
                </div>

                <div v-if="activeTab === 'contractors'" class="space-y-4">
                    <div class="rounded-xl border bg-card p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <SectionHeader title="Équipe affectée" />
                            <div class="flex items-center gap-2">
                                <span class="rounded-full bg-muted px-3 py-1 text-xs font-semibold text-muted-foreground">
                                    {{ project.contractors.length }} intervenant(s)
                                </span>
                                <Button type="button" size="sm" @click="openAssignContractor">Ajouter</Button>
                            </div>
                        </div>
                        <div v-if="project.contractors.length" class="mt-4 divide-y">
                            <div
                                v-for="contractor in project.contractors"
                                :key="contractor.id"
                                class="flex flex-wrap items-center justify-between gap-3 py-3"
                            >
                                <div>
                                    <p class="font-semibold">{{ contractor.name }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ contractor.role || 'Intervenant' }} · {{ contractor.company || 'Entreprise' }}
                                    </p>
                                    <div class="text-xs text-muted-foreground">
                                        {{ contractor.email || 'Email non renseigné' }}
                                    </div>
                                </div>
                                <Button type="button" size="sm" variant="destructive" @click="openRemoveContractor(contractor)">
                                    Retirer
                                </Button>
                            </div>
                        </div>
                        <EmptyState v-else title="Aucun intervenant" description="Ajoutez des artisans au chantier." />
                    </div>
                </div>

                <div v-if="activeTab === 'documents'" class="space-y-4">
                    <div class="rounded-xl border bg-card p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <SectionHeader title="Documents du chantier" />
                            <div class="flex items-center gap-2">
                                <span class="rounded-full bg-muted px-3 py-1 text-xs font-semibold text-muted-foreground">
                                    {{ project.documents.length }} document(s)
                                </span>
                                <Button type="button" size="sm" @click="openCreateDocument">Nouveau</Button>
                            </div>
                        </div>
                        <div v-if="project.documents.length" class="mt-4 divide-y">
                            <div
                                v-for="document in project.documents"
                                :key="document.id"
                                class="flex flex-wrap items-center justify-between gap-4 py-3"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-lg border bg-muted">
                                        <StatusIcon :status="document.status" />
                                    </div>
                                    <div>
                                        <p class="font-semibold">{{ document.title }}</p>
                                        <p class="text-sm text-muted-foreground">
                                            {{ document.category }} · {{ document.version }}
                                        </p>
                                        <div class="mt-1 flex items-center gap-2 text-xs text-muted-foreground">
                                            <StatusBadge :label="formatStatus(document.status)" :tone="statusTone(document.status)" />
                                            <span v-if="document.created_at">Ajouté le {{ formatDate(document.created_at) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <Button type="button" size="sm" variant="secondary" @click="openDocumentLightbox(document)">
                                        Prévisualiser
                                    </Button>
                                    <a
                                        v-if="document.media_url"
                                        :href="document.media_url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="rounded-md border px-3 py-1 text-xs font-medium"
                                    >
                                        Télécharger
                                    </a>
                                    <Button type="button" size="sm" variant="outline" @click="openEditDocument(document)">Modifier</Button>
                                    <Button type="button" size="sm" variant="destructive" @click="openDeleteDocument(document)">
                                        Supprimer
                                    </Button>
                                </div>
                            </div>
                        </div>
                        <EmptyState v-else title="Aucun document" description="Importez les plans et devis." />
                    </div>
                </div>

                <div v-if="activeTab === 'validations'" class="space-y-4">
                    <div class="rounded-xl border bg-card p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <SectionHeader title="Demandes de validation" />
                            <div class="flex items-center gap-2">
                                <span class="rounded-full bg-muted px-3 py-1 text-xs font-semibold text-muted-foreground">
                                    {{ project.validations.length }} demande(s)
                                </span>
                                <Button type="button" size="sm" @click="openCreateValidation">Nouvelle</Button>
                            </div>
                        </div>
                        <div v-if="project.validations.length" class="mt-4 divide-y">
                            <div
                                v-for="validation in project.validations"
                                :key="validation.id"
                                class="flex flex-wrap items-center justify-between gap-4 py-3"
                            >
                                <div>
                                    <div class="flex items-center gap-2">
                                        <StatusIcon :status="validation.status" />
                                        <p class="font-semibold">{{ validation.title }}</p>
                                    </div>
                                    <p class="text-sm text-muted-foreground">{{ validation.type }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <StatusBadge :label="formatStatus(validation.status)" :tone="statusTone(validation.status)" />
                                    <Button type="button" size="sm" variant="outline" @click="openEditValidation(validation)">Modifier</Button>
                                    <Button type="button" size="sm" variant="destructive" @click="openDeleteValidation(validation)">
                                        Supprimer
                                    </Button>
                                </div>
                            </div>
                        </div>
                        <EmptyState v-else title="Aucune validation" description="Les demandes apparaîtront ici." />
                    </div>
                </div>

                <div v-if="activeTab === 'incidents'" class="space-y-4">
                    <div class="rounded-xl border bg-card p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <SectionHeader title="Incidents" />
                            <div class="flex items-center gap-2">
                                <span class="rounded-full bg-muted px-3 py-1 text-xs font-semibold text-muted-foreground">
                                    {{ project.incidents.length }} incident(s)
                                </span>
                                <Button type="button" size="sm" @click="openCreateIncident">Nouvel incident</Button>
                            </div>
                        </div>
                        <div v-if="project.incidents.length" class="mt-4 divide-y">
                            <div
                                v-for="incident in project.incidents"
                                :key="incident.id"
                                class="flex flex-wrap items-center justify-between gap-4 py-3"
                            >
                                <div>
                                    <div class="flex items-center gap-2">
                                        <StatusIcon :status="incident.status" />
                                        <p class="font-semibold">{{ incident.title }}</p>
                                    </div>
                                    <p class="text-sm text-muted-foreground">
                                        {{ incident.description || 'Aucun détail' }}
                                    </p>
                                    <div class="text-xs text-muted-foreground">
                                        Impact: {{ incident.impact_days }} jour(s) ·
                                        {{ incident.impact_cost.toLocaleString('fr-FR') }} €
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Button type="button" size="sm" variant="outline" @click="openEditIncident(incident)">Modifier</Button>
                                    <Button type="button" size="sm" variant="destructive" @click="openDeleteIncident(incident)">
                                        Supprimer
                                    </Button>
                                </div>
                            </div>
                        </div>
                        <EmptyState v-else title="Aucun incident" description="Aucun retard signalé." />
                    </div>
                </div>

                <div v-if="activeTab === 'tasks'" class="space-y-4">
                    <div class="rounded-xl border bg-card p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <SectionHeader title="Checklist terrain" />
                            <div class="flex items-center gap-2">
                                <span class="rounded-full bg-muted px-3 py-1 text-xs font-semibold text-muted-foreground">
                                    {{ project.tasks.length }} tâche(s)
                                </span>
                                <Button type="button" size="sm" @click="openCreateTask">Nouvelle tâche</Button>
                            </div>
                        </div>
                        <div v-if="project.tasks.length" class="mt-4 divide-y">
                            <div
                                v-for="task in project.tasks"
                                :key="task.id"
                                class="flex flex-wrap items-center justify-between gap-4 py-3"
                            >
                                <div>
                                    <p class="font-semibold">{{ task.title }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ task.assigned_to || 'Non assigné' }} · {{ formatDate(task.due_date) }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <StatusBadge
                                        :label="formatStatus(task.status)"
                                        :tone="statusTone(task.status)"
                                    />
                                    <Button type="button" size="sm" variant="outline" @click="openComments('task', task.id)">
                                        Commentaires
                                    </Button>
                                    <Button type="button" size="sm" variant="outline" @click="openEditTask(task)">Modifier</Button>
                                    <Button type="button" size="sm" variant="destructive" @click="openDeleteTask(task)">
                                        Supprimer
                                    </Button>
                                </div>
                            </div>
                        </div>
                        <EmptyState v-else title="Aucune tâche" description="Ajoutez des tâches terrain." />
                    </div>
                </div>

                <div v-if="activeTab === 'communication'" class="space-y-6">
                    <div class="rounded-xl border bg-card p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <SectionHeader title="Messages chantier" />
                            <span class="rounded-full bg-muted px-3 py-1 text-xs font-semibold text-muted-foreground">
                                {{ project.messages.length }} message(s)
                            </span>
                        </div>
                        <div class="mt-4 space-y-3">
                            <div
                                v-for="message in project.messages"
                                :key="message.id"
                                class="rounded-lg border p-3 text-sm"
                            >
                                <div class="flex items-center justify-between gap-3">
                                    <p class="font-semibold">{{ message.author?.name || 'Utilisateur' }}</p>
                                    <span class="text-xs text-muted-foreground">{{ formatDate(message.created_at) }}</span>
                                </div>
                                <p class="mt-2 text-sm text-muted-foreground">{{ message.body }}</p>
                            </div>
                            <EmptyState
                                v-if="!project.messages.length"
                                title="Aucun message"
                                description="Commencez la discussion de chantier."
                            />
                        </div>
                        <form class="mt-4 grid gap-3" @submit.prevent="submitMessage">
                            <div class="grid gap-2">
                                <Label for="message-body">Nouveau message</Label>
                                <Input id="message-body" v-model="messageForm.body" placeholder="Votre message..." />
                            </div>
                            <div class="flex justify-end">
                                <Button type="submit" :disabled="messageForm.processing || !messageForm.body.trim()">
                                    Envoyer
                                </Button>
                            </div>
                        </form>
                    </div>

                    <div class="rounded-xl border bg-card p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <SectionHeader title="Historique" />
                            <span class="rounded-full bg-muted px-3 py-1 text-xs font-semibold text-muted-foreground">
                                {{ project.activities.length }} évènement(s)
                            </span>
                        </div>
                        <div class="mt-4 space-y-3">
                            <div
                                v-for="activity in project.activities"
                                :key="activity.id"
                                class="flex flex-wrap items-center justify-between gap-3 rounded-lg border p-3 text-sm"
                            >
                                <div>
                                    <p class="font-semibold">{{ activityLabel(activity) }}</p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ activity.actor?.name || 'Système' }}
                                    </p>
                                </div>
                                <span class="text-xs text-muted-foreground">{{ formatDate(activity.created_at) }}</span>
                            </div>
                            <EmptyState
                                v-if="!project.activities.length"
                                title="Aucun évènement"
                                description="Les actions du chantier apparaîtront ici."
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Dialog v-model:open="contractorAssignOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Affecter un intervenant</DialogTitle>
                    <DialogDescription>Sélectionnez un intervenant existant.</DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submitAssignContractor">
                    <div class="grid gap-2">
                        <Label for="contractor">Intervenant</Label>
                        <select id="contractor" v-model="contractorForm.contractor_id" class="rounded-lg border px-3 py-2 text-sm" required>
                            <option value="">Sélectionner</option>
                            <option v-for="contractor in contractorsCatalog" :key="contractor.id" :value="contractor.id">
                                {{ contractor.name }}{{ contractor.role ? ` · ${contractor.role}` : '' }}
                            </option>
                        </select>
                        <p v-if="contractorForm.errors.contractor_id" class="text-xs text-red-500">
                            {{ contractorForm.errors.contractor_id }}
                        </p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="assign-role">Rôle sur chantier</Label>
                        <Input id="assign-role" v-model="contractorForm.role" placeholder="Chef d'équipe" />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="secondary" @click="contractorAssignOpen = false">Annuler</Button>
                        <Button type="submit" :disabled="contractorForm.processing">Affecter</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="editProjectOpen">
            <DialogContent class="sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Éditer le chantier</DialogTitle>
                    <DialogDescription>Modifiez les informations principales du chantier.</DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submitEditProject">
                    <div class="grid gap-2">
                        <Label for="edit-name">Nom</Label>
                        <Input id="edit-name" v-model="projectForm.name" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-client">Client</Label>
                        <Input id="edit-client" v-model="projectForm.client_name" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-address">Adresse</Label>
                        <Input id="edit-address" v-model="projectForm.address" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-city">Ville</Label>
                        <Input id="edit-city" v-model="projectForm.city" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-status">Statut</Label>
                        <select id="edit-status" v-model="projectForm.status" class="rounded-lg border px-3 py-2 text-sm">
                            <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>
                    <div class="grid gap-2 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="edit-budget">Budget ({{ Number(projectForm.budget || 0).toLocaleString('fr-FR') }} €)</Label>
                            <Input id="edit-budget" v-model="projectForm.budget" type="number" min="0" step="100" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="edit-progress">Avancement (%)</Label>
                            <Input id="edit-progress" v-model="projectForm.progress_value" type="number" min="0" max="100" />
                        </div>
                    </div>
                    <div class="grid gap-2 md:grid-cols-2">
                        <label class="flex items-center gap-2 text-sm">
                            <input
                                id="edit-budget-alert"
                                v-model="projectForm.budget_alert_enabled"
                                type="checkbox"
                                class="h-4 w-4 rounded border"
                            />
                            <span>Alertes de dépassement</span>
                        </label>
                        <div class="grid gap-2">
                            <Label for="edit-budget-threshold">Seuil (%)</Label>
                            <Input
                                id="edit-budget-threshold"
                                v-model="projectForm.budget_alert_threshold"
                                type="number"
                                min="0"
                            />
                        </div>
                    </div>
                    <div class="grid gap-2 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="edit-starts">Début</Label>
                            <Input id="edit-starts" v-model="projectForm.start_date" type="date" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="edit-ends">Fin</Label>
                            <Input id="edit-ends" v-model="projectForm.end_date" type="date" />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="secondary" @click="editProjectOpen = false">Annuler</Button>
                        <Button type="submit" :disabled="projectForm.processing">Enregistrer</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="cancelProjectOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Annuler le chantier</DialogTitle>
                    <DialogDescription>
                        Cette action marque le chantier comme annulé. Merci d’indiquer la raison.
                    </DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submitCancelProject">
                    <div class="grid gap-2">
                        <Label for="cancel-reason">Raison</Label>
                        <Input id="cancel-reason" v-model="cancelForm.reason" placeholder="Ex: budget revu, client indisponible" />
                        <p v-if="cancelForm.errors.reason" class="text-xs text-red-500">{{ cancelForm.errors.reason }}</p>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="secondary" @click="cancelProjectOpen = false">Fermer</Button>
                        <Button type="submit" variant="destructive" :disabled="cancelForm.processing">
                            Confirmer l’annulation
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="contractorRemoveOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Retirer l'intervenant</DialogTitle>
                    <DialogDescription>Confirmer le retrait de l'intervenant du chantier.</DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button type="button" variant="secondary" @click="contractorRemoveOpen = false">Annuler</Button>
                    <Button type="button" variant="destructive" :disabled="contractorForm.processing" @click="submitRemoveContractor">
                        Retirer
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="documentCreateOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Nouveau document</DialogTitle>
                    <DialogDescription>Ajoutez un document pour ce chantier.</DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submitCreateDocument">
                    <div class="grid gap-2">
                        <Label for="doc-title">Titre</Label>
                        <Input id="doc-title" v-model="documentForm.title" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="doc-category">Catégorie</Label>
                        <Input id="doc-category" v-model="documentForm.category" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="doc-version">Version</Label>
                        <Input id="doc-version" v-model="documentForm.version" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="doc-status">Statut</Label>
                        <select id="doc-status" v-model="documentForm.status" class="rounded-lg border px-3 py-2 text-sm">
                            <option value="pending">En attente</option>
                            <option value="approved">Validé</option>
                            <option value="rejected">Refusé</option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="doc-file">Fichier</Label>
                        <Input
                            id="doc-file"
                            type="file"
                            accept=".pdf,.png,.jpg,.jpeg,.webp,.gif,.dwg,.dxf,.ifc"
                            @change="onDocumentFileChange"
                            required
                        />
                        <p class="text-xs text-muted-foreground">
                            Formats supportés : PDF, images, DWG/DXF, IFC.
                        </p>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="secondary" @click="documentCreateOpen = false">Annuler</Button>
                        <Button type="submit" :disabled="documentForm.processing">Créer</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="documentEditOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Modifier le document</DialogTitle>
                    <DialogDescription>Mettez à jour le document.</DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submitEditDocument">
                    <div class="grid gap-2">
                        <Label for="doc-edit-title">Titre</Label>
                        <Input id="doc-edit-title" v-model="documentForm.title" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="doc-edit-category">Catégorie</Label>
                        <Input id="doc-edit-category" v-model="documentForm.category" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="doc-edit-version">Version</Label>
                        <Input id="doc-edit-version" v-model="documentForm.version" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="doc-edit-status">Statut</Label>
                        <select id="doc-edit-status" v-model="documentForm.status" class="rounded-lg border px-3 py-2 text-sm">
                            <option value="pending">En attente</option>
                            <option value="approved">Validé</option>
                            <option value="rejected">Refusé</option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="doc-edit-file">Nouveau fichier</Label>
                        <Input
                            id="doc-edit-file"
                            type="file"
                            accept=".pdf,.png,.jpg,.jpeg,.webp,.gif,.dwg,.dxf,.ifc"
                            @change="onDocumentFileChange"
                        />
                        <p class="text-xs text-muted-foreground">
                            Formats supportés : PDF, images, DWG/DXF, IFC.
                        </p>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="secondary" @click="documentEditOpen = false">Annuler</Button>
                        <Button type="submit" :disabled="documentForm.processing">Enregistrer</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="documentDeleteOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Supprimer le document</DialogTitle>
                    <DialogDescription>Cette action est définitive.</DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button type="button" variant="secondary" @click="documentDeleteOpen = false">Annuler</Button>
                    <Button type="button" variant="destructive" :disabled="documentForm.processing" @click="submitDeleteDocument">
                        Supprimer
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <DocumentPreviewDialog v-model:open="documentLightboxOpen" :document="documentLightboxDocument" />

        <Dialog v-model:open="validationCreateOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Nouvelle validation</DialogTitle>
                    <DialogDescription>Créez une demande de validation.</DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submitCreateValidation">
                    <div class="grid gap-2">
                        <Label for="val-title">Titre</Label>
                        <Input id="val-title" v-model="validationForm.title" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="val-type">Type</Label>
                        <Input id="val-type" v-model="validationForm.type" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="val-status">Statut</Label>
                        <select id="val-status" v-model="validationForm.status" class="rounded-lg border px-3 py-2 text-sm">
                            <option value="pending">En attente</option>
                            <option value="approved">Validé</option>
                            <option value="rejected">Refusé</option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="val-requested-by">Demandé par</Label>
                        <Input id="val-requested-by" v-model="validationForm.requested_by" />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="secondary" @click="validationCreateOpen = false">Annuler</Button>
                        <Button type="submit" :disabled="validationForm.processing">Créer</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="validationEditOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Modifier la validation</DialogTitle>
                    <DialogDescription>Mettez à jour la demande.</DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submitEditValidation">
                    <div class="grid gap-2">
                        <Label for="val-edit-title">Titre</Label>
                        <Input id="val-edit-title" v-model="validationForm.title" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="val-edit-type">Type</Label>
                        <Input id="val-edit-type" v-model="validationForm.type" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="val-edit-status">Statut</Label>
                        <select id="val-edit-status" v-model="validationForm.status" class="rounded-lg border px-3 py-2 text-sm">
                            <option value="pending">En attente</option>
                            <option value="approved">Validé</option>
                            <option value="rejected">Refusé</option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="val-edit-requested-by">Demandé par</Label>
                        <Input id="val-edit-requested-by" v-model="validationForm.requested_by" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="val-edit-decided-by">Décidé par</Label>
                        <Input id="val-edit-decided-by" v-model="validationForm.decided_by" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="val-edit-decided-at">Date décision</Label>
                        <Input id="val-edit-decided-at" v-model="validationForm.decided_at" type="date" />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="secondary" @click="validationEditOpen = false">Annuler</Button>
                        <Button type="submit" :disabled="validationForm.processing">Enregistrer</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="validationDeleteOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Supprimer la validation</DialogTitle>
                    <DialogDescription>Cette action est définitive.</DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button type="button" variant="secondary" @click="validationDeleteOpen = false">Annuler</Button>
                    <Button type="button" variant="destructive" :disabled="validationForm.processing" @click="submitDeleteValidation">
                        Supprimer
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="incidentCreateOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Nouvel incident</DialogTitle>
                    <DialogDescription>Signalez un incident sur ce chantier.</DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submitCreateIncident">
                    <div class="grid gap-2">
                        <Label for="inc-title">Titre</Label>
                        <Input id="inc-title" v-model="incidentForm.title" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="inc-description">Description</Label>
                        <Input id="inc-description" v-model="incidentForm.description" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="inc-status">Statut</Label>
                        <select id="inc-status" v-model="incidentForm.status" class="rounded-lg border px-3 py-2 text-sm">
                            <option value="open">Ouvert</option>
                            <option value="resolved">Résolu</option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="inc-impact-days">Impact (jours)</Label>
                        <Input id="inc-impact-days" v-model="incidentForm.impact_days" type="number" min="0" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="inc-impact-cost">Impact (€)</Label>
                        <Input id="inc-impact-cost" v-model="incidentForm.impact_cost" type="number" min="0" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="inc-reported-by">Signalé par</Label>
                        <Input id="inc-reported-by" v-model="incidentForm.reported_by" />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="secondary" @click="incidentCreateOpen = false">Annuler</Button>
                        <Button type="submit" :disabled="incidentForm.processing">Créer</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="incidentEditOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Modifier l'incident</DialogTitle>
                    <DialogDescription>Mettez à jour l'incident.</DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submitEditIncident">
                    <div class="grid gap-2">
                        <Label for="inc-edit-title">Titre</Label>
                        <Input id="inc-edit-title" v-model="incidentForm.title" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="inc-edit-description">Description</Label>
                        <Input id="inc-edit-description" v-model="incidentForm.description" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="inc-edit-status">Statut</Label>
                        <select id="inc-edit-status" v-model="incidentForm.status" class="rounded-lg border px-3 py-2 text-sm">
                            <option value="open">Ouvert</option>
                            <option value="resolved">Résolu</option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="inc-edit-impact-days">Impact (jours)</Label>
                        <Input id="inc-edit-impact-days" v-model="incidentForm.impact_days" type="number" min="0" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="inc-edit-impact-cost">Impact (€)</Label>
                        <Input id="inc-edit-impact-cost" v-model="incidentForm.impact_cost" type="number" min="0" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="inc-edit-reported-by">Signalé par</Label>
                        <Input id="inc-edit-reported-by" v-model="incidentForm.reported_by" />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="secondary" @click="incidentEditOpen = false">Annuler</Button>
                        <Button type="submit" :disabled="incidentForm.processing">Enregistrer</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="incidentDeleteOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Supprimer l'incident</DialogTitle>
                    <DialogDescription>Cette action est définitive.</DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button type="button" variant="secondary" @click="incidentDeleteOpen = false">Annuler</Button>
                    <Button type="button" variant="destructive" :disabled="incidentForm.processing" @click="submitDeleteIncident">
                        Supprimer
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="taskCreateOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Nouvelle tâche</DialogTitle>
                    <DialogDescription>Ajoutez une tâche pour ce chantier.</DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submitCreateTask">
                    <div class="grid gap-2">
                        <Label for="task-title">Titre</Label>
                        <Input id="task-title" v-model="taskForm.title" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="task-status">Statut</Label>
                        <select id="task-status" v-model="taskForm.status" class="rounded-lg border px-3 py-2 text-sm">
                            <option value="pending">À faire</option>
                            <option value="in_progress">En cours</option>
                            <option value="done">Terminé</option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="task-assigned">Responsable</Label>
                        <Input id="task-assigned" v-model="taskForm.assigned_to" list="task-contractors" />
                        <datalist id="task-contractors">
                            <option v-for="contractor in contractorsCatalog" :key="contractor.id" :value="contractor.name">
                                {{ contractor.role ? `${contractor.name} · ${contractor.role}` : contractor.name }}
                            </option>
                        </datalist>
                    </div>
                    <div class="grid gap-2">
                        <Label for="task-due">Date</Label>
                        <Input id="task-due" v-model="taskForm.due_date" type="date" />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="secondary" @click="taskCreateOpen = false">Annuler</Button>
                        <Button type="submit" :disabled="taskForm.processing">Créer</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="taskEditOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Modifier la tâche</DialogTitle>
                    <DialogDescription>Mettez à jour la tâche.</DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submitEditTask">
                    <div class="grid gap-2">
                        <Label for="task-edit-title">Titre</Label>
                        <Input id="task-edit-title" v-model="taskForm.title" required />
                    </div>
                    <div class="grid gap-2">
                        <Label for="task-edit-status">Statut</Label>
                        <select id="task-edit-status" v-model="taskForm.status" class="rounded-lg border px-3 py-2 text-sm">
                            <option value="pending">À faire</option>
                            <option value="in_progress">En cours</option>
                            <option value="done">Terminé</option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="task-edit-assigned">Responsable</Label>
                        <Input id="task-edit-assigned" v-model="taskForm.assigned_to" list="task-contractors-edit" />
                        <datalist id="task-contractors-edit">
                            <option v-for="contractor in contractorsCatalog" :key="contractor.id" :value="contractor.name">
                                {{ contractor.role ? `${contractor.name} · ${contractor.role}` : contractor.name }}
                            </option>
                        </datalist>
                    </div>
                    <div class="grid gap-2">
                        <Label for="task-edit-due">Date</Label>
                        <Input id="task-edit-due" v-model="taskForm.due_date" type="date" />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="secondary" @click="taskEditOpen = false">Annuler</Button>
                        <Button type="submit" :disabled="taskForm.processing">Enregistrer</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="taskDeleteOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Supprimer la tâche</DialogTitle>
                    <DialogDescription>Cette action est définitive.</DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button type="button" variant="secondary" @click="taskDeleteOpen = false">Annuler</Button>
                    <Button type="button" variant="destructive" :disabled="taskForm.processing" @click="submitDeleteTask">
                        Supprimer
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="commentOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Commentaires</DialogTitle>
                    <DialogDescription>Suivi des échanges liés à la tâche.</DialogDescription>
                </DialogHeader>
                <div class="space-y-3 text-sm">
                    <div v-for="comment in currentComments" :key="comment.id" class="rounded-lg border p-3">
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-semibold">{{ comment.author?.name || 'Utilisateur' }}</p>
                            <span class="text-xs text-muted-foreground">{{ formatDate(comment.created_at) }}</span>
                        </div>
                        <p class="mt-2 text-sm text-muted-foreground">{{ comment.body }}</p>
                    </div>
                    <EmptyState
                        v-if="!currentComments.length"
                        title="Aucun commentaire"
                        description="Ajoutez un commentaire pour tracer les échanges."
                    />
                </div>
                <form class="mt-4 grid gap-3" @submit.prevent="submitComment">
                    <div class="grid gap-2">
                        <Label for="comment-body">Nouveau commentaire</Label>
                        <Input id="comment-body" v-model="commentForm.body" placeholder="Votre commentaire..." />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="secondary" @click="commentOpen = false">Annuler</Button>
                        <Button type="submit" :disabled="commentForm.processing || !commentForm.body.trim()">Publier</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

