<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useUiStore } from '@/stores/ui.store'
import { getErrorMessage } from '@/utils/error-handler'
import * as adminApi from '@/features/admin/api/admin.api'
import type { SystemSetting } from '@/features/admin/types/admin.types'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'

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
    uiStore.success('System settings saved successfully.')
  } catch (err) {
    uiStore.error(getErrorMessage(err, 'Failed to save settings.'))
  } finally {
    saving.value = false
  }
}

onMounted(fetchSettings)
</script>

<template>
  <div class="space-y-5">
    <div v-if="loading" class="p-8 text-center text-xs text-slate-400">
      Loading settings...
    </div>

    <div v-else class="space-y-4">
      <div
        v-for="setting in settings"
        :key="setting.id"
        class="flex items-start gap-4 p-4 rounded-xl border border-slate-200 bg-white"
      >
        <div class="flex-1 min-w-0">
          <label :for="`setting-${setting.key}`" class="text-xs font-semibold text-slate-700 block mb-0.5">
            {{ setting.key }}
          </label>
          <p v-if="setting.description" class="text-[11px] text-slate-400 mb-2">{{ setting.description }}</p>
          <AppInput
            :id="`setting-${setting.key}`"
            :model-value="String(setting.value)"
            @update:model-value="setting.value = $event"
          />
        </div>
      </div>
    </div>

    <div class="flex justify-end pt-3 border-t border-slate-100">
      <AppButton variant="primary" :loading="saving" @click="handleSave">
        Save All Settings
      </AppButton>
    </div>
  </div>
</template>
