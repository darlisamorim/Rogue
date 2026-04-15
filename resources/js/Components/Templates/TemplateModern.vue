<script setup lang="ts">
import { computed } from 'vue'
import type { ResumeData, ResumeCustomization } from '@/types/resume'

const props = defineProps<{
    resumeData: ResumeData
    customization: ResumeCustomization
}>()

function formatDate(date: string): string {
    if (!date) return ''
    const [y, m] = date.split('-')
    const months = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez']
    return `${months[parseInt(m) - 1]}/${y}`
}

function dateRange(start: string, end: string, current: boolean): string {
    const s = formatDate(start)
    const e = current ? 'Atual' : formatDate(end)
    return [s, e].filter(Boolean).join(' – ')
}

// Tamanho base da fonte em px (responde ao slider: Pequeno / Normal / Grande)
const baseFontSize = computed(() => ({ sm: 9, md: 11, lg: 14 }[props.customization.fontSize] ?? 11))

// Tamanhos derivados (múltiplos do base)
const fs = computed(() => {
    const b = baseFontSize.value
    return {
        xxs:  `${Math.round(b * 0.7)}px`,
        xs:   `${Math.round(b * 0.9)}px`,
        sm:   `${Math.round(b)}px`,
        base: `${Math.round(b * 1.18)}px`,
        lg:   `${Math.round(b * 1.45)}px`,
        xl:   `${Math.round(b * 1.8)}px`,
    }
})

// Line-height baseado no espaçamento
const lh = computed(() => ({ compact: '1.25', normal: '1.6', relaxed: '2.0' }[props.customization.spacing] ?? '1.6'))

// Espaçamento entre seções (responde ao slider)
const sectionGap = computed(() => ({ compact: '12px', normal: '22px', relaxed: '34px' }[props.customization.spacing] ?? '22px'))

// Habilidades
const skillLabels: Record<number, string> = {
    1: 'Básico', 2: 'Básico', 3: 'Intermediário', 4: 'Avançado', 5: 'Especialista'
}
function skillPercent(level: number) {
    return level > 0 ? level * 20 : 0
}
</script>

