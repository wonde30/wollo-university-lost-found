<script setup lang="ts">
import { ref, computed } from 'vue'
import { useSettingsStore } from '@/stores/settings.store'
import { t } from '@/i18n'
import { ChevronDown } from 'lucide-vue-next'

const settingsStore = useSettingsStore()

const openItems = ref<Record<number, boolean>>({
  0: true, // First item open by default
})

function toggleFaq(index: number) {
  openItems.value[index] = !openItems.value[index]
}

const faqCol1 = computed(() => [
  {
    id: 0,
    q: t('home.faq.q1'),
    a: t('home.faq.a1', { institution: settingsStore.institutionName }),
  },
  {
    id: 1,
    q: t('home.faq.q2'),
    a: t('home.faq.a2', { institution: settingsStore.institutionName }),
  },
  {
    id: 2,
    q: t('home.faq.q3'),
    a: t('home.faq.a3', { institution: settingsStore.institutionName }),
  },
])

const faqCol2 = computed(() => [
  {
    id: 3,
    q: t('home.faq.q4'),
    a: t('home.faq.a4', { institution: settingsStore.institutionName }),
  },
  {
    id: 4,
    q: t('home.faq.q5'),
    a: t('home.faq.a5', { institution: settingsStore.institutionName }),
  },
  {
    id: 5,
    q: t('home.faq.q6'),
    a: t('home.faq.a6', { institution: settingsStore.institutionName }),
  },
])
</script>

<template>
  <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-20 sm:mb-24" aria-labelledby="faq-heading">
    <!-- Header -->
    <div class="text-left mb-10 space-y-2">
      <span class="text-xs font-black uppercase tracking-wider text-[#0B5D3B] dark:text-[#75bd97]">
        {{ t('home.faq.tag') }}
      </span>
      <h2 id="faq-heading" class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
        {{ t('home.faq.title') }}
      </h2>
      <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
        {{ t('home.faq.subtitle', { institution: settingsStore.institutionName }) }}
      </p>
    </div>

    <!-- 2 Column Accordions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      <!-- Column 1 -->
      <div class="space-y-3">
        <div
          v-for="item in faqCol1"
          :key="item.id"
          :class="[
            'rounded-2xl border bg-white dark:bg-[#111827] shadow-2xs overflow-hidden transition-all duration-200',
            openItems[item.id]
              ? 'border-[#0B5D3B]/40 dark:border-[#75bd97]/40 ring-1 ring-[#0B5D3B]/20'
              : 'border-slate-200/80 dark:border-slate-800'
          ]"
        >
          <button
            :id="'faq-btn-' + item.id"
            type="button"
            class="w-full p-4 sm:p-5 flex items-center justify-between text-left text-xs sm:text-sm font-bold text-slate-900 dark:text-white hover:text-[#0B5D3B] dark:hover:text-[#75bd97] transition-colors cursor-pointer select-none focus:outline-hidden focus-visible:ring-2 focus-visible:ring-[#0B5D3B]/40"
            :aria-expanded="!!openItems[item.id]"
            :aria-controls="'faq-content-' + item.id"
            @click="toggleFaq(item.id)"
          >
            <span>{{ item.q }}</span>
            <ChevronDown
              :class="[
                'h-4 w-4 shrink-0 transition-transform duration-200',
                openItems[item.id] ? 'rotate-180 text-[#0B5D3B] dark:text-[#75bd97]' : 'text-slate-400',
              ]"
            />
          </button>

          <div
            v-show="openItems[item.id]"
            :id="'faq-content-' + item.id"
            role="region"
            :aria-labelledby="'faq-btn-' + item.id"
            class="px-4 pb-4 sm:px-5 sm:pb-5 pt-0 text-xs text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-50 dark:border-slate-800/60 mt-1"
          >
            {{ item.a }}
          </div>
        </div>
      </div>

      <!-- Column 2 -->
      <div class="space-y-3">
        <div
          v-for="item in faqCol2"
          :key="item.id"
          :class="[
            'rounded-2xl border bg-white dark:bg-[#111827] shadow-2xs overflow-hidden transition-all duration-200',
            openItems[item.id]
              ? 'border-[#0B5D3B]/40 dark:border-[#75bd97]/40 ring-1 ring-[#0B5D3B]/20'
              : 'border-slate-200/80 dark:border-slate-800'
          ]"
        >
          <button
            :id="'faq-btn-' + item.id"
            type="button"
            class="w-full p-4 sm:p-5 flex items-center justify-between text-left text-xs sm:text-sm font-bold text-slate-900 dark:text-white hover:text-[#0B5D3B] dark:hover:text-[#75bd97] transition-colors cursor-pointer select-none focus:outline-hidden focus-visible:ring-2 focus-visible:ring-[#0B5D3B]/40"
            :aria-expanded="!!openItems[item.id]"
            :aria-controls="'faq-content-' + item.id"
            @click="toggleFaq(item.id)"
          >
            <span>{{ item.q }}</span>
            <ChevronDown
              :class="[
                'h-4 w-4 shrink-0 transition-transform duration-200',
                openItems[item.id] ? 'rotate-180 text-[#0B5D3B] dark:text-[#75bd97]' : 'text-slate-400',
              ]"
            />
          </button>

          <div
            v-show="openItems[item.id]"
            :id="'faq-content-' + item.id"
            role="region"
            :aria-labelledby="'faq-btn-' + item.id"
            class="px-4 pb-4 sm:px-5 sm:pb-5 pt-0 text-xs text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-50 dark:border-slate-800/60 mt-1"
          >
            {{ item.a }}
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
