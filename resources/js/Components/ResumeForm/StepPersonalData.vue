<script setup lang="ts">
import { ref } from 'vue'
import type { PersonalData } from '@/types/resume'

const props = defineProps<{
    data: PersonalData
}>()

const emit = defineEmits<{
    (e: 'update', value: PersonalData): void
}>()

const showDetails = ref(false)
const photoInputRef = ref<HTMLInputElement | null>(null)

function update(field: keyof PersonalData, value: string) {
    emit('update', { ...props.data, [field]: value })
}

function onPhotoChange(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0]
    if (!file) return
    const reader = new FileReader()
    reader.onload = () => {
        update('photo', reader.result as string)
    }
    reader.readAsDataURL(file)
}

function removePhoto() {
    update('photo', '')
    if (photoInputRef.value) photoInputRef.value.value = ''
}

const countries = [
    'Brasil', 'Portugal', 'Argentina', 'Estados Unidos', 'Canadá',
    'Reino Unido', 'Alemanha', 'França', 'Espanha', 'Itália',
    'Japão', 'China', 'Austrália', 'México', 'Outro',
]
</script>

<template>
    <div class="space-y-5">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">Dados pessoais</h2>
            <p class="text-sm text-gray-500 mt-0.5">
                Adicione suas informações de contato e e-mail recebem positivo dos recrutadores.
            </p>
        </div>

        <!-- Foto de perfil -->
        <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl border border-gray-200">
            <!-- Avatar preview -->
            <div class="relative shrink-0">
                <div
                    class="w-20 h-20 rounded-full border-2 border-dashed border-gray-300 overflow-hidden flex items-center justify-center bg-white"
                    :class="data.photo ? 'border-solid border-blue-300' : ''"
                >
                    <img
                        v-if="data.photo"
                        :src="data.photo"
                        alt="Foto de perfil"
                        class="w-full h-full object-cover"
                    />
                    <div v-else class="flex flex-col items-center text-gray-400">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                </div>
                <!-- Remove button -->
                <button
                    v-if="data.photo"
                    type="button"
                    @click="removePhoto"
                    class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors"
                    title="Remover foto"
                >
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Upload info -->
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-700">Foto de perfil</p>
                <p class="text-xs text-gray-500 mt-0.5">JPG, PNG ou WEBP. Recomendado: 300×300px.</p>
                <input
                    ref="photoInputRef"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    class="hidden"
                    @change="onPhotoChange"
                />
                <button
                    type="button"
                    @click="photoInputRef?.click()"
                    class="mt-2 inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    {{ data.photo ? 'Trocar foto' : 'Adicionar foto' }}
                </button>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <!-- Nome / Sobrenome -->
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nome</label>
                <input
                    type="text"
                    :value="data.firstName"
                    @input="update('firstName', ($event.target as HTMLInputElement).value)"
                    placeholder="Darlis"
                    class="w-full rounded-lg border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-blue-500 focus:ring-blue-500 transition-colors"
                />
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Sobrenome</label>
                <input
                    type="text"
                    :value="data.lastName"
                    @input="update('lastName', ($event.target as HTMLInputElement).value)"
                    placeholder="Amorim"
                    class="w-full rounded-lg border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-blue-500 focus:ring-blue-500 transition-colors"
                />
            </div>

            <!-- E-mail -->
            <div class="col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">E-mail</label>
                <input
                    type="email"
                    :value="data.email"
                    @input="update('email', ($event.target as HTMLInputElement).value)"
                    placeholder="darlisamorim@gmail.com"
                    class="w-full rounded-lg border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-blue-500 focus:ring-blue-500 transition-colors"
                />
            </div>

            <!-- Telefone -->
            <div class="col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Telefone</label>
                <input
                    type="tel"
                    :value="data.phone"
                    @input="update('phone', ($event.target as HTMLInputElement).value)"
                    placeholder="(11) 99999-9999"
                    class="w-full rounded-lg border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-blue-500 focus:ring-blue-500 transition-colors"
                />
            </div>

            <!-- Endereço -->
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Endereço</label>
                <input
                    type="text"
                    :value="data.location"
                    @input="update('location', ($event.target as HTMLInputElement).value)"
                    placeholder="Zona Sul, Interlagos"
                    class="w-full rounded-lg border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-blue-500 focus:ring-blue-500 transition-colors"
                />
            </div>

            <!-- País -->
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">País</label>
                <select
                    :value="data.country"
                    @change="update('country', ($event.target as HTMLSelectElement).value)"
                    class="w-full rounded-lg border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-blue-500 focus:ring-blue-500 transition-colors"
                >
                    <option v-for="c in countries" :key="c" :value="c">{{ c }}</option>
                </select>
            </div>
        </div>

        <!-- Toggle: Adicione mais detalhes -->
        <button
            type="button"
            @click="showDetails = !showDetails"
            class="flex items-center gap-1.5 text-sm text-blue-600 font-medium hover:text-blue-700 transition-colors"
        >
            <svg
                class="w-4 h-4 transition-transform"
                :class="showDetails ? 'rotate-180' : ''"
                fill="none" stroke="currentColor" viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
            {{ showDetails ? 'Ocultar detalhes' : 'Adicione mais detalhes' }}
        </button>

        <!-- Detalhes adicionais expansíveis -->
        <div v-if="showDetails" class="grid grid-cols-2 gap-3 pt-1 border-t border-gray-100">
            <div class="col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Cargo profissional</label>
                <input
                    type="text"
                    :value="data.title"
                    @input="update('title', ($event.target as HTMLInputElement).value)"
                    placeholder="Desenvolvedor Full Stack"
                    class="w-full rounded-lg border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-blue-500 focus:ring-blue-500 transition-colors"
                />
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Data de nascimento</label>
                <input
                    type="date"
                    :value="data.dateOfBirth"
                    @input="update('dateOfBirth', ($event.target as HTMLInputElement).value)"
                    class="w-full rounded-lg border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-blue-500 focus:ring-blue-500 transition-colors"
                />
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nacionalidade</label>
                <input
                    type="text"
                    :value="data.nationality"
                    @input="update('nationality', ($event.target as HTMLInputElement).value)"
                    placeholder="Brasileira"
                    class="w-full rounded-lg border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-blue-500 focus:ring-blue-500 transition-colors"
                />
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">LinkedIn</label>
                <input
                    type="url"
                    :value="data.linkedIn"
                    @input="update('linkedIn', ($event.target as HTMLInputElement).value)"
                    placeholder="linkedin.com/in/seu-perfil"
                    class="w-full rounded-lg border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-blue-500 focus:ring-blue-500 transition-colors"
                />
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">CNH</label>
                <input
                    type="text"
                    :value="data.drivingLicense"
                    @input="update('drivingLicense', ($event.target as HTMLInputElement).value)"
                    placeholder="Categoria B"
                    class="w-full rounded-lg border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-blue-500 focus:ring-blue-500 transition-colors"
                />
            </div>

            <div class="col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Site / Portfólio</label>
                <input
                    type="url"
                    :value="data.website"
                    @input="update('website', ($event.target as HTMLInputElement).value)"
                    placeholder="https://seusite.com"
                    class="w-full rounded-lg border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-blue-500 focus:ring-blue-500 transition-colors"
                />
            </div>
        </div>
    </div>
</template>
