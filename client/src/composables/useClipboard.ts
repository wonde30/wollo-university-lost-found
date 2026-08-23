/**
 * Clipboard copy composable with temporary state feedback.
 */

import { ref } from 'vue'

export function useClipboard(timeout = 2000) {
  const copied = ref(false)
  let timer: ReturnType<typeof setTimeout> | null = null

  async function copy(text: string): Promise<boolean> {
    if (!navigator.clipboard) {
      try {
        const textarea = document.createElement('textarea')
        textarea.value = text
        textarea.style.position = 'fixed'
        textarea.style.opacity = '0'
        document.body.appendChild(textarea)
        textarea.select()
        document.execCommand('copy')
        document.body.removeChild(textarea)
        setCopied()
        return true
      } catch {
        return false
      }
    }

    try {
      await navigator.clipboard.writeText(text)
      setCopied()
      return true
    } catch {
      return false
    }
  }

  function setCopied() {
    copied.value = true
    if (timer) clearTimeout(timer)
    timer = setTimeout(() => {
      copied.value = false
      timer = null
    }, timeout)
  }

  return {
    copied,
    copy,
  }
}
