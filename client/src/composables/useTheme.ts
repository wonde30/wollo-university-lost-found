import { computed } from 'vue'
import { useUiStore, type ThemeMode } from '@/stores/ui.store'

export type { ThemeMode }

export function useTheme() {
  const uiStore = useUiStore()

  const theme = computed({
    get: () => uiStore.theme,
    set: (val: ThemeMode) => uiStore.setTheme(val),
  })

  const isDark = computed(() => uiStore.isDark)

  function toggle() {
    uiStore.toggleTheme()
  }

  function set(mode: ThemeMode) {
    uiStore.setTheme(mode)
  }

  return {
    theme,
    isDark,
    toggle,
    set,
    setTheme: set,
    toggleTheme: toggle,
  }
}
