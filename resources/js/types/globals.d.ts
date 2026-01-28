import type { AppPageProps } from './index';

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

declare module '@inertiajs/core' {
    interface PageProps extends InertiaPageProps, AppPageProps {}
}

declare module 'vue' {
    interface ComponentCustomProperties {
        $inertia: typeof Router;
        $page: Page;
        $headManager: ReturnType<typeof createHeadManager>;
    }
}

declare module 'three-dxf' {
    export function Viewer(
        data: unknown,
        parent: HTMLElement,
        width?: number,
        height?: number,
        font?: unknown,
    ): {
        resize: (width: number, height: number) => void;
    };
}

declare module 'dxf-parser' {
    export default class DxfParser {
        parseSync: (content: string) => unknown;
    }
}

declare module 'three/examples/jsm/loaders/FontLoader.js' {
    export class FontLoader {
        parse: (json: unknown) => unknown;
    }
}

declare module 'three/examples/jsm/geometries/TextGeometry.js' {
    export class TextGeometry {}
}
