<script setup lang="ts">
import { ref } from 'vue'
import type { AdditionalData, LanguageEntry, CertificationEntry } from '@/types/resume'

const props = defineProps<{
    data: AdditionalData
}>()

const emit = defineEmits<{
    (e: 'update', value: AdditionalData): void
}>()

type SectionKey = 'languages' | 'certifications' | 'courses' | 'hobbies' | 'references' | 'internships'
const activeSection = ref<SectionKey | null>(null)

const sections = [
    { key: 'languages' as SectionKey, label: 'Idiomas', icon: '🌍', description: 'Idiomas que você fala' },
    { key: 'certifications' as SectionKey, label: 'Cursos', icon: '📚', description: 'Certificados e cursos' },
    { key: 'courses' as SectionKey, label: 'Estágios', icon: '🏢', description: 'Estágios realizados' },
    { key: 'hobbies' as SectionKey, label: 'Passatempos', icon: '🎯', description: 'Hobbies e interesses' },
    { key: 'references' as SectionKey, label: 'Referências', icon: '👥', description: 'Referências profissionais' },
    { key: 'internships' as SectionKey, label: 'Atividades', icon: '⭐', description: 'Atividades extracurriculares' },
]

function toggleSection(key: SectionKey) {
    activeSection.value = activeSection.value === key ? null : key
}

// Languages
function addLanguage() {
    emit('update', {
        ...props.data,
        languages: [...props.data.languages, { language: '', level: 'Intermediário' }],
    })
}
function removeLanguage(index: number) {
    emit('update', {
        ...props.data,
        languages: props.data.languages.filter((_, i) => i !== index),
    })
}
function updateLanguage(index: number, field: keyof LanguageEntry, value: string) {
    const langs = [...props.data.languages]
    langs[index] = { ...langs[index], [field]: value }
    emit('update', { ...props.data, languages: langs })
}

// Certifications
function addCertification() {
    emit('update', {
        ...props.data,
        certifications: [...props.data.certifications, { name: '', issuer: '', year: '' }],
    })
}
function removeCertification(index: number) {
    emit('update', {
        ...props.data,
        certifications: props.data.certifications.filter((_, i) => i !== index),
    })
}
function updateCertification(index: number, field: keyof CertificationEntry, value: string) {
    const certs = [...props.data.certifications]
    certs[index] = { ...certs[index], [field]: value }
    emit('update', { ...props.data, certifications: certs })
}

// Hobbies
const hobbyInput = ref('')
function addHobby() {
    const h = hobbyInput.value.trim()
    if (h) {
        emit('update', { ...props.data, hobbies: [...props.data.hobbies, h] })
        hobbyInput.value = ''
    }
}
function removeHobby(index: number) {
    emit('update', { ...props.data, hobbies: props.data.hobbies.filter((_, i) => i !== index) })
}

// Courses (using courses array)
const courseInput = ref('')
function addCourse() {
    const c = courseInput.value.trim()
    if (c) {
        emit('update', { ...props.data, courses: [...props.data.courses, c] })
        courseInput.value = ''
    }
}
function removeCourse(index: number) {
    emit('update', { ...props.data, courses: props.data.courses.filter((_, i) => i !== index) })
}

const levelOptions = ['Básico', 'Pré-Intermediário', 'Intermediário', 'Avançado', 'Fluente', 'Nativo']

function hasContent(key: SectionKey): boolean {
    if (key === 'languages') return props.data.languages.length > 0
    if (key === 'certifications') return props.data.certifications.length > 0
    if (key === 'courses') return props.data.courses.length > 0
    if (key === 'hobbies') return props.data.hobbies.length > 0
    return false
}
</script>

