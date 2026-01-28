<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue';
import SectionHeader from '@/components/SectionHeader.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { toast } from '@/lib/toast';
import { UserRound } from 'lucide-vue-next';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

type Contractor = {
    id: number;
    name: string;
    company?: string | null;
    role?: string | null;
    email?: string | null;
    phone?: string | null;
};

type Pagination<T> = { data: T[] };

const props = defineProps<{
    filters: Record<string, string | null>;
    contractors: Pagination<Contractor>;
}>();

const filters = reactive({ ...props.filters });
const createOpen = ref(false);
const editOpen = ref(false);
const deleteOpen = ref(false);
const selectedContractor = ref<Contractor | null>(null);

const form = useForm({
    name: '',
    company: '',
    role: '',
    email: '',
    phone: '',
    insurance_policy: '',
});

const tradeOptions = [
    'Électricien',
    'Plombier',
    'Maçon',
    'Carreleur',
    'Peintre',
    'Charpentier',
    'Couvreur',
    'Menuisier',
    'Terrassier',
    'Plaquiste',
    'Chauffagiste',
    'Façadier',
    'Étanchéiste',
    'Serrurier',
    'Grutier',
    'Cuisiniste',
    'Paysagiste',
    'Façadeur',
    'Diagnostiqueur',
    'Conducteur de travaux',
    'Chef de chantier',
];

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Intervenants', href: '/contractors' },
];

const onFilterChange = () => {
    router.get('/contractors', filters, { preserveState: true, replace: true });
};

const setRoleFilter = (role: string) => {
    filters.role = role;
    onFilterChange();
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
};

const setRoleValue = (role: string) => {
    form.role = role;
};

const openCreate = () => {
    resetForm();
    createOpen.value = true;
};

const openEdit = (contractor: Contractor) => {
    selectedContractor.value = contractor;
    form.name = contractor.name;
    form.company = contractor.company ?? '';
    form.role = contractor.role ?? '';
    form.email = contractor.email ?? '';
    form.phone = contractor.phone ?? '';
    form.insurance_policy = '';
    editOpen.value = true;
};

const openDelete = (contractor: Contractor) => {
    selectedContractor.value = contractor;
    deleteOpen.value = true;
};

const submitCreate = () => {
    form.post('/contractors', {
        onSuccess: () => {
            createOpen.value = false;
            resetForm();
            toast.success('Intervenant créé');
        },
        onError: () => toast.error('Impossible de créer l’intervenant'),
    });
};

const submitEdit = () => {
    if (!selectedContractor.value) return;
    form.put(`/contractors/${selectedContractor.value.id}`, {
        onSuccess: () => {
            editOpen.value = false;
            selectedContractor.value = null;
            resetForm();
            toast.success('Intervenant mis à jour');
        },
        onError: () => toast.error('Impossible de mettre à jour l’intervenant'),
    });
};

const submitDelete = () => {
    if (!selectedContractor.value) return;
    form.delete(`/contractors/${selectedContractor.value.id}`, {
        onSuccess: () => {
            deleteOpen.value = false;
            selectedContractor.value = null;
            toast.success('Intervenant supprimé');
        },
        onError: () => toast.error('Impossible de supprimer l’intervenant'),
    });
};
</script>

