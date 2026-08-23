<script setup lang="ts">
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { useItems } from '@/features/items/composables/useItems'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import ItemPhotoGallery from '@/features/items/components/ItemPhotoGallery.vue'
import ItemStatusBadge from '@/features/items/components/ItemStatusBadge.vue'
import ItemTimeline from '@/features/items/components/ItemTimeline.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppSkeleton from '@/components/ui/AppSkeleton.vue'
import { formatDate } from '@/utils/date'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const { currentItem, loading, loadItem } = useItems()

const itemId = Number(route.params.id)

onMounted(async () => {
  await loadItem(itemId)
})

function handleClaim() {
  router.push(`/student/submit-claim?item_id=${itemId}`)
}
</script>

<template>
  <DefaultLayout>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <!-- Loading Skeleton -->
      <div v-if="loading" class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        <AppSkeleton height="24rem" class="rounded-2xl" />
        <div class="space-y-4">
          <AppSkeleton height="2rem" width="60%" />
          <AppSkeleton height="1rem" width="40%" />
          <AppSkeleton height="5rem" />
          <AppSkeleton height="1rem" width="30%" />
        </div>
      </div>

      <div v-else-if="currentItem" class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        <!-- Photo Gallery -->
        <div>
          <ItemPhotoGallery :photos="currentItem.photos" />
        </div>

        <!-- Details -->
        <div class="space-y-5">
          <!-- Header -->
          <div class="space-y-2">
            <div class="flex items-center gap-2 flex-wrap">
              <span
                :class="[
                  'px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider',
                  currentItem.type === 'found' ? 'bg-[#0F5132] text-white' : 'bg-amber-600 text-white',
                ]"
              >
                {{ currentItem.type }}
              </span>
              <ItemStatusBadge :status="currentItem.status" />
              <span v-if="currentItem.is_high_value" class="px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300">
                ★ High Value
              </span>
            </div>

            <h1 class="text-2xl font-black text-slate-900">{{ currentItem.title }}</h1>
            <p class="text-xs font-mono text-slate-400">Reference: #{{ currentItem.reference_code }}</p>
          </div>

          <!-- Meta -->
          <div class="grid grid-cols-2 gap-3 text-xs">
            <div class="rounded-xl bg-slate-50 p-3 border border-slate-200/80">
              <span class="text-slate-400 block mb-0.5">Category</span>
              <span class="font-semibold text-slate-800">{{ currentItem.category?.name || 'General' }}</span>
            </div>
            <div class="rounded-xl bg-slate-50 p-3 border border-slate-200/80">
              <span class="text-slate-400 block mb-0.5">{{ currentItem.type === 'found' ? 'Date Found' : 'Date Lost' }}</span>
              <span class="font-semibold text-slate-800">{{ formatDate(currentItem.incident_date, 'medium') }}</span>
            </div>
            <div v-if="currentItem.location" class="rounded-xl bg-slate-50 p-3 border border-slate-200/80 col-span-2">
              <span class="text-slate-400 block mb-0.5">Location</span>
              <span class="font-semibold text-slate-800">{{ currentItem.location?.name }}</span>
            </div>
          </div>

          <!-- Description -->
          <div>
            <h3 class="text-sm font-bold text-slate-700 mb-2">Description</h3>
            <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-line">{{ currentItem.description }}</p>
          </div>

          <!-- CTA -->
          <div v-if="currentItem.status === 'found_unclaimed'" class="pt-4 border-t border-slate-100">
            <div v-if="authStore.isAuthenticated && authStore.isStudent">
              <AppButton variant="primary" size="md" block @click="handleClaim">
                Submit Ownership Claim
              </AppButton>
            </div>
            <div v-else-if="!authStore.isAuthenticated">
              <p class="text-xs text-slate-500 mb-3 text-center">Sign in to claim this item</p>
              <AppButton variant="primary" size="md" block @click="router.push('/auth/login')">
                Sign in to Claim
              </AppButton>
            </div>
          </div>
        </div>
      </div>

      <!-- Timeline -->
      <div v-if="currentItem && currentItem.status_histories?.length" class="mt-10 pt-10 border-t border-slate-200">
        <ItemTimeline :histories="currentItem.status_histories" />
      </div>
    </div>
  </DefaultLayout>
</template>
