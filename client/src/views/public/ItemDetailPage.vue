<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { useItems } from '@/features/items/composables/useItems'
import { useUiStore } from '@/stores/ui.store'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import ItemPhotoGallery from '@/features/items/components/ItemPhotoGallery.vue'
import ItemStatusBadge from '@/features/items/components/ItemStatusBadge.vue'
import ItemTimeline from '@/features/items/components/ItemTimeline.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppSkeleton from '@/components/ui/AppSkeleton.vue'
import AppModal from '@/components/ui/AppModal.vue'
import AppTextarea from '@/components/ui/AppTextarea.vue'
import { formatDate } from '@/utils/date'
import { currentLocale, t } from '@/i18n'
import {
  Sparkles,
  MapPin,
  Calendar,
  RotateCcw,
  Handshake,
  Tag,
  PackageSearch,
  ArrowLeft,
} from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const uiStore = useUiStore()
const { currentItem, loading, loadItem, updateStatus } = useItems()

const itemId = Number(route.params.id)

const reopenModalOpen = ref(false)
const reopenReason = ref('')
const reopening = ref(false)

onMounted(async () => {
  await loadItem(itemId)
})

onUnmounted(() => {
  if (currentItem.value?.id === itemId) {
    currentItem.value = null
  }
})

function handleClaim() {
  router.push(`/student/claim/${itemId}`)
}

async function submitReopen() {
  if (!reopenReason.value.trim()) {
    uiStore.error(t('validation.required'))
    return
  }
  if (!currentItem.value) return

  reopening.value = true
  const newStatus = currentItem.value.type === 'lost' ? 'lost' : 'found_unclaimed'
  
  try {
    await updateStatus(itemId, { status: newStatus, reason: reopenReason.value.trim() })
    uiStore.success(t('items.updatedSuccess'))
    reopenModalOpen.value = false
    reopenReason.value = ''
  } catch (err) {
    uiStore.error(t('common.errorOccurred'))
  } finally {
    reopening.value = false
  }
}
</script>

