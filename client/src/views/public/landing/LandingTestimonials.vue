<script setup lang="ts">
import { useSettingsStore } from '@/stores/settings.store'
import { Star } from 'lucide-vue-next'

const settingsStore = useSettingsStore()

const testimonials = [
  {
    id: 1,
    name: 'Aster Bekele',
    role: 'Software Engineering Student, KIoT Campus',
    avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=120&auto=format&fit=crop&q=80',
    quote: `I recovered my lost smartphone through this platform! The custody team at Kombolcha gate verified my ownership within hours. Thank you ${settingsStore.institutionName}!`,
    rating: 5,
  },
  {
    id: 2,
    name: 'Daniel Tesfaye',
    role: 'Instructor, College of Informatics, Dessie Campus',
    avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=120&auto=format&fit=crop&q=80',
    quote: 'A transparent and dependable system for our academic community. It fosters integrity, accountability, and student trust across all campuses.',
    rating: 5,
  },
  {
    id: 3,
    name: 'Mehari Worku',
    role: 'Civil Engineering Student, Dessie Campus',
    avatar: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=120&auto=format&fit=crop&q=80',
    quote: 'I returned a found laptop bag with textbooks using this portal. The handover verification was smooth and safe. Proud of our university initiative!',
    rating: 5,
  },
]
</script>

<template>
  <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-20 sm:mb-24" aria-labelledby="testimonials-heading">
    <!-- Header -->
    <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-14 space-y-2.5">
      <span class="text-xs font-black uppercase tracking-wider text-[#0B5D3B] dark:text-[#75bd97]">
        TESTIMONIALS
      </span>
      <h2 id="testimonials-heading" class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight text-slate-900 dark:text-white">
        What Our Community Says
      </h2>
      <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
        Real experiences from {{ settingsStore.institutionName }} students, faculty, and campus staff.
      </p>
    </div>

    <!-- 3 Testimonial Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div
        v-for="t in testimonials"
        :key="t.id"
        class="bg-white dark:bg-[#111827] rounded-2xl p-6 sm:p-7 border border-slate-200/80 dark:border-slate-800 shadow-2xs hover:shadow-md hover:border-[#0B5D3B]/30 dark:hover:border-[#75bd97]/30 transition-all duration-200 flex flex-col justify-between space-y-4"
      >
        <!-- User Bio -->
        <div class="flex items-center gap-3.5">
          <img
            :src="t.avatar"
            :alt="t.name"
            class="h-12 w-12 rounded-full object-cover shadow-sm ring-1 ring-slate-200 dark:ring-slate-700"
            loading="lazy"
          />
          <div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white leading-snug">
              {{ t.name }}
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              {{ t.role }}
            </p>
          </div>
        </div>

        <!-- Quote -->
        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed italic">
          "{{ t.quote }}"
        </p>

        <!-- Star Rating -->
        <div class="flex items-center gap-1 text-[#D4AF37] pt-1" aria-label="5 out of 5 stars">
          <Star
            v-for="star in t.rating"
            :key="star"
            class="h-4 w-4 fill-[#D4AF37]"
          />
        </div>
      </div>
    </div>
  </section>
</template>
