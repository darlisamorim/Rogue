<script setup lang="ts">
import type { ResumeData, ResumeCustomization } from '@/types/resume'

defineProps<{
    resumeData: ResumeData
    customization: ResumeCustomization
}>()

function formatDate(date: string): string {
    if (!date) return ''
    const [y, m] = date.split('-')
    return `${m}/${y}`
}

function dateRange(start: string, end: string, current: boolean): string {
    const s = formatDate(start)
    const e = current ? 'present' : formatDate(end)
    return [s, e].filter(Boolean).join(' → ')
}
</script>

<template>
    <div
        class="w-[794px] min-h-[1123px] bg-gray-950 text-gray-100 p-10"
        :style="{ fontFamily: customization.font + ', JetBrains Mono, monospace' }"
    >
        <!-- Header -->
        <div class="border-b pb-6 mb-6" :style="{ borderColor: customization.color + '40' }">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-mono" :style="{ color: customization.color }">$ whoami</span>
                    <h1 class="text-2xl font-bold mt-1 text-white">{{ [resumeData.personalData.firstName, resumeData.personalData.lastName].filter(Boolean).join(' ') || 'Nome' }}</h1>
                    <p v-if="resumeData.personalData.title" class="text-sm mt-0.5" :style="{ color: customization.color }">
                        // {{ resumeData.personalData.title }}
                    </p>
                </div>
                <div class="text-right space-y-0.5">
                    <p v-if="resumeData.personalData.email" class="text-xs text-gray-400 font-mono">{{ resumeData.personalData.email }}</p>
                    <p v-if="resumeData.personalData.phone" class="text-xs text-gray-400 font-mono">{{ resumeData.personalData.phone }}</p>
                    <p v-if="resumeData.personalData.location" class="text-xs text-gray-400 font-mono">{{ resumeData.personalData.location }}</p>
                    <p v-if="resumeData.personalData.website" class="text-xs font-mono" :style="{ color: customization.color }">{{ resumeData.personalData.website }}</p>
                    <p v-for="link in resumeData.links" :key="link.id" class="text-xs font-mono" :style="{ color: customization.color }">{{ link.label }}: {{ link.url }}</p>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div v-if="resumeData.summary" class="mb-7">
            <p class="text-xs font-mono mb-2" :style="{ color: customization.color }">/** about **/</p>
            <p class="text-sm text-gray-300 leading-relaxed font-mono">{{ resumeData.summary }}</p>
        </div>

        <!-- Skills -->
        <div v-if="resumeData.skills.length > 0" class="mb-7">
            <p class="text-xs font-mono mb-3" :style="{ color: customization.color }">const skills = [</p>
            <div class="flex flex-wrap gap-2 ml-4">
                <span
                    v-for="skill in resumeData.skills"
                    :key="skill.id"
                    class="text-xs px-2.5 py-1 rounded font-mono border"
                    :style="{ borderColor: customization.color + '50', color: customization.color }"
                >{{ skill.name }}</span>
            </div>
            <p class="text-xs font-mono mt-2" :style="{ color: customization.color }">]</p>
        </div>

        <!-- Work History -->
        <div v-if="resumeData.workHistory.length > 0" class="mb-7">
            <p class="text-xs font-mono mb-4" :style="{ color: customization.color }">// experience</p>
            <div class="space-y-5">
                <div v-for="job in resumeData.workHistory" :key="job.id" class="border-l-2 pl-4" :style="{ borderColor: customization.color }">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-bold text-sm text-white font-mono">{{ job.role }}</p>
                            <p class="text-xs font-mono" :style="{ color: customization.color }">@{{ job.company }}</p>
                        </div>
                        <span class="text-xs text-gray-500 font-mono whitespace-nowrap">{{ dateRange(job.startDate, job.endDate, job.current) }}</span>
                    </div>
                    <p v-if="job.description" class="text-xs text-gray-400 mt-2 leading-relaxed font-mono">{{ job.description }}</p>
                </div>
            </div>
        </div>

        <!-- Education -->
        <div v-if="resumeData.education.length > 0" class="mb-7">
            <p class="text-xs font-mono mb-4" :style="{ color: customization.color }">// education</p>
            <div class="space-y-3">
                <div v-for="edu in resumeData.education" :key="edu.id" class="flex justify-between">
                    <div>
                        <p class="text-sm font-bold text-white font-mono">{{ edu.degree }}<span v-if="edu.field"> · {{ edu.field }}</span></p>
                        <p class="text-xs text-gray-400 font-mono">{{ edu.institution }}</p>
                    </div>
                    <span class="text-xs text-gray-500 font-mono whitespace-nowrap">{{ dateRange(edu.startDate, edu.endDate, edu.current) }}</span>
                </div>
            </div>
        </div>

        <!-- Languages + Certifications -->
        <div v-if="resumeData.additional.languages.length > 0 || resumeData.additional.certifications.length > 0" class="grid grid-cols-2 gap-6">
            <div v-if="resumeData.additional.languages.length > 0">
                <p class="text-xs font-mono mb-2" :style="{ color: customization.color }">// languages</p>
                <div class="space-y-1">
                    <p v-for="lang in resumeData.additional.languages" :key="lang.language" class="text-xs text-gray-400 font-mono">
                        <span :style="{ color: customization.color }">{{ lang.language }}</span>: {{ lang.level }}
                    </p>
                </div>
            </div>
            <div v-if="resumeData.additional.certifications.length > 0">
                <p class="text-xs font-mono mb-2" :style="{ color: customization.color }">// certs</p>
                <div class="space-y-1">
                    <p v-for="cert in resumeData.additional.certifications" :key="cert.name" class="text-xs text-gray-400 font-mono">
                        <span :style="{ color: customization.color }">{{ cert.name }}</span>
                        <span v-if="cert.issuer"> · {{ cert.issuer }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