<template>
  <DefaultLayout>
    <div class="max-w-5xl mx-auto space-y-6">
      <!-- Loading Skeleton -->
      <div v-if="loading" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <AppSkeleton height="24rem" class="rounded-2xl" />
        <div class="space-y-4">
          <AppSkeleton height="2rem" width="60%" />
          <AppSkeleton height="1rem" width="40%" />
          <AppSkeleton height="5rem" />
          <AppSkeleton height="1rem" width="30%" />
        </div>
      </div>

      <div v-else-if="currentItem" class="grid grid-cols-1 lg:grid-cols-2 gap-8 sm:gap-10">
        <!-- Photo Gallery -->
        <div>
          <ItemPhotoGallery :photos="currentItem.photos" />
        </div>

        <!-- Details -->
        <div class="space-y-4 sm:space-y-5">
          <!-- Header -->
          <div class="space-y-1.5">
            <div class="flex items-center gap-2 flex-wrap mb-1">
              <span
                :class="[
                  'px-2.5 py-0.5 rounded-full text-xs font-extrabold uppercase tracking-wider',
                  currentItem.type === 'found' ? 'bg-[#0B5D3B] text-white' : 'bg-amber-600 text-white',
                ]"
              >
                {{ currentItem.type === 'found' ? t('items.types.found') : t('items.types.lost') }}
              </span>
              <ItemStatusBadge :status="currentItem.status" />
              <span v-if="currentItem.is_high_value" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-amber-100 dark:bg-amber-950/70 text-amber-900 dark:text-amber-300 border border-amber-300 dark:border-amber-700">
                <Sparkles class="h-3.5 w-3.5 text-amber-600 dark:text-amber-400" />
                {{ t('reportWizard.highValue') }}
              </span>
            </div>

            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight text-slate-900 dark:text-white leading-tight">{{ currentItem.title }}</h1>
            <p class="text-xs font-mono font-bold text-slate-400 dark:text-slate-500">#{{ currentItem.reference_code }}</p>
          </div>

          <!-- Meta -->
          <div class="grid grid-cols-2 gap-2.5 sm:gap-3 text-xs">
            <div class="rounded-xl bg-slate-50 dark:bg-slate-800/60 p-3 border border-slate-200/90 dark:border-slate-700/60">
              <span class="text-slate-400 dark:text-slate-400 block mb-0.5 font-bold text-[10px] uppercase tracking-wider">{{ t('items.myItems.category') }}</span>
              <span class="font-bold text-slate-900 dark:text-slate-100 flex items-center gap-1">
                <Tag class="h-3.5 w-3.5 text-[#0B5D3B] dark:text-[#75bd97]" />
                {{ (currentLocale === 'am' && currentItem.category?.display_name_am) ? currentItem.category.display_name_am : (currentItem.category?.display_name || currentItem.category?.name || 'General') }}
              </span>
            </div>
            <div class="rounded-xl bg-slate-50 dark:bg-slate-800/60 p-3 border border-slate-200/90 dark:border-slate-700/60">
              <span class="text-slate-400 dark:text-slate-400 block mb-0.5 font-bold text-[10px] uppercase tracking-wider">{{ currentItem.type === 'found' ? t('reportWizard.dateFound') : t('matchSuggestions.dateLost') }}</span>
              <span class="font-bold text-slate-900 dark:text-slate-100 flex items-center gap-1">
                <Calendar class="h-3.5 w-3.5 text-[#0B5D3B] dark:text-[#75bd97]" />
                {{ formatDate(currentItem.incident_date, 'medium') }}
              </span>
            </div>
            <div v-if="currentItem.location || currentItem.campus" class="rounded-xl bg-slate-50 dark:bg-slate-800/60 p-3 border border-slate-200/90 dark:border-slate-700/60 col-span-2">
              <span class="text-slate-400 dark:text-slate-400 block mb-0.5 font-bold text-[10px] uppercase tracking-wider">{{ t('nav.locations') }}</span>
              <span class="font-bold text-slate-900 dark:text-slate-100 flex items-center gap-1">
                <MapPin class="h-3.5 w-3.5 text-[#0B5D3B] dark:text-[#75bd97]" />
                {{ (currentLocale === 'am' && (currentItem.location?.display_name_am || currentItem.campus?.display_name_am)) ? (currentItem.location?.display_name_am || currentItem.campus?.display_name_am) : (currentItem.location?.name || currentItem.campus?.name || 'Dessie Main Campus') }}
              </span>
            </div>
          </div>

          <!-- Description -->
          <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">{{ t('items.form.description') }}</h3>
            <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-200 leading-relaxed whitespace-pre-line bg-white dark:bg-[#111827] p-3.5 sm:p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 font-medium">{{ currentItem.description }}</p>
          </div>

          <!-- CTA -->
          <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-col gap-3">
            <div v-if="currentItem.status === 'found_unclaimed'">
              <div v-if="authStore.isAuthenticated && authStore.can('SUBMIT_CLAIM')">
                <AppButton variant="primary" size="md" block @click="handleClaim">
                  <template #icon-left>
                    <Handshake class="h-4 w-4 mr-1" />
                  </template>
                  {{ t('claims.submitClaim') }}
                </AppButton>
              </div>
              <div v-else-if="!authStore.isAuthenticated">
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-2 text-center">{{ t('auth.login.subtitle') }}</p>
                <AppButton variant="primary" size="md" block @click="router.push('/auth/login')">
                  {{ t('auth.login.signIn') }}
                </AppButton>
              </div>
            </div>

            <!-- Admin Reopen (FR-26) -->
            <div v-if="authStore.isAdmin && ['withdrawn', 'closed', 'expired'].includes(currentItem.status)">
              <AppButton variant="outline" size="md" block class="border-amber-500 text-amber-700 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/30" @click="reopenModalOpen = true">
                <template #icon-left>
                  <RotateCcw class="h-4 w-4 mr-1" />
                </template>
                {{ t('claims.actions.reevaluate') }}
              </AppButton>
            </div>
          </div>
        </div>
      </div>

      <!-- Not Found / Empty State -->
      <div v-else class="py-16 text-center">
        <div class="max-w-md mx-auto space-y-3">
          <div class="h-16 w-16 mx-auto rounded-3xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 dark:text-slate-500">
            <PackageSearch class="h-8 w-8" />
          </div>
          <h2 class="text-xl font-black text-slate-900 dark:text-white">Item Not Found</h2>
          <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
            The requested item may have been archived, returned to its owner, or removed from public custody.
          </p>
          <div class="pt-3">
            <AppButton variant="primary" size="md" @click="router.push('/browse')">
              <template #icon-left>
                <ArrowLeft class="h-4 w-4 mr-1.5" />
              </template>
              <span>Browse All Items</span>
            </AppButton>
          </div>
        </div>
      </div>

      <!-- Timeline -->
      <div v-if="currentItem && currentItem.status_histories?.length" class="mt-12 pt-10 border-t border-slate-200 dark:border-slate-800">
        <ItemTimeline :histories="currentItem.status_histories" />
      </div>

      <!-- Reopen Modal -->
      <AppModal
        :open="reopenModalOpen"
        :title="t('claims.actions.reevaluate')"
        @close="reopenModalOpen = false"
      >
        <div class="space-y-4 text-xs">
          <p class="text-slate-600 dark:text-slate-400">
            {{ t('admin.dashboard.subtitle') }}
          </p>
          <AppTextarea
            :label="t('admin.auditLogs.details') + ' *'"
            :placeholder="t('admin.auditLogs.details')"
            :rows="3"
            :model-value="reopenReason"
            required
            @update:model-value="reopenReason = $event"
          />
        </div>
        <template #footer>
          <div class="flex items-center justify-between w-full">
            <AppButton variant="outline" size="sm" @click="reopenModalOpen = false">
              {{ t('common.cancel') }}
            </AppButton>
            <AppButton variant="primary" size="sm" :loading="reopening" @click="submitReopen">
              {{ t('common.confirm') }}
            </AppButton>
          </div>
        </template>
      </AppModal>
    </div>
  </DefaultLayout>
</template>
