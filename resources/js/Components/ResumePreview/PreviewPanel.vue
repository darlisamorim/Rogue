<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue'
import type { ResumeData, ResumeCustomization } from '@/types/resume'
import type { Template } from '@/types/template'
import TemplateMinimalist from '@/Components/Templates/TemplateMinimalist.vue'
import TemplateModern from '@/Components/Templates/TemplateModern.vue'
import TemplateClassic from '@/Components/Templates/TemplateClassic.vue'
import TemplateCreative from '@/Components/Templates/TemplateCreative.vue'
import TemplateTech from '@/Components/Templates/TemplateTech.vue'

const props = defineProps<{
    resumeData: ResumeData
    customization: ResumeCustomization
    template: Template | null
    saveStatus?: 'idle' | 'saving' | 'saved' | 'error'
}>()

const templateComponents: Record<string, unknown> = {
    TemplateMinimalist,
    TemplateModern,
    TemplateClassic,
    TemplateCreative,
    TemplateTech,
}

const activeComponent = computed(() => {
    if (!props.template) return TemplateMinimalist
    return templateComponents[props.template.component_name] ?? TemplateMinimalist
})

// Scale the A4 (794px wide) to fit the container
const containerRef = ref<HTMLElement | null>(null)
const scale = ref(0.5)

function recalcScale() {
    if (containerRef.value) {
        const available = containerRef.value.clientWidth - 32
        scale.value = Math.min(available / 794, 1)
    }
}

onMounted(() => {
    recalcScale()
    window.addEventListener('resize', recalcScale)
})

onUnmounted(() => {
    window.removeEventListener('resize', recalcScale)
})

const saveLabel = computed(() => {
    switch (props.saveStatus) {
        case 'saving': return '⏳ Salvando...'
        case 'saved': return '✓ Salvo'
        case 'error': return '✗ Erro ao salvar'
        default: return ''
    }
})

const saveLabelColor = computed(() => {
    switch (props.saveStatus) {
        case 'saving': return 'text-amber-500'
        case 'saved': return 'text-green-600'
        case 'error': return 'text-red-500'
        default: return 'text-transparent'
    }
})
</script>

<template>
    <div class="flex flex-col h-full">
        <!-- Save status bar -->
        <div class="flex items-center justify-between px-4 py-2 border-b border-gray-100 bg-gray-50 shrink-0">
            <span class="text-xs font-medium text-gray-500">Pré-visualização</span>
            <span class="text-xs font-medium transition-all" :class="saveLabelColor">{{ saveLabel }}</span>
        </div>

        <!-- Preview container -->
        <div ref="containerRef" class="flex-1 overflow-y-auto bg-gray-200 p-4 flex justify-center">
            <div
                class="shadow-2xl origin-top"
                :style="{
                    transform: `scale(${scale})`,
                    width: '794px',
                    transformOrigin: 'top center',
                    marginBottom: `${(1123 * scale) - 1123}px`,
                }"
            >
                <component
                    :is="activeComponent"
                    :resumeData="resumeData"
                    :customization="customization"
                />
            </div>
        </div>
    </div>
</template>
