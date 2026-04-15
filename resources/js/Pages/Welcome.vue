<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'

defineProps<{
    canLogin?: boolean
    canRegister?: boolean
    pricing?: { action_slug: string; label: string; price: string }[]
    creditPackages?: { name: string; price: string; credits: string; bonus_percentage: number }[]
}>()

const steps = [
    {
        icon: '✍️',
        title: 'Preencha suas informações',
        desc: 'Dados pessoais, experiências, formação e habilidades em um formulário simples e guiado.',
    },
    {
        icon: '🎨',
        title: 'Escolha um template',
        desc: 'Selecione entre modelos profissionais e personalize cores, fonte e layout.',
    },
    {
        icon: '⬇️',
        title: 'Baixe o PDF',
        desc: 'Pague apenas quando quiser baixar. Sem assinatura, sem mensalidade.',
    },
]

const templates = [
    { name: 'Minimalista', color: 'from-slate-100 to-slate-200', accent: 'bg-slate-700', desc: 'Clean e objetivo' },
    { name: 'Moderno', color: 'from-blue-100 to-indigo-200', accent: 'bg-blue-600', desc: 'Com sidebar lateral' },
    { name: 'Clássico', color: 'from-stone-100 to-stone-200', accent: 'bg-stone-700', desc: 'Corporativo e formal' },
    { name: 'Criativo', color: 'from-violet-100 to-purple-200', accent: 'bg-violet-600', desc: 'Header impactante' },
    { name: 'Tech', color: 'from-emerald-100 to-teal-200', accent: 'bg-emerald-600', desc: 'Para área de TI' },
]

function formatPrice(val: string | number) {
    return 'R$ ' + Number(val).toFixed(2).replace('.', ',')
}
</script>

