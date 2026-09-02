import { defineStore } from 'pinia'
import { ref } from 'vue'

export interface Toast {
  id: string
  type: 'success' | 'error' | 'warning' | 'info'
  title?: string
  message: string
  duration?: number
}

export interface ConfirmDialogOptions {
  title: string
  message: string
  confirmText?: string
  cancelText?: string
  variant?: 'danger' | 'primary' | 'warning'
  onConfirm: () => void | Promise<void>
  onCancel?: () => void
}

export type ThemeMode = 'light' | 'dark' | 'system'

export const useUiStore = defineStore('ui', () => {
  // Theme state
  const theme = ref<ThemeMode>(
    (typeof localStorage !== 'undefined' ? (localStorage.getItem('wu_theme') as ThemeMode) : null) || 'system'
  )
  const isDark = ref<boolean>(false)

  function applyTheme(newTheme: ThemeMode): void {
    theme.value = newTheme
    if (typeof window === 'undefined') return

    localStorage.setItem('wu_theme', newTheme)
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
    const shouldBeDark = newTheme === 'dark' || (newTheme === 'system' && prefersDark)
    
    isDark.value = shouldBeDark
    if (shouldBeDark) {
      document.documentElement.classList.add('dark')
    } else {
      document.documentElement.classList.remove('dark')
    }
  }

  function setTheme(newTheme: ThemeMode): void {
    applyTheme(newTheme)
  }

  function toggleTheme(): void {
    if (theme.value === 'dark') {
      applyTheme('light')
    } else if (theme.value === 'light') {
      applyTheme('dark')
    } else {
      // If currently system, toggle opposite to current computed dark state
      applyTheme(isDark.value ? 'light' : 'dark')
    }
  }

  // Initialize theme on creation
  if (typeof window !== 'undefined') {
    applyTheme(theme.value)
    
    // Listen for OS color scheme change
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
    mediaQuery.addEventListener('change', (e) => {
      if (theme.value === 'system') {
        isDark.value = e.matches
        if (e.matches) {
          document.documentElement.classList.add('dark')
        } else {
          document.documentElement.classList.remove('dark')
        }
      }
    })
  }

  // Sidebar state
  const sidebarCollapsed = ref(false)
  const sidebarMobileOpen = ref(false)

  // Backward-compatibility aliases
  const isSidebarOpen = sidebarMobileOpen
  const isMobileMenuOpen = sidebarMobileOpen

  // Global loading overlay
  const globalLoading = ref(false)
  const loadingMessage = ref('')

  // Toast notifications
  const activeToasts = ref<Toast[]>([])
  const toasts = activeToasts // Alias for backwards compatibility

  // Confirm dialog state
  const confirmDialog = ref<ConfirmDialogOptions | null>(null)
  const isConfirmDialogOpen = ref(false)
  const confirmDialogLoading = ref(false)

  function toggleSidebar(): void {
    sidebarCollapsed.value = !sidebarCollapsed.value
  }

  function setSidebarCollapsed(collapsed: boolean): void {
    sidebarCollapsed.value = collapsed
  }

  function toggleMobileMenu(): void {
    sidebarMobileOpen.value = !sidebarMobileOpen.value
  }

  function setSidebarOpen(open: boolean): void {
    sidebarMobileOpen.value = open
  }

  function setMobileMenuOpen(open: boolean): void {
    sidebarMobileOpen.value = open
  }

  function showGlobalLoading(message: string = 'Loading...'): void {
    loadingMessage.value = message
    globalLoading.value = true
  }

  function hideGlobalLoading(): void {
    globalLoading.value = false
    loadingMessage.value = ''
  }

  function showToast(options: Omit<Toast, 'id'> | string, type: Toast['type'] = 'info'): string {
    const id = Math.random().toString(36).substring(2, 9)
    const toastConfig: Toast = typeof options === 'string'
      ? { id, type, message: options, duration: 4000 }
      : { id, duration: 4000, ...options }

    activeToasts.value.push(toastConfig)

    if (toastConfig.duration && toastConfig.duration > 0) {
      setTimeout(() => {
        dismissToast(id)
      }, toastConfig.duration)
    }

    return id
  }

  function dismissToast(id: string): void {
    const index = activeToasts.value.findIndex(t => t.id === id)
    if (index !== -1) {
      activeToasts.value.splice(index, 1)
    }
  }

  const removeToast = dismissToast // Alias

  function success(message: string, title?: string): string {
    return showToast({ type: 'success', message, title })
  }

  function error(message: string, title?: string): string {
    return showToast({ type: 'error', message, title })
  }

  function warning(message: string, title?: string): string {
    return showToast({ type: 'warning', message, title })
  }

  function info(message: string, title?: string): string {
    return showToast({ type: 'info', message, title })
  }

  function showConfirm(options: ConfirmDialogOptions): void {
    confirmDialog.value = options
    isConfirmDialogOpen.value = true
  }

  async function handleConfirm(): Promise<void> {
    if (!confirmDialog.value) return
    confirmDialogLoading.value = true
    try {
      await confirmDialog.value.onConfirm()
      isConfirmDialogOpen.value = false
      confirmDialog.value = null
    } finally {
      confirmDialogLoading.value = false
    }
  }

  function handleCancel(): void {
    if (confirmDialog.value?.onCancel) {
      confirmDialog.value.onCancel()
    }
    isConfirmDialogOpen.value = false
    confirmDialog.value = null
  }

  return {
    theme,
    isDark,
    setTheme,
    toggleTheme,
    sidebarCollapsed,
    sidebarMobileOpen,
    isSidebarOpen,
    isMobileMenuOpen,
    globalLoading,
    loadingMessage,
    activeToasts,
    toasts,
    confirmDialog,
    isConfirmDialogOpen,
    confirmDialogLoading,
    toggleSidebar,
    setSidebarCollapsed,
    toggleMobileMenu,
    setSidebarOpen,
    setMobileMenuOpen,
    showGlobalLoading,
    hideGlobalLoading,
    showToast,
    dismissToast,
    removeToast,
    success,
    error,
    warning,
    info,
    showConfirm,
    confirm: showConfirm,
    handleConfirm,
    handleCancel,
  }
})
