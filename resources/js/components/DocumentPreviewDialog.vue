<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import * as pdfjsLib from 'pdfjs-dist/legacy/build/pdf';
import pdfjsWorker from 'pdfjs-dist/legacy/build/pdf.worker.min.mjs?url';
import DxfParser from 'dxf-parser';
import { Viewer } from 'three-dxf';
import * as THREE from 'three';
import { FontLoader } from 'three/examples/jsm/loaders/FontLoader.js';
import dxfFontUrl from 'three/examples/fonts/helvetiker_regular.typeface.json?url';
import { IfcViewerAPI } from 'web-ifc-viewer';

pdfjsLib.GlobalWorkerOptions.workerSrc = pdfjsWorker;

type DocumentPreview = {
    title?: string | null;
    category?: string | null;
    media_url?: string | null;
};

const props = defineProps<{
    open: boolean;
    document: DocumentPreview | null;
}>();

const emit = defineEmits<{
    (event: 'update:open', value: boolean): void;
}>();

const canvasRef = ref<HTMLCanvasElement | null>(null);
const dxfContainerRef = ref<HTMLDivElement | null>(null);
const ifcContainerRef = ref<HTMLDivElement | null>(null);
let ifcViewer: IfcViewerAPI | null = null;
let dxfResizeObserver: ResizeObserver | null = null;
type DxfViewer = {
    resize: (width: number, height: number) => void;
};
let dxfViewer: DxfViewer | null = null;
let dxfFont: ReturnType<FontLoader['parse']> | null = null;
let dxfFontPromise: Promise<ReturnType<FontLoader['parse']>> | null = null;
let dxfLoadController: AbortController | null = null;
let dxfLoadToken = 0;
const loadDxfFont = async () => {
    if (dxfFont) return dxfFont;
    if (!dxfFontPromise) {
        dxfFontPromise = fetch(dxfFontUrl)
            .then((response) => {
                if (!response.ok) {
                    throw new Error('DXF font fetch failed');
                }
                return response.json();
            })
            .then((json) => {
                const loader = new FontLoader();
                dxfFont = loader.parse(json);
                return dxfFont;
            })
            .catch((error) => {
                dxfFontPromise = null;
                throw error;
            });
    }
    return dxfFontPromise;
};
const pdfDoc = ref<pdfjsLib.PDFDocumentProxy | null>(null);
const pageNumber = ref(1);
const pageCount = ref(1);
const loading = ref(false);
const dxfLoading = ref(false);
const loadError = ref('');
const useIframe = ref(false);

const fileUrl = computed(() => props.document?.media_url ?? '');
const extension = computed(() => {
    const parts = fileUrl.value.split('?')[0].split('.');
    return parts.length > 1 ? parts[parts.length - 1].toLowerCase() : '';
});

const isPdf = computed(() => extension.value === 'pdf');
const isDxf = computed(() => extension.value === 'dxf');
const isIfc = computed(() => extension.value === 'ifc');
const isImage = computed(() =>
    ['png', 'jpg', 'jpeg', 'webp', 'gif'].includes(extension.value),
);
const isCad = computed(() => ['dwg'].includes(extension.value));

const renderPage = async () => {
    if (!pdfDoc.value || !canvasRef.value) return;
    const page = await pdfDoc.value.getPage(pageNumber.value);
    const viewport = page.getViewport({ scale: 1.2 });
    const canvas = canvasRef.value;
    const context = canvas.getContext('2d');

    canvas.height = viewport.height;
    canvas.width = viewport.width;

    if (!context) return;
    await page.render({ canvasContext: context, viewport }).promise;
};

const loadPdf = async () => {
    if (!fileUrl.value) return;
    loading.value = true;
    loadError.value = '';
    useIframe.value = false;
    try {
        const doc = await pdfjsLib
            .getDocument({
                url: fileUrl.value,
                withCredentials: true,
            })
            .promise;
        pdfDoc.value = doc;
        pageCount.value = doc.numPages;
        pageNumber.value = 1;
        await renderPage();
    } catch (error) {
        loadError.value = 'Impossible de prévisualiser ce PDF.';
        useIframe.value = true;
    } finally {
        loading.value = false;
    }
};

