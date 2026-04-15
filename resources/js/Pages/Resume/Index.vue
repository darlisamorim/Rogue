<script setup lang="ts">
import { ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'

type ResumeItem = {
    id: number
    title: string
    template: { name: string; component_name: string } | null
    is_downloaded: boolean
    updated_at: string
}

const props = defineProps<{
    resumes: ResumeItem[]
}>()

// Cópia local para delete otimista (sem reload da página)
const localResumes = ref<ResumeItem[]>([...props.resumes])

const confirmingDelete = ref<number | null>(null)
const deletingIds = ref<Set<number>>(new Set())

function requestDelete(id: number) {
    confirmingDelete.value = id
}

function confirmDelete(id: number) {
    confirmingDelete.value = null
    deletingIds.value.add(id)

    // Remove otimisticamente da lista local
    const index = localResumes.value.findIndex((r) => r.id === id)
    const removed = index !== -1 ? localResumes.value.splice(index, 1)[0] : null

    router.delete(route('resumes.destroy', id), {
        preserveScroll: true,
        onError: () => {
            // Restaura se der erro
            if (removed) localResumes.value.splice(index, 0, removed)
            deletingIds.value.delete(id)
        },
        onFinish: () => {
            deletingIds.value.delete(id)
        },
    })
}

function cancelDelete() {
    confirmingDelete.value = null
}
</script>

<template>
    <Head title="Meus Currículos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Meus Currículos</h2>
                <Link
                    :href="route('resumes.create')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Novo currículo
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

                <!-- Empty state -->
                <div v-if="resumes.length === 0" class="text-center py-20">
                    <div class="text-6xl mb-4">📄</div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Nenhum currículo ainda</h3>
                    <p class="text-gray-500 text-sm mb-6">Crie seu primeiro currículo profissional agora mesmo.</p>
                    <Link
                        :href="route('resumes.create')"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors"
                    >
                        Criar meu primeiro currículo
                    </Link>
                </div>

                <!-- Resume grid -->
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="resume in localResumes"
                        :key="resume.id"
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow group"
                    >
                        <!-- Thumbnail placeholder -->
                        <div class="h-40 bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center">
                            <span class="text-5xl opacity-30">📄</span>
                        </div>

                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 truncate">{{ resume.title }}</h3>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ resume.template?.name ?? 'Template removido' }}
                                <span v-if="resume.is_downloaded" class="ml-2 inline-flex items-center gap-0.5 text-green-600">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    Baixado
                                </span>
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">Editado {{ resume.updated_at }}</p>

                            <div class="flex flex-col gap-2 mt-4">
                                <!-- Dois botões de edição -->
                                <div class="flex gap-2">
                                    <Link
                                        :href="route('resumes.edit', resume.id)"
                                        class="flex-1 flex items-center justify-center gap-1.5 py-2 px-3 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Informações
                                    </Link>
                                    <Link
                                        :href="route('resumes.edit', resume.id) + '?visual=1'"
                                        class="flex-1 flex items-center justify-center gap-1.5 py-2 px-3 bg-indigo-50 text-indigo-700 border border-indigo-200 text-xs font-medium rounded-lg hover:bg-indigo-100 transition-colors"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                                        </svg>
                                        Visual
                                    </Link>
                                </div>

                                <!-- Confirmação inline de exclusão -->
                                <div v-if="confirmingDelete === resume.id" class="flex gap-2 items-center bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                                    <span class="text-xs text-red-700 font-medium flex-1">Excluir permanentemente?</span>
                                    <button
                                        type="button"
                                        @click="confirmDelete(resume.id)"
                                        class="px-2.5 py-1 bg-red-600 text-white text-xs font-semibold rounded-md hover:bg-red-700 transition-colors"
                                    >
                                        Sim
                                    </button>
                                    <button
                                        type="button"
                                        @click="cancelDelete()"
                                        class="px-2.5 py-1 bg-white text-gray-600 text-xs font-medium border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
                                    >
                                        Não
                                    </button>
                                </div>

                                <button
                                    v-else
                                    type="button"
                                    @click="requestDelete(resume.id)"
                                    class="w-full flex items-center justify-center gap-1.5 py-1.5 border border-gray-200 text-gray-400 text-xs rounded-lg hover:border-red-300 hover:text-red-500 transition-colors"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Excluir
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Create new card -->
                    <Link
                        :href="route('resumes.create')"
                        class="border-2 border-dashed border-gray-300 rounded-xl h-64 flex flex-col items-center justify-center gap-3 text-gray-400 hover:border-blue-400 hover:text-blue-500 transition-colors"
                    >
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4" />
                        </svg>
                        <span class="text-sm font-medium">Novo currículo</span>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