<template>
    <Head title="Intervenants" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <SectionHeader title="Annuaire des intervenants" />
                <Button type="button" @click="openCreate">Nouvel intervenant</Button>
            </div>

            <div class="grid gap-4 rounded-xl border bg-card p-4 md:grid-cols-3">
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-muted-foreground">Recherche</label>
                    <input
                        v-model="filters.search"
                        type="text"
                        class="mt-2 w-full rounded-lg border px-3 py-2 text-sm"
                        placeholder="Nom, entreprise, email"
                        @change="onFilterChange"
                    />
                </div>
                <div>
                    <label class="text-xs font-semibold text-muted-foreground">Rôle</label>
                    <input
                        v-model="filters.role"
                        type="text"
                        list="trade-options-filter"
                        class="mt-2 w-full rounded-lg border px-3 py-2 text-sm"
                        placeholder="Électricien, plombier..."
                        @change="onFilterChange"
                    />
                    <datalist id="trade-options-filter">
                        <option v-for="trade in tradeOptions" :key="trade" :value="trade" />
                    </datalist>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <button
                            v-for="trade in tradeOptions.slice(0, 6)"
                            :key="trade"
                            type="button"
                            class="rounded-full border px-3 py-1 text-xs text-muted-foreground hover:bg-muted"
                            @click="setRoleFilter(trade)"
                        >
                            {{ trade }}
                        </button>
                        <button
                            type="button"
                            class="rounded-full border px-3 py-1 text-xs text-muted-foreground hover:bg-muted"
                            @click="setRoleFilter('')"
                        >
                            Tout
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="contractors.data.length" class="grid gap-4 md:grid-cols-2">
                <div
                    v-for="contractor in contractors.data"
                    :key="contractor.id"
                    class="rounded-xl border bg-card p-4"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <UserRound class="size-4 text-muted-foreground" />
                            <div>
                                <p class="font-semibold">{{ contractor.name }}</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ contractor.company || 'Entreprise non renseignée' }}
                                </p>
                            </div>
                        </div>
                        <span class="rounded-full bg-muted px-3 py-1 text-xs font-semibold text-muted-foreground">
                            {{ contractor.role || 'Intervenant' }}
                        </span>
                    </div>
                    <div class="mt-3 text-xs text-muted-foreground">
                        {{ contractor.email || 'Email non renseigné' }}
                    </div>
                    <div class="text-xs text-muted-foreground">
                        {{ contractor.phone || 'Téléphone non renseigné' }}
                    </div>
                    <div class="mt-4 flex items-center justify-end gap-2">
                        <Button type="button" variant="secondary" @click="openEdit(contractor)">Modifier</Button>
                        <Button type="button" variant="destructive" @click="openDelete(contractor)">Supprimer</Button>
                    </div>
                </div>
            </div>

            <EmptyState
                v-else
                title="Aucun intervenant"
                description="Ajoutez vos artisans pour les assigner aux chantiers."
            />
        </div>

        <Dialog v-model:open="createOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Nouvel intervenant</DialogTitle>
                    <DialogDescription>Ajoutez un artisan à votre annuaire.</DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submitCreate">
                    <div class="grid gap-2">
                        <Label for="name">Nom</Label>
                        <Input id="name" v-model="form.name" placeholder="Jean Moreau" required />
                        <p v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="company">Entreprise</Label>
                        <Input id="company" v-model="form.company" placeholder="Moreau Électricité" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="role">Métier</Label>
                        <Input id="role" v-model="form.role" list="trade-options" placeholder="Électricien" />
                        <datalist id="trade-options">
                            <option v-for="trade in tradeOptions" :key="trade" :value="trade" />
                        </datalist>
                        <div class="mt-1 flex flex-wrap gap-2">
                            <button
                                v-for="trade in tradeOptions.slice(0, 6)"
                                :key="trade"
                                type="button"
                                class="rounded-full border px-3 py-1 text-xs text-muted-foreground hover:bg-muted"
                                @click="setRoleValue(trade)"
                            >
                                {{ trade }}
                            </button>
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input id="email" v-model="form.email" type="email" placeholder="contact@email.com" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="phone">Téléphone</Label>
                        <Input id="phone" v-model="form.phone" placeholder="06 00 00 00 00" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="insurance">Assurance</Label>
                        <Input id="insurance" v-model="form.insurance_policy" placeholder="POL-12345" />
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
                    <DialogTitle>Modifier l'intervenant</DialogTitle>
                    <DialogDescription>Mettez à jour les informations.</DialogDescription>
                </DialogHeader>
                <form class="grid gap-4" @submit.prevent="submitEdit">
                    <div class="grid gap-2">
                        <Label for="edit-name">Nom</Label>
                        <Input id="edit-name" v-model="form.name" required />
                        <p v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-company">Entreprise</Label>
                        <Input id="edit-company" v-model="form.company" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-role">Métier</Label>
                        <Input id="edit-role" v-model="form.role" list="trade-options-edit" />
                        <datalist id="trade-options-edit">
                            <option v-for="trade in tradeOptions" :key="trade" :value="trade" />
                        </datalist>
                        <div class="mt-1 flex flex-wrap gap-2">
                            <button
                                v-for="trade in tradeOptions.slice(0, 6)"
                                :key="trade"
                                type="button"
                                class="rounded-full border px-3 py-1 text-xs text-muted-foreground hover:bg-muted"
                                @click="setRoleValue(trade)"
                            >
                                {{ trade }}
                            </button>
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-email">Email</Label>
                        <Input id="edit-email" v-model="form.email" type="email" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-phone">Téléphone</Label>
                        <Input id="edit-phone" v-model="form.phone" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-insurance">Assurance</Label>
                        <Input id="edit-insurance" v-model="form.insurance_policy" />
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
                    <DialogTitle>Supprimer l'intervenant</DialogTitle>
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

