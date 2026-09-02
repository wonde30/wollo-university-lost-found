<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { useNotificationsStore } from '../stores/notifications.store'
import { useUiStore } from '@/stores/ui.store'
import { getErrorMessage } from '@/utils/error-handler'
import { t } from '@/i18n'
import AppButton from '@/components/ui/AppButton.vue'
import AppCheckbox from '@/components/ui/AppCheckbox.vue'
import AppSkeleton from '@/components/ui/AppSkeleton.vue'

const notificationsStore = useNotificationsStore()
const uiStore = useUiStore()

const loading = ref(true)
const saving = ref(false)

const form = reactive({
  email_on_report_submitted: true,
  email_on_match_found: true,
  email_on_claim_received: true,
  email_on_claim_decided: true,
  email_on_item_returned: true,
  email_on_expiry_warning: true,
  email_on_item_expired: false,
  email_on_system_announcements: true,
})

onMounted(async () => {
  try {
    await notificationsStore.fetchPreferences()
    if (notificationsStore.preferences) {
      Object.assign(form, notificationsStore.preferences)
    }
  } catch (err) {
    uiStore.error(getErrorMessage(err, t('common.errorOccurred')))
  } finally {
    loading.value = false
  }
})

async function handleSave(): Promise<void> {
  saving.value = true
  try {
    await notificationsStore.updatePreferences({
      email_on_report_submitted: form.email_on_report_submitted,
      email_on_match_found: form.email_on_match_found,
      email_on_claim_received: form.email_on_claim_received,
      email_on_claim_decided: form.email_on_claim_decided,
      email_on_item_returned: form.email_on_item_returned,
      email_on_expiry_warning: form.email_on_expiry_warning,
      email_on_item_expired: form.email_on_item_expired,
      email_on_system_announcements: form.email_on_system_announcements,
    })
    uiStore.success(t('notifications.preferencesSaved'))
  } catch (err) {
    uiStore.error(getErrorMessage(err, t('common.errorOccurred')))
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="space-y-6">
    <div>
      <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">
        {{ t('notifications.preferencesTitle') }}
      </h3>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
        {{ t('notifications.preferencesDesc') }}
      </p>
    </div>

    <!-- Skeleton Loading -->
    <div v-if="loading" class="space-y-4">
      <AppSkeleton height="2.5rem" class="rounded-xl" />
      <AppSkeleton height="2.5rem" class="rounded-xl" />
      <AppSkeleton height="2.5rem" class="rounded-xl" />
    </div>

    <!-- Preferences Checkbox Grid -->
    <div v-else class="space-y-3">
      <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 transition-colors">
        <AppCheckbox
          :label="t('notifications.emailReportSubmitted')"
          :model-value="form.email_on_report_submitted"
          @update:model-value="form.email_on_report_submitted = $event"
        />
      </div>

      <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 transition-colors">
        <AppCheckbox
          :label="t('notifications.emailMatchFound')"
          :model-value="form.email_on_match_found"
          @update:model-value="form.email_on_match_found = $event"
        />
      </div>

      <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 transition-colors">
        <AppCheckbox
          :label="t('notifications.emailClaimReceived')"
          :model-value="form.email_on_claim_received"
          @update:model-value="form.email_on_claim_received = $event"
        />
      </div>

      <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 transition-colors">
        <AppCheckbox
          :label="t('notifications.emailClaimDecided')"
          :model-value="form.email_on_claim_decided"
          @update:model-value="form.email_on_claim_decided = $event"
        />
      </div>

      <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 transition-colors">
        <AppCheckbox
          :label="t('notifications.emailItemReturned')"
          :model-value="form.email_on_item_returned"
          @update:model-value="form.email_on_item_returned = $event"
        />
      </div>

      <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 transition-colors">
        <AppCheckbox
          :label="t('notifications.emailExpiryWarning')"
          :model-value="form.email_on_expiry_warning"
          @update:model-value="form.email_on_expiry_warning = $event"
        />
      </div>

      <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 transition-colors">
        <AppCheckbox
          :label="t('notifications.emailItemExpired')"
          :model-value="form.email_on_item_expired"
          @update:model-value="form.email_on_item_expired = $event"
        />
      </div>

      <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 transition-colors">
        <AppCheckbox
          :label="t('notifications.emailSystemAnnouncements')"
          :model-value="form.email_on_system_announcements"
          @update:model-value="form.email_on_system_announcements = $event"
        />
      </div>

      <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
        <AppButton variant="primary" size="md" :loading="saving" @click="handleSave">
          {{ t('common.save') }}
        </AppButton>
      </div>
    </div>
  </div>
</template>
