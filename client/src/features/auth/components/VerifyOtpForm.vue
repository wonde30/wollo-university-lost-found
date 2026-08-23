<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth.store'
import { getErrorMessage } from '@/utils/error-handler'
import { useUiStore } from '@/stores/ui.store'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const uiStore = useUiStore()

const form = reactive({
  email: (route.query.email as string) || authStore.user?.email || '',
  code: '',
})

const generalError = ref<string | null>(null)
const loading = ref(false)
const resending = ref(false)

async function handleSubmit(): Promise<void> {
  if (!form.email || !form.code) {
    generalError.value = 'Please enter both email and OTP code.'
    return
  }

  generalError.value = null
  loading.value = true
  try {
    await authStore.verifyEmail(form.email, form.code)
    uiStore.success('Email verified successfully!')

    if (authStore.isAdmin) router.push('/admin/dashboard')
    else if (authStore.isStaff) router.push('/staff/dashboard')
    else router.push('/student/dashboard')
  } catch (err) {
    generalError.value = getErrorMessage(err, 'Invalid or expired OTP code.')
  } finally {
    loading.value = false
  }
}

async function handleResend(): Promise<void> {
  if (!form.email) {
    generalError.value = 'Please specify your email.'
    return
  }
  resending.value = true
  try {
    await authStore.resendVerification(form.email)
    uiStore.success('New OTP verification code sent to your email.')
  } catch (err) {
    generalError.value = getErrorMessage(err, 'Failed to resend OTP.')
  } finally {
    resending.value = false
  }
}
</script>

<template>
  <form class="space-y-4" @submit.prevent="handleSubmit">
    <div v-if="generalError" class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700">
      {{ generalError }}
    </div>

    <p class="text-xs text-slate-500">
      Please enter the 6-digit verification code sent to your university email address.
    </p>

    <AppInput
      id="verify-email"
      label="University Email"
      type="email"
      placeholder="student@wu.edu.et"
      :model-value="form.email"
      required
      @update:model-value="form.email = $event"
    />

    <AppInput
      id="verify-code"
      label="6-Digit OTP Code"
      placeholder="e.g. 123456"
      :model-value="form.code"
      required
      @update:model-value="form.code = $event"
    />

    <AppButton
      type="submit"
      variant="primary"
      size="md"
      block
      :loading="loading"
    >
      Verify Email Address
    </AppButton>

    <div class="flex items-center justify-between pt-2 text-xs">
      <button
        type="button"
        class="text-slate-500 hover:text-slate-700 underline"
        :disabled="resending"
        @click="handleResend"
      >
        {{ resending ? 'Resending...' : 'Resend Code' }}
      </button>

      <RouterLink to="/auth/login" class="font-semibold text-[#0F5132] hover:underline">
        Back to Login
      </RouterLink>
    </div>
  </form>
</template>
