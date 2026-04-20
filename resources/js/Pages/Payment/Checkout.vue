<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PixQrCode from '@/Components/Payment/PixQrCode.vue'
import axios from 'axios'

interface CreditPackage {
    id: number
    name: string
    price: number
    credits: number
    bonus_percentage: number
}

interface Props {
    resumeId: number | null
    type: string
    price: number
    hasCredits: boolean
    creditBalance: number
    creditPackages: CreditPackage[]
    userCpf: string | null
}

const props = defineProps<Props>()

type Step = 'choose' | 'cpf_step' | 'pix_loading' | 'pix_waiting' | 'confirmed' | 'error'

const step = ref<Step>('choose')
const redirectCountdown = ref(4)
let redirectTimer: ReturnType<typeof setInterval> | null = null

const pixData = ref<{
    transactionId: number
    pixQrCode: string | null
    pixCopyPaste: string | null
    expiresAt: string | null
    amount: number
    gateway: string
} | null>(null)

const errorMessage = ref('')
const loadingAction = ref<string | null>(null)

// CPF do pagador
const payerCpf = ref(props.userCpf ?? '')
const cpfError = ref('')
const cpfMasked = computed(() => formatCpf(payerCpf.value))

// Ação pendente (o que executar após confirmar CPF)
type PendingAction = { kind: 'pix' } | { kind: 'package'; pkg: CreditPackage }
const pendingAction = ref<PendingAction | null>(null)

// Cupom
const couponInput = ref('')
const couponLoading = ref(false)
const couponError = ref('')
const appliedCoupon = ref<{ code: string; discount: number; finalPrice: number; message: string } | null>(null)

// Preço efetivo após desconto
const effectivePrice = computed(() =>
    appliedCoupon.value ? appliedCoupon.value.finalPrice : props.price
)

const formattedPrice = computed(() =>
    fmt(effectivePrice.value)
)
const formattedBalance = computed(() =>
    fmt(props.creditBalance)
)
const typeLabel = computed(() => {
    const labels: Record<string, string> = {
        first_download: 'Primeiro Download',
        redownload: 'Re-download',
        template_change: 'Troca de Template',
    }
    return labels[props.type] ?? props.type
})

// ─── Cupom ────────────────────────────────────────────────────────────────────

async function applyCoupon() {
    const code = couponInput.value.trim()
    if (!code) return

    couponLoading.value = true
    couponError.value = ''

    try {
        const { data } = await axios.post('/payment/coupon/validate', {
            code,
            type: mapType(props.type),
            package_id: null,
        })
        appliedCoupon.value = {
            code: code.toUpperCase(),
            discount: data.discount,
            finalPrice: data.final_price,
            message: data.message,
        }
        couponInput.value = ''
    } catch (err: any) {
        couponError.value = err?.response?.data?.message ?? 'Cupom inválido ou expirado.'
    } finally {
        couponLoading.value = false
    }
}

function removeCoupon() {
    appliedCoupon.value = null
    couponError.value = ''
}

// ─── CPF ──────────────────────────────────────────────────────────────────────

function onCpfInput(e: Event) {
    const raw = (e.target as HTMLInputElement).value.replace(/\D/g, '').slice(0, 11)
    payerCpf.value = raw
    cpfError.value = ''
}

function formatCpf(raw: string): string {
    const d = raw.replace(/\D/g, '')
    if (d.length <= 3)  return d
    if (d.length <= 6)  return `${d.slice(0,3)}.${d.slice(3)}`
    if (d.length <= 9)  return `${d.slice(0,3)}.${d.slice(3,6)}.${d.slice(6)}`
    return `${d.slice(0,3)}.${d.slice(3,6)}.${d.slice(6,9)}-${d.slice(9,11)}`
}

function validateCpfValue(raw: string): boolean {
    const d = raw.replace(/\D/g, '')
    if (d.length !== 11 || /^(\d)\1{10}$/.test(d)) return false
    for (let t = 9; t < 11; t++) {
        let sum = 0
        for (let i = 0; i < t; i++) sum += parseInt(d[i]) * ((t + 1) - i)
        const rem = (10 * sum) % 11 % 10
        if (parseInt(d[t]) !== rem) return false
    }
    return true
}

function requestPixWithCpf(action: PendingAction) {
    pendingAction.value = action
    step.value = 'cpf_step'
}

