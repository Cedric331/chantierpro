<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue';
import SectionHeader from '@/components/SectionHeader.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { toast } from '@/lib/toast';

type NotificationItem = {
    id: string;
    type: string;
    data: Record<string, unknown>;
    read_at: string | null;
    created_at: string;
};

type Pagination<T> = {
    data: T[];
};

const props = defineProps<{
    notifications: Pagination<NotificationItem>;
    unreadCount: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Notifications', href: '/notifications' }];

const localNotifications = ref<NotificationItem[]>([]);

watch(
    () => props.notifications.data,
    (value) => {
        localNotifications.value = [...value];
    },
    { immediate: true },
);

const unreadCount = computed(() =>
    localNotifications.value.filter((item) => !item.read_at).length,
);

const resolveNotification = (item: NotificationItem) => {
    const type = item.type;
    const data = item.data as Record<string, unknown>;

    if (type.includes('DecisionLogged')) {
        return {
            title: 'Décision enregistrée',
            description: String(data.title ?? 'Nouvelle décision'),
            href: data.project_id ? `/projects/${data.project_id}` : '/decisions',
        };
    }
    if (type.includes('IncidentReported')) {
        return {
            title: 'Incident signalé',
            description: String(data.title ?? 'Nouvel incident'),
            href: data.project_id ? `/projects/${data.project_id}` : '/incidents',
        };
    }
    if (type.includes('ValidationRequested')) {
        return {
            title: 'Validation en attente',
            description: String(data.title ?? 'Nouvelle validation'),
            href: data.project_id ? `/projects/${data.project_id}` : '/validations',
        };
    }

    return {
        title: 'Notification',
        description: String(data.title ?? 'Mise à jour'),
        href: '/dashboard',
    };
};

const markAsRead = (item: NotificationItem) => {
    router.patch(`/notifications/${item.id}`, undefined, {
        preserveScroll: true,
        onSuccess: () => {
            item.read_at = new Date().toISOString();
            router.reload({ only: ['unreadNotificationsCount'] });
        },
        onError: () => toast.error("Impossible de marquer comme lu"),
    });
};

const markAllRead = () => {
    router.patch('/notifications', undefined, {
        preserveScroll: true,
        onSuccess: () => {
            localNotifications.value = localNotifications.value.map((item) => ({
                ...item,
                read_at: item.read_at ?? new Date().toISOString(),
            }));
            router.reload({ only: ['unreadNotificationsCount'] });
            toast.success('Toutes les notifications sont marquées comme lues');
        },
        onError: () => toast.error('Impossible de tout marquer comme lu'),
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Notifications" />

        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <SectionHeader title="Notifications" />
                <Button
                    v-if="unreadCount > 0"
                    type="button"
                    variant="secondary"
                    @click="markAllRead"
                >
                    Tout marquer comme lu
                </Button>
            </div>

            <div v-if="localNotifications.length" class="space-y-3">
                <div
                    v-for="item in localNotifications"
                    :key="item.id"
                    class="flex flex-col gap-4 rounded-xl border bg-card p-4 md:flex-row md:items-center md:justify-between"
                >
                    <div>
                        <p class="text-sm font-semibold">
                            {{ resolveNotification(item).title }}
                        </p>
                        <p class="text-sm text-muted-foreground">
                            {{ resolveNotification(item).description }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a
                            class="text-sm font-medium text-foreground underline decoration-muted-foreground/60 underline-offset-4"
                            :href="resolveNotification(item).href"
                        >
                            Voir le détail
                        </a>
                        <span
                            v-if="!item.read_at"
                            class="rounded-full bg-emerald-100 px-2 py-1 text-xs text-emerald-700"
                        >
                            Nouveau
                        </span>
                        <Button
                            v-if="!item.read_at"
                            type="button"
                            variant="secondary"
                            @click="markAsRead(item)"
                        >
                            Marquer comme lu
                        </Button>
                    </div>
                </div>
            </div>

            <EmptyState
                v-else
                title="Aucune notification"
                description="Les dernières activités apparaîtront ici."
            />
        </div>
    </AppLayout>
</template>

