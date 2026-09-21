// composables/useHistory.js
import { ref} from 'vue'
import { router } from '@inertiajs/vue3'

const history = ref([])

router.on('navigate', (event) => {
  const url = event.detail.page.url
  history.value.push(url)
})

export function useHistory() {
    const back = () => {
        history.value.pop() // current
        const prev = history.value.pop() // previous
        if (prev) router.visit(prev)
        else router.visit('/dashboard')
    }
    return {
        history,
        back
    }
}