<template>
    <div class="space-y-5">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">Adicionar seções adicionais</h2>
            <p class="text-sm text-gray-500 mt-0.5">
                Adicione seções extras para destacar outras informações relevantes do seu currículo.
            </p>
        </div>

        <!-- Seções não abertas: grid de cards -->
        <div v-if="!activeSection" class="grid grid-cols-2 gap-3">
            <button
                v-for="section in sections"
                :key="section.key"
                type="button"
                @click="toggleSection(section.key)"
                class="relative flex items-center gap-3 p-4 rounded-xl border-2 text-left transition-all hover:border-blue-300 hover:bg-blue-50"
                :class="hasContent(section.key)
                    ? 'border-blue-200 bg-blue-50'
                    : 'border-gray-200 bg-white'"
            >
                <span class="text-2xl">{{ section.icon }}</span>
                <div>
                    <p class="text-sm font-semibold text-gray-800">{{ section.label }}</p>
                    <p class="text-xs text-gray-400">{{ section.description }}</p>
                </div>
                <!-- Badge de conteúdo -->
                <div
                    v-if="hasContent(section.key)"
                    class="absolute top-2 right-2 w-2 h-2 rounded-full bg-blue-500"
                />
            </button>
        </div>

        <!-- Seção aberta: form específico -->
        <div v-else>
            <button
                type="button"
                @click="activeSection = null"
                class="flex items-center gap-1.5 text-sm text-blue-600 font-medium mb-4 hover:text-blue-700"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Voltar às seções
            </button>

            <!-- IDIOMAS -->
            <div v-if="activeSection === 'languages'" class="space-y-3">
                <h3 class="text-base font-semibold text-gray-800 flex items-center gap-2">🌍 Idiomas</h3>

                <div v-if="data.languages.length === 0" class="text-sm text-gray-400 py-4 text-center border border-dashed rounded-lg">
                    Nenhum idioma adicionado.
                </div>

                <div v-for="(lang, i) in data.languages" :key="i" class="flex gap-2 items-center">
                    <input
                        type="text"
                        :value="lang.language"
                        @input="updateLanguage(i, 'language', ($event.target as HTMLInputElement).value)"
                        placeholder="Inglês"
                        class="flex-1 rounded-lg border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-blue-500 focus:ring-blue-500"
                    />
                    <select
                        :value="lang.level"
                        @change="updateLanguage(i, 'level', ($event.target as HTMLSelectElement).value)"
                        class="rounded-lg border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option v-for="lv in levelOptions" :key="lv" :value="lv">{{ lv }}</option>
                    </select>
                    <button type="button" @click="removeLanguage(i)" class="text-gray-300 hover:text-red-400 p-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <button type="button" @click="addLanguage" class="text-sm text-blue-600 font-medium hover:text-blue-700 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Adicionar idioma
                </button>
            </div>

            <!-- CERTIFICAÇÕES / CURSOS -->
            <div v-else-if="activeSection === 'certifications'" class="space-y-3">
                <h3 class="text-base font-semibold text-gray-800 flex items-center gap-2">📚 Cursos e Certificações</h3>

                <div v-if="data.certifications.length === 0" class="text-sm text-gray-400 py-4 text-center border border-dashed rounded-lg">
                    Nenhuma certificação adicionada.
                </div>

                <div v-for="(cert, i) in data.certifications" :key="i" class="border border-gray-200 rounded-lg p-3 space-y-2">
                    <div class="flex justify-between items-start">
                        <input
                            type="text"
                            :value="cert.name"
                            @input="updateCertification(i, 'name', ($event.target as HTMLInputElement).value)"
                            placeholder="Nome do curso / certificação"
                            class="flex-1 rounded-lg border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-blue-500 focus:ring-blue-500"
                        />
                        <button type="button" @click="removeCertification(i)" class="text-gray-300 hover:text-red-400 p-1 ml-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <input
                            type="text"
                            :value="cert.issuer"
                            @input="updateCertification(i, 'issuer', ($event.target as HTMLInputElement).value)"
                            placeholder="Emitido por (AWS, Google...)"
                            class="rounded-lg border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-blue-500 focus:ring-blue-500"
                        />
                        <input
                            type="text"
                            :value="cert.year"
                            @input="updateCertification(i, 'year', ($event.target as HTMLInputElement).value)"
                            placeholder="Ano (2024)"
                            class="rounded-lg border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>
                </div>

                <button type="button" @click="addCertification" class="text-sm text-blue-600 font-medium hover:text-blue-700 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Adicionar certificação
                </button>
            </div>

            <!-- PASSATEMPOS -->
            <div v-else-if="activeSection === 'hobbies'" class="space-y-3">
                <h3 class="text-base font-semibold text-gray-800 flex items-center gap-2">🎯 Passatempos</h3>
                <div class="flex flex-wrap gap-2">
                    <span
                        v-for="(h, i) in data.hobbies"
                        :key="i"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm bg-blue-50 text-blue-700 border border-blue-200"
                    >
                        {{ h }}
                        <button type="button" @click="removeHobby(i)" class="hover:text-red-500">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </span>
                </div>
                <div class="flex gap-2">
                    <input
                        v-model="hobbyInput"
                        type="text"
                        @keydown.enter.prevent="addHobby"
                        placeholder="Ex: Leitura, Fotografia..."
                        class="flex-1 rounded-lg border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-blue-500 focus:ring-blue-500"
                    />
                    <button type="button" @click="addHobby" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">Adicionar</button>
                </div>
            </div>

            <!-- CURSOS (estágios / extracurriculares) -->
            <div v-else class="space-y-3">
                <h3 class="text-base font-semibold text-gray-800">
                    {{ sections.find(s => s.key === activeSection)?.icon }}
                    {{ sections.find(s => s.key === activeSection)?.label }}
                </h3>
                <div class="flex flex-wrap gap-2">
                    <span
                        v-for="(c, i) in data.courses"
                        :key="i"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm bg-gray-100 text-gray-700 border border-gray-200"
                    >
                        {{ c }}
                        <button type="button" @click="removeCourse(i)" class="hover:text-red-500">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </button>
                    </span>
                </div>
                <div class="flex gap-2">
                    <input
                        v-model="courseInput"
                        type="text"
                        @keydown.enter.prevent="addCourse"
                        placeholder="Adicionar item..."
                        class="flex-1 rounded-lg border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-blue-500 focus:ring-blue-500"
                    />
                    <button type="button" @click="addCourse" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">Adicionar</button>
                </div>
            </div>
        </div>
    </div>
</template>
