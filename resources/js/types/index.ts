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
    } | null;
    roles?: string[];
    sidebarOpen: boolean;
    [key: string]: unknown;
};
