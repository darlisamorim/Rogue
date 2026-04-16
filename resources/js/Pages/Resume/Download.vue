<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import type { ResumeData, ResumeCustomization } from '@/types/resume'
import TemplateMinimalist from '@/Components/Templates/TemplateMinimalist.vue'
import TemplateModern from '@/Components/Templates/TemplateModern.vue'
import TemplateClassic from '@/Components/Templates/TemplateClassic.vue'
import TemplateCreative from '@/Components/Templates/TemplateCreative.vue'
import TemplateTech from '@/Components/Templates/TemplateTech.vue'

interface Props {
    resume: {
        id: number
        title: string
        data: ResumeData
        customization: ResumeCustomization
    }
    template: {
        name: string
        component_name: string
    } | null
}

const props = defineProps<Props>()

const templateComponents: Record<string, unknown> = {
    TemplateMinimalist,
    TemplateModern,
    TemplateClassic,
    TemplateCreative,
    TemplateTech,
}

const activeComponent = props.template
    ? (templateComponents[props.template.component_name] ?? TemplateMinimalist)
    : TemplateMinimalist

function goBack() {
    router.visit(route('resumes.index'), { replace: true })
}

function printPdf() {
    window.print()
}

function setResumeScale() {
    const available = Math.min(window.innerWidth - 32, 794)
    const scale = available / 794
    document.documentElement.style.setProperty('--resume-scale', String(scale))
}

onMounted(() => {
    setResumeScale()
    window.addEventListener('resize', setResumeScale)
    // Aguarda fontes + um tick de renderização antes de abrir o diálogo
    document.fonts.ready.then(() => {
        setTimeout(() => window.print(), 600)
    })
})

onUnmounted(() => {
    window.removeEventListener('resize', setResumeScale)
})
</script>

<template>
    <Head :title="`Baixar PDF — ${resume.title}`" />

    <!-- Barra superior (só na tela, não no PDF) -->
    <div class="no-print fixed top-0 left-0 right-0 z-50 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3">
            <button
                type="button"
                @click="goBack"
                class="text-sm text-gray-500 hover:text-gray-800 flex items-center gap-1.5 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Meus Currículos
            </button>
            <span class="text-gray-300">|</span>
            <span class="text-sm font-semibold text-gray-800 truncate max-w-xs">{{ resume.title }}</span>
            <span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">
                {{ template?.name ?? 'Template' }}
            </span>
        </div>
        <div class="flex items-center gap-3">
            <p class="text-xs text-gray-400 hidden sm:block">
                Escolha <strong>"Salvar como PDF"</strong> no diálogo de impressão.
            </p>
            <button
                type="button"
                @click="printPdf"
                class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Baixar PDF
            </button>
        </div>
    </div>

    <!-- Visualização na tela (escalada para caber) -->
    <div class="no-print screen-wrapper">
        <div class="resume-scaler">
            <component
                :is="activeComponent"
                :resume-data="resume.data"
                :customization="resume.customization"
            />
        </div>
    </div>

    <!-- Área exclusiva de impressão -->
    <div class="print-only">
        <component
            :is="activeComponent"
            :resume-data="resume.data"
            :customization="resume.customization"
        />
    </div>
</template>

<style>
.screen-wrapper {
    padding-top: 56px;
    min-height: 100vh;
    background: #e5e7eb;
    display: flex;
    justify-content: center;
    padding-bottom: 2rem;
}

.resume-scaler {
    margin-top: 2rem;
    transform-origin: top center;
    transform: scale(var(--resume-scale, 1));
    width: 794px;
    /* Compensa o espaço que o scale "rouba" verticalmente */
    margin-bottom: calc((var(--resume-scale, 1) - 1) * 1123px);
    box-shadow: 0 8px 40px rgba(0,0,0,0.18);
}

.print-only {
    display: none;
}

@media print {
    .no-print        { display: none !important; }
    .screen-wrapper  { display: none !important; }
    .print-only      { display: block !important; }

    html, body {
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }

    @page {
        size: A4;
        margin: 0;
    }
}
</style>
