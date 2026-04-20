import { ref, computed } from 'vue'
import type { ResumeData, ResumeCustomization } from '@/types/resume'
import { defaultResumeData, defaultCustomization } from '@/types/resume'

export const STEPS = [
    { key: 'personal', label: 'Dados Pessoais', icon: '👤' },
    { key: 'work', label: 'Experiências', icon: '💼' },
    { key: 'education', label: 'Formação', icon: '🎓' },
    { key: 'skills', label: 'Habilidades', icon: '⚡' },
    { key: 'summary', label: 'Resumo', icon: '📝' },
    { key: 'links', label: 'Links', icon: '🔗' },
    { key: 'additional', label: 'Adicional', icon: '➕' },
] as const

export type StepKey = (typeof STEPS)[number]['key']

export function useResumeForm(
    initialData?: Partial<ResumeData>,
    initialCustomization?: Partial<ResumeCustomization>,
) {
    const resumeData = ref<ResumeData>({
        ...defaultResumeData(),
        ...initialData,
        // Deep-merge `additional` so old resumes (stored with `courses`) still work
        // and new fields (internships / references / activities) always exist
        additional: {
            ...defaultResumeData().additional,
            ...(initialData?.additional ?? {}),
        },
    })

    const customization = ref<ResumeCustomization>({
        ...defaultCustomization(),
        ...initialCustomization,
    })

    const currentStep = ref(0)

    const currentStepKey = computed(() => STEPS[currentStep.value].key)

    function goToStep(index: number) {
        if (index >= 0 && index < STEPS.length) {
            currentStep.value = index
        }
    }

    function nextStep() {
        goToStep(currentStep.value + 1)
    }

    function prevStep() {
        goToStep(currentStep.value - 1)
    }

    const isFirstStep = computed(() => currentStep.value === 0)
    const isLastStep = computed(() => currentStep.value === STEPS.length - 1)

    return {
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
    }
}
