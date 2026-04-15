<script setup lang="ts">
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'

defineProps<{
    mustVerifyEmail?: boolean
    status?: string
}>()

const user = usePage().props.auth.user as unknown as Record<string, string | null>

const form = useForm({
    name:               user.name               ?? '',
    email:              user.email              ?? '',
    professional_title: user.professional_title ?? '',
    phone:              user.phone              ?? '',
    location:           user.location           ?? '',
    linkedin_url:       user.linkedin_url        ?? '',
    website_url:        user.website_url         ?? '',
    bio:                user.bio                ?? '',
    date_of_birth:      user.date_of_birth      ?? '',
    nationality:        user.nationality        ?? '',
    cpf:                user.cpf                ?? '',
})

function maskPhone(value: string): string {
    const d = value.replace(/\D/g, '').slice(0, 11)
    if (d.length <= 10) {
        return d.replace(/(\d{2})(\d)/, '($1) $2').replace(/(\d{4})(\d)/, '$1-$2')
    }
    return d.replace(/(\d{2})(\d)/, '($1) $2').replace(/(\d{5})(\d)/, '$1-$2')
}

function onPhoneInput(e: Event) {
    form.phone = maskPhone((e.target as HTMLInputElement).value)
}

function maskCpf(value: string): string {
    return value
        .replace(/\D/g, '')
        .slice(0, 11)
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d{1,2})$/, '$1-$2')
}

function onCpfInput(e: Event) {
    const raw = (e.target as HTMLInputElement).value
    form.cpf = maskCpf(raw)
}
</script>

