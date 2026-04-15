<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
    value: string
    max: number
    warn?: number
}>()

const count = computed(() => props.value.length)
const warnAt = computed(() => props.warn ?? Math.floor(props.max * 0.85))

const colorClass = computed(() => {
    if (count.value > props.max) return 'text-red-500'
    if (count.value >= warnAt.value) return 'text-amber-500'
    return 'text-gray-400'
})
</script>

<template>
    <span class="text-xs" :class="colorClass">
        {{ count }}/{{ max }}
    </span>
</template>
