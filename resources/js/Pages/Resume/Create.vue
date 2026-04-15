<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import type { Template } from '@/types/template'

const props = defineProps<{
    templates: Template[]
}>()

const form = useForm({
    title: '',
    template_id: null as number | null,
})

const selectedTemplate = ref<Template | null>(null)

function selectTemplate(template: Template) {
    selectedTemplate.value = template
    form.template_id = template.id
}

function submit() {
    if (!form.template_id) return
    form.post(route('resumes.store'))
}
</script>

<template>
    <Head title="Novo Currículo" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Novo Currículo</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8 space-y-8">

                <!-- Step 1: Title -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-1">Dê um nome ao seu currículo</h3>
                    <p class="text-sm text-gray-500 mb-4">Use um nome que te ajude a identificar, ex: "Currículo Dev Senior"</p>
                    <input
                        v-model="form.title"
                        type="text"
                        placeholder="Ex: Currículo Desenvolvedor Full Stack"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                        :class="{ 'border-red-400': form.errors.title }"
                        autofocus
                    />
                    <p v-if="form.errors.title" class="text-sm text-red-500 mt-1">{{ form.errors.title }}</p>
                </div>

                <!-- Step 2: Template -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-1">Escolha um template</h3>
                    <p class="text-sm text-gray-500 mb-5">Você poderá trocar o template depois.</p>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
                        <button
                            v-for="template in templates"
                            :key="template.id"
                            type="button"
                            @click="selectTemplate(template)"
                            class="group relative rounded-xl border-2 transition-all overflow-hidden"
                            :class="
                                selectedTemplate?.id === template.id
                                    ? 'border-blue-600 ring-2 ring-blue-200'
                                    : 'border-gray-200 hover:border-blue-300'
                            "
                        >
                            <!-- Thumbnail -->
                            <div class="aspect-[3/4] bg-gradient-to-br from-gray-50 to-gray-100 flex flex-col items-center justify-center p-3 gap-1">
                                <!-- Mini visual representations -->
                                <template v-if="template.slug === 'modern'">
                                    <div class="flex w-full h-full gap-1">
                                        <div class="w-1/3 rounded" :style="{ backgroundColor: template.config.colors[0] + '80' }" />
                                        <div class="flex-1 flex flex-col gap-1">
                                            <div class="h-2 bg-gray-300 rounded w-3/4" />
                                            <div class="h-1 bg-gray-200 rounded" />
                                            <div class="h-1 bg-gray-200 rounded w-5/6" />
                                        </div>
                                    </div>
                                </template>
                                <template v-else-if="template.slug === 'creative'">
                                    <div class="w-full h-full flex flex-col gap-1">
                                        <div class="h-1/3 rounded" :style="{ backgroundColor: template.config.colors[0] + '80' }" />
                                        <div class="flex-1 flex flex-col gap-1 p-1">
                                            <div class="h-1.5 bg-gray-300 rounded w-2/3" />
                                            <div class="h-1 bg-gray-200 rounded" />
                                        </div>
                                    </div>
                                </template>
                                <template v-else-if="template.slug === 'tech'">
                                    <div class="w-full h-full bg-gray-900 rounded flex flex-col gap-1 p-1.5">
                                        <div class="h-1 rounded w-1/2" :style="{ backgroundColor: template.config.colors[0] }" />
                                        <div class="h-1 bg-gray-700 rounded" />
                                        <div class="h-1 bg-gray-700 rounded w-3/4" />
                                    </div>
                                </template>
                                <template v-else>
                                    <div class="w-full h-full flex flex-col gap-1.5 p-1">
                                        <div class="h-2 rounded w-3/4 mx-auto" :style="{ backgroundColor: template.config.colors[0] + '60' }" />
                                        <div class="h-px bg-gray-300" />
                                        <div class="h-1 bg-gray-200 rounded" />
                                        <div class="h-1 bg-gray-200 rounded w-5/6" />
                                        <div class="h-1 bg-gray-200 rounded w-4/6" />
                                    </div>
                                </template>

                                <!-- Selected checkmark -->
                                <div
                                    v-if="selectedTemplate?.id === template.id"
                                    class="absolute top-2 right-2 w-5 h-5 bg-blue-600 rounded-full flex items-center justify-center"
                                >
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <p class="text-xs font-medium text-center py-2 px-1 text-gray-700">{{ template.name }}</p>
                        </button>
                    </div>

                    <p v-if="form.errors.template_id" class="text-sm text-red-500 mt-3">{{ form.errors.template_id }}</p>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3">
                    <a :href="route('resumes.index')" class="px-5 py-2.5 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancelar
                    </a>
                    <button
                        type="button"
                        @click="submit"
                        :disabled="!form.title || !form.template_id || form.processing"
                        class="px-6 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2"
                    >
                        <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        Criar e começar a editar
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
