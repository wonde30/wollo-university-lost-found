<script setup lang="ts">
import {
  FileText,
  Search,
  Bell,
  CheckCircle2,
  ArrowRight,
} from 'lucide-vue-next'

const steps = [
  {
    step: 1,
    title: 'Report',
    description: 'Submit details about your lost or found item with photos.',
    icon: FileText,
    badgeBg: 'bg-[#0B5D3B] text-white',
    iconColor: 'text-[#0B5D3B] dark:text-[#75bd97]',
  },
  {
    step: 2,
    title: 'Search & Match',
    description: 'Our system helps match items using smart search and AI.',
    icon: Search,
    badgeBg: 'bg-[#B7791F] text-white',
    iconColor: 'text-[#B7791F] dark:text-[#D4AF37]',
  },
  {
    step: 3,
    title: 'Get Notified',
    description: 'Receive updates when a potential match is found.',
    icon: Bell,
    badgeBg: 'bg-emerald-700 text-white',
    iconColor: 'text-emerald-700 dark:text-emerald-400',
  },
  {
    step: 4,
    title: 'Reunite',
    description: 'Complete the verification process and get your item back.',
    icon: CheckCircle2,
    badgeBg: 'bg-[#084C30] text-[#D4AF37]',
    iconColor: 'text-[#0B5D3B] dark:text-[#75bd97]',
  },
]
</script>

<template>
  <section id="how-it-works" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-20 sm:mb-24">
    <!-- Section Header -->
    <div class="text-center max-w-3xl mx-auto mb-14 space-y-2.5">
      <span class="text-xs font-black uppercase tracking-wider text-[#0B5D3B] dark:text-[#75bd97]">
        HOW IT WORKS
      </span>
      <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight text-slate-900 dark:text-white">
        Simple Steps, Real Results
      </h2>
      <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
        Reuniting items with their owners is easy.
      </p>
    </div>

    <!-- 4 Steps Horizontal Chain -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 relative">
      <div
        v-for="(s, idx) in steps"
        :key="s.step"
        class="relative flex flex-col items-center text-center group"
      >
        <!-- Connector Arrow (desktop only) -->
        <div
          v-if="idx < steps.length - 1"
          class="hidden lg:block absolute top-7 -right-4 translate-x-1/2 text-slate-300 dark:text-slate-700 z-0 pointer-events-none"
        >
          <ArrowRight class="h-5 w-5" />
        </div>

        <!-- Step Icon with Number Badge -->
        <div class="relative mb-5">
          <div class="h-16 w-16 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 shadow-md flex items-center justify-center group-hover:scale-105 transition-transform duration-200">
            <component :is="s.icon" :class="['h-7 w-7', s.iconColor]" />
          </div>

          <!-- Step Number Pill -->
          <div
            :class="[
              'absolute -top-2 -left-2 h-6 w-6 rounded-full flex items-center justify-center text-xs font-black shadow-sm',
              s.badgeBg,
            ]"
          >
            {{ s.step }}
          </div>
        </div>

        <!-- Step Text -->
        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">
          {{ s.title }}
        </h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed max-w-xs">
          {{ s.description }}
        </p>
      </div>
    </div>
  </section>
</template>
