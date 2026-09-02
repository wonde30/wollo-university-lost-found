<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { useUiStore } from '@/stores/ui.store'
import { getErrorMessage } from '@/utils/error-handler'
import { t } from '@/i18n'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'

const authStore = useAuthStore()
const uiStore = useUiStore()

const form = reactive({
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
})

const errors = ref<Record<string, string>>({})
const generalError = ref<string | null>(null)
const loading = ref(false)

function clearError(field: string) {
  if (errors.value[field]) {
    const { [field]: _, ...rest } = errors.value
    errors.value = rest
  }
}

function validate(): boolean {
  errors.value = {}
  if (!form.current_password) {
    errors.value.current_password = t('validation.required')
  }
  if (!form.new_password) {
    errors.value.new_password = t('validation.required')
  } else if (form.new_password.length < 8) {
    errors.value.new_password = t('validation.minLength', { min: 8 })
  } else if (!/(?=.*[a-z])/.test(form.new_password)) {
    errors.value.new_password = t('validation.passwordComplexity')
  } else if (!/(?=.*[A-Z])/.test(form.new_password)) {
    errors.value.new_password = t('validation.passwordComplexity')
  } else if (!/(?=.*\d)/.test(form.new_password)) {
    errors.value.new_password = t('validation.passwordComplexity')
  } else if (!/(?=.*[@$!%*?&])/.test(form.new_password)) {
    errors.value.new_password = t('validation.passwordComplexity')
  }
  if (!form.new_password_confirmation) {
    errors.value.new_password_confirmation = t('validation.required')
  } else if (form.new_password !== form.new_password_confirmation) {
    errors.value.new_password_confirmation = t('validation.passwordMatch')
  }
  return Object.keys(errors.value).length === 0
}

async function handleSubmit(): Promise<void> {
  if (!validate()) return
  generalError.value = null
  loading.value = true
  try {
    await authStore.changePassword(form.current_password, form.new_password, form.new_password_confirmation)
    uiStore.success(t('profile.passwordUpdated'))
    form.current_password = ''
    form.new_password = ''
    form.new_password_confirmation = ''
  } catch (err) {
    generalError.value = getErrorMessage(err, t('common.errorOccurred'))
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <form class="space-y-5" @submit.prevent="handleSubmit">
    <div v-if="generalError" class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-xs text-rose-700 dark:text-rose-400">
      {{ generalError }}
    </div>

    <AppInput
      id="pw-current"
      :label="t('profile.currentPassword')"
      type="password"
      placeholder="••••••••"
      :model-value="form.current_password"
      :error="errors.current_password"
      required
      @update:model-value="form.current_password = $event; clearError('current_password')"
    />

    <AppInput
      id="pw-new"
      :label="t('auth.resetPassword.newPassword')"
      type="password"
      :placeholder="t('auth.placeholders.newPassword')"
      :model-value="form.new_password"
      :error="errors.new_password"
      :hint="t('validation.passwordComplexity')"
      required
      @update:model-value="form.new_password = $event; clearError('new_password'); clearError('new_password_confirmation')"
    />

    <AppInput
      id="pw-confirm"
      :label="t('auth.resetPassword.confirmNewPassword')"
      type="password"
      :placeholder="t('auth.placeholders.repeatNewPassword')"
      :model-value="form.new_password_confirmation"
      :error="errors.new_password_confirmation"
      required
      @update:model-value="form.new_password_confirmation = $event; clearError('new_password_confirmation')"
    />

    <div class="flex justify-end pt-3 border-t border-slate-100 dark:border-slate-800">
      <AppButton variant="primary" type="submit" :loading="loading">
        {{ t('common.save') }}
      </AppButton>
    </div>
  </form>
</template>
