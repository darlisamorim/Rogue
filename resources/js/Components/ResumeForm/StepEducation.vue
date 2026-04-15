<script setup lang="ts">
import { ref } from 'vue'
import type { EducationEntry } from '@/types/resume'

const props = defineProps<{
    data: EducationEntry[]
}>()

const emit = defineEmits<{
    (e: 'update', value: EducationEntry[]): void
}>()

function addEntry() {
    const entry: EducationEntry = {
        id: crypto.randomUUID(),
        institution: '',
        degree: '',
        field: '',
        startDate: '',
        endDate: '',
        current: false,
        description: '',
    }
    emit('update', [...props.data, entry])
}

function removeEntry(id: string) {
    emit('update', props.data.filter((e) => e.id !== id))
}

function updateEntry(id: string, field: keyof EducationEntry, value: string | boolean) {
    emit(
        'update',
        props.data.map((e) => (e.id === id ? { ...e, [field]: value } : e)),
    )
}

const expandedId = ref<string | null>(props.data[0]?.id ?? null)

const degreeOptions = [
    'Ensino Médio',
    'Técnico',
    'Tecnólogo',
    'Graduação (Bacharelado)',
    'Graduação (Licenciatura)',
    'Pós-Graduação / Especialização',
    'MBA',
    'Mestrado',
    'Doutorado',
    'Curso Livre',
    'Bootcamp',
]
</script>

<template>
    <div class="space-y-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">Formação Acadêmica</h2>
            <p class="text-sm text-gray-500 mt-0.5">Adicione sua formação da mais recente para a mais antiga.</p>
        </div>

        <div v-if="data.length === 0" class="text-center py-8 text-gray-400 border-2 border-dashed rounded-lg">
            <p class="text-sm">Nenhuma formação adicionada ainda.</p>
        </div>

        <div v-for="entry in data" :key="entry.id" class="border border-gray-200 rounded-lg overflow-hidden">
            <button
                type="button"
                @click="expandedId = expandedId === entry.id ? null : entry.id"
                class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 hover:bg-gray-100 text-left"
            >
                <div>
                    <span class="font-medium text-sm text-gray-800">
                        {{ entry.degree || 'Formação não informada' }}
                    </span>
                    <span v-if="entry.institution" class="text-xs text-gray-500 ml-2">— {{ entry.institution }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click.stop="removeEntry(entry.id)" class="text-gray-400 hover:text-red-500 p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="expandedId === entry.id ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </button>

            <div v-show="expandedId === entry.id" class="p-4 space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Instituição *</label>
                        <input
                            type="text"
                            :value="entry.institution"
                            @input="updateEntry(entry.id, 'institution', ($event.target as HTMLInputElement).value)"
                            placeholder="Universidade de São Paulo"
                            class="w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Grau / Nível</label>
                        <select
                            :value="entry.degree"
                            @change="updateEntry(entry.id, 'degree', ($event.target as HTMLSelectElement).value)"
                            class="w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">Selecione...</option>
                            <option v-for="opt in degreeOptions" :key="opt" :value="opt">{{ opt }}</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Curso / Área</label>
                        <input
                            type="text"
                            :value="entry.field"
                            @input="updateEntry(entry.id, 'field', ($event.target as HTMLInputElement).value)"
                            placeholder="Ciência da Computação"
                            class="w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Início</label>
                        <input
                            type="month"
                            :value="entry.startDate"
                            @input="updateEntry(entry.id, 'startDate', ($event.target as HTMLInputElement).value)"
                            class="w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Término</label>
                        <input
                            type="month"
                            :value="entry.endDate"
                            :disabled="entry.current"
                            @input="updateEntry(entry.id, 'endDate', ($event.target as HTMLInputElement).value)"
                            class="w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-400"
                        />
                    </div>
                    <div class="flex items-center gap-2 mt-1">
                        <input
                            type="checkbox"
                            :id="`edu-current-${entry.id}`"
                            :checked="entry.current"
                            @change="updateEntry(entry.id, 'current', ($event.target as HTMLInputElement).checked)"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        />
                        <label :for="`edu-current-${entry.id}`" class="text-sm text-gray-700">Em andamento</label>
                    </div>
                </div>
            </div>
        </div>

        <button
            type="button"
            @click="addEntry"
            class="w-full py-2.5 px-4 border-2 border-dashed border-blue-300 rounded-lg text-sm text-blue-600 font-medium hover:border-blue-400 hover:bg-blue-50 transition-colors flex items-center justify-center gap-2"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Adicionar formação
        </button>
    </div>
</template>
