<script setup lang="ts">
import { t } from '@/i18n'
import { useSettingsStore } from '@/stores/settings.store'

const currentYear = new Date().getFullYear()
const settingsStore = useSettingsStore()
</script>

<template>
  <footer class="border-t border-slate-200/80 dark:border-slate-800 bg-white dark:bg-[#111827] py-8 px-4 sm:px-6 lg:px-8 text-slate-500 dark:text-slate-400 text-xs transition-colors duration-150">
    <div class="max-w-7xl mx-auto space-y-6">
      <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-center md:text-left">
        <div class="flex items-center gap-3">
          <img
            :src="settingsStore.logoUrl"
            :alt="settingsStore.institutionName + ' Emblem'"
            class="h-9 w-9 shrink-0 object-contain rounded-full bg-white dark:bg-slate-800 shadow-2xs ring-1 ring-slate-300/40 dark:ring-slate-700/40 p-0.5"
          />
          <div>
            <span class="font-bold text-slate-900 dark:text-slate-100 block">
              {{ settingsStore.siteName }}
            </span>
            <span class="text-[11px] text-slate-400 dark:text-slate-500">
              {{ settingsStore.campusesText }}
            </span>
          </div>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-6 text-slate-600 dark:text-slate-300 font-medium text-xs">
          <RouterLink to="/" class="hover:text-slate-800 dark:hover:text-slate-100 transition-colors">{{ t('nav.home') }}</RouterLink>
          <RouterLink to="/browse" class="hover:text-slate-800 dark:hover:text-slate-100 transition-colors">{{ t('nav.browse') }}</RouterLink>
          <RouterLink to="/track" class="hover:text-slate-800 dark:hover:text-slate-100 transition-colors">{{ t('nav.track') }}</RouterLink>
          <a :href="settingsStore.institutionWebsite" target="_blank" rel="noopener noreferrer" class="hover:text-slate-800 dark:hover:text-slate-100 transition-colors">
            {{ settingsStore.institutionName }}
          </a>
        </div>
      </div>

      <div class="pt-5 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-slate-400 dark:text-slate-500 text-center sm:text-left">
        <p>&copy; {{ currentYear }} {{ settingsStore.institutionName }}.</p>
        <p class="font-medium text-slate-500 dark:text-slate-400">
          {{ settingsStore.developerCredit }}
        </p>
      </div>
    </div>
  </footer>
</template>
