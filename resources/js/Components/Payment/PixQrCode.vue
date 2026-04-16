<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import axios from 'axios'

interface Props {
  transactionId: number
  pixQrCode: string | null      // base64 imagem
  pixCopyPaste: string | null
  expiresAt: string | null
  amount: number
  gateway: string
}

const props = defineProps<Props>()
const emit = defineEmits<{
  confirmed: [transactionId: number]
  expired: []
}>()

const copied = ref(false)
const secondsLeft = ref(0)
const pollInterval = ref<ReturnType<typeof setInterval> | null>(null)
const countdownInterval = ref<ReturnType<typeof setInterval> | null>(null)

const formattedAmount = computed(() =>
  new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(props.amount)
)

const formattedCountdown = computed(() => {
  const m = Math.floor(secondsLeft.value / 60)
  const s = secondsLeft.value % 60
  return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
})

const isExpired = computed(() => secondsLeft.value <= 0)

function startCountdown() {
  if (!props.expiresAt) return
  const expiresMs = new Date(props.expiresAt).getTime()
  secondsLeft.value = Math.max(0, Math.floor((expiresMs - Date.now()) / 1000))

  countdownInterval.value = setInterval(() => {
    secondsLeft.value = Math.max(0, Math.floor((expiresMs - Date.now()) / 1000))
    if (secondsLeft.value <= 0) {
      clearInterval(countdownInterval.value!)
      stopPolling()
      emit('expired')
    }
  }, 1000)
}

function startPolling() {
  pollInterval.value = setInterval(async () => {
    try {
      const { data } = await axios.get(`/payment/status/${props.transactionId}`)
      if (data.confirmed) {
        stopPolling()
        emit('confirmed', props.transactionId)
      }
    } catch {
      // silencia erros de polling
    }
  }, 3000)
}

function stopPolling() {
  if (pollInterval.value) {
    clearInterval(pollInterval.value)
    pollInterval.value = null
  }
}

async function copyCode() {
  if (!props.pixCopyPaste) return
  try {
    await navigator.clipboard.writeText(props.pixCopyPaste)
    copied.value = true
    setTimeout(() => (copied.value = false), 2500)
  } catch {
    // fallback para browsers antigos
    const el = document.createElement('textarea')
    el.value = props.pixCopyPaste
    document.body.appendChild(el)
    el.select()
    document.execCommand('copy')
    document.body.removeChild(el)
    copied.value = true
    setTimeout(() => (copied.value = false), 2500)
  }
}

onMounted(() => {
  startCountdown()
  startPolling()
})

onUnmounted(() => {
  stopPolling()
  if (countdownInterval.value) clearInterval(countdownInterval.value)
})
</script>

<template>
  <div class="flex flex-col items-center gap-4 p-6 bg-white rounded-2xl shadow-md max-w-sm mx-auto">

    <!-- Valor e instrução -->
    <div class="text-center">
      <p class="text-3xl font-bold text-gray-900">{{ formattedAmount }}</p>
      <p class="mt-1 text-sm text-gray-500">Escaneie o QR Code ou copie o código Pix</p>
    </div>

    <!-- QR Code -->
    <div v-if="pixQrCode && !isExpired" class="border-4 border-blue-500 rounded-xl p-2">
      <img
        :src="`data:image/png;base64,${pixQrCode}`"
        alt="QR Code Pix"
        class="w-48 h-48 object-contain"
      />
    </div>

    <div v-else-if="isExpired" class="flex flex-col items-center gap-2 py-6 text-red-500">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <p class="font-semibold">Pix expirado</p>
      <p class="text-sm text-gray-500">Clique em "Gerar novo Pix" para tentar novamente.</p>
    </div>

    <!-- Countdown -->
    <div v-if="!isExpired" class="flex items-center gap-2 text-sm">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <span :class="secondsLeft < 60 ? 'text-red-500 font-bold' : 'text-gray-600'">
        Expira em {{ formattedCountdown }}
      </span>
    </div>

    <!-- Copia e cola -->
    <div v-if="pixCopyPaste && !isExpired" class="w-full">
      <p class="mb-1 text-xs font-medium text-gray-500 uppercase tracking-wide">Pix Copia e Cola</p>
      <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
        <p class="flex-1 text-xs text-gray-700 truncate font-mono">{{ pixCopyPaste }}</p>
        <button
          @click="copyCode"
          class="shrink-0 text-blue-600 hover:text-blue-800 transition-colors"
          :title="copied ? 'Copiado!' : 'Copiar código'"
        >
          <svg v-if="!copied" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
        </button>
      </div>
      <p v-if="copied" class="mt-1 text-xs text-green-600 text-center">Código copiado!</p>
    </div>

    <!-- Aguardando confirmação -->
    <div v-if="!isExpired" class="flex items-center gap-2 text-sm text-blue-600 animate-pulse">
      <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
      </svg>
      Aguardando pagamento...
    </div>

    <p class="text-xs text-gray-400 text-center">
      Gateway: {{ gateway === 'mercadopago' ? 'Mercado Pago' : 'Asaas' }}
    </p>
  </div>
</template>
