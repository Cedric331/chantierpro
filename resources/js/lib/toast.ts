import { notify } from '@kyvg/vue3-notification';

export const toast = {
    success: (message: string) => notify({ type: 'success', text: message, duration: 3500 }),
    error: (message: string) => notify({ type: 'error', text: message, duration: 4500 }),
    info: (message: string) => notify({ type: 'info', text: message, duration: 3500 }),
};