<template>
    <div
        class="w-[794px] min-h-[1123px] bg-white flex"
        :style="{ fontFamily: customization.font + ', sans-serif' }"
    >
        <!-- ── SIDEBAR ── -->
        <div
            class="w-[220px] shrink-0 min-h-full flex flex-col"
            :style="{ backgroundColor: customization.color, padding: '32px 20px', gap: sectionGap }"
        >
            <!-- Foto + Nome -->
            <div class="flex flex-col items-center text-center gap-3">
                <!-- Foto de perfil -->
                <div
                    v-if="resumeData.personalData.photo"
                    class="w-[72px] h-[72px] rounded-full overflow-hidden border-[2.5px] border-white/40 shrink-0"
                >
                    <img :src="resumeData.personalData.photo" alt="Foto" class="w-full h-full object-cover" />
                </div>

                <!-- Nome e cargo -->
                <div>
                    <h1
                        class="font-bold text-white leading-tight"
                        :style="{ fontSize: fs.lg, lineHeight: '1.15' }"
                    >
                        {{ [resumeData.personalData.firstName, resumeData.personalData.lastName]
                            .filter(Boolean).join(' ') || 'Seu Nome' }}
                    </h1>
                    <p
                        v-if="resumeData.personalData.title"
                        class="text-white/75 mt-1"
                        :style="{ fontSize: fs.xs }"
                    >
                        {{ resumeData.personalData.title }}
                    </p>
                </div>
            </div>

            <div class="border-t border-white/20" />

            <!-- Contato -->
            <div class="space-y-1.5">
                <p
                    class="font-bold uppercase tracking-widest text-white/50 mb-2"
                    :style="{ fontSize: fs.xxs }"
                >Contato</p>

                <div v-if="resumeData.personalData.email" class="flex items-start gap-1.5">
                    <span class="text-white/55 shrink-0 mt-px" :style="{ fontSize: fs.xs }">✉</span>
                    <span class="text-white/90 break-all" :style="{ fontSize: fs.xs, lineHeight: lh }">{{ resumeData.personalData.email }}</span>
                </div>
                <div v-if="resumeData.personalData.phone" class="flex items-start gap-1.5">
                    <span class="text-white/55 shrink-0" :style="{ fontSize: fs.xs }">📱</span>
                    <span class="text-white/90" :style="{ fontSize: fs.xs }">{{ resumeData.personalData.phone }}</span>
                </div>
                <div v-if="resumeData.personalData.location" class="flex items-start gap-1.5">
                    <span class="text-white/55 shrink-0" :style="{ fontSize: fs.xs }">📍</span>
                    <span class="text-white/90" :style="{ fontSize: fs.xs }">
                        {{ resumeData.personalData.location }}{{ resumeData.personalData.country ? ', ' + resumeData.personalData.country : '' }}
                    </span>
                </div>
                <div v-if="resumeData.personalData.website" class="flex items-start gap-1.5">
                    <span class="text-white/55 shrink-0" :style="{ fontSize: fs.xs }">🌐</span>
                    <span class="text-white/90 break-all" :style="{ fontSize: fs.xs }">{{ resumeData.personalData.website }}</span>
                </div>
                <div v-if="resumeData.personalData.linkedIn" class="flex items-start gap-1.5">
                    <span class="text-white/55 font-bold shrink-0" :style="{ fontSize: fs.xs }">in</span>
                    <span class="text-white/90 break-all" :style="{ fontSize: fs.xs }">{{ resumeData.personalData.linkedIn }}</span>
                </div>
                <div v-if="resumeData.personalData.dateOfBirth" class="flex items-start gap-1.5">
                    <span class="text-white/55 shrink-0" :style="{ fontSize: fs.xs }">📅</span>
                    <span class="text-white/90" :style="{ fontSize: fs.xs }">{{ resumeData.personalData.dateOfBirth }}</span>
                </div>
                <div v-if="resumeData.personalData.nationality" class="flex items-start gap-1.5">
                    <span class="text-white/55 shrink-0" :style="{ fontSize: fs.xs }">🌍</span>
                    <span class="text-white/90" :style="{ fontSize: fs.xs }">{{ resumeData.personalData.nationality }}</span>
                </div>
                <div v-if="resumeData.personalData.drivingLicense" class="flex items-start gap-1.5">
                    <span class="text-white/55 shrink-0" :style="{ fontSize: fs.xs }">🚗</span>
                    <span class="text-white/90" :style="{ fontSize: fs.xs }">CNH {{ resumeData.personalData.drivingLicense }}</span>
                </div>
                <div v-for="link in resumeData.links" :key="link.id" class="flex items-start gap-1.5">
                    <span class="text-white/55 shrink-0" :style="{ fontSize: fs.xs }">🔗</span>
                    <span class="text-white/90 break-all" :style="{ fontSize: fs.xs }">{{ link.label }}</span>
                </div>
            </div>

            <!-- Habilidades -->
            <div v-if="resumeData.skills.length > 0">
                <p class="font-bold uppercase tracking-widest text-white/50 mb-3" :style="{ fontSize: fs.xxs }">Habilidades</p>
                <div class="flex flex-col gap-2.5">
                    <div v-for="skill in resumeData.skills" :key="skill.id">
                        <div class="flex items-center justify-between mb-0.5">
                            <span class="text-white/90" :style="{ fontSize: fs.xs }">{{ skill.name }}</span>
                            <span
                                v-if="skill.level > 0 && customization.showSkillLevels"
                                class="text-white/45"
                                :style="{ fontSize: fs.xxs }"
                            >{{ skillLabels[skill.level] }}</span>
                        </div>
                        <!-- Barra de nível -->
                        <div
                            v-if="skill.level > 0 && customization.showSkillLevels"
                            class="h-[3px] rounded-full bg-white/15 overflow-hidden"
                        >
                            <div
                                class="h-full rounded-full bg-white/65"
                                :style="{ width: skillPercent(skill.level) + '%' }"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Idiomas -->
            <div v-if="resumeData.additional.languages.length > 0">
                <p class="font-bold uppercase tracking-widest text-white/50 mb-3" :style="{ fontSize: fs.xxs }">Idiomas</p>
                <div class="space-y-1.5">
                    <div v-for="lang in resumeData.additional.languages" :key="lang.language">
                        <p class="text-white/90" :style="{ fontSize: fs.xs }">{{ lang.language }}</p>
                        <p class="text-white/50" :style="{ fontSize: fs.xxs }">{{ lang.level }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── CONTEÚDO PRINCIPAL ── -->
        <div class="flex-1 p-8" :style="{ display: 'flex', flexDirection: 'column', gap: sectionGap }">

            <!-- Resumo -->
            <div v-if="resumeData.summary">
                <h2
                    class="font-bold uppercase tracking-wider mb-2 pb-1 border-b-2"
                    :style="{ color: customization.color, borderColor: customization.color, fontSize: fs.xs }"
                >Resumo</h2>
                <p class="text-gray-700" :style="{ fontSize: fs.xs, lineHeight: lh }">{{ resumeData.summary }}</p>
            </div>

            <!-- Experiência -->
            <div v-if="resumeData.workHistory.length > 0">
                <h2
                    class="font-bold uppercase tracking-wider mb-3 pb-1 border-b-2"
                    :style="{ color: customization.color, borderColor: customization.color, fontSize: fs.xs }"
                >Experiência</h2>
                <div class="space-y-4">
                    <div v-for="job in resumeData.workHistory" :key="job.id">
                        <div class="flex justify-between items-start gap-2">
                            <div>
                                <p class="font-semibold text-gray-900" :style="{ fontSize: fs.sm, lineHeight: lh }">{{ job.role }}</p>
                                <p class="text-gray-500" :style="{ fontSize: fs.xs, lineHeight: lh }">
                                    {{ job.company }}<span v-if="job.location"> · {{ job.location }}</span>
                                </p>
                            </div>
                            <span class="text-gray-400 whitespace-nowrap shrink-0" :style="{ fontSize: fs.xs, lineHeight: lh }">
                                {{ dateRange(job.startDate, job.endDate, job.current) }}
                            </span>
                        </div>
                        <p v-if="job.description" class="text-gray-600 mt-1.5" :style="{ fontSize: fs.xs, lineHeight: lh }">{{ job.description }}</p>
                    </div>
                </div>
            </div>

            <!-- Formação -->
            <div v-if="resumeData.education.length > 0">
                <h2
                    class="font-bold uppercase tracking-wider mb-3 pb-1 border-b-2"
                    :style="{ color: customization.color, borderColor: customization.color, fontSize: fs.xs }"
                >Formação</h2>
                <div class="space-y-3">
                    <div v-for="edu in resumeData.education" :key="edu.id">
                        <div class="flex justify-between items-start gap-2">
                            <div>
                                <p class="font-semibold text-gray-900" :style="{ fontSize: fs.sm, lineHeight: lh }">
                                    {{ edu.degree }}<span v-if="edu.field"> em {{ edu.field }}</span>
                                </p>
                                <p class="text-gray-500" :style="{ fontSize: fs.xs, lineHeight: lh }">{{ edu.institution }}</p>
                            </div>
                            <span class="text-gray-400 whitespace-nowrap shrink-0" :style="{ fontSize: fs.xs, lineHeight: lh }">
                                {{ dateRange(edu.startDate, edu.endDate, edu.current) }}
                            </span>
                        </div>
                        <p v-if="edu.description" class="text-gray-600 mt-1" :style="{ fontSize: fs.xs, lineHeight: lh }">{{ edu.description }}</p>
                    </div>
                </div>
            </div>

            <!-- Certificações -->
            <div v-if="resumeData.additional.certifications.length > 0">
                <h2
                    class="font-bold uppercase tracking-wider mb-3 pb-1 border-b-2"
                    :style="{ color: customization.color, borderColor: customization.color, fontSize: fs.xs }"
                >Certificações</h2>
                <div class="space-y-1.5">
                    <p v-for="cert in resumeData.additional.certifications" :key="cert.name" class="text-gray-700" :style="{ fontSize: fs.xs }">
                        <span class="font-medium">{{ cert.name }}</span>
                        <span v-if="cert.issuer" class="text-gray-500"> · {{ cert.issuer }}</span>
                        <span v-if="cert.year" class="text-gray-400"> ({{ cert.year }})</span>
                    </p>
                </div>
            </div>

            <!-- Cursos -->
            <div v-if="resumeData.additional.courses.length > 0">
                <h2
                    class="font-bold uppercase tracking-wider mb-3 pb-1 border-b-2"
                    :style="{ color: customization.color, borderColor: customization.color, fontSize: fs.xs }"
                >Cursos</h2>
                <ul class="list-disc list-inside space-y-0.5">
                    <li v-for="course in resumeData.additional.courses" :key="course" class="text-gray-700" :style="{ fontSize: fs.xs }">{{ course }}</li>
                </ul>
            </div>
        </div>
    </div>
</template>
