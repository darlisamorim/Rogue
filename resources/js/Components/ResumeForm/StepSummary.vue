<script setup lang="ts">
import CharacterCount from '@/Components/UI/CharacterCount.vue'

const props = defineProps<{
    data: string
    jobTitle?: string
}>()

const emit = defineEmits<{
    (e: 'update', value: string): void
}>()

const MAX_CHARS = 600
const MIN_CHARS = 300
</script>

<template>
    <div class="space-y-5">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">Resumo Profissional</h2>
            <p class="text-sm text-gray-500 mt-0.5">
                Um parágrafo conciso que destaca sua experiência e objetivos. Ideal: {{ MIN_CHARS }}–{{ MAX_CHARS }} caracteres.
            </p>
        </div>

        <div class="relative">
            <textarea
                :value="data"
                @input="emit('update', ($event.target as HTMLTextAreaElement).value)"
                rows="7"
                placeholder="Ex: Desenvolvedor Full Stack com 5 anos de experiência em criação de aplicações web escaláveis. Especialista em React e Node.js, com sólida experiência em arquitetura de microsserviços e metodologias ágeis. Busco oportunidades onde possa contribuir com soluções inovadoras e crescer profissionalmente."
                class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500 resize-none"
                :class="data.length > MAX_CHARS ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : ''"
            />
            <div class="flex items-center justify-between mt-1.5">
                <span
                    v-if="data.length > 0 && data.length < MIN_CHARS"
                    class="text-xs text-amber-600"
                >
                    Muito curto. Adicione mais detalhes.
                </span>
                <span v-else-if="data.length > MAX_CHARS" class="text-xs text-red-500">
                    Muito longo. Reduza o texto.
                </span>
                <span v-else class="text-xs text-gray-400" />
                <CharacterCount :value="data" :max="MAX_CHARS" />
            </div>
        </div>

        <!-- Tips -->
        <div class="bg-blue-50 rounded-lg p-4">
            <p class="text-xs font-semibold text-blue-700 mb-2">Dicas para um bom resumo:</p>
            <ul class="text-xs text-blue-600 space-y-1 list-disc list-inside">
                <li>Mencione seus anos de experiência e área de atuação</li>
                <li>Destaque suas principais competências técnicas</li>
                <li>Inclua o que você busca ou oferece profissionalmente</li>
                <li>Evite clichês como "proativo" e "dedicado"</li>
                <li>Use a 3ª pessoa ou omita o sujeito (sem "Eu sou...")</li>
            </ul>
        </div>
    </div>
</template>
