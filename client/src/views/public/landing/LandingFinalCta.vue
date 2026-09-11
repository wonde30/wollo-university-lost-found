<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { useSettingsStore } from '@/stores/settings.store'
import AppButton from '@/components/ui/AppButton.vue'
import { FilePlus2, PackagePlus } from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const settingsStore = useSettingsStore()

function handleLostClick() {
  if (authStore.isAuthenticated) {
    router.push('/report-lost')
  } else {
    router.push({ name: 'login', query: { redirect: '/report-lost' } })
  }
}

function handleFoundClick() {
  if (authStore.isAuthenticated) {
    router.push('/report-found')
  } else {
    router.push({ name: 'login', query: { redirect: '/report-found' } })
  }
}
</script>

<template>
  <section class="relative overflow-hidden bg-[#0B5D3B] text-white py-16 sm:py-20 px-6 sm:px-12 text-center shadow-xl my-20 sm:my-24 w-full border-y border-[#0B5D3B]/40" aria-labelledby="cta-heading">
    <!-- Real Campus Gate Background with University Green Overlay -->
    <div class="absolute inset-0 z-0 pointer-events-none">
      <img
        src="/images/campus-gate.jpg"
        :alt="settingsStore.institutionName + ' Campus'"
        class="w-full h-full object-cover object-center filter brightness-40"
        loading="lazy"
      />
      <div class="absolute inset-0 bg-gradient-to-r from-[#0B5D3B]/95 via-[#084C30]/90 to-[#063D27]/95" />
      <div class="absolute inset-0 bg-radial-at-c from-transparent via-black/20 to-black/50" />
    </div>

    <div class="relative z-10 max-w-3xl mx-auto space-y-5">
      <h2 id="cta-heading" class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight text-white drop-shadow-sm">
        Lost or Found Something?
      </h2>

      <p class="text-xs sm:text-sm text-slate-200/90 max-w-lg mx-auto leading-relaxed">
        Help us reunite items with their rightful owners across {{ settingsStore.institutionName }}.
      </p>

      <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-3">
        <AppButton
          variant="gold"
          size="lg"
          class="w-full sm:w-auto font-bold border-0 shadow-lg shadow-black/30"
          @click="handleLostClick"
        >
          <template #icon-left>
            <FilePlus2 class="h-4 w-4 mr-1" />
          </template>
          Report Lost Item
        </AppButton>

        <AppButton
          variant="outline-white"
          size="lg"
          class="w-full sm:w-auto font-bold border-white/40 bg-white/10 hover:bg-white/20 text-white backdrop-blur-sm shadow-md"
          @click="handleFoundClick"
        >
          <template #icon-left>
            <PackagePlus class="h-4 w-4 mr-1" />
          </template>
          Report Found Item
        </AppButton>
      </div>
    </div>
  </section>
</template>
