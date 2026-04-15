<script setup lang="ts">
import type { ResumeData, ResumeCustomization } from '@/types/resume'

defineProps<{
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
    return [s, e].filter(Boolean).join(' – ')
}
</script>

<template>
    <div
        class="w-[794px] min-h-[1123px] bg-white font-sans"
        :style="{ fontFamily: customization.font + ', sans-serif' }"
    >
        <!-- Big colored header -->
        <div class="px-12 pt-10 pb-8" :style="{ backgroundColor: customization.color }">
            <h1 class="text-4xl font-black text-white leading-none tracking-tight">
                {{ [resumeData.personalData.firstName, resumeData.personalData.lastName].filter(Boolean).join(' ') || 'Seu Nome' }}
            </h1>
            <p v-if="resumeData.personalData.title" class="text-lg text-white/80 mt-2 font-medium">
                {{ resumeData.personalData.title }}
            </p>
            <div class="flex flex-wrap gap-x-5 mt-4 text-sm text-white/70">
                <span v-if="resumeData.personalData.email">{{ resumeData.personalData.email }}</span>
                <span v-if="resumeData.personalData.phone">{{ resumeData.personalData.phone }}</span>
                <span v-if="resumeData.personalData.location">{{ resumeData.personalData.location }}</span>
                <span v-if="resumeData.personalData.website">{{ resumeData.personalData.website }}</span>
            </div>
        </div>

        <div class="px-12 py-8 space-y-7">
            <!-- Summary -->
            <div v-if="resumeData.summary" class="bg-gray-50 rounded-xl p-5 border-l-4" :style="{ borderColor: customization.color }">
                <p class="text-sm text-gray-700 leading-relaxed">{{ resumeData.summary }}</p>
            </div>

            <!-- Work History -->
            <div v-if="resumeData.workHistory.length > 0">
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-0.5 flex-1 bg-gray-200" />
                    <h2 class="text-sm font-bold uppercase tracking-widest" :style="{ color: customization.color }">Experiência</h2>
                    <div class="h-0.5 flex-1 bg-gray-200" />
                </div>
                <div class="space-y-5">
                    <div v-for="job in resumeData.workHistory" :key="job.id" class="flex gap-4">
                        <div class="w-1.5 shrink-0 rounded-full mt-1.5" :style="{ backgroundColor: customization.color + '40' }" />
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-bold text-sm text-gray-900">{{ job.role }}</p>
                                    <p class="text-sm font-medium" :style="{ color: customization.color }">{{ job.company }}<span v-if="job.location" class="text-gray-500 font-normal"> · {{ job.location }}</span></p>
                                </div>
                                <span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full whitespace-nowrap">{{ dateRange(job.startDate, job.endDate, job.current) }}</span>
                            </div>
                            <p v-if="job.description" class="text-xs text-gray-600 mt-2 leading-relaxed">{{ job.description }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2-column: Education + Skills -->
            <div class="grid grid-cols-2 gap-6">
                <div v-if="resumeData.education.length > 0">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="h-0.5 flex-1 bg-gray-200" />
                        <h2 class="text-sm font-bold uppercase tracking-widest" :style="{ color: customization.color }">Formação</h2>
                        <div class="h-0.5 flex-1 bg-gray-200" />
                    </div>
                    <div class="space-y-3">
                        <div v-for="edu in resumeData.education" :key="edu.id">
                            <p class="font-semibold text-sm text-gray-900">{{ edu.degree }}</p>
                            <p class="text-xs" :style="{ color: customization.color }">{{ edu.field }}</p>
                            <p class="text-xs text-gray-500">{{ edu.institution }}</p>
                            <p class="text-xs text-gray-400">{{ dateRange(edu.startDate, edu.endDate, edu.current) }}</p>
                        </div>
                    </div>
                </div>

                <div>
                    <div v-if="resumeData.skills.length > 0" class="mb-4">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="h-0.5 flex-1 bg-gray-200" />
                            <h2 class="text-sm font-bold uppercase tracking-widest" :style="{ color: customization.color }">Skills</h2>
                            <div class="h-0.5 flex-1 bg-gray-200" />
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            <span
                                v-for="skill in resumeData.skills"
                                :key="skill.id"
                                class="text-xs px-2.5 py-1 rounded-full text-white font-medium"
                                :style="{ backgroundColor: customization.color }"
                            >{{ skill.name }}</span>
                        </div>
                    </div>
                    <div v-if="resumeData.additional.languages.length > 0">
                        <p class="text-xs font-bold uppercase tracking-wider mb-2" :style="{ color: customization.color }">Idiomas</p>
                        <div class="space-y-1">
                            <p v-for="lang in resumeData.additional.languages" :key="lang.language" class="text-xs text-gray-700">
                                <span class="font-medium">{{ lang.language }}</span> · {{ lang.level }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
