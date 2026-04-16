<script setup lang="ts">
import { ref, computed, toRef, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import type { Resume, ResumeCustomization } from '@/types/resume'
import type { Template } from '@/types/template'
import { useResumeForm } from '@/Composables/useResumeForm'
import { useAutoSave } from '@/Composables/useAutoSave'

// Form Steps
import StepPersonalData from '@/Components/ResumeForm/StepPersonalData.vue'
import StepWorkHistory from '@/Components/ResumeForm/StepWorkHistory.vue'
import StepEducation from '@/Components/ResumeForm/StepEducation.vue'
import StepSkills from '@/Components/ResumeForm/StepSkills.vue'
import StepSummary from '@/Components/ResumeForm/StepSummary.vue'
import StepLinks from '@/Components/ResumeForm/StepLinks.vue'
import StepAdditional from '@/Components/ResumeForm/StepAdditional.vue'

// UI + Preview
import PreviewPanel from '@/Components/ResumePreview/PreviewPanel.vue'
import TemplateSelector from '@/Components/TemplateSelector/TemplateSelector.vue'

const props = defineProps<{
    resume: Resume
    templates: Template[]
}>()

const {
    resumeData,
    customization,
    currentStep,
    currentStepKey,
    goToStep,
    nextStep,
    prevStep,
    isFirstStep,
    isLastStep,
    STEPS,
} = useResumeForm(props.resume.data, props.resume.customization)

const resumeTitle = ref(props.resume.title)
const { saveStatus } = useAutoSave(
    props.resume.id,
    resumeData,
    customization,
    resumeTitle,
)

const selectedTemplateId = ref(props.resume.template_id)
const showTemplateSelector = ref(false)
const showDownloadConfirm = ref(false)

const currentTemplate = computed(() =>
    props.templates.find((t) => t.id === selectedTemplateId.value) ?? props.templates[0] ?? null,
)

function onSelectTemplate(template: Template) {
    selectedTemplateId.value = template.id
    customization.value.color = template.config.colors[0] ?? customization.value.color
    customization.value.font = template.config.fonts[0] ?? customization.value.font
}

function onUpdateCustomization(field: keyof ResumeCustomization, value: unknown) {
    ;(customization.value as Record<string, unknown>)[field] = value
}

// Step labels para navegação
const nextStepLabel = computed(() =>
    currentStep.value < STEPS.length - 1 ? STEPS[currentStep.value + 1].label : null,
)
const currentStepLabel = computed(() => STEPS[currentStep.value].label)

function goToCheckout() {
    const type = props.resume.is_downloaded ? 'redownload' : 'first_download'
    router.get(route('payment.checkout'), { resume_id: props.resume.id, type })
}

// Chamado quando o usuário clica "Baixar PDF" dentro do TemplateSelector
function onTemplateSelectorDownload() {
    showTemplateSelector.value = false
    showDownloadConfirm.value = true
}

// Auto-abrir visual se vier de ?visual=1
onMounted(() => {
    const params = new URLSearchParams(window.location.search)
    if (params.get('visual') === '1') {
        showTemplateSelector.value = true
    }
})

const saveLabel = computed(() => {
    switch (saveStatus.value) {
        case 'saving': return '⏳ Salvando...'
        case 'saved': return '✓ Todas as mudanças salvas'
        case 'error': return '✗ Erro ao salvar'
        default: return ''
    }
})
const saveLabelColor = computed(() => {
    switch (saveStatus.value) {
        case 'saving': return 'text-amber-400'
        case 'saved': return 'text-green-400'
        case 'error': return 'text-red-400'
        default: return 'text-transparent'
    }
})
</script>

<template>
    <Head :title="`Editando: ${resumeTitle}`" />

    <!-- Template Selector overlay -->
    <TemplateSelector
        v-if="showTemplateSelector"
        :templates="templates"
        :selected-template-id="selectedTemplateId"
        :customization="customization"
        :resume-data="resumeData"
        :resume-title="resumeTitle"
        @select="onSelectTemplate"
        @update-customization="onUpdateCustomization"
        @close="showTemplateSelector = false"
        @download="onTemplateSelectorDownload"
    />

    <!-- Modal de confirmação de download -->
    <Transition name="fade">
        <div v-if="showDownloadConfirm"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
            @click.self="showDownloadConfirm = false"
        >
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 space-y-5">
                <!-- Ícone -->
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-base">Tudo certo para baixar?</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Revise o estilo antes de confirmar o pagamento.</p>
                    </div>
                </div>

                <!-- Detalhes do currículo -->
                <div class="bg-gray-50 rounded-xl px-4 py-3 space-y-1.5 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Título</span>
                        <span class="font-medium text-gray-800 truncate max-w-[160px]">{{ resumeTitle }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Template</span>
                        <span class="font-medium text-gray-800">{{ currentTemplate?.name ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Fonte</span>
                        <span class="font-medium text-gray-800">{{ customization.font }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Cor principal</span>
                        <span class="w-5 h-5 rounded-full border border-gray-200 inline-block" :style="{ background: customization.color }"></span>
                    </div>
                </div>

                <!-- Ações -->
                <div class="flex flex-col gap-2">
                    <button
                        type="button"
                        @click="showDownloadConfirm = false; goToCheckout()"
                        class="w-full py-3 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 transition-colors flex items-center justify-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Confirmar e ir para pagamento
                    </button>
                    <button
                        type="button"
                        @click="showDownloadConfirm = false; showTemplateSelector = true"
                        class="w-full py-2 text-sm text-gray-500 hover:text-gray-700 transition-colors"
                    >
                        Voltar e ajustar o estilo
                    </button>
                </div>
            </div>
        </div>
    </Transition>

    <div class="h-screen flex flex-col bg-gray-50 overflow-hidden">
        <!-- Top bar -->
        <div class="h-14 bg-white border-b border-gray-200 flex items-center px-4 gap-3 shrink-0 z-10">
            <!-- Back -->
            <Link
                :href="route('resumes.index')"
                class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 transition-colors shrink-0 font-medium"
                title="Ver todos os currículos"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Meus Currículos
            </Link>

            <!-- Title (editable) -->
            <input
                v-model="resumeTitle"
                type="text"
                class="text-sm font-semibold text-gray-800 border-0 focus:ring-0 focus:outline-none bg-transparent min-w-0 max-w-xs"
                placeholder="Título do currículo"
            />

            <!-- Save status -->
            <span class="text-xs font-medium transition-all ml-1" :class="saveLabelColor">
                {{ saveLabel }}
            </span>

            <div class="flex-1" />

            <!-- Template / Visual button -->
            <button
                type="button"
                @click="showTemplateSelector = true"
                class="flex items-center gap-2 text-sm font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-1.5 hover:bg-indigo-100 hover:border-indigo-400 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                </svg>
                Estilo & Cores
                <span class="text-xs text-indigo-400 font-normal">({{ currentTemplate?.name ?? 'Template' }})</span>
            </button>

            <!-- Download button -->
            <button
                type="button"
                @click="goToCheckout"
                class="flex items-center gap-2 px-4 py-1.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Baixar PDF
            </button>
        </div>

        <!-- Main area -->
        <div class="flex flex-1 overflow-hidden">

            <!-- Left panel: form -->
            <div class="w-[440px] shrink-0 flex flex-col border-r border-gray-200 bg-white">

                <!-- Section title -->
                <div class="px-6 pt-5 pb-3 shrink-0 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-semibold text-gray-900">{{ currentStepLabel }}</h2>
                        <div class="flex gap-1">
                            <button
                                v-for="(step, i) in STEPS"
                                :key="step.key"
                                type="button"
                                @click="goToStep(i)"
                                :title="step.label"
                                class="w-2 h-2 rounded-full transition-all"
                                :class="i === currentStep ? 'bg-blue-600 scale-125' : i < currentStep ? 'bg-blue-300' : 'bg-gray-200 hover:bg-gray-300'"
                            />
                        </div>
                    </div>
                </div>

                <!-- Step content (scrollable) -->
                <div class="flex-1 overflow-y-auto p-6">
                    <StepPersonalData
                        v-if="currentStepKey === 'personal'"
                        :data="resumeData.personalData"
                        @update="Object.assign(resumeData.personalData, $event)"
                    />

                    <StepSummary
                        v-else-if="currentStepKey === 'summary'"
                        :data="resumeData.summary"
                        :job-title="resumeData.personalData.title"
                        @update="resumeData.summary = $event"
                    />

                    <StepWorkHistory
                        v-else-if="currentStepKey === 'work'"
                        :data="resumeData.workHistory"
                        @update="resumeData.workHistory = $event"
                    />

                    <StepEducation
                        v-else-if="currentStepKey === 'education'"
                        :data="resumeData.education"
                        @update="resumeData.education = $event"
                    />

                    <StepLinks
                        v-else-if="currentStepKey === 'links'"
                        :data="resumeData.links"
                        @update="resumeData.links = $event"
                    />

                    <StepSkills
                        v-else-if="currentStepKey === 'skills'"
                        :data="resumeData.skills"
                        :customization="customization"
                        @update="resumeData.skills = $event"
                        @update-customization="onUpdateCustomization"
                    />

                    <StepAdditional
                        v-else-if="currentStepKey === 'additional'"
                        :data="resumeData.additional"
                        @update="resumeData.additional = $event"
                    />
                </div>

                <!-- Navigation -->
                <div class="shrink-0 px-6 py-4 border-t border-gray-100 space-y-2">
                    <!-- Next CTA -->
                    <button
                        v-if="!isLastStep"
                        type="button"
                        @click="nextStep"
                        class="w-full py-3 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-colors"
                    >
                        Próximo: {{ nextStepLabel }}
                    </button>

                    <!-- Last step: abre estilo antes do checkout -->
                    <button
                        v-else
                        type="button"
                        @click="showTemplateSelector = true"
                        class="w-full py-3 bg-green-600 text-white text-sm font-semibold rounded-xl hover:bg-green-700 transition-colors flex items-center justify-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Finalizar e Baixar PDF
                    </button>

                    <!-- Voltar (secondary) -->
                    <button
                        v-if="!isFirstStep"
                        type="button"
                        @click="prevStep"
                        class="w-full py-2 text-sm text-gray-500 hover:text-gray-700 transition-colors"
                    >
                        Voltar
                    </button>
                </div>
            </div>

            <!-- Right panel: preview -->
            <div class="flex-1 overflow-hidden">
                <PreviewPanel
                    :resume-data="resumeData"
                    :customization="customization"
                    :template="currentTemplate"
                    :save-status="saveStatus"
                />
            </div>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
