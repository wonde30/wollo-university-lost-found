<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth.store'
import { validateForgotPasswordForm } from '../validation/auth.validation'
import { getErrorMessage } from '@/utils/error-handler'
import { useUiStore } from '@/stores/ui.store'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'

const router = useRouter()
const authStore = useAuthStore()
const uiStore = useUiStore()

const form = reactive({ email: '' })
const errors = ref<Record<string, string>>({})
const generalError = ref<string | null>(null)
const loading = ref(false)

async function handleSubmit(): Promise<void> {
  generalError.value = null
  errors.value = validateForgotPasswordForm(form)
  if (Object.keys(errors.value).length > 0) return

  loading.value = true
  try {
    await authStore.forgotPassword(form.email)
    uiStore.success('Password reset OTP sent to your email.')
    router.push({ path: '/auth/reset-password', query: { email: form.email } })
  } catch (err) {
    generalError.value = getErrorMessage(err, 'Failed to request password reset.')
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

    <p class="text-xs text-slate-500">
      Enter your university email address and we'll send you an OTP code to reset your password.
    </p>

    <AppInput
      id="forgot-email"
      label="University Email"
      type="email"
      placeholder="student@wu.edu.et"
      :model-value="form.email"
      :error="errors.email"
      required
      @update:model-value="form.email = $event"
    />

    <AppButton
      type="submit"
      variant="primary"
      size="md"
      block
      :loading="loading"
    >
      Send Reset OTP
    </AppButton>

    <div class="text-center pt-2">
      <RouterLink to="/auth/login" class="text-xs font-semibold text-[#0F5132] hover:underline">
        &larr; Back to Login
      </RouterLink>
    </div>
  </form>
</template>
