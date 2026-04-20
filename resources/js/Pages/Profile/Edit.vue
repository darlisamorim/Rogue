<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import DeleteUserForm from './Partials/DeleteUserForm.vue'
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue'
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import type { User } from '@/types'

defineProps<{
    mustVerifyEmail?: boolean
    status?: string
}>()

const page = usePage()
const user = computed(() => page.props.auth.user as User)

const profileFields: (keyof User)[] = [
    'name', 'email', 'professional_title', 'phone',
    'location', 'linkedin_url', 'bio', 'nationality',
]
const completeness = computed(() => {
    const u = user.value
    const filled = profileFields.filter((f) => {
        const v = u[f]
        return v != null && String(v).trim() !== ''
    }).length
    return Math.round((filled / profileFields.length) * 100)
})

const initials = computed(() => {
    const parts = (user.value.name ?? '').split(' ').filter(Boolean)
    return parts.slice(0, 2).map((p) => p[0].toUpperCase()).join('')
})

const avatarSrc = computed(() => {
    const url = user.value.avatar_url
    if (!url) return null
    if (url.startsWith('http')) return url
    return `/storage/${url}`
})

const avatarUploading = ref(false)

function onAvatarChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0]
    if (!file) return
    avatarUploading.value = true
    router.post(
        (route as any)('profile.avatar'),
        { avatar: file },
        {
            forceFormData: true,
            onFinish: () => { avatarUploading.value = false },
        },
    )
}
</script>

<template>
    <Head title="Meu Perfil" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Meu Perfil</h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- ── CARD DE HEADER DO PERFIL ── -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                    <!-- Faixa gradiente com padrão decorativo -->
                    <div class="h-28 bg-gradient-to-r from-blue-600 to-indigo-700 relative">
                        <svg class="absolute inset-0 w-full h-full opacity-10" preserveAspectRatio="xMidYMid slice">
                            <defs>
                                <pattern id="grid" width="32" height="32" patternUnits="userSpaceOnUse">
                                    <circle cx="16" cy="16" r="1.5" fill="white"/>
                                </pattern>
                            </defs>
                            <rect width="100%" height="100%" fill="url(#grid)"/>
                        </svg>
                    </div>

                    <div class="px-6 pb-6">

                        <!-- Avatar flutuando sobre o gradiente -->
                        <div class="-mt-12 mb-3">
                            <div class="relative inline-block group">
                                <div
                                    class="w-24 h-24 rounded-2xl border-4 border-white shadow-lg overflow-hidden bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center"
                                    :class="{ 'opacity-70': avatarUploading }"
                                >
                                    <img
                                        v-if="avatarSrc"
                                        :src="avatarSrc"
                                        alt="Foto de perfil"
                                        class="w-full h-full object-cover"
                                    />
                                    <span v-else class="text-white text-3xl font-bold select-none">{{ initials }}</span>
                                </div>

                                <!-- Overlay câmera ao hover -->
                                <label
                                    for="avatar-input"
                                    class="absolute inset-0 rounded-2xl bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer flex flex-col items-center justify-center gap-1"
                                    :class="{ 'opacity-100': avatarUploading }"
                                >
                                    <svg v-if="!avatarUploading" class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <svg v-else class="w-5 h-5 text-white animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    <span class="text-white text-[10px] font-medium leading-none">
                                        {{ avatarUploading ? 'Enviando…' : 'Alterar foto' }}
                                    </span>
                                </label>

                                <input
                                    id="avatar-input"
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="hidden"
                                    @change="onAvatarChange"
                                />
                            </div>
                        </div>

                        <!-- Nome e cargo — sobre fundo branco, sempre visível -->
                        <div class="mb-5">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-2xl font-extrabold text-gray-900 leading-tight tracking-tight">
                                    {{ user.name }}
                                </h3>
                                <span
                                    v-if="completeness === 100"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-[11px] font-semibold"
                                >
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Perfil completo
                                </span>
                            </div>

                            <p v-if="user.professional_title" class="text-sm font-semibold text-blue-600 mt-1">
                                {{ user.professional_title }}
                            </p>
                            <p v-else class="text-sm text-gray-400 italic mt-1">
                                Adicione um cargo profissional
                            </p>

                            <p v-if="user.location" class="flex items-center gap-1 text-xs text-gray-400 mt-1.5">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ user.location }}
                            </p>
                        </div>

                        <!-- Barra de completude -->
                        <div class="border-t border-gray-100 pt-4">
                            <div class="flex items-center justify-between mb-1.5">
                                <p class="text-xs font-semibold text-gray-500">Completude do perfil</p>
                                <span
                                    class="text-xs font-bold tabular-nums"
                                    :class="completeness >= 80 ? 'text-green-600' : completeness >= 40 ? 'text-amber-600' : 'text-red-500'"
                                >{{ completeness }}%</span>
                            </div>
                            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div
                                    class="h-full rounded-full transition-all duration-700"
                                    :class="completeness >= 80 ? 'bg-green-500' : completeness >= 40 ? 'bg-amber-500' : 'bg-red-400'"
                                    :style="{ width: completeness + '%' }"
                                />
                            </div>
                            <p v-if="completeness < 100" class="text-xs text-gray-400 mt-1.5">
                                Quanto mais completo, mais seus currículos serão pré-preenchidos automaticamente.
                            </p>
                            <p v-else class="text-xs text-green-600 mt-1.5 font-medium">
                                Perfil completo! Seus currículos serão pré-preenchidos automaticamente.
                            </p>
                        </div>

                    </div>
                </div>

                <!-- ── FORMULÁRIO DE PERFIL ── -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                    />
                </div>

                <!-- ── SENHA ── -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
                    <UpdatePasswordForm />
                </div>

                <!-- ── EXCLUSÃO DE CONTA ── -->
                <div class="bg-white rounded-2xl shadow-sm border border-red-100 p-6 sm:p-8">
                    <DeleteUserForm />
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