<template>
    <Head title="Rogue — Currículo Profissional em Minutos" />

    <div class="min-h-screen bg-white text-gray-900 antialiased">

        <!-- NAV -->
        <nav class="fixed top-0 inset-x-0 z-50 bg-white/80 backdrop-blur border-b border-gray-100">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
                <span class="text-xl font-black tracking-tight text-blue-600">Rogue</span>
                <div class="flex items-center gap-3">
                    <template v-if="canLogin">
                        <Link :href="route('login')" class="text-sm font-medium text-gray-600 hover:text-gray-900 px-3 py-1.5">
                            Entrar
                        </Link>
                        <Link v-if="canRegister" :href="route('register')"
                            class="text-sm font-semibold bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            Criar conta grátis
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- HERO -->
        <section class="pt-32 pb-20 px-4 text-center">
            <div class="max-w-3xl mx-auto">
                <span class="inline-block mb-4 px-3 py-1 bg-blue-50 text-blue-700 text-xs font-semibold rounded-full tracking-wide uppercase">
                    Currículo profissional online
                </span>
                <h1 class="text-5xl sm:text-6xl font-black tracking-tight leading-tight mb-6">
                    Seu currículo<br>
                    <span class="text-blue-600">pronto em minutos</span>
                </h1>
                <p class="text-xl text-gray-500 leading-relaxed mb-10 max-w-xl mx-auto">
                    Crie, personalize e baixe um currículo profissional em PDF. Sem assinatura. Pague apenas quando quiser baixar.
                </p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <Link v-if="canRegister" :href="route('register')"
                        class="px-8 py-4 bg-blue-600 text-white font-bold text-lg rounded-xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 hover:shadow-blue-300">
                        Criar meu currículo grátis →
                    </Link>
                    <a href="#como-funciona"
                        class="px-8 py-4 bg-gray-100 text-gray-700 font-semibold text-lg rounded-xl hover:bg-gray-200 transition-colors">
                        Como funciona
                    </a>
                </div>
                <p class="mt-5 text-sm text-gray-400">
                    Sem cartão de crédito. Download a partir de <strong class="text-gray-600">R$ 0,99</strong>.
                </p>
            </div>
        </section>

        <!-- COMO FUNCIONA -->
        <section id="como-funciona" class="py-20 px-4 bg-gray-50">
            <div class="max-w-4xl mx-auto">
                <div class="text-center mb-14">
                    <h2 class="text-3xl font-black mb-3">Simples assim</h2>
                    <p class="text-gray-500 text-lg">Três passos para ter seu currículo profissional</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
                    <div v-for="(step, i) in steps" :key="i"
                        class="bg-white rounded-2xl p-8 text-center shadow-sm border border-gray-100 relative">
                        <div class="absolute -top-4 left-1/2 -translate-x-1/2 w-8 h-8 bg-blue-600 text-white text-sm font-bold rounded-full flex items-center justify-center">
                            {{ i + 1 }}
                        </div>
                        <div class="text-4xl mb-4 mt-2">{{ step.icon }}</div>
                        <h3 class="font-bold text-gray-900 mb-2">{{ step.title }}</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">{{ step.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- TEMPLATES -->
        <section class="py-20 px-4">
            <div class="max-w-5xl mx-auto">
                <div class="text-center mb-14">
                    <h2 class="text-3xl font-black mb-3">Templates profissionais</h2>
                    <p class="text-gray-500 text-lg">Escolha o estilo que combina com você</p>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                    <div v-for="tpl in templates" :key="tpl.name"
                        class="group cursor-pointer">
                        <div :class="`bg-gradient-to-br ${tpl.color} rounded-xl aspect-[3/4] flex flex-col p-4 gap-2 transition-transform group-hover:-translate-y-1 group-hover:shadow-lg`">
                            <div :class="`${tpl.accent} h-2 rounded-full w-3/4`" />
                            <div class="bg-white/60 h-1.5 rounded-full w-1/2" />
                            <div class="mt-2 space-y-1">
                                <div class="bg-white/50 h-1.5 rounded-full" />
                                <div class="bg-white/50 h-1.5 rounded-full w-4/5" />
                                <div class="bg-white/50 h-1.5 rounded-full w-3/5" />
                            </div>
                            <div class="mt-2 space-y-1">
                                <div :class="`${tpl.accent} h-1 rounded-full w-1/3 opacity-60`" />
                                <div class="bg-white/50 h-1 rounded-full" />
                                <div class="bg-white/50 h-1 rounded-full w-4/5" />
                            </div>
                        </div>
                        <div class="mt-2 text-center">
                            <p class="text-sm font-semibold text-gray-800">{{ tpl.name }}</p>
                            <p class="text-xs text-gray-400">{{ tpl.desc }}</p>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-8">
                    <Link v-if="canRegister" :href="route('register')"
                        class="inline-flex items-center gap-2 text-blue-600 font-semibold hover:underline">
                        Ver todos os templates →
                    </Link>
                </div>
            </div>
        </section>

        <!-- PREÇOS -->
        <section class="py-20 px-4 bg-gray-50">
            <div class="max-w-5xl mx-auto">
                <div class="text-center mb-14">
                    <h2 class="text-3xl font-black mb-3">Pague só o que usar</h2>
                    <p class="text-gray-500 text-lg">Sem mensalidade. Pix instantâneo. Sem complicação.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-start">

                    <!-- Avulso -->
                    <div>
                        <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4">Pagamento avulso</h3>
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 divide-y divide-gray-100 overflow-hidden">
                            <div v-for="item in pricing" :key="item.action_slug"
                                class="flex items-center justify-between px-6 py-4">
                                <span class="text-sm text-gray-700">{{ item.label }}</span>
                                <span class="text-lg font-bold text-blue-600">{{ formatPrice(item.price) }}</span>
                            </div>
                            <div v-if="!pricing?.length" class="px-6 py-4 text-sm text-gray-400">
                                Carregando preços...
                            </div>
                        </div>
                        <p class="mt-3 text-xs text-gray-400 text-center">Pagamento via Pix. Confirmação em segundos.</p>
                    </div>

                    <!-- Pacotes de créditos -->
                    <div>
                        <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4">Pacotes de créditos</h3>
                        <div class="space-y-3">
                            <div v-for="(pkg, i) in creditPackages" :key="pkg.name"
                                :class="[
                                    'relative bg-white rounded-2xl border p-5 shadow-sm transition-shadow hover:shadow-md',
                                    i === 1 ? 'border-blue-300 ring-2 ring-blue-100' : 'border-gray-100'
                                ]">
                                <div v-if="i === 1"
                                    class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 bg-blue-600 text-white text-xs font-bold rounded-full">
                                    Mais popular
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-bold text-gray-900">{{ pkg.name }}</p>
                                        <p class="text-sm text-gray-500 mt-0.5">
                                            Receba <span class="font-semibold text-green-600">{{ formatPrice(pkg.credits) }}</span> em créditos
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-2xl font-black text-blue-600">{{ formatPrice(pkg.price) }}</p>
                                        <span v-if="pkg.bonus_percentage > 0"
                                            class="inline-block mt-0.5 px-2 py-0.5 bg-green-100 text-green-700 text-xs font-bold rounded-full">
                                            +{{ pkg.bonus_percentage }}% bônus
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div v-if="!creditPackages?.length" class="text-sm text-gray-400 text-center py-4">
                                Carregando pacotes...
                            </div>
                        </div>
                        <p class="mt-3 text-xs text-gray-400 text-center">Créditos não expiram. Use quando quiser.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA FINAL -->
        <section class="py-24 px-4 text-center">
            <div class="max-w-2xl mx-auto">
                <h2 class="text-4xl font-black mb-4">Pronto para começar?</h2>
                <p class="text-gray-500 text-lg mb-8">
                    Crie sua conta grátis, monte seu currículo e baixe quando estiver pronto.
                </p>
                <Link v-if="canRegister" :href="route('register')"
                    class="inline-flex items-center gap-2 px-10 py-4 bg-blue-600 text-white font-bold text-lg rounded-xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-200">
                    Começar agora — é grátis →
                </Link>
            </div>
        </section>

        <!-- FOOTER -->
        <footer class="border-t border-gray-100 py-8 px-4">
            <div class="max-w-5xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
                <span class="text-lg font-black text-blue-600">Rogue</span>
                <p class="text-sm text-gray-400">© {{ new Date().getFullYear() }} Rogue. Todos os direitos reservados.</p>
                <div class="flex gap-4 text-sm text-gray-400">
                    <a href="#" class="hover:text-gray-600">Privacidade</a>
                    <a href="#" class="hover:text-gray-600">Termos de uso</a>
                </div>
            </div>
        </footer>

    </div>
</template>
