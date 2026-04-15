<script setup lang="ts">
import type { ResumeData, ResumeCustomization } from '@/types/resume'

const props = defineProps<{
    resumeData: ResumeData
    customization: ResumeCustomization
}>()

function formatDate(date: string): string {
    if (!date) return ''
    const [y, m] = date.split('-')
    const months = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez']
    return `${months[parseInt(m) - 1]} ${y}`
}

function dateRange(start: string, end: string, current: boolean): string {
    const s = formatDate(start)
    const e = current ? 'Atual' : formatDate(end)
    if (!s && !e) return ''
    return [s, e].filter(Boolean).join(' – ')
}
</script>

<template>
    <div
        class="w-[794px] min-h-[1123px] bg-white text-gray-800 font-sans p-12"
        :style="{ '--accent': customization.color, fontFamily: customization.font + ', sans-serif' }"
    >
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
                {{ [resumeData.personalData.firstName, resumeData.personalData.lastName].filter(Boolean).join(' ') || 'Seu Nome' }}
            </h1>
            <p v-if="resumeData.personalData.title" class="text-lg mt-1" :style="{ color: customization.color }">
                {{ resumeData.personalData.title }}
            </p>
            <div class="flex flex-wrap gap-x-4 gap-y-1 mt-3 text-sm text-gray-500">
                <span v-if="resumeData.personalData.email">{{ resumeData.personalData.email }}</span>
                <span v-if="resumeData.personalData.phone">{{ resumeData.personalData.phone }}</span>
                <span v-if="resumeData.personalData.location">{{ resumeData.personalData.location }}</span>
                <span v-if="resumeData.personalData.website">{{ resumeData.personalData.website }}</span>
                <span v-for="link in resumeData.links" :key="link.id">{{ link.label }}: {{ link.url }}</span>
            </div>
        </div>

        <div class="border-t-2 mb-8" :style="{ borderColor: customization.color }" />

        <!-- Summary -->
        <div v-if="resumeData.summary" class="mb-8">
            <h2 class="text-xs font-bold uppercase tracking-widest mb-3" :style="{ color: customization.color }">Resumo</h2>
            <p class="text-sm text-gray-700 leading-relaxed">{{ resumeData.summary }}</p>
        </div>

        <!-- Work History -->
        <div v-if="resumeData.workHistory.length > 0" class="mb-8">
            <h2 class="text-xs font-bold uppercase tracking-widest mb-4" :style="{ color: customization.color }">Experiência</h2>
            <div class="space-y-5">
                <div v-for="job in resumeData.workHistory" :key="job.id">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-semibold text-sm text-gray-900">{{ job.role }}</p>
                            <p class="text-sm text-gray-600">{{ job.company }}<span v-if="job.location"> · {{ job.location }}</span></p>
                        </div>
                        <span class="text-xs text-gray-400 whitespace-nowrap mt-0.5">{{ dateRange(job.startDate, job.endDate, job.current) }}</span>
                    </div>
                    <p v-if="job.description" class="text-xs text-gray-600 mt-1.5 leading-relaxed">{{ job.description }}</p>
                </div>
            </div>
        </div>

        <!-- Education -->
        <div v-if="resumeData.education.length > 0" class="mb-8">
            <h2 class="text-xs font-bold uppercase tracking-widest mb-4" :style="{ color: customization.color }">Formação</h2>
            <div class="space-y-4">
                <div v-for="edu in resumeData.education" :key="edu.id">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-semibold text-sm text-gray-900">{{ edu.degree }} <span v-if="edu.field">em {{ edu.field }}</span></p>
                            <p class="text-sm text-gray-600">{{ edu.institution }}</p>
                        </div>
                        <span class="text-xs text-gray-400 whitespace-nowrap mt-0.5">{{ dateRange(edu.startDate, edu.endDate, edu.current) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Skills -->
        <div v-if="resumeData.skills.length > 0" class="mb-8">
            <h2 class="text-xs font-bold uppercase tracking-widest mb-3" :style="{ color: customization.color }">Habilidades</h2>
            <div class="flex flex-wrap gap-2">
                <span
                    v-for="skill in resumeData.skills"
                    :key="skill.id"
                    class="text-xs px-2.5 py-1 rounded border text-gray-700"
                    :style="{ borderColor: customization.color + '60' }"
                >{{ skill.name }}</span>
            </div>
        </div>

        <!-- Additional -->
        <div v-if="resumeData.additional.languages.length > 0 || resumeData.additional.certifications.length > 0">
            <h2 class="text-xs font-bold uppercase tracking-widest mb-3" :style="{ color: customization.color }">Complementar</h2>
            <div v-if="resumeData.additional.languages.length > 0" class="mb-3">
                <p class="text-xs font-medium text-gray-500 mb-1">Idiomas</p>
                <div class="flex flex-wrap gap-x-4 gap-y-1">
                    <span v-for="lang in resumeData.additional.languages" :key="lang.language" class="text-sm text-gray-700">
                        {{ lang.language }} <span class="text-gray-400">({{ lang.level }})</span>
                    </span>
                </div>
            </div>
            <div v-if="resumeData.additional.certifications.length > 0">
                <p class="text-xs font-medium text-gray-500 mb-1">Certificações</p>
                <div class="space-y-1">
                    <p v-for="cert in resumeData.additional.certifications" :key="cert.name" class="text-sm text-gray-700">
                        {{ cert.name }}<span v-if="cert.issuer" class="text-gray-400"> · {{ cert.issuer }}</span><span v-if="cert.year" class="text-gray-400"> ({{ cert.year }})</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