async function proceedWithCpf() {
    if (!validateCpfValue(payerCpf.value)) {
        cpfError.value = 'CPF inválido. Verifique os números e tente novamente.'
        return
    }

    const formattedCpfValue = formatCpf(payerCpf.value)
    const action = pendingAction.value
    if (!action) return

    if (action.kind === 'pix') {
        await executePix(formattedCpfValue)
    } else {
        await executePackage(action.pkg, formattedCpfValue)
    }
}

// ─── Pagamentos ───────────────────────────────────────────────────────────────

async function payWithCredits() {
    loadingAction.value = 'credits'
    try {
        const { data } = await axios.post('/payment/initiate', {
            type: mapType(props.type),
            resume_id: props.resumeId,
            coupon_code: appliedCoupon.value?.code ?? null,
        })
        if (data.paid_with === 'credits' && data.status === 'confirmed') {
            step.value = 'confirmed'
            startRedirectCountdown()
        }
    } catch (err: any) {
        errorMessage.value = err?.response?.data?.error ?? 'Erro ao processar pagamento.'
        step.value = 'error'
    } finally {
        loadingAction.value = null
    }
}

async function payWithPix() {
    requestPixWithCpf({ kind: 'pix' })
}

async function buyPackageAndPay(pkg: CreditPackage) {
    requestPixWithCpf({ kind: 'package', pkg })
}

async function executePix(cpf: string) {
    loadingAction.value = 'pix'
    step.value = 'pix_loading'
    try {
        const { data } = await axios.post('/payment/initiate', {
            type: mapType(props.type),
            resume_id: props.resumeId,
            payer_cpf: cpf,
            coupon_code: appliedCoupon.value?.code ?? null,
        })
        pixData.value = {
            transactionId: data.transaction_id,
            pixQrCode: data.pix_qr_code,
            pixCopyPaste: data.pix_copy_paste,
            expiresAt: data.expires_at,
            amount: Number(data.amount),
            gateway: data.gateway,
        }
        step.value = 'pix_waiting'
    } catch (err: any) {
        errorMessage.value = err?.response?.data?.error ?? 'Não foi possível gerar o Pix.'
        step.value = 'error'
    } finally {
        loadingAction.value = null
    }
}

async function executePackage(pkg: CreditPackage, cpf: string) {
    loadingAction.value = `package_${pkg.id}`
    step.value = 'pix_loading'
    try {
        const { data } = await axios.post('/payment/initiate', {
            type: 'credit_purchase',
            package_id: pkg.id,
            payer_cpf: cpf,
            coupon_code: appliedCoupon.value?.code ?? null,
        })
        pixData.value = {
            transactionId: data.transaction_id,
            pixQrCode: data.pix_qr_code,
            pixCopyPaste: data.pix_copy_paste,
            expiresAt: data.expires_at,
            amount: Number(data.amount),
            gateway: data.gateway,
        }
        step.value = 'pix_waiting'
    } catch (err: any) {
        errorMessage.value = err?.response?.data?.error ?? 'Não foi possível gerar o Pix.'
        step.value = 'error'
    } finally {
        loadingAction.value = null
    }
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function fmt(value: number): string {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value)
}

function onPaymentConfirmed() {
    step.value = 'confirmed'
    startRedirectCountdown()
}

function startRedirectCountdown() {
    redirectCountdown.value = 4
    redirectTimer = setInterval(() => {
        redirectCountdown.value--
        if (redirectCountdown.value <= 0) {
            clearInterval(redirectTimer!)
            goToResumes()
        }
    }, 1000)
}

function onPaymentExpired() {
    step.value = 'choose'
    pixData.value = null
}

function goBack() {
    step.value = 'choose'
    pixData.value = null
    errorMessage.value = ''
    pendingAction.value = null
}

function goToDashboard() {
    router.visit(route('dashboard'))
}

function goToResumes() {
    if (props.resumeId && (props.type === 'first_download' || props.type === 'redownload')) {
        router.visit(route('resumes.show', props.resumeId))
    } else {
        router.visit(route('resumes.index'))
    }
}

function mapType(t: string): string {
    const map: Record<string, string> = {
        first_download: 'download',
        redownload: 'redownload',
        template_change: 'template_change',
    }
    return map[t] ?? t
}
</script>

