<script setup lang="ts">
import BlankLayout from '@/layouts/BlankLayout.vue'
import { t } from '@/i18n'
</script>

<template>
  <BlankLayout>
    <div class="min-h-screen flex flex-col items-center justify-center bg-slate-50 dark:bg-[#0F172A] p-6 text-center transition-colors duration-150">
      <div class="max-w-md w-full space-y-6">
        <div class="text-[7rem] font-black text-slate-300 dark:text-slate-700 leading-none select-none">500</div>
        <div>
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">{{ t('errors.500.title') }}</h1>
          <p class="text-sm text-slate-500 dark:text-slate-400">
            {{ t('errors.500.message') }}
          </p>
        </div>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-xl bg-[#0B5D3B] text-white px-5 py-2.5 text-sm font-semibold hover:bg-[#084C30] transition-colors cursor-pointer shadow-2xs"
            @click="$router.go(0)"
          >
            {{ t('errors.500.tryAgain') }}
          </button>
          <RouterLink
            to="/"
            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#111827] text-slate-700 dark:text-slate-300 px-5 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
          >
            {{ t('errors.404.backHome') }}
          </RouterLink>
        </div>
      </div>
    </div>
  </BlankLayout>
</template>
