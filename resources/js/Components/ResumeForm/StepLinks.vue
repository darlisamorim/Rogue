<script setup lang="ts">
import type { LinkEntry } from '@/types/resume'

const props = defineProps<{
    data: LinkEntry[]
}>()

const emit = defineEmits<{
    (e: 'update', value: LinkEntry[]): void
}>()

const quickLinks = [
    { label: 'LinkedIn', placeholder: 'https://linkedin.com/in/seu-perfil' },
    { label: 'GitHub', placeholder: 'https://github.com/seu-usuario' },
    { label: 'Portfólio', placeholder: 'https://seuportfolio.com' },
    { label: 'Behance', placeholder: 'https://behance.net/seu-perfil' },
    { label: 'Dribbble', placeholder: 'https://dribbble.com/seu-perfil' },
]

function addEntry(label = '', url = '') {
    const entry: LinkEntry = {
        id: crypto.randomUUID(),
        label,
        url,
    }
    emit('update', [...props.data, entry])
}

function removeEntry(id: string) {
    emit('update', props.data.filter((e) => e.id !== id))
}

function updateEntry(id: string, field: keyof LinkEntry, value: string) {
    emit(
        'update',
        props.data.map((e) => (e.id === id ? { ...e, [field]: value } : e)),
    )
}

const labelIcons: Record<string, string> = {
    LinkedIn: '💼',
    GitHub: '💻',
    Portfólio: '🌐',
    Behance: '🎨',
    Dribbble: '🎯',
}
</script>

<template>
    <div class="space-y-5">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">Links e Redes Sociais</h2>
            <p class="text-sm text-gray-500 mt-0.5">Adicione links relevantes para sua presença online.</p>
        </div>

        <!-- Quick add buttons -->
        <div>
            <p class="text-xs font-medium text-gray-500 mb-2">Adicionar rapidamente:</p>
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="ql in quickLinks"
                    :key="ql.label"
                    type="button"
                    :disabled="data.some((e) => e.label === ql.label)"
                    @click="addEntry(ql.label)"
                    class="px-3 py-1.5 text-xs rounded-full border border-gray-300 text-gray-600 hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                >
                    {{ labelIcons[ql.label] ?? '🔗' }} {{ ql.label }}
                </button>
            </div>
        </div>

        <!-- Links list -->
        <div v-if="data.length === 0" class="text-center py-8 text-gray-400 border-2 border-dashed rounded-lg">
            <p class="text-sm">Nenhum link adicionado ainda.</p>
        </div>

        <div class="space-y-3">
            <div v-for="entry in data" :key="entry.id" class="flex gap-2 items-start">
                <div class="flex-1 grid grid-cols-3 gap-2">
                    <input
                        type="text"
                        :value="entry.label"
                        @input="updateEntry(entry.id, 'label', ($event.target as HTMLInputElement).value)"
                        placeholder="LinkedIn"
                        class="rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                    <input
                        type="url"
                        :value="entry.url"
                        @input="updateEntry(entry.id, 'url', ($event.target as HTMLInputElement).value)"
                        placeholder="https://..."
                        class="col-span-2 rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                </div>
                <button
                    type="button"
                    @click="removeEntry(entry.id)"
                    class="text-gray-400 hover:text-red-500 p-1.5 mt-0.5 shrink-0"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        </div>

        <button
            type="button"
            @click="addEntry()"
            class="w-full py-2.5 px-4 border-2 border-dashed border-blue-300 rounded-lg text-sm text-blue-600 font-medium hover:border-blue-400 hover:bg-blue-50 transition-colors flex items-center justify-center gap-2"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Adicionar link personalizado
        </button>
    </div>
</template>
