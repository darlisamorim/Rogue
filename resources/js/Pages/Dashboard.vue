<script setup lang="ts">
import { computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'

function goToBuyCredits() {
    router.get(route('payment.checkout'), { type: 'first_download' })
}

type ResumeItem = {
    id: number
    title: string
    template: { name: string } | null
    is_downloaded: boolean
    updated_at: string
}

type PricingItem = {
    action_slug: string
    label: string
    price: string
}

const props = defineProps<{
    recentResumes: ResumeItem[]
    totalResumes: number
    totalDownloads: number
    pendingPayments: number
    pricing: Record<string, PricingItem>
}>()

const page = usePage()
const user = computed(() => page.props.auth.user as any)

const creditBalance = computed(() => Number(user.value?.credit_balance ?? 0))
const hasLowCredits = computed(() => creditBalance.value < 2)

function formatPrice(val: string | number) {
    return 'R$ ' + Number(val).toFixed(2).replace('.', ',')
}

function formatBalance(val: number) {
    return val.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
}

const stats = computed(() => [
    {
        label: 'Currículos criados',
        value: props.totalResumes,
        icon: '📄',
        color: 'bg-blue-50 text-blue-600',
    },
    {
        label: 'Downloads realizados',
        value: props.totalDownloads,
        icon: '⬇️',
        color: 'bg-green-50 text-green-600',
    },
    {
        label: 'Saldo de créditos',
        value: formatBalance(creditBalance.value),
        icon: '💰',
        color: hasLowCredits.value ? 'bg-orange-50 text-orange-600' : 'bg-emerald-50 text-emerald-600',
    },
    {
        label: 'Pagamentos pendentes',
        value: props.pendingPayments,
        icon: '⏳',
        color: props.pendingPayments > 0 ? 'bg-yellow-50 text-yellow-600' : 'bg-gray-50 text-gray-400',
    },
])
</script>

<template>
    <Head title="Painel" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Painel</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-8">

                <!-- SAUDAÇÃO -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            Olá, {{ user?.name?.split(' ')[0] }} 👋
                        </h1>
                        <p class="text-gray-500 text-sm mt-1">Bem-vindo ao seu painel de currículos.</p>
                    </div>
                    <Link
                        :href="route('resumes.create')"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-colors shadow-sm"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Novo currículo
                    </Link>
                </div>

                <!-- BANNER CRÉDITOS BAIXOS -->
                <div v-if="hasLowCredits"
                    class="bg-gradient-to-r from-orange-50 to-amber-50 border border-orange-200 rounded-2xl px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <span class="text-2xl">⚡</span>
                        <div>
                            <p class="font-semibold text-orange-900 text-sm">Seu saldo está baixo</p>
                            <p class="text-orange-700 text-xs mt-0.5">
                                Você tem {{ formatBalance(creditBalance) }} em créditos.
                                Para baixar um PDF você precisa de
                                <strong>{{ pricing.first_download ? formatPrice(pricing.first_download.price) : 'R$ 1,99' }}</strong>.
                            </p>
                        </div>
                    </div>
                    <button @click="goToBuyCredits" class="shrink-0 px-4 py-2 bg-orange-500 text-white text-sm font-semibold rounded-lg hover:bg-orange-600 transition-colors">
                        Comprar créditos
                    </button>
                </div>

                <!-- STATS -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div
                        v-for="stat in stats"
                        :key="stat.label"
                        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center gap-4"
                    >
                        <div :class="`w-12 h-12 rounded-xl flex items-center justify-center text-2xl shrink-0 ${stat.color}`">
                            {{ stat.icon }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-2xl font-black text-gray-900 truncate">{{ stat.value }}</p>
                            <p class="text-xs text-gray-400 mt-0.5 leading-tight">{{ stat.label }}</p>
                        </div>
                    </div>
                </div>

                <!-- CURRÍCULOS RECENTES + PREÇOS -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Currículos recentes -->
                    <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <h2 class="font-semibold text-gray-900">Currículos recentes</h2>
                            <Link :href="route('resumes.index')" class="text-xs text-blue-600 hover:underline font-medium">
                                Ver todos →
                            </Link>
                        </div>

                        <!-- Empty state -->
                        <div v-if="recentResumes.length === 0" class="flex flex-col items-center justify-center py-14 text-center px-6">
                            <span class="text-5xl mb-3">📄</span>
                            <p class="font-semibold text-gray-700 mb-1">Nenhum currículo ainda</p>
                            <p class="text-sm text-gray-400 mb-5">Crie seu primeiro currículo em minutos.</p>
                            <Link
                                :href="route('resumes.create')"
                                class="px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors"
                            >
                                Criar meu primeiro currículo
                            </Link>
                        </div>

                        <!-- Resume list -->
                        <div v-else class="divide-y divide-gray-50">
                            <div
                                v-for="resume in recentResumes"
                                :key="resume.id"
                                class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 transition-colors"
                            >
                                <!-- Mini thumbnail -->
                                <div class="w-10 h-12 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-lg flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-900 truncate text-sm">{{ resume.title }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ resume.template?.name ?? 'Sem template' }} · {{ resume.updated_at }}
                                    </p>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <span v-if="resume.is_downloaded"
                                        class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        Baixado
                                    </span>
                                    <Link
                                        :href="route('resumes.edit', resume.id)"
                                        class="px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"
                                    >
                                        Editar
                                    </Link>
                                </div>
                            </div>

                            <!-- Ver todos -->
                            <div v-if="totalResumes > 4" class="px-6 py-3 text-center">
                                <Link :href="route('resumes.index')" class="text-sm text-blue-600 hover:underline font-medium">
                                    Ver todos os {{ totalResumes }} currículos →
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Coluna lateral: preços + ações rápidas -->
                    <div class="space-y-4">

                        <!-- Saldo atual -->
                        <div class="bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl p-6 text-white">
                            <p class="text-blue-200 text-sm font-medium mb-1">Seu saldo</p>
                            <p class="text-4xl font-black">{{ formatBalance(creditBalance) }}</p>
                            <p class="text-blue-200 text-xs mt-2">em créditos disponíveis</p>
                            <button @click="goToBuyCredits" class="mt-4 w-full py-2.5 bg-white/20 hover:bg-white/30 text-white text-sm font-semibold rounded-lg transition-colors">
                                + Comprar créditos
                            </button>
                        </div>

                        <!-- Tabela de preços -->
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                            <div class="px-5 py-4 border-b border-gray-100">
                                <h3 class="font-semibold text-gray-900 text-sm">Tabela de preços</h3>
                            </div>
                            <div class="divide-y divide-gray-50">
                                <div
                                    v-for="item in pricing"
                                    :key="item.action_slug"
                                    class="flex items-center justify-between px-5 py-3"
                                >
                                    <span class="text-xs text-gray-600">{{ item.label }}</span>
                                    <span class="text-sm font-bold text-blue-600">{{ formatPrice(item.price) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Ação rápida -->
                        <Link
                            :href="route('resumes.create')"
                            class="block w-full text-center py-3.5 border-2 border-dashed border-blue-200 text-blue-500 text-sm font-semibold rounded-2xl hover:border-blue-400 hover:bg-blue-50 transition-colors"
                        >
                            + Novo currículo
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
