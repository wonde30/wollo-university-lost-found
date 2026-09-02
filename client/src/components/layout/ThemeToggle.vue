<script setup lang="ts">
import { useTheme } from '@/composables/useTheme'
import { t } from '@/i18n'
import { Sun, Moon } from 'lucide-vue-next'

const { isDark, toggleTheme } = useTheme()
</script>

<template>
  <button
    type="button"
    :title="isDark ? t('common.lightMode') : t('common.darkMode')"
    :aria-label="isDark ? t('common.lightMode') : t('common.darkMode')"
    class="p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-300 dark:hover:text-white dark:hover:bg-slate-800/80 transition-all duration-150 cursor-pointer inline-flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-[#0B5D3B]/40"
    @click="toggleTheme"
  >
    <Sun
      v-if="isDark"
      class="h-4.5 w-4.5 text-[#D4AF37] transition-transform duration-300 rotate-0 hover:rotate-45"
    />
    <Moon
      v-else
      class="h-4.5 w-4.5 text-slate-700 transition-transform duration-300 rotate-0 hover:-rotate-12 dark:text-slate-300"
    />
  </button>
</template>
