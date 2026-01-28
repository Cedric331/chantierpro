export * from './auth';
export * from './navigation';
export * from './ui';

import type { Auth } from './auth';

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    auth: Auth;
    account?: {
        id: number;
        name: string;
        slug: string;
        trial_ends_at?: string | null;
        address?: string | null;
        city?: string | null;
        phone?: string | null;
    } | null;
    roles?: string[];
    unreadNotificationsCount?: number;
    sidebarOpen: boolean;
    [key: string]: unknown;
};
