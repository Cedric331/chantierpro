import { inject, provide, reactive } from 'vue';

export type DataContextState = {
    filters: Record<string, string | number | null>;
    view: 'grid' | 'list';
    setFilter: (key: string, value: string | number | null) => void;
    setFilters: (next: Record<string, string | number | null>) => void;
    setView: (view: 'grid' | 'list') => void;
};

const DataContextKey = Symbol('DataContext');

export const provideDataContext = (
    initialFilters: Record<string, string | number | null> = {},
    initialView: 'grid' | 'list' = 'grid',
): DataContextState => {
    const context = reactive({
        filters: { ...initialFilters },
        view: initialView,
    }) as DataContextState;

    context.setFilter = (key: string, value: string | number | null) => {
        context.filters[key] = value;
    };

    context.setFilters = (next: Record<string, string | number | null>) => {
        Object.keys(context.filters).forEach((key) => {
            delete context.filters[key];
        });
        Object.assign(context.filters, next);
    };

    context.setView = (view: 'grid' | 'list') => {
        context.view = view;
    };

    provide(DataContextKey, context);

    return context;
};

export const useDataContext = (): DataContextState => {
    const context = inject<DataContextState>(DataContextKey);

    if (!context) {
        throw new Error('DataContext not available');
    }

    return context;
};

