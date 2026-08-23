<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth.store'
import { validateResetPasswordForm } from '../validation/auth.validation'
import { getErrorMessage } from '@/utils/error-handler'
import { useUiStore } from '@/stores/ui.store'
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
    uiStore.success('Password reset successfully! Please sign in with your new password.')
    router.push('/auth/login')
  } catch (err) {
    generalError.value = getErrorMessage(err, 'Failed to reset password. Please check your OTP.')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <form class="space-y-4" @submit.prevent="handleSubmit">
    <div v-if="generalError" class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700">
      {{ generalError }}
    </div>

    <AppInput
      id="reset-email"
      label="University Email"
      type="email"
      placeholder="student@wu.edu.et"
      :model-value="form.email"
      :error="errors.email"
      required
      @update:model-value="form.email = $event"
    />

    <AppInput
      id="reset-otp"
      label="6-Digit OTP Code"
      placeholder="e.g. 123456"
      :model-value="form.otp"
      :error="errors.otp"
      required
      @update:model-value="form.otp = $event"
    />

    <AppInput
      id="reset-password"
      label="New Password"
      type="password"
      placeholder="Min. 8 characters"
      :model-value="form.password"
      :error="errors.password"
      required
      @update:model-value="form.password = $event"
    />

    <AppInput
      id="reset-password-confirm"
      label="Confirm New Password"
      type="password"
      placeholder="Repeat new password"
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
      Reset Password
    </AppButton>
  </form>
</template>
