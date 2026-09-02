<script setup lang="ts">
import SystemSettingsForm from '@/features/admin/components/SystemSettingsForm.vue'
import { t } from '@/i18n'
</script>

<template>
  <div class="max-w-3xl space-y-4 sm:space-y-5">
    <div>
      <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">{{ t('nav.settings') }}</h1>
      <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 font-medium">{{ t('admin.dashboard.subtitle') }}</p>
    </div>

    <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-2xs p-4 sm:p-5 transition-colors duration-150">
      <SystemSettingsForm />
    </div>
  </div>
</template>
