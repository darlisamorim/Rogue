<script setup lang="ts">
import { ref, computed } from 'vue'
import type { Template } from '@/types/template'
import type { ResumeCustomization } from '@/types/resume'
import PreviewPanel from '@/Components/ResumePreview/PreviewPanel.vue'
import type { ResumeData } from '@/types/resume'

const props = defineProps<{
    templates: Template[]
    selectedTemplateId: number
    customization: ResumeCustomization
    resumeData: ResumeData
    resumeTitle?: string
}>()

const emit = defineEmits<{
    (e: 'select', template: Template): void
    (e: 'update-customization', field: keyof ResumeCustomization, value: unknown): void
    (e: 'close'): void
    (e: 'download'): void
}>()

type Tab = 'templates' | 'text' | 'layout'
const activeTab = ref<Tab>('templates')

const currentTemplate = computed(() =>
    props.templates.find((t) => t.id === props.selectedTemplateId) ?? props.templates[0],
)

const colorOptions = computed(() => currentTemplate.value?.config?.colors ?? [])

// Presets de fontes com grupos de nome/amostra
const fontPresets = [
    { name: 'Inter',         category: 'Moderno' },
    { name: 'Poppins',       category: 'Arredondado' },
    { name: 'Roboto',        category: 'Neutro' },
    { name: 'Montserrat',    category: 'Elegante' },
    { name: 'Lato',          category: 'Leve' },
    { name: 'Raleway',       category: 'Criativo' },
    { name: 'Nunito',        category: 'Amigável' },
    { name: 'Open Sans',     category: 'Legível' },
    { name: 'Ubuntu',        category: 'Técnico' },
    { name: 'Source Sans Pro', category: 'Editorial' },
]

const fontSizeValues: Array<'sm' | 'md' | 'lg'> = ['sm', 'md', 'lg']
const fontSizeIndex = computed(() => fontSizeValues.indexOf(props.customization.fontSize))
function setFontSize(index: number) {
    emit('update-customization', 'fontSize', fontSizeValues[index])
}

const spacingValues: Array<'compact' | 'normal' | 'relaxed'> = ['compact', 'normal', 'relaxed']
const spacingIndex = computed(() => spacingValues.indexOf(props.customization.spacing))
function setSpacing(index: number) {
    emit('update-customization', 'spacing', spacingValues[index])
}
</script>

