<script setup lang="ts">
import { ref } from 'vue'
import { useSettingsStore } from '@/stores/settings.store'
import { ChevronDown } from 'lucide-vue-next'

const settingsStore = useSettingsStore()

const openItems = ref<Record<number, boolean>>({
  0: true, // First item open by default
})

function toggleFaq(index: number) {
  openItems.value[index] = !openItems.value[index]
}

const faqCol1 = [
  {
    id: 0,
    q: 'How do I report a lost item?',
    a: `Click "Report Lost Item", sign in with your ${settingsStore.institutionName} student or staff account, and submit details including the category, incident location (e.g. Block, Hall, or Lab), and descriptive photos. Our system will immediately check active custody inventories for matching items.`,
  },
  {
    id: 1,
    q: 'How do I claim a found item listed on the platform?',
    a: 'Browse the found items catalog, click "Claim This Item", and provide proof of ownership (such as serial numbers, unique markings, purchase receipts, or unlock codes). Campus property custody officers review claims within 24 business hours.',
  },
  {
    id: 2,
    q: 'Where are property custody offices located on campus?',
    a: `Found items are kept securely in custody vaults at the Dessie Main Campus Administration & Security Division and the Kombolcha Institute of Technology (KIoT) Main Gate Security Office.`,
  },
]

const faqCol2 = [
  {
    id: 3,
    q: 'How long are found items kept in custody?',
    a: 'Found items are held in verified custody vaults for up to 90 calendar days. Owners of matching items receive periodic notifications before standard university property disposition committee reviews.',
  },
  {
    id: 4,
    q: 'Can campus visitors or guests report items without an account?',
    a: 'Yes. Visitors can search public listings and track items using reference codes without logging in. Submitting a new lost or found report requires basic registration to maintain verification security.',
  },
  {
    id: 5,
    q: 'Is my personal and contact information kept confidential?',
    a: `Yes. Your contact information is never published publicly. Only authorized ${settingsStore.institutionName} security and custody officers have access to verified claim details during the handover process.`,
  },
]
</script>

<template>
  <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-20 sm:mb-24" aria-labelledby="faq-heading">
    <!-- Header -->
    <div class="text-left mb-10 space-y-2">
      <span class="text-xs font-black uppercase tracking-wider text-[#0B5D3B] dark:text-[#75bd97]">
        FAQ
      </span>
      <h2 id="faq-heading" class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
        Frequently Asked Questions
      </h2>
      <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
        Answers to common questions regarding lost property custody and recovery at {{ settingsStore.institutionName }}.
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
