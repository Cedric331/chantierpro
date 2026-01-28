import { computed } from 'vue';
import type { DataContextState } from './DataContext';
import { useDataContext } from './DataContext';

export const useChantierData = (context?: DataContextState) => {
    const resolved = context ?? useDataContext();

    const isGrid = computed(() => resolved.view === 'grid');
    const isList = computed(() => resolved.view === 'list');

    return {
        ...resolved,
        isGrid,
        isList,
    };
};

