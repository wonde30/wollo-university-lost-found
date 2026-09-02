/*
 Responsive viewport breakpoints composable.
 */

import { ref, computed, onMounted, onUnmounted } from 'vue'

export function useBreakpoints() {
  const width = ref(typeof window !== 'undefined' ? window.innerWidth : 1024)

  function onResize() {
    width.value = window.innerWidth
  }

  onMounted(() => {
    if (typeof window !== 'undefined') {
      window.addEventListener('resize', onResize, { passive: true })
    }
  })

  onUnmounted(() => {
    if (typeof window !== 'undefined') {
      window.removeEventListener('resize', onResize)
    }
  })

  const isMobile = computed(() => width.value < 768)
  const isTablet = computed(() => width.value >= 768 && width.value < 1024)
  const isDesktop = computed(() => width.value >= 1024)

  return {
    width,
    isMobile,
    isTablet,
    isDesktop,
  }
}
