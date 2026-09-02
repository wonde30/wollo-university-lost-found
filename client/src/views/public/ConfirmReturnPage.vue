<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import BlankLayout from '@/layouts/BlankLayout.vue'
import * as returnsApi from '@/features/returns/api/returns.api'
import type { ReturnRecord } from '@/features/returns/types/return.types'
import { formatDate } from '@/utils/date'
import { t } from '@/i18n'
import AppButton from '@/components/ui/AppButton.vue'
import AppBadge from '@/components/ui/AppBadge.vue'
import AppSkeleton from '@/components/ui/AppSkeleton.vue'
import { CheckCircle2, AlertTriangle, Handshake } from 'lucide-vue-next'

const route = useRoute()
const token = route.params.token as string

const record = ref<ReturnRecord | null>(null)
const loading = ref(true)
const confirming = ref(false)
const confirmed = ref(false)
const error = ref<string | null>(null)

async function loadDetails() {
  if (!token) {
    error.value = t('common.errorOccurred')
    loading.value = false
    return
  }

  loading.value = true
  error.value = null
  try {
    const data = await returnsApi.getReturnByToken(token)
    record.value = data
    if (data.recipient_confirmed) {
      confirmed.value = true
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || t('common.errorOccurred')
  } finally {
    loading.value = false
  }
}

async function handleConfirm() {
  confirming.value = true
  try {
    const data = await returnsApi.confirmReturnByToken(token)
    record.value = data
    confirmed.value = true
  } catch (err: any) {
    error.value = err.response?.data?.message || t('common.errorOccurred')
  } finally {
    confirming.value = false
  }
}

onMounted(() => {
  loadDetails()
})
</script>

<template>
  <BlankLayout>
    <div class="min-h-screen bg-slate-50 dark:bg-[#0F172A] flex flex-col justify-center py-12 sm:px-6 lg:px-8 transition-colors duration-150">
      <div class="sm:mx-auto sm:w-full sm:max-w-lg">
        <!-- University Logo / Header -->
        <div class="text-center space-y-2 mb-5">
          <img
            src="/images/wu-logo.png"
            alt="Wollo University Emblem"
            class="h-11 w-11 object-contain rounded-full bg-white dark:bg-slate-800 shadow-2xs ring-1 ring-[#0B5D3B]/40 p-0.5 mx-auto"
          />
          <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
            {{ t('common.appName') }}
          </h1>
          <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[#E8F4EE] dark:bg-[#153C2D] text-[#0B5D3B] dark:text-[#75bd97] border border-[#0B5D3B]/20">
            <Handshake class="h-3.5 w-3.5" />
            {{ t('notifications.returnConfirmTitle') }}
          </div>
        </div>

        <div class="bg-white dark:bg-[#111827] py-6 px-5 sm:py-7 sm:px-8 shadow-xl sm:rounded-2xl border border-slate-200/90 dark:border-slate-800 space-y-5 transition-colors duration-150">
          <!-- Loading State -->
          <div v-if="loading" class="space-y-4">
            <AppSkeleton height="2rem" class="rounded-xl" />
            <AppSkeleton height="5rem" class="rounded-2xl" />
            <AppSkeleton height="3rem" class="rounded-xl" />
          </div>

          <!-- Error / Expired Link State -->
          <div v-else-if="error" class="text-center space-y-4 py-4">
            <div class="h-16 w-16 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 rounded-full flex items-center justify-center mx-auto border border-rose-100 dark:border-rose-800">
              <AlertTriangle class="h-8 w-8" />
            </div>
            <div class="space-y-1">
              <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ t('notifications.title') }}</h2>
              <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">{{ error }}</p>
            </div>
            <div class="pt-2">
              <RouterLink to="/browse">
                <AppButton variant="outline" size="sm">
                  {{ t('errors.404.backHome') }}
                </AppButton>
              </RouterLink>
            </div>
          </div>

          <!-- Success State -->
          <div v-else-if="confirmed" class="text-center space-y-5 py-2">
            <div class="h-16 w-16 bg-emerald-50 dark:bg-emerald-950/40 text-[#0B5D3B] dark:text-[#75bd97] rounded-full flex items-center justify-center mx-auto border border-emerald-100 dark:border-emerald-800">
              <CheckCircle2 class="h-8 w-8" />
            </div>
            <div class="space-y-1">
              <h2 class="text-lg font-black text-slate-900 dark:text-white">{{ t('returns.process.confirmed') }}</h2>
              <p class="text-xs text-slate-600 dark:text-slate-400">
                {{ t('home.stats.safeHandover') }}
              </p>
            </div>

            <div v-if="record" class="bg-slate-50 dark:bg-slate-800/60 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 text-left text-xs space-y-2">
              <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-400">{{ t('items.myItems.item') }}:</span>
                <strong class="text-slate-800 dark:text-slate-200">{{ record.item?.title }}</strong>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-400">{{ t('common.referenceCode') }}:</span>
                <code class="font-mono text-[#0B5D3B] dark:text-[#75bd97] font-bold">{{ record.item?.reference_code }}</code>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-400">{{ t('returns.process.returnDate') }}:</span>
                <span class="text-slate-800 dark:text-slate-200">{{ formatDate(record.confirmed_at || new Date().toISOString()) }}</span>
              </div>
            </div>

            <RouterLink to="/browse">
              <AppButton variant="primary" size="md" class="w-full">
                {{ t('nav.browse') }}
              </AppButton>
            </RouterLink>
          </div>

          <!-- Pending Confirmation Card -->
          <div v-else-if="record" class="space-y-5">
            <div class="space-y-1">
              <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ t('notifications.returnConfirmTitle') }}</h2>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                {{ t('home.stats.safeHandover') }}
              </p>
            </div>

            <div class="bg-slate-50 dark:bg-slate-800/60 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-700 space-y-3 text-xs">
              <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-slate-700">
                <div>
                  <span class="text-slate-400 dark:text-slate-400 font-medium block text-[11px]">{{ t('returns.process.itemTitle') }}</span>
                  <strong class="text-slate-900 dark:text-white text-sm">{{ record.item?.title }}</strong>
                </div>
                <AppBadge variant="success" size="sm">{{ t('returns.process.pendingReceipt') }}</AppBadge>
              </div>

              <div class="grid grid-cols-2 gap-2 text-slate-600 dark:text-slate-300">
                <div>
                  <span class="text-slate-400 dark:text-slate-400 block text-[10px] uppercase font-bold">{{ t('common.referenceCode') }}</span>
                  <code class="font-mono text-[#0B5D3B] dark:text-[#75bd97] font-bold">{{ record.item?.reference_code }}</code>
                </div>
                <div>
                  <span class="text-slate-400 dark:text-slate-400 block text-[10px] uppercase font-bold">{{ t('returns.process.returnDate') }}</span>
                  <span class="font-medium text-slate-800 dark:text-slate-200">{{ formatDate(record.return_date) }}</span>
                </div>
                <div>
                  <span class="text-slate-400 dark:text-slate-400 block text-[10px] uppercase font-bold">{{ t('returns.process.condition') }}</span>
                  <span class="font-medium capitalize text-slate-800 dark:text-slate-200">{{ record.condition_on_return || 'Good' }}</span>
                </div>
                <div>
                  <span class="text-slate-400 dark:text-slate-400 block text-[10px] uppercase font-bold">{{ t('returns.process.handedBy') }}</span>
                  <span class="font-medium text-slate-800 dark:text-slate-200">{{ record.staff?.full_name || t('admin.roles.staff') }}</span>
                </div>
              </div>

              <div v-if="record.notes" class="pt-2 border-t border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 italic text-[11px]">
                Note: "{{ record.notes }}"
              </div>
            </div>

            <div class="p-3 bg-amber-50 dark:bg-amber-950/40 rounded-xl border border-amber-200 dark:border-amber-800 text-[11px] text-amber-900 dark:text-amber-300">
              <strong>{{ t('common.confirm') }}:</strong> {{ t('home.stats.safeHandover') }}
            </div>

            <AppButton
              variant="primary"
              size="lg"
              class="w-full"
              :loading="confirming"
              @click="handleConfirm"
            >
              {{ t('returns.process.confirmed') }}
            </AppButton>
          </div>
        </div>
      </div>
    </div>
  </BlankLayout>
</template>
