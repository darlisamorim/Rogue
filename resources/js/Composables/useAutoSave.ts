import { ref, watch, type Ref } from 'vue'
import axios from 'axios'

type SaveStatus = 'idle' | 'saving' | 'saved' | 'error'

export function useAutoSave(
    resumeId: number,
    data: Ref<object>,
    customization: Ref<object>,
    title: Ref<string>,
    delayMs = 3000,
) {
    const saveStatus = ref<SaveStatus>('idle')
    let debounceTimer: ReturnType<typeof setTimeout> | null = null

    async function save() {
        saveStatus.value = 'saving'
        try {
            await axios.put(
                `/resumes/${resumeId}`,
                {
                    data: data.value,
                    customization: customization.value,
                    title: title.value,
                },
                {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                    },
                },
            )
            saveStatus.value = 'saved'
            setTimeout(() => {
                if (saveStatus.value === 'saved') saveStatus.value = 'idle'
            }, 2000)
        } catch {
            saveStatus.value = 'error'
        }
    }

    function scheduleSave() {
        if (debounceTimer) clearTimeout(debounceTimer)
        saveStatus.value = 'saving'
        debounceTimer = setTimeout(save, delayMs)
    }

    watch([data, customization, title], scheduleSave, { deep: true })

    return { saveStatus, save }
}
