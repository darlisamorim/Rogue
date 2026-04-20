<script setup lang="ts">
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

interface Transaction {
    id: number
    type: string
    type_label: string
    status: 'pending' | 'confirmed' | 'failed'
    amount: number
    discount_amount: number
    gateway: string
    coupon_code: string | null
    pix_copy_paste: string | null
    expires_at: string | null
    confirmed_at: string | null
    created_at: string
}

interface PaginatedTransactions {
    data: Transaction[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    links: { url: string | null; label: string; active: boolean }[]
}

defineProps<{
    transactions: PaginatedTransactions
}>()

const copiedId = ref<number | null>(null)

function fmt(value: number): string {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value)
}

async function copyPix(transaction: Transaction) {
    if (!transaction.pix_copy_paste) return
    try {
        await navigator.clipboard.writeText(transaction.pix_copy_paste)
    } catch {
        const el = document.createElement('textarea')
        el.value = transaction.pix_copy_paste
        document.body.appendChild(el)
        el.select()
        document.execCommand('copy')
        document.body.removeChild(el)
    }
    copiedId.value = transaction.id
    setTimeout(() => { copiedId.value = null }, 2000)
}

function goToPage(url: string | null) {
    if (url) router.visit(url)
}

const statusConfig: Record<string, { label: string; classes: string; dot: string }> = {
    confirmed: {
        label: 'Confirmado',
        classes: 'bg-green-100 text-green-800',
        dot: 'bg-green-500',
    },
    pending: {
        label: 'Aguardando pagamento',
        classes: 'bg-yellow-100 text-yellow-800',
        dot: 'bg-yellow-500',
    },
    failed: {
        label: 'Não pago / expirado',
        classes: 'bg-red-100 text-red-700',
        dot: 'bg-red-400',
    },
}

const typeIcon: Record<string, string> = {
    download:        '⬇️',
    redownload:      '🔄',
    template_change: '🎨',
    credit_purchase: '💳',
}

const gatewayLabel: Record<string, string> = {
    mercadopago: 'Mercado Pago',
    asaas:       'Asaas',
    credits:     'Créditos',
}
</script>

<template>
    <Head title="Histórico de Pagamentos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Histórico de Pagamentos</h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-4">

                <!-- Vazio -->
                <div v-if="transactions.data.length === 0"
                    class="bg-white rounded-2xl border border-gray-200 shadow-sm p-16 text-center">
                    <p class="text-4xl mb-4">💳</p>
                    <p class="text-gray-600 font-medium">Nenhuma transação encontrada.</p>
                    <p class="text-sm text-gray-400 mt-1">Suas compras e downloads aparecerão aqui.</p>
                </div>

                <!-- Lista -->
                <div v-else class="bg-white rounded-2xl border border-gray-200 shadow-sm divide-y divide-gray-100">
                    <div
                        v-for="tx in transactions.data"
                        :key="tx.id"
                        class="px-5 py-4 flex items-start gap-4"
                    >
                        <!-- Ícone -->
                        <div class="text-2xl w-9 text-center shrink-0 mt-0.5">
                            {{ typeIcon[tx.type] ?? '💰' }}
                        </div>

                        <!-- Dados principais -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-semibold text-gray-900">{{ tx.type_label }}</p>
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium"
                                    :class="statusConfig[tx.status]?.classes"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full inline-block" :class="statusConfig[tx.status]?.dot"></span>
                                    {{ statusConfig[tx.status]?.label }}
                                </span>
                                <span v-if="tx.coupon_code" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full font-medium">
                                    Cupom: {{ tx.coupon_code }}
                                </span>
                            </div>

                            <div class="flex items-center gap-3 mt-1 flex-wrap text-xs text-gray-400">
                                <span>{{ tx.created_at }}</span>
                                <span v-if="tx.confirmed_at" class="text-green-600">Pago em {{ tx.confirmed_at }}</span>
                                <span>{{ gatewayLabel[tx.gateway] ?? tx.gateway }}</span>
                                <span>Nº {{ tx.id }}</span>
                            </div>

                            <!-- Botão copiar Pix (só para pendentes com código) -->
                            <button
                                v-if="tx.status === 'pending' && tx.pix_copy_paste"
                                @click="copyPix(tx)"
                                class="mt-2 inline-flex items-center gap-1.5 text-xs text-blue-600 hover:text-blue-700 font-medium"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                {{ copiedId === tx.id ? 'Copiado!' : 'Copiar código Pix' }}
                            </button>
                        </div>

                        <!-- Valor -->
                        <div class="text-right shrink-0">
                            <p class="text-sm font-bold" :class="tx.status === 'failed' ? 'text-gray-400 line-through' : 'text-gray-900'">
                                {{ fmt(tx.amount) }}
                            </p>
                            <p v-if="tx.discount_amount > 0" class="text-xs text-green-600 mt-0.5">
                                -{{ fmt(tx.discount_amount) }} desconto
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Paginação -->
                <div v-if="transactions.last_page > 1" class="flex justify-center gap-1">
                    <button
                        v-for="link in transactions.links"
                        :key="link.label"
                        @click="goToPage(link.url)"
                        :disabled="!link.url"
                        v-html="link.label"
                        class="px-3 py-1.5 text-sm rounded-lg border transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                        :class="link.active
                            ? 'bg-blue-600 text-white border-blue-600'
                            : 'bg-white text-gray-700 border-gray-200 hover:border-blue-400 hover:text-blue-600'"
                    />
                </div>

                <!-- Resumo total -->
                <p class="text-center text-xs text-gray-400">
                    {{ transactions.total }} transação{{ transactions.total !== 1 ? 'ões' : '' }} no total
                </p>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
