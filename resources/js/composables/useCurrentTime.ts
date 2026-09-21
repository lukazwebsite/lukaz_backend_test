import { ref, onMounted, onUnmounted } from 'vue'

export function useDateTime(interval = 1000) {
    const date = ref("")
    const time = ref("")
    let timer: number | undefined

    const updateDateTime = () => {
        const now = new Date()
        // format date
        date.value = now.getDate().toString().padStart(2, "0") + "/" + (now.getMonth() + 1).toString().padStart(2, "0") + "/" + now.getFullYear();

        // format time
        time.value = now.getHours().toString().padStart(2, "0") + ":" + now.getMinutes().toString().padStart(2, "0") + ":" + now.getSeconds().toString().padStart(2, "0")
    }

    onMounted(() => {
        updateDateTime()
        timer = window.setInterval(updateDateTime, interval)
    })

    onUnmounted(() => {
        if (timer) clearInterval(timer)
    })

    return { date, time }
}
