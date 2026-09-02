<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth.store'
import { validateResetPasswordForm } from '../validation/auth.validation'
import { getErrorMessage } from '@/utils/error-handler'
import { useUiStore } from '@/stores/ui.store'
import { t } from '@/i18n'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const uiStore = useUiStore()

const form = reactive({
  email: (route.query.email as string) || '',
  otp: '',
  password: '',
  password_confirmation: '',
})

const errors = ref<Record<string, string>>({})
const generalError = ref<string | null>(null)
const loading = ref(false)

async function handleSubmit(): Promise<void> {
  generalError.value = null
  errors.value = validateResetPasswordForm(form)
  if (Object.keys(errors.value).length > 0) return

  loading.value = true
  try {
    await authStore.resetPassword(form.email, form.otp, form.password, form.password_confirmation)
    uiStore.success(t('auth.resetSuccessSignIn'))
    router.push('/auth/login')
  } catch (err) {
    generalError.value = getErrorMessage(err, t('common.errorOccurred'))
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <form class="space-y-4" @submit.prevent="handleSubmit">
    <div v-if="generalError" class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-xs text-rose-700 dark:text-rose-400">
      {{ generalError }}
    </div>

    <AppInput
      id="reset-email"
      :label="t('auth.forgotPassword.email')"
      type="email"
      placeholder="student@wu.edu.et"
      :model-value="form.email"
      :error="errors.email"
      required
      @update:model-value="form.email = $event"
    />

    <AppInput
      id="reset-otp"
      :label="t('auth.verifyOtp.code')"
      placeholder="e.g. 123456"
      :model-value="form.otp"
      :error="errors.otp"
      required
      @update:model-value="form.otp = $event"
    />

    <AppInput
      id="reset-password"
      :label="t('auth.resetPassword.newPassword')"
      type="password"
      :placeholder="t('validation.minLength', { min: 12 })"
      :model-value="form.password"
      :error="errors.password"
      required
      @update:model-value="form.password = $event"
    />

    <AppInput
      id="reset-password-confirm"
      :label="t('auth.resetPassword.confirmNewPassword')"
      type="password"
      :placeholder="t('auth.placeholders.repeatNewPassword')"
      :model-value="form.password_confirmation"
      :error="errors.password_confirmation"
      required
      @update:model-value="form.password_confirmation = $event"
    />

    <AppButton
      type="submit"
      variant="primary"
      size="md"
      block
      :loading="loading"
    >
      {{ t('auth.resetPassword.submit') }}
    </AppButton>
  </form>
</template>