const resetPdf = () => {
    pdfDoc.value = null;
    pageNumber.value = 1;
    pageCount.value = 1;
    useIframe.value = false;
};

const resetDxf = () => {
    if (dxfLoadController) {
        dxfLoadController.abort();
        dxfLoadController = null;
    }
    if (dxfResizeObserver) {
        dxfResizeObserver.disconnect();
        dxfResizeObserver = null;
    }
    dxfViewer = null;
    if (dxfContainerRef.value) {
        dxfContainerRef.value.innerHTML = '';
    }
};

const loadDxf = async () => {
    if (!fileUrl.value) return;
    resetDxf();
    loadError.value = '';
    dxfLoading.value = true;
    const currentToken = ++dxfLoadToken;
    await nextTick();
    try {
        const container = dxfContainerRef.value;
        if (!container) {
            return;
        }
        dxfLoadController = new AbortController();
        const response = await fetch(fileUrl.value, {
            credentials: 'include',
            signal: dxfLoadController.signal,
        });
        if (!response.ok) {
            throw new Error('DXF fetch failed');
        }
        const dxfText = await response.text();
        const parser = new DxfParser();
        type DxfEntity = { inPaperSpace?: boolean };
        type DxfData = { entities?: DxfEntity[] } & Record<string, unknown>;
        const dxfData = parser.parseSync(dxfText) as DxfData;
        const entities = dxfData.entities ?? [];
        const modelEntities = entities.filter((entity) => !entity.inPaperSpace);
        const dxfDataForViewer = modelEntities.length
            ? { ...dxfData, entities: modelEntities }
            : dxfData;
        if (currentToken !== dxfLoadToken) {
            return;
        }
        try {
            dxfFont = await loadDxfFont();
        } catch (error) {
            dxfFont = null;
            console.warn('DXF font load failed, rendering without text.', error);
        }
        if (currentToken !== dxfLoadToken) {
            return;
        }
        const width = container.clientWidth || 1;
        const height = container.clientHeight || 1;
        dxfViewer = new Viewer(dxfDataForViewer, container, width, height, dxfFont) as DxfViewer;
        dxfResizeObserver = new ResizeObserver(() => {
            const nextWidth = container.clientWidth || 1;
            const nextHeight = container.clientHeight || 1;
            dxfViewer?.resize(nextWidth, nextHeight);
        });
        dxfResizeObserver.observe(container);
    } catch (error) {
        resetDxf();
        console.error('DXF preview failed.', error);
        loadError.value = 'Impossible de prévisualiser ce DXF.';
    } finally {
        dxfLoading.value = false;
    }
};

const resetIfc = () => {
    if (ifcViewer) {
        try {
            ifcViewer.dispose();
        } catch (error) {
            // ignore
        }
        ifcViewer = null;
    }
};

const close = () => emit('update:open', false);

const nextPage = async () => {
    if (pageNumber.value >= pageCount.value) return;
    pageNumber.value += 1;
    await renderPage();
};

const prevPage = async () => {
    if (pageNumber.value <= 1) return;
    pageNumber.value -= 1;
    await renderPage();
};

watch(
    () => props.open,
    async (open) => {
        if (!open) {
            resetPdf();
            resetDxf();
            resetIfc();
            return;
        }
        if (isPdf.value) {
            await loadPdf();
        }
        if (isDxf.value) {
            await loadDxf();
        }
        if (isIfc.value && ifcContainerRef.value && fileUrl.value) {
            resetIfc();
            try {
                ifcViewer = new IfcViewerAPI({
                    container: ifcContainerRef.value,
                    backgroundColor: new THREE.Color(0xffffff),
                });
                ifcViewer.grid.setGrid();
                ifcViewer.axes.setAxes();
                ifcViewer.IFC.setWasmPath('https://unpkg.com/web-ifc@0.0.47/');
                await ifcViewer.IFC.loadIfcUrl(fileUrl.value);
            } catch (error) {
                loadError.value = 'Impossible de prévisualiser ce IFC.';
            }
        }
    },
);

