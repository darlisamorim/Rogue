<script setup lang="ts">
import { ref, computed } from 'vue'
import type { SkillEntry, ResumeCustomization } from '@/types/resume'

const props = defineProps<{
    data: SkillEntry[]
    customization: ResumeCustomization
}>()

const emit = defineEmits<{
    (e: 'update', value: SkillEntry[]): void
    (e: 'update-customization', field: keyof ResumeCustomization, value: unknown): void
}>()

const inputValue = ref('')

const levelLabels = ['Iniciante', 'Básico', 'Intermediário', 'Avançado', 'Especialista']
const levelColors = ['#94a3b8', '#60a5fa', '#34d399', '#f59e0b', '#f97316']

function addSkill(name?: string) {
    const skillName = (name ?? inputValue.value).trim()
    if (skillName && !props.data.find((s) => s.name === skillName)) {
        emit('update', [
            ...props.data,
            { id: crypto.randomUUID(), name: skillName, level: 3 },
        ])
        inputValue.value = ''
    }
}

function removeSkill(id: string) {
    emit('update', props.data.filter((s) => s.id !== id))
}

function updateLevel(id: string, level: number) {
    emit(
        'update',
        props.data.map((s) => (s.id === id ? { ...s, level } : s)),
    )
}

function handleKeydown(e: KeyboardEvent) {
    if (e.key === 'Enter' || e.key === ',') {
        e.preventDefault()
        addSkill()
    }
}

const suggestions = [
    'Trabalho em Equipe', 'Orientação ao Cliente', 'Gerenciamento de Projetos',
    'Marketing', 'Liderança', 'Habilidades Interpessoais',
    'Comunicação', 'Aprendizado Rápido', 'Pensamento Crítico',
    'JavaScript', 'TypeScript', 'Python', 'React', 'Vue.js', 'Node.js',
    'Laravel', 'Docker', 'Git', 'MySQL', 'PostgreSQL', 'AWS',
    'Scrum', 'Agile', 'Excel', 'Power BI', 'Figma', 'Inglês',
]

const filteredSuggestions = computed(() => {
    if (!inputValue.value) return []
    const q = inputValue.value.toLowerCase()
    return suggestions
        .filter((s) => s.toLowerCase().includes(q) && !props.data.find((d) => d.name === s))
        .slice(0, 8)
})

const availableSuggestions = computed(() =>
    suggestions.filter((s) => !props.data.find((d) => d.name === s)).slice(0, 12),
)
</script>

<template>
    <div class="space-y-5">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">Habilidades</h2>
            <p class="text-sm text-gray-500 mt-0.5">
                As habilidades relevantes que você possui no seu cargo. Certifique-se de que correspondem
                à vaga ou ao cargo. Você pode adicionar pelo menos 5 por empregos.
            </p>
        </div>

        <!-- Toggle nível de experiência -->
        <label class="flex items-center gap-3 cursor-pointer">
            <div
                class="relative w-10 h-5 rounded-full transition-colors"
                :class="customization.showSkillLevels ? 'bg-blue-600' : 'bg-gray-300'"
                @click="emit('update-customization', 'showSkillLevels', !customization.showSkillLevels)"
            >
                <div
                    class="absolute top-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform"
                    :class="customization.showSkillLevels ? 'translate-x-5' : 'translate-x-0.5'"
                />
            </div>
            <span class="text-sm text-gray-700">
                {{ customization.showSkillLevels ? 'Mostrar nível de experiência' : 'Não mostrar nível de experiência' }}
            </span>
        </label>

        <!-- Sugestões de habilidades (chips) -->
        <div>
            <div class="flex flex-wrap gap-2 mb-3">
                <button
                    v-for="s in availableSuggestions"
                    :key="s"
                    type="button"
                    @click="addSkill(s)"
                    class="px-3 py-1.5 text-xs rounded-full bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition-colors font-medium"
                >
                    + {{ s }}
                </button>
            </div>
        </div>

        <!-- Skills adicionadas -->
        <div class="space-y-2">
            <div
                v-for="skill in data"
                :key="skill.id"
                class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200"
            >
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-800">{{ skill.name }}</p>

                    <!-- Nível de experiência -->
                    <div v-if="customization.showSkillLevels" class="mt-2">
                        <div class="flex gap-1">
                            <button
                                v-for="(label, i) in levelLabels"
                                :key="i"
                                type="button"
                                @click="updateLevel(skill.id, i + 1)"
                                :title="label"
                                class="flex-1 h-1.5 rounded-full transition-all"
                                :style="{
                                    backgroundColor: skill.level >= i + 1 ? levelColors[skill.level - 1] : '#e5e7eb',
                                }"
                            />
                        </div>
                        <p class="text-xs text-gray-400 mt-1">{{ levelLabels[skill.level - 1] ?? 'Selecione o nível' }}</p>
                    </div>
                </div>

                <button
                    type="button"
                    @click="removeSkill(skill.id)"
                    class="text-gray-300 hover:text-red-400 transition-colors shrink-0"
                >
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Input para habilidade customizada -->
        <div class="relative">
            <div class="flex gap-2">
                <input
                    v-model="inputValue"
                    type="text"
                    @keydown="handleKeydown"
                    placeholder="Digite uma habilidade e pressione Enter..."
                    class="flex-1 rounded-lg border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-blue-500 focus:ring-blue-500 transition-colors"
                />
                <button
                    type="button"
                    @click="addSkill()"
                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                >
                    Adicionar
                </button>
            </div>

            <!-- Autocomplete -->
            <div
                v-if="filteredSuggestions.length > 0"
                class="absolute z-10 top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden"
            >
                <button
                    v-for="suggestion in filteredSuggestions"
                    :key="suggestion"
                    type="button"
                    @click="addSkill(suggestion)"
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700"
                >
                    {{ suggestion }}
                </button>
            </div>
        </div>

        <button
            v-if="data.length === 0"
            type="button"
            @click="addSkill()"
            class="text-sm text-blue-600 font-medium hover:text-blue-700 flex items-center gap-1"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Adicionar mais uma habilidade
        </button>
    </div>
</template>