<template>
    <section>
        <div class="mb-7">
            <h2 class="text-base font-semibold text-gray-900">Informações do perfil</h2>
            <p class="mt-0.5 text-sm text-gray-500">
                Preencha seus dados uma vez e eles serão usados para pré-preencher todos os seus currículos.
            </p>
        </div>

        <form @submit.prevent="form.patch(route('profile.update'))" class="divide-y divide-gray-100">

            <!-- ── CONTA ── -->
            <div class="pb-8">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-1 h-4 rounded-full bg-blue-500"></div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Conta</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="name" value="Nome completo" />
                        <TextInput
                            id="name" type="text" class="mt-1 block w-full"
                            v-model="form.name" required autofocus autocomplete="name"
                        />
                        <InputError class="mt-1.5" :message="form.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="email" value="E-mail" />
                        <TextInput
                            id="email" type="email" class="mt-1 block w-full"
                            v-model="form.email" required autocomplete="username"
                        />
                        <InputError class="mt-1.5" :message="form.errors.email" />
                        <div v-if="mustVerifyEmail && !user.email_verified_at" class="mt-1.5">
                            <p class="text-xs text-amber-600">
                                E-mail não verificado.
                                <Link
                                    :href="route('verification.send')" method="post" as="button"
                                    class="underline hover:text-amber-800"
                                >Reenviar verificação.</Link>
                            </p>
                            <p v-show="status === 'verification-link-sent'" class="text-xs text-green-600 mt-1">
                                Link de verificação enviado.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── DADOS PROFISSIONAIS ── -->
            <div class="py-8">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-1 h-4 rounded-full bg-indigo-500"></div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Dados profissionais</p>
                </div>
                <div class="space-y-4">
                    <div>
                        <InputLabel for="professional_title" value="Cargo / Área de atuação" />
                        <TextInput
                            id="professional_title" type="text" class="mt-1 block w-full"
                            v-model="form.professional_title"
                            placeholder="ex: Desenvolvedor Full Stack, Designer UX/UI"
                            autocomplete="organization-title"
                        />
                        <InputError class="mt-1.5" :message="form.errors.professional_title" />
                    </div>

                    <div>
                        <InputLabel for="bio" value="Bio profissional" />
                        <textarea
                            id="bio"
                            v-model="form.bio"
                            rows="3"
                            maxlength="600"
                            placeholder="Uma descrição curta sobre você, sua trajetória e o que busca profissionalmente…"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm resize-none"
                        />
                        <div class="flex justify-between mt-1">
                            <InputError :message="form.errors.bio" />
                            <span class="text-xs text-gray-400 ml-auto">{{ form.bio.length }}/600</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── CONTATO ── -->
            <div class="py-8">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-1 h-4 rounded-full bg-violet-500"></div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Contato</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="phone" value="Telefone / WhatsApp" />
                        <input
                            id="phone" type="tel"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            :value="form.phone"
                            @input="onPhoneInput"
                            placeholder="(11) 99999-9999"
                            autocomplete="tel"
                            inputmode="numeric"
                            maxlength="15"
                        />
                        <InputError class="mt-1.5" :message="form.errors.phone" />
                    </div>

                    <div>
                        <InputLabel for="location" value="Cidade / Região" />
                        <TextInput
                            id="location" type="text" class="mt-1 block w-full"
                            v-model="form.location"
                            placeholder="ex: São Paulo, SP"
                        />
                        <InputError class="mt-1.5" :message="form.errors.location" />
                    </div>

                    <div>
                        <InputLabel for="linkedin_url" value="LinkedIn" />
                        <div class="mt-1 flex rounded-lg shadow-sm border border-gray-300 focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500">
                            <span class="inline-flex items-center px-3 rounded-l-lg border-r border-gray-300 bg-gray-50 text-gray-500 text-xs font-medium select-none">
                                linkedin.com/in/
                            </span>
                            <input
                                id="linkedin_url"
                                v-model="form.linkedin_url"
                                type="text"
                                placeholder="seu-perfil"
                                class="block flex-1 min-w-0 rounded-r-lg border-0 py-2 px-3 text-sm text-gray-900 focus:ring-0 bg-white"
                            />
                        </div>
                        <InputError class="mt-1.5" :message="form.errors.linkedin_url" />
                    </div>

                    <div>
                        <InputLabel for="website_url" value="Site / Portfólio" />
                        <TextInput
                            id="website_url" type="url" class="mt-1 block w-full"
                            v-model="form.website_url"
                            placeholder="https://seusite.com.br"
                            autocomplete="url"
                        />
                        <InputError class="mt-1.5" :message="form.errors.website_url" />
                    </div>
                </div>
            </div>

            <!-- ── DADOS PESSOAIS ── -->
            <div class="py-8">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-1 h-4 rounded-full bg-sky-500"></div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Dados pessoais</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="date_of_birth" value="Data de nascimento" />
                        <TextInput
                            id="date_of_birth" type="date" class="mt-1 block w-full"
                            v-model="form.date_of_birth"
                        />
                        <InputError class="mt-1.5" :message="form.errors.date_of_birth" />
                    </div>

                    <div>
                        <InputLabel for="nationality" value="Nacionalidade" />
                        <TextInput
                            id="nationality" type="text" class="mt-1 block w-full"
                            v-model="form.nationality"
                            placeholder="ex: Brasileira"
                        />
                        <InputError class="mt-1.5" :message="form.errors.nationality" />
                    </div>
                </div>
            </div>

            <!-- ── DADOS DE PAGAMENTO ── -->
            <div class="py-8">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-1 h-4 rounded-full bg-emerald-500"></div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Dados de pagamento</p>
                </div>
                <p class="text-xs text-gray-400 mb-4 ml-3">Usado para identificação nas cobranças via Pix. Obrigatório na primeira compra.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="cpf" value="CPF" />
                        <input
                            id="cpf" type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            :value="form.cpf"
                            @input="onCpfInput"
                            placeholder="000.000.000-00"
                            autocomplete="off"
                            inputmode="numeric"
                            maxlength="14"
                        />
                        <InputError class="mt-1.5" :message="form.errors.cpf" />
                    </div>
                </div>
            </div>

            <!-- ── SALVAR ── -->
            <div class="flex items-center gap-4 pt-6">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 disabled:opacity-60 transition-colors"
                >
                    <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    {{ form.processing ? 'Salvando...' : 'Salvar alterações' }}
                </button>

                <Transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 translate-y-1"
                    leave-active-class="transition ease-in duration-150"
                    leave-to-class="opacity-0"
                >
                    <div v-if="form.recentlySuccessful" class="flex items-center gap-1.5 text-sm text-green-600 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        Salvo com sucesso!
                    </div>
                </Transition>
            </div>

        </form>
    </section>
</template>