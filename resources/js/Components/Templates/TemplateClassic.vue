<script setup lang="ts">
import type { ResumeData, ResumeCustomization } from '@/types/resume'

defineProps<{
    resumeData: ResumeData
    customization: ResumeCustomization
}>()

function formatDate(date: string): string {
    if (!date) return ''
    const [y, m] = date.split('-')
    const months = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro']
    return `${months[parseInt(m) - 1]} de ${y}`
}

function dateRange(start: string, end: string, current: boolean): string {
    const s = formatDate(start)
    const e = current ? 'Presente' : formatDate(end)
    return [s, e].filter(Boolean).join(' a ')
}
</script>

<template>
    <div
        class="w-[794px] min-h-[1123px] bg-white text-gray-900 px-14 py-12"
        :style="{ fontFamily: customization.font + ', Georgia, serif' }"
    >
        <!-- Header -->
        <div class="text-center mb-6 pb-6 border-b-2 border-gray-900">
            <h1 class="text-3xl font-bold uppercase tracking-widest text-gray-900">
                {{ [resumeData.personalData.firstName, resumeData.personalData.lastName].filter(Boolean).join(' ') || 'Seu Nome' }}
            </h1>
            <p v-if="resumeData.personalData.title" class="text-base mt-1 font-medium" :style="{ color: customization.color }">
                {{ resumeData.personalData.title }}
            </p>
            <div class="flex flex-wrap justify-center gap-x-5 mt-3 text-xs text-gray-600">
                <span v-if="resumeData.personalData.email">{{ resumeData.personalData.email }}</span>
                <span v-if="resumeData.personalData.phone">{{ resumeData.personalData.phone }}</span>
                <span v-if="resumeData.personalData.location">{{ resumeData.personalData.location }}</span>
                <span v-if="resumeData.personalData.website">{{ resumeData.personalData.website }}</span>
            </div>
        </div>

        <!-- Summary -->
        <div v-if="resumeData.summary" class="mb-7">
            <h2 class="text-sm font-bold uppercase tracking-widest mb-2 text-center" :style="{ color: customization.color }">
                Objetivo Profissional
            </h2>
            <p class="text-sm text-gray-700 leading-relaxed text-center italic">{{ resumeData.summary }}</p>
        </div>

        <!-- Work History -->
        <div v-if="resumeData.workHistory.length > 0" class="mb-7">
            <h2 class="text-sm font-bold uppercase tracking-widest mb-3 text-center border-b border-gray-300 pb-2" :style="{ color: customization.color }">
                Experiência Profissional
            </h2>
            <div class="space-y-5">
                <div v-for="job in resumeData.workHistory" :key="job.id">
                    <div class="flex justify-between items-baseline">
                        <p class="font-bold text-sm text-gray-900">{{ job.role }}</p>
                        <span class="text-xs text-gray-500 italic">{{ dateRange(job.startDate, job.endDate, job.current) }}</span>
                    </div>
                    <p class="text-sm font-medium" :style="{ color: customization.color }">{{ job.company }}</p>
                    <p v-if="job.location" class="text-xs text-gray-500">{{ job.location }}</p>
                    <p v-if="job.description" class="text-xs text-gray-700 mt-2 leading-relaxed">{{ job.description }}</p>
                </div>
            </div>
        </div>

        <!-- Education -->
        <div v-if="resumeData.education.length > 0" class="mb-7">
            <h2 class="text-sm font-bold uppercase tracking-widest mb-3 text-center border-b border-gray-300 pb-2" :style="{ color: customization.color }">
                Formação Acadêmica
            </h2>
            <div class="space-y-4">
                <div v-for="edu in resumeData.education" :key="edu.id">
                    <div class="flex justify-between items-baseline">
                        <p class="font-bold text-sm">{{ edu.degree }}<span v-if="edu.field"> em {{ edu.field }}</span></p>
                        <span class="text-xs text-gray-500 italic">{{ dateRange(edu.startDate, edu.endDate, edu.current) }}</span>
                    </div>
                    <p class="text-sm" :style="{ color: customization.color }">{{ edu.institution }}</p>
                </div>
            </div>
        </div>

        <!-- Skills + Languages -->
        <div class="grid grid-cols-2 gap-6 mb-7">
            <div v-if="resumeData.skills.length > 0">
                <h2 class="text-sm font-bold uppercase tracking-widest mb-2 border-b border-gray-300 pb-1" :style="{ color: customization.color }">
                    Habilidades
                </h2>
                <ul class="text-xs text-gray-700 space-y-1 list-disc list-inside">
                    <li v-for="skill in resumeData.skills" :key="skill.id">{{ skill.name }}</li>
                </ul>
            </div>
            <div v-if="resumeData.additional.languages.length > 0">
                <h2 class="text-sm font-bold uppercase tracking-widest mb-2 border-b border-gray-300 pb-1" :style="{ color: customization.color }">
                    Idiomas
                </h2>
                <ul class="text-xs text-gray-700 space-y-1 list-disc list-inside">
                    <li v-for="lang in resumeData.additional.languages" :key="lang.language">
                        {{ lang.language }} — {{ lang.level }}
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