<template>
    <!-- Full-screen overlay -->
    <div class="fixed inset-0 z-50 flex bg-gray-950">

        <!-- ─── LEFT SIDEBAR ─── -->
        <div class="w-56 shrink-0 bg-gray-900 flex flex-col border-r border-gray-800">

            <!-- Navegação / Breadcrumb -->
            <div class="px-3 pt-3 pb-2.5 border-b border-gray-800">
                <!-- Voltar ao editor -->
                <button
                    type="button"
                    @click="emit('close')"
                    class="flex items-center gap-1.5 text-sm text-gray-300 hover:text-white transition-colors font-medium"
                >
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                    Voltar ao editor
                </button>

                <!-- Breadcrumb: currículo > modelo ativo -->
                <div class="flex items-center gap-1 mt-2.5">
                    <span class="text-[11px] text-gray-500 truncate max-w-[65px] leading-tight">
                        {{ resumeTitle || 'Currículo' }}
                    </span>
                    <svg class="w-3 h-3 text-gray-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <div class="flex items-center gap-1 min-w-0">
                        <div
                            class="w-2 h-2 rounded-full shrink-0"
                            :style="{ backgroundColor: customization.color }"
                        />
                        <span class="text-[11px] text-white font-semibold truncate max-w-[80px] leading-tight">
                            {{ currentTemplate?.name ?? 'Modelo' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Abas com ícones -->
            <div class="flex border-b border-gray-800">
                <!-- Modelos -->
                <button
                    type="button"
                    @click="activeTab = 'templates'"
                    class="flex-1 flex flex-col items-center gap-0.5 py-2.5 text-[10px] font-semibold tracking-wide uppercase transition-all"
                    :class="activeTab === 'templates'
                        ? 'text-blue-400 border-b-2 border-blue-500 bg-gray-800/60'
                        : 'text-gray-500 hover:text-gray-300 hover:bg-gray-800/30'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                    </svg>
                    Modelos
                </button>
                <!-- Texto -->
                <button
                    type="button"
                    @click="activeTab = 'text'"
                    class="flex-1 flex flex-col items-center gap-0.5 py-2.5 text-[10px] font-semibold tracking-wide uppercase transition-all"
                    :class="activeTab === 'text'
                        ? 'text-blue-400 border-b-2 border-blue-500 bg-gray-800/60'
                        : 'text-gray-500 hover:text-gray-300 hover:bg-gray-800/30'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6h16M4 12h8m-8 6h16" />
                    </svg>
                    Texto
                </button>
                <!-- Layout -->
                <button
                    type="button"
                    @click="activeTab = 'layout'"
                    class="flex-1 flex flex-col items-center gap-0.5 py-2.5 text-[10px] font-semibold tracking-wide uppercase transition-all"
                    :class="activeTab === 'layout'
                        ? 'text-blue-400 border-b-2 border-blue-500 bg-gray-800/60'
                        : 'text-gray-500 hover:text-gray-300 hover:bg-gray-800/30'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7" />
                    </svg>
                    Layout
                </button>
            </div>

            <!-- ─── TAB: MODELOS ─── -->
            <div v-if="activeTab === 'templates'" class="flex-1 overflow-y-auto p-3 space-y-2">
                <button
                    v-for="template in templates"
                    :key="template.id"
                    type="button"
                    @click="emit('select', template)"
                    class="w-full rounded-xl border-2 overflow-hidden transition-all text-left"
                    :class="selectedTemplateId === template.id
                        ? 'border-blue-500 ring-1 ring-blue-500/30'
                        : 'border-gray-700 hover:border-gray-500'"
                >
                    <div class="aspect-[3/4] bg-gray-800 flex items-center justify-center relative overflow-hidden">
                        <div
                            class="w-full h-full opacity-60"
                            :style="{ backgroundColor: template.config.colors[0] + '20' }"
                        >
                            <div class="p-2 space-y-1">
                                <div class="h-2 rounded w-2/3" :style="{ backgroundColor: template.config.colors[0] }" />
                                <div class="h-1 bg-gray-600 rounded" />
                                <div class="h-1 bg-gray-600 rounded w-4/5" />
                                <div class="h-1 bg-gray-600 rounded w-3/5 mt-2" />
                                <div class="h-1 bg-gray-700 rounded" />
                                <div class="h-1 bg-gray-700 rounded w-5/6" />
                            </div>
                        </div>
                        <!-- Checkmark ativo -->
                        <div
                            v-if="selectedTemplateId === template.id"
                            class="absolute top-1.5 right-1.5 w-5 h-5 bg-blue-500 rounded-full flex items-center justify-center"
                        >
                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <div class="py-1.5 px-2 text-center">
                        <p class="text-xs text-gray-300 font-medium leading-tight">{{ template.name }}</p>
                        <p
                            v-if="selectedTemplateId === template.id"
                            class="text-[10px] text-blue-400 font-semibold mt-0.5 uppercase tracking-wide"
                        >Ativo</p>
                    </div>
                </button>
            </div>

            <!-- ─── TAB: TEXTO ─── -->
            <div v-else-if="activeTab === 'text'" class="flex-1 overflow-y-auto">

                <!-- Presets de fontes (pares pré-definidos) -->
                <div class="p-3 border-b border-gray-800">
                    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest mb-2.5">Predefinidos</p>
                    <div class="grid grid-cols-3 gap-1.5 mb-3">
                        <button
                            v-for="preset in fontPresets.slice(0, 9)"
                            :key="preset.name"
                            type="button"
                            @click="emit('update-customization', 'font', preset.name)"
                            class="flex flex-col items-center justify-center gap-0.5 rounded-lg border py-2.5 px-1 transition-all"
                            :class="customization.font === preset.name
                                ? 'border-blue-500 bg-blue-500/10 ring-1 ring-blue-500/20'
                                : 'border-gray-700 hover:border-gray-500 bg-gray-800/40'"
                        >
                            <span
                                class="text-xl font-bold leading-none"
                                :class="customization.font === preset.name ? 'text-blue-300' : 'text-gray-300'"
                                :style="{ fontFamily: preset.name + ', sans-serif' }"
                            >Aa</span>
                            <span class="text-[8px] text-gray-500 truncate w-full text-center mt-0.5">{{ preset.name }}</span>
                        </button>
                    </div>
                    <!-- Fonte atual destacada -->
                    <div class="flex items-center gap-2 bg-gray-800/60 rounded-lg px-2.5 py-2 border border-gray-700">
                        <span class="text-[10px] text-gray-500 uppercase tracking-widest shrink-0">Fonte</span>
                        <span
                            class="text-sm font-semibold text-white truncate"
                            :style="{ fontFamily: customization.font + ', sans-serif' }"
                        >{{ customization.font }}</span>
                        <span class="ml-auto text-[10px] text-blue-400 shrink-0">{{ fontPresets.find(f => f.name === customization.font)?.category ?? '' }}</span>
                    </div>
                </div>

                <!-- Tamanho da fonte -->
                <div class="p-3 border-b border-gray-800">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Tamanho do texto</p>
                        <span class="text-xs text-blue-400 font-semibold">
                            {{ { sm: 'Pequeno', md: 'Normal', lg: 'Grande' }[customization.fontSize] }}
                        </span>
                    </div>
                    <!-- Botões visuais de tamanho -->
                    <div class="flex gap-1.5">
                        <button
                            v-for="(size, i) in ([['sm','A','Pequeno'],['md','Aa','Normal'],['lg','Aaa','Grande']] as const)"
                            :key="size[0]"
                            type="button"
                            @click="setFontSize(i)"
                            class="flex-1 flex flex-col items-center justify-center gap-1 rounded-lg border py-2.5 transition-all"
                            :class="customization.fontSize === size[0]
                                ? 'border-blue-500 bg-blue-500/10 text-blue-300'
                                : 'border-gray-700 hover:border-gray-500 text-gray-400 bg-gray-800/40'"
                        >
                            <span class="font-bold leading-none" :style="{ fontSize: i === 0 ? '11px' : i === 1 ? '14px' : '18px' }">{{ size[1] }}</span>
                            <span class="text-[9px] leading-none">{{ size[2] }}</span>
                        </button>
                    </div>
                </div>

                <!-- Espaçamento entre linhas e seções -->
                <div class="p-3">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Espaçamento</p>
                        <span class="text-xs text-blue-400 font-semibold">
                            {{ { compact: 'Compacto', normal: 'Normal', relaxed: 'Amplo' }[customization.spacing] }}
                        </span>
                    </div>
                    <!-- Botões visuais de espaçamento -->
                    <div class="flex gap-1.5">
                        <button
                            v-for="(sp, i) in ([['compact','Compacto'],['normal','Normal'],['relaxed','Amplo']] as const)"
                            :key="sp[0]"
                            type="button"
                            @click="setSpacing(i)"
                            class="flex-1 flex flex-col items-center justify-center gap-1.5 rounded-lg border py-2.5 px-1 transition-all"
                            :class="customization.spacing === sp[0]
                                ? 'border-blue-500 bg-blue-500/10 text-blue-300'
                                : 'border-gray-700 hover:border-gray-500 text-gray-400 bg-gray-800/40'"
                        >
                            <!-- Ícone de linhas com espaçamento visual -->
                            <div class="flex flex-col items-start gap-px w-7">
                                <div class="h-px bg-current w-full" />
                                <div class="h-px bg-current w-4/5" :style="{ marginTop: i === 0 ? '2px' : i === 1 ? '3.5px' : '6px' }" />
                                <div class="h-px bg-current w-full" :style="{ marginTop: i === 0 ? '2px' : i === 1 ? '3.5px' : '6px' }" />
                            </div>
                            <span class="text-[9px] leading-none text-center">{{ sp[1] }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ─── TAB: LAYOUT ─── -->
            <div v-else-if="activeTab === 'layout'" class="flex-1 overflow-y-auto p-3 space-y-4">
                <div>
                    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest mb-3">Formato do papel</p>
                    <div class="space-y-2">
                        <button
                            type="button"
                            @click="emit('update-customization', 'layout', 'A4')"
                            class="w-full flex items-center gap-3 p-3 rounded-lg border transition-all"
                            :class="customization.layout === 'A4'
                                ? 'border-blue-500 bg-blue-500/10 text-white'
                                : 'border-gray-700 text-gray-400 hover:border-gray-500'"
                        >
                            <div class="w-6 h-8 border-2 rounded-sm shrink-0" :class="customization.layout === 'A4' ? 'border-blue-400' : 'border-gray-600'" />
                            <div class="text-left">
                                <p class="text-sm font-semibold">A4</p>
                                <p class="text-[10px] text-gray-500">210 × 297 mm</p>
                            </div>
                            <div v-if="customization.layout === 'A4'" class="ml-auto w-4 h-4 bg-blue-500 rounded-full flex items-center justify-center">
                                <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                        <button
                            type="button"
                            @click="emit('update-customization', 'layout', 'letter')"
                            class="w-full flex items-center gap-3 p-3 rounded-lg border transition-all"
                            :class="customization.layout === 'letter'
                                ? 'border-blue-500 bg-blue-500/10 text-white'
                                : 'border-gray-700 text-gray-400 hover:border-gray-500'"
                        >
                            <div class="w-6 h-7 border-2 rounded-sm shrink-0" :class="customization.layout === 'letter' ? 'border-blue-400' : 'border-gray-600'" />
                            <div class="text-left">
                                <p class="text-sm font-semibold">Carta (EUA)</p>
                                <p class="text-[10px] text-gray-500">216 × 279 mm</p>
                            </div>
                            <div v-if="customization.layout === 'letter'" class="ml-auto w-4 h-4 bg-blue-500 rounded-full flex items-center justify-center">
                                <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Mostrar nível das habilidades -->
                <div class="border-t border-gray-800 pt-4">
                    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest mb-3">Habilidades</p>
                    <label class="flex items-center justify-between cursor-pointer">
                        <span class="text-xs text-gray-300">Mostrar nível</span>
                        <div
                            class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors"
                            :class="customization.showSkillLevels ? 'bg-blue-600' : 'bg-gray-700'"
                            @click="emit('update-customization', 'showSkillLevels', !customization.showSkillLevels)"
                        >
                            <span
                                class="inline-block h-3.5 w-3.5 transform rounded-full bg-white transition-transform"
                                :class="customization.showSkillLevels ? 'translate-x-4' : 'translate-x-1'"
                            />
                        </div>
                    </label>
                </div>
            </div>

            <!-- ─── CORES (sempre visível no rodapé) ─── -->
            <div class="p-3 border-t border-gray-800 shrink-0">
                <!-- Paleta de cores do tema -->
                <div class="flex items-center justify-between mb-2">
                    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Cor do tema</p>
                    <div class="flex items-center gap-1.5">
                        <div class="w-3.5 h-3.5 rounded-full border border-white/20" :style="{ backgroundColor: customization.color }" />
                        <span class="text-[9px] text-gray-400 font-mono uppercase">{{ customization.color }}</span>
                    </div>
                </div>

                <!-- Swatches do template em linha -->
                <div class="flex flex-wrap gap-2 mb-3">
                    <button
                        v-for="color in colorOptions"
                        :key="color"
                        type="button"
                        @click="emit('update-customization', 'color', color)"
                        class="w-7 h-7 rounded-full transition-all hover:scale-110 relative"
                        :style="{
                            backgroundColor: color,
                            boxShadow: customization.color === color
                                ? '0 0 0 2px #0f172a, 0 0 0 4px white'
                                : 'none',
                        }"
                    >
                        <svg
                            v-if="customization.color === color"
                            class="absolute inset-0 m-auto w-3.5 h-3.5 text-white"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </button>
                </div>

                <!-- Linha de referência visual: Destaque / Texto / Fundo -->
                <div class="grid grid-cols-3 gap-1">
                    <div class="flex flex-col items-center gap-1">
                        <div class="w-full h-5 rounded" :style="{ backgroundColor: customization.color }" />
                        <span class="text-[9px] text-gray-600">Destaque</span>
                    </div>
                    <div class="flex flex-col items-center gap-1">
                        <div class="w-full h-5 rounded bg-gray-800 border border-gray-700 flex items-center justify-center">
                            <span class="text-[9px] text-gray-300 font-bold" :style="{ fontFamily: customization.font + ', sans-serif' }">Texto</span>
                        </div>
                        <span class="text-[9px] text-gray-600">Tipografia</span>
                    </div>
                    <div class="flex flex-col items-center gap-1">
                        <div class="w-full h-5 rounded bg-white border border-gray-700" />
                        <span class="text-[9px] text-gray-600">Fundo</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── MAIN PREVIEW ─── -->
        <div class="flex-1 flex flex-col">
            <!-- Top bar -->
            <div class="h-14 bg-gray-900 border-b border-gray-800 flex items-center justify-end px-6">
                <button
                    type="button"
                    @click="emit('download')"
                    class="flex items-center gap-2 px-5 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Baixar PDF
                </button>
            </div>

            <!-- Preview -->
            <div class="flex-1 overflow-hidden bg-gray-950">
                <PreviewPanel
                    :resume-data="resumeData"
                    :customization="customization"
                    :template="currentTemplate"
                />
            </div>
        </div>
    </div>
</template>
