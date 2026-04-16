<script setup lang="ts">
import { onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
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

onMounted(() => {
    // Aguarda renderização completa antes de imprimir
    setTimeout(() => window.print(), 800)
})
</script>

<template>
    <Head :title="`Baixar — ${resume.title}`" />

    <!-- Barra superior — só aparece na tela, some na impressão -->
    <div class="no-print fixed top-0 left-0 right-0 z-50 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3">
            <button
                type="button"
                onclick="history.back()"
                class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Voltar
            </button>
            <span class="text-gray-300">|</span>
            <span class="text-sm font-medium text-gray-700">{{ resume.title }}</span>
        </div>
        <div class="flex items-center gap-3">
            <p class="text-xs text-gray-400">
                Janela de impressão abriu automaticamente. Se não abriu:
            </p>
            <button
                type="button"
                onclick="window.print()"
                class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Baixar PDF
            </button>
        </div>
    </div>

    <!-- Área de impressão — template em tamanho real -->
    <div class="print-area pt-14">
        <component
            :is="activeComponent"
            :resume-data="resume.data"
            :customization="resume.customization"
        />
    </div>
</template>

<style>
/* ===== ESTILOS DE IMPRESSÃO ===== */
@media print {
    /* Esconde tudo que não é o currículo */
    .no-print { display: none !important; }

    /* Remove margens e padding da página */
    html, body {
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
    }

    .print-area {
        padding-top: 0 !important;
    }

    /* Garante que o currículo ocupa a página inteira sem scroll */
    @page {
        size: A4;
        margin: 0;
    }
}

/* ===== VISUALIZAÇÃO NA TELA ===== */
@media screen {
    .print-area {
        display: flex;
        justify-content: center;
        background: #f3f4f6;
        min-height: 100vh;
        padding: 2rem 1rem;
    }

    .print-area > * {
        width: 794px;
        min-height: 1123px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.15);
    }
}
</style>