watch(
    () => fileUrl.value,
    async () => {
        if (props.open && isPdf.value) {
            await loadPdf();
        }
        if (props.open && isDxf.value) {
            await loadDxf();
        }
        if (props.open && isIfc.value && ifcContainerRef.value && fileUrl.value) {
            resetIfc();
            try {
                ifcViewer = new IfcViewerAPI({
                    container: ifcContainerRef.value,
                    backgroundColor: new THREE.Color(0xffffff),
                });
                ifcViewer.grid.setGrid();
                ifcViewer.axes.setAxes();
                ifcViewer.IFC.setWasmPath('https://unpkg.com/web-ifc@0.0.47/');
                await ifcViewer.IFC.loadIfcUrl(fileUrl.value);
            } catch (error) {
                loadError.value = 'Impossible de prévisualiser ce IFC.';
            }
        }
    },
);

onBeforeUnmount(() => {
    resetPdf();
    resetDxf();
    resetIfc();
});
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="w-[98vw] max-w-[98vw] sm:max-w-[98vw] h-[95vh] max-h-[95vh]">
            <DialogHeader>
                <DialogTitle>{{ document?.title || 'Document' }}</DialogTitle>
                <DialogDescription>
                    {{ document?.category }}
                    <span v-if="extension" class="ml-2 uppercase">· {{ extension }}</span>
                </DialogDescription>
            </DialogHeader>

            <div class="rounded-lg bg-muted p-3">
                <div v-if="isImage && fileUrl" class="flex items-center justify-center">
                    <img :src="fileUrl" alt="Document" class="max-h-[78vh] w-full object-contain rounded-md" />
                </div>
                <div v-else-if="isPdf" class="flex flex-col items-center gap-3">
                    <p v-if="loading" class="text-sm text-muted-foreground">Chargement…</p>
                    <p v-if="loadError && !useIframe" class="text-sm text-red-500">{{ loadError }}</p>
                    <canvas v-show="!loading && !loadError" ref="canvasRef" class="max-h-[78vh] w-full rounded-md bg-white" />
                    <div v-if="!loading && !loadError" class="flex items-center gap-2 text-xs text-muted-foreground">
                        <Button type="button" variant="secondary" size="sm" @click="prevPage" :disabled="pageNumber <= 1">
                            Page précédente
                        </Button>
                        <span>Page {{ pageNumber }} / {{ pageCount }}</span>
                        <Button type="button" variant="secondary" size="sm" @click="nextPage" :disabled="pageNumber >= pageCount">
                            Page suivante
                        </Button>
                    </div>
                    <iframe
                        v-else-if="useIframe && fileUrl"
                        :src="fileUrl"
                        class="h-[78vh] w-full rounded-md bg-white"
                    />
                </div>
                <div v-else-if="isDxf" class="space-y-2 text-sm text-muted-foreground">
                    <p v-if="dxfLoading" class="text-sm text-muted-foreground">Chargement…</p>
                    <p v-if="loadError" class="text-sm text-red-500">{{ loadError }}</p>
                    <div
                        ref="dxfContainerRef"
                        class="h-[78vh] w-full overflow-hidden rounded-md bg-white"
                    />
                </div>
                <div v-else-if="isIfc" class="space-y-2 text-sm text-muted-foreground">
                    <div
                        ref="ifcContainerRef"
                        class="h-[78vh] w-full overflow-hidden rounded-md bg-white"
                    />
                </div>
                <div v-else-if="isCad" class="space-y-2 text-sm text-muted-foreground">
                    <p>
                        Ce format AutoCAD/BIM n’est pas prévisualisable directement dans le navigateur.
                    </p>
                    <p>
                        Utilisez un logiciel compatible (AutoCAD, Revit, Navisworks, DWG TrueView, IFC viewer) ou
                        ouvrez le fichier dans un nouvel onglet.
                    </p>
                </div>
                <p v-else class="text-sm text-muted-foreground">Prévisualisation non disponible.</p>
            </div>

            <DialogFooter>
                <a
                    v-if="fileUrl"
                    :href="fileUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-sm font-medium underline decoration-neutral-300 underline-offset-4"
                >
                    Ouvrir dans un nouvel onglet
                </a>
                <Button type="button" variant="secondary" @click="close">Fermer</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

