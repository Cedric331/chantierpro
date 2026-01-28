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
import { Color } from 'three';
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
const pdfDoc = ref<pdfjsLib.PDFDocumentProxy | null>(null);
const pageNumber = ref(1);
const pageCount = ref(1);
const loading = ref(false);
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
    if (dxfContainerRef.value) {
        dxfContainerRef.value.innerHTML = '';
    }
};

const renderDxfToSvg = (dxfText: string) => {
    if (!dxfContainerRef.value) return;
    const parser = new DxfParser();
    const dxf = parser.parseSync(dxfText);

    let minX = Infinity;
    let minY = Infinity;
    let maxX = -Infinity;
    let maxY = -Infinity;

    const updateBounds = (x: number, y: number) => {
        minX = Math.min(minX, x);
        minY = Math.min(minY, y);
        maxX = Math.max(maxX, x);
        maxY = Math.max(maxY, y);
    };

    const entities = dxf.entities ?? [];

    entities.forEach((entity: any) => {
        if (entity.type === 'LINE') {
            updateBounds(entity.start.x, entity.start.y);
            updateBounds(entity.end.x, entity.end.y);
        }
        if (entity.type === 'LWPOLYLINE' || entity.type === 'POLYLINE') {
            const vertices = entity.vertices ?? [];
            vertices.forEach((vertex: any) => updateBounds(vertex.x, vertex.y));
        }
        if (entity.type === 'CIRCLE' || entity.type === 'ARC') {
            updateBounds(entity.center.x - entity.radius, entity.center.y - entity.radius);
            updateBounds(entity.center.x + entity.radius, entity.center.y + entity.radius);
        }
        if (entity.type === 'TEXT' && entity.startPoint) {
            updateBounds(entity.startPoint.x, entity.startPoint.y);
        }
    });

    if (!Number.isFinite(minX) || !Number.isFinite(minY)) {
        minX = 0;
        minY = 0;
        maxX = 1;
        maxY = 1;
    }

    const width = Math.max(maxX - minX, 1);
    const height = Math.max(maxY - minY, 1);

    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('viewBox', `0 0 ${width} ${height}`);
    svg.setAttribute('preserveAspectRatio', 'xMidYMid meet');
    svg.style.width = '100%';
    svg.style.height = '100%';

    const geometryGroup = document.createElementNS('http://www.w3.org/2000/svg', 'g');
    geometryGroup.setAttribute('transform', `translate(${-minX} ${maxY}) scale(1 -1)`);
    svg.appendChild(geometryGroup);

    const textGroup = document.createElementNS('http://www.w3.org/2000/svg', 'g');
    svg.appendChild(textGroup);

    const stroke = '#111827';

    const createPath = (d: string) => {
        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.setAttribute('d', d);
        path.setAttribute('fill', 'none');
        path.setAttribute('stroke', stroke);
        path.setAttribute('stroke-width', '0.02');
        path.setAttribute('vector-effect', 'non-scaling-stroke');
        return path;
    };

    entities.forEach((entity: any) => {
        if (entity.type === 'LINE') {
            const path = createPath(`M ${entity.start.x} ${entity.start.y} L ${entity.end.x} ${entity.end.y}`);
            geometryGroup.appendChild(path);
        }
        if (entity.type === 'LWPOLYLINE' || entity.type === 'POLYLINE') {
            const vertices = entity.vertices ?? [];
            if (!vertices.length) return;
            const d = vertices
                .map((vertex: any, index: number) => `${index === 0 ? 'M' : 'L'} ${vertex.x} ${vertex.y}`)
                .join(' ');
            const close = entity.shape || entity.closed ? ' Z' : '';
            const path = createPath(`${d}${close}`);
            geometryGroup.appendChild(path);
        }
        if (entity.type === 'CIRCLE') {
            const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            circle.setAttribute('cx', entity.center.x);
            circle.setAttribute('cy', entity.center.y);
            circle.setAttribute('r', entity.radius);
            circle.setAttribute('fill', 'none');
            circle.setAttribute('stroke', stroke);
            circle.setAttribute('stroke-width', '0.02');
            circle.setAttribute('vector-effect', 'non-scaling-stroke');
            geometryGroup.appendChild(circle);
        }
        if (entity.type === 'ARC') {
            const startRad = (entity.startAngle * Math.PI) / 180;
            const endRad = (entity.endAngle * Math.PI) / 180;
            const sx = entity.center.x + entity.radius * Math.cos(startRad);
            const sy = entity.center.y + entity.radius * Math.sin(startRad);
            const ex = entity.center.x + entity.radius * Math.cos(endRad);
            const ey = entity.center.y + entity.radius * Math.sin(endRad);
            const delta = (entity.endAngle - entity.startAngle + 360) % 360;
            const largeArc = delta > 180 ? 1 : 0;
            const path = createPath(`M ${sx} ${sy} A ${entity.radius} ${entity.radius} 0 ${largeArc} 1 ${ex} ${ey}`);
            geometryGroup.appendChild(path);
        }
        if (entity.type === 'TEXT' && entity.startPoint) {
            const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            text.setAttribute('x', String(entity.startPoint.x - minX));
            text.setAttribute('y', String(maxY - entity.startPoint.y));
            text.setAttribute('fill', stroke);
            text.setAttribute('font-size', String(entity.height || 0.3));
            text.textContent = entity.text;
            textGroup.appendChild(text);
        }
    });

    dxfContainerRef.value.innerHTML = '';
    dxfContainerRef.value.appendChild(svg);
};

const loadDxf = async () => {
    if (!dxfContainerRef.value || !fileUrl.value) return;
    resetDxf();
    loadError.value = '';
    await nextTick();
    try {
        const response = await fetch(fileUrl.value, { credentials: 'include' });
        if (!response.ok) {
            throw new Error('DXF fetch failed');
        }
        const dxfText = await response.text();
        renderDxfToSvg(dxfText);
    } catch (error) {
        loadError.value = 'Impossible de prévisualiser ce DXF.';
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
                    backgroundColor: new Color(0xffffff),
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
                    backgroundColor: new Color(0xffffff),
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

