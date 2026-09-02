<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useUiStore } from '@/stores/ui.store'
import { getErrorMessage } from '@/utils/error-handler'
import { t } from '@/i18n'
import * as adminApi from '@/features/admin/api/admin.api'
import type { SystemSetting } from '@/features/admin/types/admin.types'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'
import { Save, Settings, ShieldCheck } from 'lucide-vue-next'

const uiStore = useUiStore()
const settings = ref<SystemSetting[]>([])
const loading = ref(false)
const saving = ref(false)

async function fetchSettings(): Promise<void> {
  loading.value = true
  try {
    settings.value = await adminApi.getSystemSettings()
  } finally {
    loading.value = false
  }
}

async function handleSave(): Promise<void> {
  saving.value = true
  try {
    await Promise.all(
      settings.value.map(s =>
        adminApi.updateSystemSetting(s.key, { value: s.value ?? '' }),
      ),
    )
    uiStore.success(t('admin.settings.updatedSuccess'))
  } catch (err) {
    uiStore.error(getErrorMessage(err, t('common.errorOccurred')))
  } finally {
    saving.value = false
  }
}

function formatSettingLabel(key: string): string {
  return key
    .replace(/_/g, ' ')
    .replace(/\b\w/g, c => c.toUpperCase())
}

onMounted(fetchSettings)
</script>

<template>
  <div class="space-y-6">
    <div v-if="loading" class="space-y-4">
      <div
        v-for="n in 3"
        :key="n"
        class="p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 space-y-3 animate-pulse"
      >
        <div class="flex items-center justify-between">
          <div class="h-4 w-40 bg-slate-200 dark:bg-slate-700 rounded" />
          <div class="h-4 w-20 bg-slate-200 dark:bg-slate-700 rounded" />
        </div>
        <div class="h-3 w-64 bg-slate-100 dark:bg-slate-800 rounded" />
        <div class="h-9 w-full bg-slate-200 dark:bg-slate-700 rounded-xl" />
      </div>
    </div>

    <div v-else-if="settings.length === 0" class="p-8 text-center text-xs text-slate-400">
      {{ t('common.noData') }}
    </div>

    <div v-else class="space-y-4">
      <div
        v-for="setting in settings"
        :key="setting.id"
        class="p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors space-y-2"
      >
        <div class="flex items-center justify-between">
          <label :for="`setting-${setting.key}`" class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
            <Settings class="h-3.5 w-3.5 text-[#0B5D3B] dark:text-[#75bd97]" />
            {{ setting.display_name || formatSettingLabel(setting.key) }}
          </label>

          <span class="text-[10px] font-mono text-slate-400 bg-white dark:bg-[#111827] px-2 py-0.5 rounded border border-slate-200 dark:border-slate-700">
            {{ setting.key }}
          </span>
        </div>

        <p v-if="setting.description" class="text-[11px] text-slate-500 leading-relaxed">{{ setting.description }}</p>

        <AppInput
          :id="`setting-${setting.key}`"
          :model-value="String(setting.value)"
          class="text-xs"
          @update:model-value="setting.value = $event"
        />
      </div>
    </div>

    <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-800">
      <div class="flex items-center gap-1.5 text-xs text-slate-400">
        <ShieldCheck class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
        <span>{{ t('common.platformWideSettings') }}</span>
      </div>
      <AppButton variant="primary" size="md" :loading="saving" @click="handleSave">
        <template #icon-left>
          <Save class="h-4 w-4 mr-1" />
        </template>
        {{ t('common.save') }}
      </AppButton>
    </div>
  </div>
</template>
