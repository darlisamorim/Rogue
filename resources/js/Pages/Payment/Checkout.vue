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
}

const props = defineProps<Props>()

type Step = 'choose' | 'pix_loading' | 'pix_waiting' | 'confirmed' | 'error'

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

const formattedPrice = computed(() =>
  new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(props.price)
)
const formattedBalance = computed(() =>
  new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(props.creditBalance)
)

const typeLabel = computed(() => {
  const labels: Record<string, string> = {
    first_download: 'Primeiro Download',
    redownload: 'Re-download',
    template_change: 'Troca de Template',
  }
  return labels[props.type] ?? props.type
})

async function payWithCredits() {
  loadingAction.value = 'credits'
  try {
    const { data } = await axios.post('/payment/initiate', {
      type: mapType(props.type),
      resume_id: props.resumeId,
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
  loadingAction.value = 'pix'
  step.value = 'pix_loading'
  try {
    const { data } = await axios.post('/payment/initiate', {
      type: mapType(props.type),
      resume_id: props.resumeId,
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

async function buyPackageAndPay(pkg: CreditPackage) {
  loadingAction.value = `package_${pkg.id}`
  step.value = 'pix_loading'
  try {
    const { data } = await axios.post('/payment/initiate', {
      type: 'credit_purchase',
      package_id: pkg.id,
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
}

function goToDashboard() {
  router.visit(route('dashboard'))
}

function goToResumes() {
  // Se tem resume_id e foi download/redownload, vai direto para a página de download
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
            <p class="text-3xl font-bold mt-1">{{ formattedPrice }}</p>
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
                Seu saldo de <strong class="text-gray-700">{{ formattedBalance }}</strong> não é suficiente para esta ação ({{ formattedPrice }}).
                Compre um pacote abaixo para acumular créditos.
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
                    <p class="text-xs text-gray-500">
                      {{ new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(pkg.credits) }} em créditos
                    </p>
                  </div>
                  <div class="text-right">
                    <p class="font-bold text-gray-900 text-sm">
                      {{ new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(pkg.price) }}
                    </p>
                    <span class="text-xs font-medium text-green-600">+{{ pkg.bonus_percentage }}% bônus</span>
                  </div>
                </button>
              </div>
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
