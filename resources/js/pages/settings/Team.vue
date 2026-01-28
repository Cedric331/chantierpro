<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { toast } from '@/lib/toast';

type Member = {
    id: number;
    name: string;
    email: string;
    role: string | null;
};

type Props = {
    account: { id: number; name: string };
    members: Member[];
    currentRole: string | null;
    roleOptions: { value: string; label: string }[];
    currentUserId: number;
};

const props = defineProps<Props>();

const localMembers = ref<Member[]>([]);
const isOwner = computed(() => props.currentRole === 'owner');

watch(
    () => props.members,
    (value) => {
        localMembers.value = [...value];
    },
    { immediate: true },
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Paramètres', href: '/settings/profile' },
    { title: 'Équipe', href: '/settings/team' },
];

const createForm = useForm({
    name: '',
    email: '',
    password: '',
    role: 'collaborator',
});

const roleForm = useForm({
    role: 'collaborator',
});

const submitCreate = () => {
    createForm.post('/settings/team', {
        onSuccess: () => {
            createForm.reset();
            createForm.clearErrors();
            toast.success('Utilisateur ajouté');
        },
        onError: () => toast.error("Impossible d'ajouter l'utilisateur"),
    });
};

const updateRole = (member: Member, role: string) => {
    roleForm.role = role;
    roleForm.patch(`/settings/team/${member.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            const target = localMembers.value.find((item) => item.id === member.id);
            if (target) {
                target.role = role;
            }
            toast.success('Rôle mis à jour');
        },
        onError: () => toast.error("Impossible de modifier le rôle"),
    });
};

const roleLabel = (role?: string | null) => {
    const match = props.roleOptions.find((option) => option.value === role);
    return match?.label ?? 'Collaborateur';
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Équipe" />

        <SettingsLayout>
            <div class="flex flex-col gap-8">
                <Heading
                    variant="small"
                    title="Utilisateurs du compte"
                    description="Gérez les accès des collaborateurs."
                />

                <div class="grid gap-6 2xl:grid-cols-[minmax(0,1fr)_520px]">
                    <div class="rounded-xl border bg-card p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <p class="text-sm font-medium text-muted-foreground">
                                Compte : {{ account.name }}
                            </p>
                            <span class="rounded-full bg-muted px-3 py-1 text-xs">
                                Votre rôle : {{ roleLabel(currentRole) }}
                            </span>
                        </div>

                        <div class="mt-4 max-h-[420px] space-y-3 overflow-y-auto pr-1 lg:max-h-[620px]">
                            <div
                                v-for="member in localMembers"
                                :key="member.id"
                                class="flex flex-col gap-3 rounded-lg border p-3 md:flex-row md:items-center md:justify-between"
                            >
                                <div>
                                    <p class="font-medium">{{ member.name }}</p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ member.email }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <select
                                        class="rounded-lg border px-3 py-2 text-sm"
                                        :disabled="!isOwner || member.id === currentUserId"
                                        :value="member.role ?? 'collaborator'"
                                        @change="updateRole(member, ($event.target as HTMLSelectElement).value)"
                                    >
                                        <option
                                            v-for="option in roleOptions"
                                            :key="option.value"
                                            :value="option.value"
                                        >
                                            {{ option.label }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <InputError class="mt-2" :message="roleForm.errors.role" />
                    </div>

                    <div class="lg:sticky lg:top-24 lg:self-start">
                        <div v-if="isOwner" class="rounded-xl border bg-card p-4">
                            <Heading
                                variant="small"
                                title="Ajouter un utilisateur"
                                description="Invitez un collaborateur sur le compte."
                            />
                            <form class="mt-4 grid gap-4" @submit.prevent="submitCreate">
                                <div class="grid gap-2">
                                    <Label for="member-name">Nom</Label>
                                    <Input
                                        id="member-name"
                                        v-model="createForm.name"
                                        placeholder="Nom complet"
                                        required
                                    />
                                    <InputError :message="createForm.errors.name" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="member-email">Adresse e-mail</Label>
                                    <Input
                                        id="member-email"
                                        v-model="createForm.email"
                                        type="email"
                                        placeholder="email@exemple.com"
                                        required
                                    />
                                    <InputError :message="createForm.errors.email" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="member-password">Mot de passe temporaire</Label>
                                    <Input
                                        id="member-password"
                                        v-model="createForm.password"
                                        type="password"
                                        placeholder="Mot de passe"
                                        required
                                    />
                                    <InputError :message="createForm.errors.password" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="member-role">Rôle</Label>
                                    <select
                                        id="member-role"
                                        v-model="createForm.role"
                                        class="rounded-lg border px-3 py-2 text-sm"
                                    >
                                        <option
                                            v-for="option in roleOptions"
                                            :key="option.value"
                                            :value="option.value"
                                        >
                                            {{ option.label }}
                                        </option>
                                    </select>
                                    <InputError :message="createForm.errors.role" />
                                </div>
                                <div class="flex items-center gap-3">
                                    <Button type="submit" :disabled="createForm.processing">
                                        Ajouter
                                    </Button>
                                </div>
                            </form>
                        </div>

                        <div
                            v-else
                            class="rounded-xl border bg-muted/40 p-4 text-sm text-muted-foreground"
                        >
                            Seuls les dirigeants peuvent ajouter des utilisateurs ou modifier les rôles.
                        </div>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

