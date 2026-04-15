<script setup lang="ts">
import type { STEPS } from '@/Composables/useResumeForm'

const props = defineProps<{
    steps: typeof STEPS
    currentStep: number
}>()

const emit = defineEmits<{
    (e: 'go-to', index: number): void
}>()
</script>

<template>
    <div class="flex items-center gap-1 px-4">
        <template v-for="(step, index) in steps" :key="step.key">
            <button
                type="button"
                @click="emit('go-to', index)"
                class="flex flex-col items-center gap-1 group"
                :title="step.label"
            >
                <div
                    class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold transition-all duration-200"
                    :class="{
                        'bg-blue-600 text-white shadow-md': index === currentStep,
                        'bg-blue-100 text-blue-700': index < currentStep,
                        'bg-gray-100 text-gray-400 group-hover:bg-gray-200': index > currentStep,
                    }"
                >
                    <svg v-if="index < currentStep" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    <span v-else>{{ index + 1 }}</span>
                </div>
                <span
                    class="text-xs font-medium hidden sm:block transition-colors"
                    :class="{
                        'text-blue-600': index === currentStep,
                        'text-blue-500': index < currentStep,
                        'text-gray-400': index > currentStep,
                    }"
                >{{ step.label }}</span>
            </button>

            <div
                v-if="index < steps.length - 1"
                class="flex-1 h-0.5 mt-[-10px] sm:mt-[-14px] transition-colors duration-200"
                :class="index < currentStep ? 'bg-blue-400' : 'bg-gray-200'"
            />
        </template>
    </div>
</template>
