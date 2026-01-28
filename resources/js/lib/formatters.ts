export const formatDate = (value?: string | null): string => {
    if (!value) return '—';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return new Intl.DateTimeFormat('fr-FR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(date);
};

export const formatStatus = (value?: string | null): string => {
    switch (value) {
        case 'preparation':
            return 'Préparation';
        case 'in_progress':
            return 'En cours';
        case 'delayed':
            return 'En retard';
        case 'completed':
            return 'Terminé';
        case 'cancelled':
            return 'Annulé';
        case 'pending':
            return 'En attente';
        case 'approved':
            return 'Validé';
        case 'rejected':
            return 'Refusé';
        case 'open':
            return 'Ouvert';
        case 'resolved':
            return 'Résolu';
        case 'done':
            return 'Terminé';
        default:
            return value ?? '—';
    }
};

export const statusTone = (value?: string | null): 'default' | 'success' | 'warning' | 'danger' | 'info' => {
    switch (value) {
        case 'approved':
        case 'completed':
        case 'done':
        case 'resolved':
            return 'success';
        case 'pending':
        case 'preparation':
            return 'warning';
        case 'delayed':
        case 'rejected':
        case 'open':
        case 'cancelled':
            return 'danger';
        case 'in_progress':
            return 'info';
        default:
            return 'default';
    }
};