<template>
    <Head title="Checkout" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Checkout</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">

                <!-- Step: escolha de pagamento -->
                <div v-if="step === 'choose'" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5 text-white">
                        <p class="text-sm font-medium opacity-80">{{ typeLabel }}</p>
                        <div class="flex items-end gap-3 mt-1">
                            <p class="text-3xl font-bold">{{ formattedPrice }}</p>
                            <p v-if="appliedCoupon" class="text-sm line-through opacity-60 pb-1">
                                {{ fmt(price) }}
                            </p>
                        </div>
                        <p v-if="appliedCoupon" class="text-sm mt-1 bg-white/20 rounded-full px-3 py-0.5 inline-block">
                            {{ appliedCoupon.message }}
                        </p>
                    </div>

                    <div class="p-6 space-y-4">

                        <!-- Opção: pagar com créditos -->
                        <div v-if="hasCredits" class="border-2 border-green-400 rounded-xl p-4 bg-green-50">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="font-semibold text-gray-900">Usar meus créditos</p>
                                    <p class="text-sm text-gray-600 mt-0.5">
                                        Saldo atual: <span class="font-medium text-green-700">{{ formattedBalance }}</span>
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">Confirmação instantânea, sem espera.</p>
                                </div>
                                <span class="text-2xl">⚡</span>
                            </div>
                            <button
                                @click="payWithCredits"
                                :disabled="loadingAction === 'credits'"
                                class="mt-4 w-full py-2.5 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50"
                            >
                                {{ loadingAction === 'credits' ? 'Processando...' : 'Pagar com Créditos' }}
                            </button>
                        </div>

                        <!-- Saldo insuficiente — nota discreta -->
                        <div v-if="!hasCredits && creditBalance > 0"
                            class="flex items-center gap-2 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>
                                Seu saldo de <strong class="text-gray-700">{{ formattedBalance }}</strong> não é suficiente
                                para esta ação ({{ fmt(price) }}). Compre um pacote abaixo.
                            </span>
                        </div>

                        <!-- Separador -->
                        <div class="flex items-center gap-3 text-gray-400 text-sm">
                            <div class="flex-1 border-t border-gray-200"></div>
                            <span>ou</span>
                            <div class="flex-1 border-t border-gray-200"></div>
                        </div>

                        <!-- Opção: pagar avulso via Pix -->
                        <div class="border border-gray-200 rounded-xl p-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="font-semibold text-gray-900">Pagar via Pix</p>
                                    <p class="text-sm text-gray-600 mt-0.5">Pagamento único de {{ formattedPrice }}</p>
                                    <p class="text-xs text-gray-400 mt-1">QR Code gerado na hora.</p>
                                </div>
                                <span class="text-2xl">🏦</span>
                            </div>
                            <button
                                @click="payWithPix"
                                :disabled="!!loadingAction"
                                class="mt-4 w-full py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
                            >
                                Gerar Pix
                            </button>
                        </div>

                        <!-- Opção: comprar pacote de créditos -->
                        <div class="border border-gray-200 rounded-xl p-4">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <p class="font-semibold text-gray-900">Comprar pacote de créditos</p>
                                    <p class="text-sm text-gray-600 mt-0.5">Economize com bônus de créditos</p>
                                </div>
                                <span class="text-2xl">💳</span>
                            </div>
                            <div class="space-y-2">
                                <button
                                    v-for="pkg in creditPackages"
                                    :key="pkg.id"
                                    @click="buyPackageAndPay(pkg)"
                                    :disabled="!!loadingAction"
                                    class="w-full flex items-center justify-between px-4 py-3 rounded-lg border border-gray-200 hover:border-blue-400 hover:bg-blue-50 transition-colors disabled:opacity-50 text-left"
                                >
                                    <div>
                                        <p class="font-medium text-gray-900 text-sm">{{ pkg.name }}</p>
                                        <p class="text-xs text-gray-500">{{ fmt(pkg.credits) }} em créditos</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-gray-900 text-sm">{{ fmt(pkg.price) }}</p>
                                        <span class="text-xs font-medium text-green-600">+{{ pkg.bonus_percentage }}% bônus</span>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Cupom de desconto -->
                        <div class="border border-dashed border-gray-300 rounded-xl p-4 space-y-2">
                            <p class="text-sm font-medium text-gray-700">Tem um cupom de desconto?</p>

                            <div v-if="appliedCoupon" class="flex items-center justify-between bg-green-50 border border-green-200 rounded-lg px-3 py-2">
                                <div>
                                    <span class="text-sm font-semibold text-green-800">{{ appliedCoupon.code }}</span>
                                    <span class="text-xs text-green-600 ml-2">{{ appliedCoupon.message }}</span>
                                </div>
                                <button @click="removeCoupon" class="text-gray-400 hover:text-red-500 text-xs underline ml-3">Remover</button>
                            </div>

                            <div v-else class="flex gap-2">
                                <input
                                    v-model="couponInput"
                                    type="text"
                                    placeholder="CODIGO123"
                                    @keydown.enter.prevent="applyCoupon"
                                    class="flex-1 rounded-lg border-gray-200 bg-gray-50 text-sm uppercase focus:bg-white focus:border-blue-500 focus:ring-blue-500"
                                />
                                <button
                                    @click="applyCoupon"
                                    :disabled="couponLoading || !couponInput.trim()"
                                    class="px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700 disabled:opacity-40 transition-colors"
                                >
                                    {{ couponLoading ? '...' : 'Aplicar' }}
                                </button>
                            </div>

                            <p v-if="couponError" class="text-xs text-red-600">{{ couponError }}</p>
                        </div>

                    </div>
                </div>

                <!-- Step: CPF do pagador -->
                <div v-else-if="step === 'cpf_step'" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5 text-white">
                        <p class="text-sm font-medium opacity-80">Identificação do pagador</p>
                        <p class="text-lg font-bold mt-1">Informe o CPF de quem vai pagar</p>
                    </div>

                    <div class="p-6 space-y-5">
                        <p class="text-sm text-gray-600">
                            O CPF é obrigatório para geração do Pix. Pode ser o seu ou o de outra pessoa que vai realizar o pagamento.
                        </p>

                        <!-- Aviso se CPF da conta está pré-preenchido -->
                        <div v-if="userCpf" class="flex items-start gap-2 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
                            <svg class="w-4 h-4 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <span>Pré-preenchido com o CPF da sua conta. Se outra pessoa vai pagar, substitua abaixo.</span>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">CPF do pagador</label>
                            <input
                                :value="cpfMasked"
                                @input="onCpfInput"
                                type="text"
                                inputmode="numeric"
                                placeholder="000.000.000-00"
                                maxlength="14"
                                class="w-full rounded-lg border-gray-200 bg-gray-50 text-sm focus:bg-white focus:border-blue-500 focus:ring-blue-500"
                                :class="{ 'border-red-400 focus:border-red-500 focus:ring-red-500': cpfError }"
                            />
                            <p v-if="cpfError" class="mt-1 text-xs text-red-600">{{ cpfError }}</p>
                        </div>

                        <div class="flex gap-3">
                            <button
                                @click="goBack"
                                class="flex-1 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors"
                            >
                                Voltar
                            </button>
                            <button
                                @click="proceedWithCpf"
                                :disabled="!!loadingAction"
                                class="flex-1 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
                            >
                                Gerar QR Code Pix
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step: carregando Pix -->
                <div v-else-if="step === 'pix_loading'" class="flex flex-col items-center gap-4 py-20">
                    <svg class="h-12 w-12 animate-spin text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    <p class="text-gray-600 font-medium">Gerando QR Code Pix...</p>
                </div>

                <!-- Step: aguardando pagamento Pix -->
                <div v-else-if="step === 'pix_waiting' && pixData" class="space-y-4">
                    <PixQrCode
                        :transaction-id="pixData.transactionId"
                        :pix-qr-code="pixData.pixQrCode"
                        :pix-copy-paste="pixData.pixCopyPaste"
                        :expires-at="pixData.expiresAt"
                        :amount="pixData.amount"
                        :gateway="pixData.gateway"
                        @confirmed="onPaymentConfirmed"
                        @expired="onPaymentExpired"
                    />
                    <div class="text-center">
                        <button @click="goBack" class="text-sm text-gray-500 hover:text-gray-700 underline">
                            Voltar e escolher outra forma
                        </button>
                    </div>
                </div>

                <!-- Step: pagamento confirmado -->
                <div v-else-if="step === 'confirmed'" class="flex flex-col items-center gap-6 py-16 text-center">
                    <div class="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center animate-bounce">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Pagamento confirmado!</h2>
                        <p class="mt-2 text-gray-600">
                            Redirecionando para seus currículos em
                            <span class="font-bold text-blue-600">{{ redirectCountdown }}s</span>...
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <button
                            @click="goToResumes"
                            class="px-5 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            Ir agora →
                        </button>
                        <button
                            @click="goToDashboard"
                            class="px-5 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Dashboard
                        </button>
                    </div>
                </div>

                <!-- Step: erro -->
                <div v-else-if="step === 'error'" class="flex flex-col items-center gap-6 py-16 text-center">
                    <div class="w-20 h-20 rounded-full bg-red-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Algo deu errado</h2>
                        <p class="mt-2 text-gray-600">{{ errorMessage }}</p>
                    </div>
                    <button
                        @click="goBack"
                        class="px-5 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        Tentar novamente
                    </button>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
