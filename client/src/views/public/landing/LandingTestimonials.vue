<script setup lang="ts">
import { computed } from 'vue'
import { t } from '@/i18n'
import { ShieldCheck, KeyRound, Lock, CheckCircle2 } from 'lucide-vue-next'

const trustPillars = computed(() => [
  {
    id: 'chain-of-custody',
    title: t('home.trust.p1.title'),
    desc: t('home.trust.p1.desc'),
    icon: ShieldCheck,
    badge: 'ISO Custody',
  },
  {
    id: 'dual-confirmation',
    title: t('home.trust.p2.title'),
    desc: t('home.trust.p2.desc'),
    icon: KeyRound,
    badge: 'Cryptographic 2FA',
  },
  {
    id: 'data-privacy',
    title: t('home.trust.p3.title'),
    desc: t('home.trust.p3.desc'),
    icon: Lock,
    badge: 'Privacy Shield',
  },
])
</script>

<template>
  <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-20 sm:mb-24" aria-labelledby="trust-heading">
    <!-- Header -->
    <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-14 space-y-2.5">
      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-emerald-50 dark:bg-emerald-950/60 text-[#0B5D3B] dark:text-[#75bd97] border border-emerald-200/60 dark:border-emerald-800/60">
        <CheckCircle2 class="w-3.5 h-3.5" />
        {{ t('home.trust.tag') }}
      </span>
      <h2 id="trust-heading" class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight text-slate-900 dark:text-white">
        {{ t('home.trust.title') }}
      </h2>
      <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
        {{ t('home.trust.subtitle') }}
      </p>
    </div>

    <!-- 3 Institutional Standard Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div
        v-for="pillar in trustPillars"
        :key="pillar.id"
        class="group relative bg-white dark:bg-[#111827] rounded-2xl p-6 sm:p-7 border border-slate-200/80 dark:border-slate-800 shadow-2xs hover:shadow-md hover:border-[#0B5D3B]/40 dark:hover:border-[#75bd97]/40 transition-all duration-200 flex flex-col justify-between"
      >
        <div class="space-y-4">
          <!-- Icon & Badge -->
          <div class="flex items-center justify-between">
            <div class="h-12 w-12 rounded-xl flex items-center justify-center bg-[#0B5D3B]/10 dark:bg-[#75bd97]/15 text-[#0B5D3B] dark:text-[#75bd97] ring-1 ring-[#0B5D3B]/20 dark:ring-[#75bd97]/30 transition-transform duration-200 group-hover:scale-105">
              <component :is="pillar.icon" class="h-6 w-6" />
            </div>
            <span class="text-[11px] font-semibold tracking-wide px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200/60 dark:border-slate-700/60">
              {{ pillar.badge }}
            </span>
          </div>

          <!-- Content -->
          <div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white leading-snug">
              {{ pillar.title }}
            </h3>
            <p class="mt-2 text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
              {{ pillar.desc }}
            </p>
          </div>
        </div>

        <!-- Subtle Institutional Guarantee Footer -->
        <div class="pt-4 mt-4 border-t border-slate-100 dark:border-slate-800/80 flex items-center gap-2 text-xs font-medium text-[#0B5D3B] dark:text-[#75bd97]">
          <CheckCircle2 class="h-3.5 w-3.5" />
          <span>{{ t('home.trust.tag') }}</span>
        </div>
      </div>
    </div>
  </section>
</template>
