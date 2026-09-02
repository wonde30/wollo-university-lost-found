<script setup lang="ts">
import BlankLayout from '@/layouts/BlankLayout.vue'
import { t } from '@/i18n'
</script>

<template>
  <BlankLayout>
    <div class="min-h-screen flex flex-col items-center justify-center bg-slate-50 dark:bg-[#0F172A] p-6 text-center transition-colors duration-150">
      <div class="max-w-md w-full space-y-6">
        <div class="text-[7rem] font-black text-amber-500/15 leading-none select-none">403</div>
        <div>
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">{{ t('errors.403.title') }}</h1>
          <p class="text-sm text-slate-500 dark:text-slate-400">
            {{ t('errors.403.message') }}
          </p>
        </div>
        <RouterLink
          to="/"
          class="inline-flex items-center gap-2 rounded-xl bg-[#0B5D3B] text-white px-5 py-2.5 text-sm font-semibold hover:bg-[#084C30] transition-colors shadow-2xs"
        >
          ← {{ t('errors.404.backHome') }}
        </RouterLink>
      </div>
    </div>
  </BlankLayout>
</template>
