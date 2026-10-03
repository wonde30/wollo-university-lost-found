<script setup lang="ts">
import { ref, computed } from 'vue'
import { useAuthStore } from '../stores/auth.store'
import { getErrorMessage, getValidationErrors } from '@/utils/error-handler'
import { useUiStore } from '@/stores/ui.store'
import { t } from '@/i18n'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'

const authStore = useAuthStore()
const uiStore = useUiStore()

const isOpen = computed(() => {
  return authStore.isAuthenticated && Boolean(authStore.user?.must_change_password)
})

const currentPassword = ref('')
const newPassword = ref('')
const newPasswordConfirmation = ref('')

const errors = ref<Record<string, string>>({})
const generalError = ref<string | null>(null)
const loading = ref(false)

async function handleSubmit(): Promise<void> {
  generalError.value = null
  errors.value = {}

  if (!currentPassword.value) {
    errors.value.current_password = t('validation.required')
  }
  if (!newPassword.value) {
    errors.value.new_password = t('validation.required')
  } else if (newPassword.value.length < 8) {
    errors.value.new_password = t('validation.minLength', { min: 8 })
  }
  if (newPassword.value !== newPasswordConfirmation.value) {
    errors.value.new_password_confirmation = t('validation.passwordMatch')
  }

  if (Object.keys(errors.value).length > 0) return

  loading.value = true
  try {
    await authStore.changePassword(
      currentPassword.value,
      newPassword.value,
      newPasswordConfirmation.value
    )

    if (authStore.user) {
      authStore.user.must_change_password = false
    }

    uiStore.success(t('auth.passwordChangedSuccess'))
    currentPassword.value = ''
    newPassword.value = ''
    newPasswordConfirmation.value = ''
  } catch (err) {
    const fieldErrors = getValidationErrors(err)
    if (fieldErrors) {
      errors.value = Object.entries(fieldErrors).reduce((acc, [k, v]) => {
        acc[k] = v[0] || ''
        return acc
      }, {} as Record<string, string>)
    } else {
      generalError.value = getErrorMessage(err, t('common.errorOccurred'))
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm"
  >
    <div class="w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 p-6 space-y-5 animate-in fade-in zoom-in-95 duration-200">
      <div class="flex items-center space-x-3 text-amber-600 dark:text-amber-400 border-b border-slate-100 dark:border-slate-800 pb-4">
        <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-950/60 flex items-center justify-center text-lg font-bold">
          🛡️
        </div>
        <div>
          <h2 class="text-sm font-bold text-slate-900 dark:text-white">
            {{ t('auth.mustChangePasswordTitle') }}
          </h2>
          <p class="text-[11px] text-slate-500 dark:text-slate-400">
            {{ t('auth.accountActivationCheck') }}
          </p>
        </div>
      </div>

      <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
        {{ t('auth.mustChangePasswordSubtitle') }}
      </p>

      <div v-if="generalError" class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-xs text-rose-700 dark:text-rose-400">
        {{ generalError }}
      </div>

      <form class="space-y-3.5" @submit.prevent="handleSubmit">
        <AppInput
          id="forced-current-pass"
          :label="t('auth.tempPassword')"
          type="password"
          :placeholder="t('auth.tempPasswordPlaceholder')"
          :model-value="currentPassword"
          :error="errors.current_password"
          required
          @update:model-value="currentPassword = $event"
        />

        <AppInput
          id="forced-new-pass"
          :label="t('auth.newPassword')"
          type="password"
          :placeholder="t('auth.newPasswordPlaceholder')"
          :model-value="newPassword"
          :error="errors.new_password"
          required
          @update:model-value="newPassword = $event"
        />

        <AppInput
          id="forced-confirm-pass"
          :label="t('auth.passwordConfirm')"
          type="password"
          :placeholder="t('auth.repeatPasswordPlaceholder')"
          :model-value="newPasswordConfirmation"
          :error="errors.new_password_confirmation"
          required
          @update:model-value="newPasswordConfirmation = $event"
        />

        <AppButton
          type="submit"
          variant="primary"
          size="md"
          block
          :loading="loading"
        >
          {{ t('auth.resetPasswordBtn') }}
        </AppButton>
      </form>
    </div>
  </div>
</template>
