<script setup lang="ts">
import { useI18n, type Locale } from '@/i18n'

const { locale, setLocale, supportedLocales, t } = useI18n()
</script>

<template>
  <div class="inline-flex items-center rounded-xl border border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-[#111827]/80 p-0.5 shadow-2xs">
    <button
      v-for="(meta, code) in supportedLocales"
      :key="code"
      type="button"
      :class="[
        'px-2.5 py-1 text-xs font-bold rounded-lg transition-all cursor-pointer select-none',
        locale === code
          ? 'bg-[#0B5D3B] text-white shadow-2xs'
          : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white',
      ]"
      :aria-label="t('common.switchLanguage') + ': ' + meta.nativeName"
      :title="meta.name"
      @click="setLocale(code as Locale)"
    >
      {{ code === 'am' ? 'አማ' : meta.code.toUpperCase() }}
    </button>
  </div>
</template>
